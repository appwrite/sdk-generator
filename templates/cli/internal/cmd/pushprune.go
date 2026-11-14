//go:build !browser

package cmd

import (
	"fmt"
	"net/url"
	"strconv"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/schema"
	"github.com/spf13/cobra"
)

// unknown marks a count that could not be read.
const unknown int64 = -1

// Backup coverage of a database or table about to be deleted.
const (
	backupCovered = "yes"
	backupNone    = "none"
	backupUnknown = "unknown"
)

// deletion is one resource that exists remotely and that the config would
// remove: a database, a table, or a column deleted or recreated.
type deletion struct {
	Resource   string
	DatabaseID string
	ID         string
	Name       string
	// Path is the DELETE route. Empty for a column, which its table's schema
	// plan deletes.
	Path   string
	Action string
	Reason string
	Tables int64
	Rows   int64
	Backup string
}

// schemaPlan is everything a database push would do.
type schemaPlan struct {
	databases schema.DatabasePlan
	children  []*childPlan
	deletions []deletion
}

// hasChanges reports whether anything other than a deletion would be applied.
func (p schemaPlan) hasChanges() bool {
	if !p.databases.Empty() {
		return true
	}
	for _, child := range p.children {
		if len(child.fields) > 0 || !child.members.WithoutDataLoss().Empty() || !child.indexes.Empty() {
			return true
		}
	}

	return false
}

func addPruneFlag(command *cobra.Command) {
	command.Flags().Bool("prune", false,
		"Delete databases, tables and columns missing from the config, and recreate columns whose type changed")
}

func pruneRequested(command *cobra.Command) (bool, error) {
	if command.Flags().Lookup("prune") == nil {
		return false, nil
	}

	return command.Flags().GetBool("prune")
}

// approveSchemaPlan prints the plan and asks for one approval of it.
//
// Without prune the deletions are listed and dropped. With prune they need
// their own confirmation at a terminal, or --reason without one.
func (c *pushContext) approveSchemaPlan(
	command *cobra.Command,
	resource pushDatabaseResource,
	plan *schemaPlan,
	prune bool,
) (bool, error) {
	out := command.OutOrStdout()
	destructive := prune && len(plan.deletions) > 0

	if destructive {
		c.measure(resource, plan.deletions)
	}

	plan.databases.Print(out)

	var fields []change
	for _, child := range plan.children {
		fields = append(fields, child.fields...)
	}
	if len(fields) > 0 {
		printChanges(command, fields)
	}

	for _, child := range plan.children {
		changes := append(child.members.WithoutDataLoss().All(), child.indexes.All()...)
		if len(changes) == 0 {
			continue
		}
		output.Log(out, "Pending changes in %s %s ( %s - %s ):", resource.Singular,
			child.local.GetString("name"), child.local.GetString("databaseId"),
			child.local.GetString("$id"))
		schema.PrintChanges(out, changes)
	}

	printDeletions(command, resource, plan.deletions, prune)

	if !prune {
		plan.deletions = nil
	}

	if destructive && !c.interactive && strings.TrimSpace(client.Reason) == "" {
		return false, fmt.Errorf(
			"--prune would delete the %d resources listed above and there is no interactive "+
				"terminal to confirm it. Pass --reason \"<why>\" to record why on the audit log",
			len(plan.deletions))
	}

	if plan.hasChanges() {
		approved, err := c.prompter.Confirm(prompt.Question{
			Message: "Would you like to apply these changes?",
			Default: false,
			Flag:    "--force",
		})
		if err != nil {
			return false, err
		}
		if !approved {
			output.Warn(out, "Skipping push action. Changes were not applied.")

			return false, nil
		}
	}

	if destructive && c.interactive {
		confirmed, err := c.prompter.Confirm(prompt.Question{
			Message: fmt.Sprintf(
				"Delete the %d resources listed above? This cannot be undone.", len(plan.deletions)),
			Default: false,
		})
		if err != nil {
			return false, err
		}
		if !confirmed {
			output.Warn(out, "Skipping deletions. Nothing was deleted.")
			plan.deletions = nil
			for _, child := range plan.children {
				child.members = child.members.WithoutDataLoss()
			}
		}
	}

	return true, nil
}

// printDeletions lists what the config would remove: with prune, as the
// deletions they are and what they hold; without, as left alone.
func printDeletions(
	command *cobra.Command,
	resource pushDatabaseResource,
	deletions []deletion,
	prune bool,
) {
	if len(deletions) == 0 {
		return
	}

	out := command.OutOrStdout()
	rows := make([][]string, 0, len(deletions))

	if !prune {
		for _, entry := range deletions {
			local := "(not in config)"
			if entry.Action == "recreating" {
				local = entry.Reason
			}
			rows = append(rows, []string{entry.ID, capitalize(entry.Resource), entry.Name, local})
		}

		output.Log(out, "Left as they are, because deleting or recreating them loses data:")
		fmt.Fprintf(out, "\n%s\n\n", output.RenderTable(
			[]string{"id", "resource", "name", "local"}, rows))
		output.Hint(out, "Run `%s pull %s` to bring %s up to date, or re-run with --prune to delete or recreate them.",
			app.ExecutableName, resource.Name, config.LocalFileName)

		return
	}

	var banners []string
	seen := map[string]bool{}
	for _, entry := range deletions {
		rows = append(rows, []string{
			entry.ID, entry.Action, capitalize(entry.Resource), entry.Name, entry.impact(),
		})

		banner := "WARNING: Database deletion will also delete all related tables"
		switch {
		case entry.Resource == "table":
			continue
		case entry.Action == "recreating":
			banner = "WARNING: " + capitalize(entry.Resource) + " recreation may cause loss of data"
		case entry.Resource != "database":
			banner = "WARNING: " + capitalize(entry.Resource) + " deletion may cause loss of data"
		}
		if !seen[banner] {
			seen[banner] = true
			banners = append(banners, banner)
		}
	}

	output.Warn(out, "--prune deletes these resources and the data in them:")
	fmt.Fprintf(out, "\n%s\n\n", output.RenderTable(
		[]string{"id", "action", "resource", "name", "impact"}, rows))

	for _, banner := range banners {
		schema.PrintBanner(out, banner)
	}
	for _, entry := range deletions {
		if entry.Rows <= 0 || entry.Backup == backupCovered {
			continue
		}
		coverage := "no backup policy covers it"
		if entry.Backup == backupUnknown {
			coverage = "its backup coverage is unknown"
		}
		schema.PrintBanner(out, fmt.Sprintf("WARNING: %s %s holds %d rows and %s",
			capitalize(entry.Resource), entry.ID, entry.Rows, coverage))
	}
}

// impact renders what deleting the entry loses.
func (d deletion) impact() string {
	switch d.Resource {
	case "database":
		return fmt.Sprintf("%s tables, %s rows, backup: %s", countOf(d.Tables), countOf(d.Rows), d.Backup)
	case "table":
		return fmt.Sprintf("%s rows, backup: %s", countOf(d.Rows), d.Backup)
	}
	if d.Action == "recreating" {
		return d.Reason
	}

	return "(not in config)"
}

// measure reads how many tables and rows each database or table deletion
// takes with it, and whether a backup policy covers it. A count that cannot be
// read stays unknown rather than failing the push.
func (c *pushContext) measure(resource pushDatabaseResource, deletions []deletion) {
	policies, err := c.page("/backups/policies", "policies")
	known := err == nil

	for index := range deletions {
		entry := &deletions[index]
		if entry.Path == "" {
			continue
		}

		switch entry.Resource {
		case "database":
			tables, err := c.page(entry.Path+resource.ChildPath, "tables")
			if err != nil {
				break
			}
			entry.Tables = int64(len(tables))
			entry.Rows = 0
			for _, table := range tables {
				count, err := c.count(
					entry.Path + resource.ChildPath + "/" + url.PathEscape(table.GetString("$id")) + "/rows")
				if err != nil {
					entry.Rows = unknown

					break
				}
				entry.Rows += count
			}
		case "table":
			if count, err := c.count(entry.Path + "/rows"); err == nil {
				entry.Rows = count
			}
		}

		entry.Backup = backupCoverage(policies, known, entry.DatabaseID)
	}
}

// count reads a list endpoint's total without reading its rows.
func (c *pushContext) count(path string) (int64, error) {
	var response jsonx.Object
	query := client.EncodeQueries([]string{`{"method":"limit","values":[1]}`})
	if err := c.api.Call("GET", path+"?"+query, nil, &response); err != nil {
		return unknown, err
	}

	return response.GetInt64("total"), nil
}

// backupCoverage reports whether an enabled policy backs up the database,
// either by naming it or by covering the whole project.
func backupCoverage(policies []*jsonx.Object, known bool, databaseID string) string {
	if !known {
		return backupUnknown
	}

	for _, policy := range policies {
		if enabled, ok := valueOf(policy, "enabled").(bool); ok && !enabled {
			continue
		}
		resourceID := policy.GetString("resourceId")
		if resourceID == "" || resourceID == databaseID {
			return backupCovered
		}
	}

	return backupNone
}

// applyDeletions deletes the approved tables and databases, after everything
// else has been pushed. A failure is reported and the rest continue.
func (c *pushContext) applyDeletions(command *cobra.Command, deletions []deletion) error {
	out := command.OutOrStdout()
	failed := 0

	for _, entry := range deletions {
		if entry.Path == "" {
			continue
		}

		output.Log(out, "Deleting %s %s ( %s ) ...", entry.Resource, entry.Name, entry.ID)
		if err := c.api.Call("DELETE", entry.Path, nil, nil); err != nil {
			output.Failure(out, "Failed to delete %s %s ( %s ): %s", entry.Resource, entry.Name, entry.ID, err)
			failed++

			continue
		}
		output.Success(out, "Deleted %s ( %s )", entry.Name, entry.ID)
	}

	if failed > 0 {
		return fmt.Errorf("%d deletions failed", failed)
	}

	return nil
}

func countOf(value int64) string {
	if value < 0 {
		return "unknown"
	}

	return strconv.FormatInt(value, 10)
}

func capitalize(text string) string {
	if text == "" {
		return text
	}

	return strings.ToUpper(text[:1]) + text[1:]
}

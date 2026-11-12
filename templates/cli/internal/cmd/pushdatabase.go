//go:build !browser

package cmd

import (
	"fmt"
	"net/url"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/schema"
	"github.com/spf13/cobra"
)

// `push table` and `push collection` do the same four things in the same order,
// and the order is the design: databases first, so a table has somewhere to
// live; then the tables; then the columns, which is internal/schema's job; then
// the indexes, last, because an index names columns.
//
// `push collection` is the deprecated half of the pair and creates a missing
// database inline instead.

// pushDatabaseResource parameterises the two commands over their route and key
// names. The same distinction pull draws (see pulldatabase.go): tablesdb/tables/
// columns against databases/collections/attributes.
type pushDatabaseResource struct {
	resourceIdentity
	// DatabaseConfigKey is the config array the containers are read from.
	DatabaseConfigKey string
	// ChildPath lists a database's children.
	ChildPath string
	// ChildKeys limits the approval diff to the fields the config owns.
	ChildKeys []string
	// MembersKey is `columns` for a table and `attributes` for a collection --
	// both the config array and the field an index references them by.
	MembersKey string
}

// Named rather than a slice indexed by position: nothing iterates the pair, so
// a table of two was three files agreeing on which row came first.
var (
	pushTable = pushDatabaseResource{
		resourceIdentity:  tableIdentity,
		DatabaseConfigKey: "tablesDB",
		ChildPath:         "/tables",
		ChildKeys:         config.TableKeys,
		MembersKey:        "columns",
	}

	pushCollection = pushDatabaseResource{
		resourceIdentity:  collectionIdentity,
		DatabaseConfigKey: "databases",
		ChildPath:         "/collections",
		ChildKeys:         config.CollectionKeys,
		MembersKey:        "attributes",
	}
)

func newPushTableCommand() *cobra.Command {
	resource := pushTable
	command := &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short:   "Push tables in the current project.",
		Args:    everyResourceArgument(),
		RunE: func(command *cobra.Command, arguments []string) error {
			return runPushTable(command, resource)
		},
	}
	addAttemptsFlag(command)
	addPruneFlag(command)

	return command
}

func newPushCollectionCommand() *cobra.Command {
	resource := pushCollection
	command := &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short: "Push collections in the current project. " +
			"(deprecated, please use 'push tables' instead)",
		Args: everyResourceArgument(),
		RunE: func(command *cobra.Command, arguments []string) error {
			return runPushCollection(command, resource)
		},
	}
	addAttemptsFlag(command)
	addPruneFlag(command)

	return command
}

// addAttemptsFlag registers --attempts.
//
// It caps how long the CLI waits for asynchronous schema changes. The default
// lives in internal/schema, which also scales it for a large collection.
func addAttemptsFlag(command *cobra.Command) {
	command.Flags().IntP("attempts", "a", 0,
		"Max number of attempts before timing out. default: 30.")
}

// runPushTable pushes the tables named in the config.
func runPushTable(command *cobra.Command, resource pushDatabaseResource) error {
	context, err := newPushContext()
	if err != nil {
		return err
	}

	return context.pushTables(command, resource)
}

// pushTables plans the whole push before writing anything: selection, the
// database diff, the table and column diffs and what --prune would delete. One
// approval covers the plan, and deletions run last.
func (c *pushContext) pushTables(command *cobra.Command, resource pushDatabaseResource) error {
	out := command.OutOrStdout()

	prune, err := pruneRequested(command)
	if err != nil {
		return err
	}

	// Looked up rather than read directly: `push all` fans out to this
	// function through its own command, which does not declare --attempts, and
	// GetInt on an undeclared flag is an error rather than a zero.
	attempts := 0
	if flag := command.Flags().Lookup("attempts"); flag != nil {
		parsed, err := command.Flags().GetInt("attempts")
		if err != nil {
			return err
		}
		attempts = parsed
	}

	tables, err := c.selectDatabaseResources(resource)
	if err != nil {
		return err
	}

	databases, err := schema.PlanTablesDBs(c.api, out, c.local)
	if err != nil {
		return err
	}

	removals, err := c.planRemovedTables(command, resource, databases.Unmanaged)
	if err != nil {
		return err
	}

	children, losses, err := c.planDatabaseChildren(command, resource, tables, prune)
	if err != nil {
		return err
	}

	plan := schemaPlan{
		databases: databases,
		children:  children,
		deletions: append(removals, losses...),
	}

	proceed, err := c.approveSchemaPlan(command, resource, &plan, prune)
	if err != nil || !proceed {
		return err
	}

	if err := schema.ApplyTablesDBs(c.api, out, plan.databases); err != nil {
		return err
	}

	if len(tables) == 0 {
		output.Log(out, "No %s found.", resource.Label)
		output.Hint(out, "%s", resource.syncHint())
	} else {
		output.Log(out, "Pushing tables ...")

		pushed, err := c.pushDatabaseChildren(command, resource, plan.children, attempts)
		if err != nil {
			return err
		}

		// No Failed count: pushDatabaseChildren returns an error rather than
		// tallying failures, so the "no %s were pushed" failure branch is not
		// reachable from here.
		pushTally{Pushed: pushed}.report(out, resource.Label)
	}

	return c.applyDeletions(command, plan.deletions)
}

// runPushCollection pushes the collections named in the config.
func runPushCollection(command *cobra.Command, resource pushDatabaseResource) error {
	out := command.OutOrStdout()

	output.Warn(out, "%s push collection has been deprecated. Please consider using "+
		"'%s push tables' instead", app.ExecutableName, app.ExecutableName)

	context, err := newPushContext()
	if err != nil {
		return err
	}

	prune, err := pruneRequested(command)
	if err != nil {
		return err
	}

	collections, err := context.selectDatabaseResources(resource)
	if err != nil {
		return err
	}
	if len(collections) == 0 {
		output.Log(out, "No %s found.", resource.Label)
		output.Hint(out, "%s", resource.syncHint())

		return nil
	}

	children, losses, err := context.planDatabaseChildren(command, resource, collections, prune)
	if err != nil {
		return err
	}

	plan := schemaPlan{children: children, deletions: losses}

	proceed, err := context.approveSchemaPlan(command, resource, &plan, prune)
	if err != nil || !proceed {
		return err
	}

	output.Log(out, "Pushing collections ...")

	if err := context.pushCollectionDatabases(command, resource, collections); err != nil {
		return err
	}

	// --attempts is declared on `push collection` and IGNORED: the flag is
	// registered but the child pass builds its pool with the default. Passing
	// it through would change behaviour on a live project, so it is left as it
	// is until that is a deliberate change.
	pushed, err := context.pushDatabaseChildren(command, resource, plan.children, 0)
	if err != nil {
		return err
	}

	pushTally{Pushed: pushed}.report(out, resource.Label)

	return nil
}

// childPlan is one table or collection's share of the plan.
type childPlan struct {
	local *jsonx.Object
	// remote is nil when the table or collection does not exist yet.
	remote  *jsonx.Object
	fields  []change
	members schema.Plan
	indexes schema.Plan
}

// planDatabaseChildren reads each selected table or collection and diffs it
// against the config. Without prune, column deletions and recreations are
// dropped from the plan and returned as deletions to report.
func (c *pushContext) planDatabaseChildren(
	command *cobra.Command,
	resource pushDatabaseResource,
	entries []*jsonx.Object,
	prune bool,
) ([]*childPlan, []deletion, error) {
	out := command.OutOrStdout()
	warn := func(format string, arguments ...any) { output.Warn(out, format, arguments...) }

	plans := make([]*childPlan, 0, len(entries))
	var losses []deletion

	for _, entry := range entries {
		current := &childPlan{local: entry}
		plans = append(plans, current)

		remote, err := c.getChild(resource, entry)
		if isNotFound(err) {
			continue
		}
		if err != nil {
			return nil, nil, err
		}
		current.remote = remote
		current.fields = fieldChanges(entry, remote, resource.ChildKeys,
			[]string{resource.MembersKey, "indexes"})

		container := schema.Container{
			DatabaseID: entry.GetString("databaseId"),
			ID:         entry.GetString("$id"),
			Name:       entry.GetString("name"),
		}

		members := schema.BuildPlan(remote.GetObjects(resource.MembersKey),
			entry.GetObjects(resource.MembersKey), container, false, warn)
		for _, loss := range members.DataLoss() {
			losses = append(losses, deletion{
				Resource: strings.TrimSuffix(resource.MembersKey, "s"),
				ID:       loss.Attribute.GetString("key"),
				Name:     fmt.Sprintf("%s ( %s - %s )", container.Name, container.DatabaseID, container.ID),
				Action:   loss.Action,
				Reason:   loss.Reason,
				Rows:     unknown,
				Tables:   unknown,
			})
		}
		if !prune {
			members = members.WithoutDataLoss()
		}
		current.members = members

		// The server rewrites an index's member references when a column is
		// renamed. The remote snapshot was read BEFORE the rename, so the
		// same rewrite is mirrored onto it -- otherwise the index pass sees
		// the old name, calls it a difference, and recreates a live index.
		applyRenamesToIndexes(remote, resource.MembersKey, members.Renames)

		current.indexes = schema.BuildPlan(remote.GetObjects("indexes"),
			entry.GetObjects("indexes"), container, true, warn)
	}

	return plans, losses, nil
}

// planRemovedTables lists tables that exist remotely, in a database the config
// lists, but not in the config, followed by the unmanaged databases.
func (c *pushContext) planRemovedTables(
	command *cobra.Command,
	resource pushDatabaseResource,
	unmanaged []*jsonx.Object,
) ([]deletion, error) {
	out := command.OutOrStdout()
	output.Log(out, "Checking for tables not in the config ...")

	localTables := c.local.ResourceEntries(resource.ConfigKey)

	var removals []deletion

	for _, database := range c.local.ResourceEntries(resource.DatabaseConfigKey) {
		databaseID := database.GetString("$id")

		remoteTables, err := c.page(
			resource.Path+"/"+url.PathEscape(databaseID)+resource.ChildPath, "tables")
		if err != nil {
			// Swallowed deliberately: a database the config lists may not
			// have been created yet.
			continue
		}

		for _, remote := range remoteTables {
			id := remote.GetString("$id")
			found := false
			for _, local := range localTables {
				if local.GetString("$id") == id &&
					local.GetString("databaseId") == databaseID {
					found = true

					break
				}
			}
			if found {
				continue
			}

			removals = append(removals, deletion{
				Resource:   "table",
				DatabaseID: databaseID,
				ID:         id,
				Name:       fmt.Sprintf("%s ( %s - %s )", remote.GetString("name"), databaseID, id),
				Path: resource.Path + "/" + url.PathEscape(databaseID) +
					resource.ChildPath + "/" + url.PathEscape(id),
				Action: "deleting",
				Tables: unknown,
				Rows:   unknown,
			})
		}
	}

	for _, database := range unmanaged {
		id := database.GetString("$id")
		removals = append(removals, deletion{
			Resource:   "database",
			DatabaseID: id,
			ID:         id,
			Name:       database.GetString("name"),
			Path:       schema.TablesDBPath + "/" + url.PathEscape(id),
			Action:     "deleting",
			Tables:     unknown,
			Rows:       unknown,
		})
	}

	return removals, nil
}

// pushCollectionDatabases creates or renames the databases the collections need.
//
// `push collection` never deletes a database, only creates one that is
// missing.
func (c *pushContext) pushCollectionDatabases(
	command *cobra.Command,
	resource pushDatabaseResource,
	collections []*jsonx.Object,
) error {
	out := command.OutOrStdout()

	seen := map[string]bool{}
	for _, collection := range collections {
		databaseID := collection.GetString("databaseId")
		if databaseID == "" || seen[databaseID] {
			continue
		}
		seen[databaseID] = true

		// The database's name comes from the config's own databases array,
		// falling back to the id.
		name := databaseID
		if entry := c.localDatabase(resource, databaseID); entry != nil {
			if value := entry.GetString("name"); value != "" {
				name = value
			}
		}

		path := resource.Path + "/" + url.PathEscape(databaseID)

		var remote jsonx.Object
		err := c.api.Call("GET", path, nil, &remote)
		switch {
		case err == nil:
			if remote.GetString("name") == name {
				continue
			}

			body := jsonx.NewObject()
			body.Set("name", name)
			if err := c.api.Call("PUT", path, body, nil); err != nil {
				return err
			}
			output.Success(out, "Updated %s ( %s ) name", name, databaseID)
		case isNotFound(err):
			output.Log(out, "Database %s not found. Creating it now ...", databaseID)

			body := jsonx.NewObject()
			body.Set("databaseId", databaseID)
			body.Set("name", name)
			if err := c.api.Call("POST", resource.Path, body, nil); err != nil {
				return err
			}
		default:
			return err
		}
	}

	return nil
}

// localDatabase finds a database entry in the config.
func (c *pushContext) localDatabase(
	resource pushDatabaseResource,
	databaseID string,
) *jsonx.Object {
	for _, entry := range c.local.ResourceEntries(resource.DatabaseConfigKey) {
		if entry.GetString("$id") == databaseID {
			return entry
		}
	}

	return nil
}

// pushDatabaseChildren creates or updates the tables and collections, then
// applies their approved schema plans. A newly created collection carries its
// attributes and indexes in the create body and is complete; a new table does
// not, and has its columns pushed afterwards.
func (c *pushContext) pushDatabaseChildren(
	command *cobra.Command,
	resource pushDatabaseResource,
	plans []*childPlan,
	attempts int,
) (int, error) {
	out := command.OutOrStdout()

	poller := schema.NewPoller(c.api, out, attempts)
	reconciler := &schema.Reconciler{
		API:    c.api,
		Out:    out,
		Poller: poller,
	}

	newlyCreated := make(map[*childPlan]bool, len(plans))
	changed := map[string]bool{}

	for _, current := range plans {
		entry := current.local

		if current.remote != nil {
			updated, err := c.updateChild(command, resource, entry, current.remote)
			if err != nil {
				return 0, err
			}
			if updated {
				changed[identityOf(entry)] = true
			}

			continue
		}

		output.Log(out, "%s %s does not exist in the project. Creating ... ",
			strings.ToUpper(resource.Singular[:1])+resource.Singular[1:],
			entry.GetString("name"))

		carriedSchema, err := c.createChild(command, resource, entry)
		if err != nil {
			return 0, err
		}
		// A collection created with its attributes and indexes needs no
		// schema pass; a table does, because createTable takes neither.
		newlyCreated[current] = carriedSchema
		if !carriedSchema {
			changed[identityOf(entry)] = true
		}
	}

	pushed := 0

	// Serial from here. Two schema changes against the same collection race,
	// and the poll after each phase is what makes the next one legal.
	for _, current := range plans {
		entry := current.local
		id := entry.GetString("$id")
		container := schema.Container{
			DatabaseID: entry.GetString("databaseId"),
			ID:         id,
			Name:       entry.GetString("name"),
		}

		if newlyCreated[current] {
			pushed++
			output.Success(out, "Successfully pushed %s ( %s )", entry.GetString("name"), id)

			continue
		}

		members := entry.GetObjects(resource.MembersKey)
		indexes := entry.GetObjects("indexes")

		if current.remote != nil {
			membersResult, err := reconciler.Apply(current.members)
			if err != nil {
				return pushed, err
			}

			indexesResult, err := reconciler.Apply(current.indexes)
			if err != nil {
				return pushed, err
			}

			members = membersResult.Attributes
			indexes = indexesResult.Attributes
			hadChanges := membersResult.HasChanges || indexesResult.HasChanges

			if !hadChanges && len(members) == 0 && len(indexes) == 0 {
				if !changed[identityOf(entry)] {
					output.Log(out, "No changes detected for %s %s. Skipping.",
						resource.Singular, entry.GetString("name"))
				}

				continue
			}
		} else if resource.Singular == "collection" {
			// pushCollections skips anything it neither found nor created.
			continue
		}

		output.Log(out, "Pushing %s %s ( %s - %s ) attributes",
			resource.Singular, entry.GetString("name"),
			entry.GetString("databaseId"), id)

		if err := reconciler.CreateAttributes(members, container, resource.MembersKey); err != nil {
			return pushed, err
		}
		if err := reconciler.CreateIndexes(indexes, container); err != nil {
			return pushed, err
		}

		changed[identityOf(entry)] = true
		pushed++
		output.Success(out, "Successfully pushed %s ( %s )", entry.GetString("name"), id)
	}

	// pushTables counts every table it TOUCHED, including one whose only change
	// was its name; pushCollections counts only the ones whose schema it pushed.
	if resource.Singular == "table" {
		return len(changed), nil
	}

	return pushed, nil
}

// getChild reads one table or collection.
func (c *pushContext) getChild(
	resource pushDatabaseResource,
	entry *jsonx.Object,
) (*jsonx.Object, error) {
	path := resource.Path + "/" + url.PathEscape(entry.GetString("databaseId")) +
		resource.ChildPath + "/" + url.PathEscape(entry.GetString("$id"))

	var remote jsonx.Object
	if err := c.api.Call("GET", path, nil, &remote); err != nil {
		return nil, err
	}

	return &remote, nil
}

// updateChild sends the table or collection update when a field differs, and
// reports whether it did.
func (c *pushContext) updateChild(
	command *cobra.Command,
	resource pushDatabaseResource,
	local, remote *jsonx.Object,
) (bool, error) {
	out := command.OutOrStdout()
	path := resource.Path + "/" + url.PathEscape(local.GetString("databaseId")) +
		resource.ChildPath + "/" + url.PathEscape(local.GetString("$id"))

	if resource.Singular == "collection" {
		// pushCollections compares the NAME only, and sends only the name.
		// Everything else on a collection -- permissions, documentSecurity --
		// is left to the deprecated command's callers.
		if remote.GetString("name") == local.GetString("name") {
			return false, nil
		}

		body := jsonx.NewObject()
		body.Set("name", local.GetString("name"))
		if err := c.api.Call("PUT", path, body, nil); err != nil {
			return false, err
		}
		output.Success(out, "Updated %s ( %s ) name",
			local.GetString("name"), local.GetString("$id"))

		return true, nil
	}

	var differing []string
	if remote.GetString("name") != local.GetString("name") {
		differing = append(differing, "name")
	}
	if !sameValue(remote, local, "rowSecurity") {
		differing = append(differing, "rowSecurity")
	}
	if !sameValue(remote, local, "enabled") {
		differing = append(differing, "enabled")
	}
	if !equalValues(valueOf(remote, "$permissions"), valueOf(local, "$permissions")) {
		differing = append(differing, "permissions")
	}

	if len(differing) == 0 {
		return false, nil
	}

	body := jsonx.NewObject()
	body.Set("name", local.GetString("name"))
	if value, present := local.Get("rowSecurity"); present {
		body.Set("rowSecurity", value)
	}
	if value, present := local.Get("$permissions"); present {
		body.Set("permissions", value)
	}
	// `enabled` is detected as a difference above and then NOT sent: the update
	// call omits it. Sending it here would be a behaviour change on a live
	// project, so it is left as it is until that is a deliberate change.

	if err := c.api.Call("PUT", path, body, nil); err != nil {
		return false, err
	}

	output.Success(out, "Updated %s ( %s ) - %s",
		local.GetString("name"), local.GetString("$id"), strings.Join(differing, ", "))

	return true, nil
}

// createChild creates a missing table or collection.
//
// The boolean reports whether the create also carried the schema, which only a
// collection's does. A table's columns are pushed separately afterwards.
func (c *pushContext) createChild(
	command *cobra.Command,
	resource pushDatabaseResource,
	entry *jsonx.Object,
) (bool, error) {
	path := resource.Path + "/" + url.PathEscape(entry.GetString("databaseId")) +
		resource.ChildPath

	body := jsonx.NewObject()

	if resource.Singular == "collection" {
		body.Set("collectionId", entry.GetString("$id"))
		body.Set("name", entry.GetString("name"))
		if value, present := entry.Get("documentSecurity"); present {
			body.Set("documentSecurity", value)
		}
		if value, present := entry.Get("$permissions"); present {
			body.Set("permissions", value)
		}
		if value, present := entry.Get("attributes"); present {
			body.Set("attributes", value)
		}
		if value, present := entry.Get("indexes"); present {
			body.Set("indexes", value)
		}

		if err := c.api.Call("POST", path, body, nil); err != nil {
			return false, err
		}

		return true, nil
	}

	body.Set("tableId", entry.GetString("$id"))
	body.Set("name", entry.GetString("name"))
	if value, present := entry.Get("rowSecurity"); present {
		body.Set("rowSecurity", value)
	}
	if value, present := entry.Get("$permissions"); present {
		body.Set("permissions", value)
	}

	if err := c.api.Call("POST", path, body, nil); err != nil {
		return false, err
	}

	output.Success(command.OutOrStdout(), "Created %s ( %s )",
		entry.GetString("name"), entry.GetString("$id"))

	return false, nil
}

// selectDatabaseResources picks which tables or collections to push.
//
// Not pushContext.selectResources: a table's identity is the database id AND
// its own id, so two tables named the same in different databases stay
// distinguishable in the prompt and in the selection.
func (c *pushContext) selectDatabaseResources(
	resource pushDatabaseResource,
) ([]*jsonx.Object, error) {
	entries := c.local.ResourceEntries(resource.ConfigKey)
	if app.Flags().All || len(entries) == 0 {
		return entries, nil
	}

	options := make([]prompt.Option, 0, len(entries))
	for _, entry := range entries {
		options = append(options, prompt.Option{
			Label: fmt.Sprintf("%s (%s - %s)", entry.GetString("name"),
				entry.GetString("databaseId"), entry.GetString("$id")),
			Value: identityOf(entry),
		})
	}

	chosen, err := c.prompter.MultiChoice(prompt.MultiChoice{
		Message: "Which " + resource.Label + " would you like to push?",
		Options: options,
		Filter:  true,
		Flag:    "--all",
	})
	if err != nil {
		return nil, err
	}

	selected := map[string]bool{}
	for _, value := range chosen {
		selected[value] = true
	}

	filtered := make([]*jsonx.Object, 0, len(chosen))
	for _, entry := range entries {
		if selected[identityOf(entry)] {
			filtered = append(filtered, entry)
		}
	}

	return filtered, nil
}

// identityOf keys a table or collection by its database and its own id.
func identityOf(entry *jsonx.Object) string {
	return entry.GetString("databaseId") + "|" + entry.GetString("$id")
}

// applyRenamesToIndexes rewrites member references on a stale remote snapshot.
func applyRenamesToIndexes(remote *jsonx.Object, membersKey string, renames []schema.Rename) {
	if len(renames) == 0 || remote == nil {
		return
	}

	replacement := make(map[string]string, len(renames))
	for _, rename := range renames {
		replacement[rename.From] = rename.To
	}

	for _, index := range remote.GetObjects("indexes") {
		value, present := index.Get(membersKey)
		if !present {
			continue
		}
		members, ok := value.([]any)
		if !ok {
			continue
		}

		rewritten := make([]any, 0, len(members))
		for _, member := range members {
			name, ok := member.(string)
			if !ok {
				rewritten = append(rewritten, member)

				continue
			}
			if renamed, found := replacement[name]; found {
				name = renamed
			}
			rewritten = append(rewritten, name)
		}
		index.Set(membersKey, rewritten)
	}
}

// valueOf reads a key, yielding nil when it is absent.
func valueOf(source *jsonx.Object, key string) any {
	value, _ := source.Get(key)

	return value
}

// sameValue compares one field between the remote and the config.
func sameValue(remote, local *jsonx.Object, key string) bool {
	return equalValues(valueOf(remote, key), valueOf(local, key))
}

// page walks a list endpoint through the push client.
func (c *pushContext) page(path, wrapper string) ([]*jsonx.Object, error) {
	return c.api.List(path, wrapper, nil)
}

package cmd

import (
	"fmt"
	"net/url"
	"os"
	"path/filepath"
	"strings"
	"unicode"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/archive"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Ports pullFunctions and pullSites (templates/cli/lib/commands/pull.ts:296
// and :401) plus downloadDeploymentCode (lib/commands/utils/deployment.ts:630).
//
// These are the pulls that write to the filesystem: each resource's latest
// deployment is downloaded and unpacked into its own directory. Everything
// else about them mirrors the flat pulls.

// codeResource describes one deployable resource.
type codeResource struct {
	resourceIdentity
	// Directory is the prefix each resource's sources are written under. It
	// matches ConfigKey for both resources today, but it names a path on the
	// user's disk rather than a key in their config, so it stays its own field.
	Directory string
	// Keys is the caller order the entry is written in. NOT a schema order:
	// addFunction goes through whitelistKeys, which follows the CALLER, and
	// the caller is pullFunctions -- so this is the order that function builds
	// its object in, which is not the order FunctionSchema declares.
	Keys []string
}

var codeResources = []codeResource{
	{
		resourceIdentity: functionIdentity,
		Directory:        "functions",
		Keys: []string{
			"$id", "name", "runtime", "path", "entrypoint", "execute",
			"enabled", "logging", "events", "schedule", "timeout", "commands",
			"scopes", "buildSpecification", "runtimeSpecification",
			"deploymentRetention",
		},
	},
	{
		resourceIdentity: siteIdentity,
		Directory:        "sites",
		Keys: []string{
			"$id", "name", "path", "framework", "logging", "timeout",
			"buildRuntime", "adapter", "installCommand", "buildCommand",
			"outputDirectory", "fallbackFile", "installationId",
			"providerRepositoryId", "providerBranch", "providerSilentMode",
			"providerRootDirectory", "startCommand", "buildSpecification",
			"runtimeSpecification", "deploymentRetention",
		},
	},
}

func newPullCodeCommand(resource codeResource) *cobra.Command {
	var (
		withVariables bool
		code          bool
	)

	command := &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short:   "Pull " + resource.Label + " from your Appwrite project",
		PreRunE: func(command *cobra.Command, args []string) error {
			return applyNegatedFlags(command)
		},
		RunE: func(command *cobra.Command, args []string) error {
			return runPullCode(command, resource, code, withVariables)
		},
	}

	command.Flags().BoolVar(&withVariables, "with-variables", false,
		"Pull "+resource.Label+" variables into a .env file")
	negatableBool(command.Flags(), &code, "code",
		"Pull the source code of the latest deployment")

	return command
}

func runPullCode(command *cobra.Command, resource codeResource, code, withVariables bool) error {
	out := command.OutOrStdout()

	context, err := newProjectPull()
	if err != nil {
		return err
	}

	output.Log(out, "Fetching %s ...", resource.Label)

	rows, err := context.page(resource.Path, resource.ConfigKey, nil)
	if err != nil {
		return err
	}
	if len(rows) == 0 {
		output.Log(out, "No %s found.", resource.Label)
		output.Success(out, "Successfully pulled 0 %s.", resource.Label)

		return nil
	}

	// Without --all the TypeScript asks WHICH resources to pull, so pulling
	// everything by default would silently do more than asked.
	if !app.Flags().All {
		rows, err = selectResources(rows, resource)
		if err != nil {
			return err
		}
	}

	// AFTER the selection, which is the order the TypeScript asks in
	// (pull.ts:901 then :905). Asking about the code first made the user answer
	// a question about resources they had not chosen yet.
	if code {
		// Downloading overwrites whatever is in the resource's directory, so
		// it is confirmed rather than assumed.
		confirmed, err := prompt.New(app.Flags().Force).Confirm(prompt.Question{
			Message: "Pull the source code of the latest deployment?",
			Default: true,
			Flag:    "--force",
		})
		if err != nil {
			return err
		}
		code = confirmed
	}

	directory := context.local.ResourceDirname(resource.ConfigKey)

	for _, row := range rows {
		name := row.GetString("name")
		output.Log(out, "Pulling %s %s ...", strings.TrimSuffix(resource.Label, "s"), name)

		relative := resource.Directory + "/" + SafeDirectoryName(name, row.GetString("$id"))

		// A path already in the config wins: the user may have moved the
		// sources, and rewriting it would orphan them.
		if existing := existingPath(context.local, resource.ConfigKey, row.GetString("$id")); existing != "" {
			relative = existing
		}

		// Set BEFORE filtering so `path` lands in its schema position. The
		// API never returns it -- it is a local concept -- so filtering first
		// and setting after would append it at the end.
		row.Set("path", relative)

		entry := config.FilterBySchema(row, resource.Keys)
		context.local.UpsertByID(resource.ConfigKey, entry)

		if !code {
			continue
		}

		absolute := filepath.Join(directory, filepath.FromSlash(relative))
		if err := os.MkdirAll(absolute, 0o755); err != nil {
			return err
		}

		if err := context.downloadDeployment(resource, row, absolute, withVariables); err != nil {
			return err
		}
	}

	if !code {
		output.Warn(out, "Source code download skipped.")
	}

	if err := context.local.Write(); err != nil {
		return err
	}

	output.Success(out, "Successfully pulled %d %s.", len(rows), resource.Label)

	return nil
}

// selectResources asks which resources to pull.
//
// Ports questionsPullFunctions. Every resource is offered unselected, matching
// the TypeScript's checkbox, and choosing none is a valid answer that pulls
// nothing.
func selectResources(rows []*jsonx.Object, resource codeResource) ([]*jsonx.Object, error) {
	options := make([]prompt.Option, 0, len(rows))
	for _, row := range rows {
		options = append(options, prompt.Option{
			Label: row.GetString("name") + " (" + row.GetString("$id") + ")",
			Value: row.GetString("$id"),
		})
	}

	chosen, err := prompt.New(app.Flags().Force).MultiChoice(prompt.MultiChoice{
		Message: "Which " + resource.Label + " would you like to pull?",
		Options: options,
		Filter:  true,
		Flag:    "--all",
	})
	if err != nil {
		return nil, err
	}

	selected := map[string]bool{}
	for _, id := range chosen {
		selected[id] = true
	}

	filtered := make([]*jsonx.Object, 0, len(chosen))
	for _, row := range rows {
		if selected[row.GetString("$id")] {
			filtered = append(filtered, row)
		}
	}

	return filtered, nil
}

// downloadDeployment fetches the latest deployment and unpacks it.
//
// A resource with no deployment is skipped silently -- that is a resource
// created but never deployed, which is normal and not a failure.
func (p *projectPull) downloadDeployment(
	resource codeResource,
	row *jsonx.Object,
	destination string,
	withVariables bool,
) error {
	id := row.GetString("$id")
	base := resource.Path + "/" + url.PathEscape(id)

	var listing jsonx.Object
	err := p.api.Call("GET",
		base+`/deployments?queries[]={"method":"limit","values":[1]}`+
			`&queries[]={"method":"orderDesc","attribute":"$id"}`,
		nil, &listing)
	if err != nil {
		return err
	}

	deployments, _ := listing.Get("deployments")
	items, _ := deployments.([]any)
	if len(items) == 0 {
		return nil
	}

	latest, ok := items[0].(*jsonx.Object)
	if !ok {
		return nil
	}

	payload, err := p.api.Download(
		base + "/deployments/" + url.PathEscape(latest.GetString("$id")) + "/download")
	if err != nil {
		return err
	}

	// Written beside the destination rather than inside it: the archive must
	// not end up in the sources it unpacks to.
	temporary, err := os.CreateTemp(filepath.Dir(destination), "."+id+"-*.tar.gz")
	if err != nil {
		return err
	}
	temporaryPath := temporary.Name()
	defer os.Remove(temporaryPath)

	if _, err := temporary.Write(payload); err != nil {
		temporary.Close()

		return err
	}
	if err := temporary.Close(); err != nil {
		return err
	}

	if err := archive.ExtractTarGz(temporaryPath, destination); err != nil {
		return err
	}

	if !withVariables {
		return nil
	}

	return writeVariables(row, destination)
}

// writeVariables writes the resource's variables to a .env file.
//
// Overwritten wholesale, matching the TypeScript: the remote is the source of
// truth for these, and merging would keep a variable deleted upstream.
func writeVariables(row *jsonx.Object, destination string) error {
	value, ok := row.Get("vars")
	if !ok {
		return nil
	}
	items, ok := value.([]any)
	if !ok {
		return nil
	}

	var contents strings.Builder
	for _, item := range items {
		variable, ok := item.(*jsonx.Object)
		if !ok {
			continue
		}
		fmt.Fprintf(&contents, "%s=%s\n",
			variable.GetString("key"), variable.GetString("value"))
	}

	// 0600: these are secrets, and the surrounding source tree is 0644.
	return os.WriteFile(filepath.Join(destination, ".env"), []byte(contents.String()), 0o600)
}

// existingPath returns the path already recorded for a resource.
func existingPath(local *config.Local, resource, id string) string {
	for _, entry := range local.ResourceEntries(resource) {
		if entry.GetString("$id") == id {
			return entry.GetString("path")
		}
	}

	return ""
}

// SafeDirectoryName turns a resource name into a directory name.
//
// Ports getSafeDirectoryName(). Accents are stripped, everything outside
// [a-z0-9] collapses to a single hyphen, and a name that reduces to nothing
// falls back to the id -- otherwise two unnamed resources would share a
// directory.
func SafeDirectoryName(value, fallback string) string {
	var builder strings.Builder
	previousHyphen := false

	for _, character := range strings.ToLower(value) {
		// Combining marks are dropped rather than transliterated, which is
		// what NFKD followed by the ̀-ͯ strip amounts to for the
		// accented Latin this actually sees.
		if unicode.Is(unicode.Mn, character) {
			continue
		}

		if (character >= 'a' && character <= 'z') || (character >= '0' && character <= '9') {
			builder.WriteRune(character)
			previousHyphen = false

			continue
		}

		if !previousHyphen {
			builder.WriteByte('-')
			previousHyphen = true
		}
	}

	normalized := strings.Trim(builder.String(), "-")
	if normalized == "" {
		return fallback
	}

	return normalized
}

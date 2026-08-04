package cmd

import (
	"encoding/json"
	"fmt"
	"sort"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
	"github.com/appwrite/appwrite-cli-go/internal/output"
	"github.com/appwrite/appwrite-cli-go/internal/prompt"
	"github.com/spf13/cobra"
)

// Shared foundation for every `push` subcommand.
//
// Ports templates/cli/lib/commands/utils/change-approval.ts and the parts of
// push.ts every resource repeats: pick the resources, show what would change,
// get approval, then apply.
//
// The approval gate is the reason `push` is not simply "send the config". It
// exists because push OVERWRITES a live project, and a user who mistyped a name
// or pulled from the wrong project needs to see that before it happens.

// pushContext is a project client plus the config being pushed.
type pushContext struct {
	api      *client.Client
	local    *config.Local
	prompter prompt.Prompter
}

func newPushContext() (*pushContext, error) {
	global, err := preferences()
	if err != nil {
		return nil, err
	}

	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil {
		return nil, fmt.Errorf(
			"no %s found. Run `%s init project` first: %w",
			config.LocalFileName, app.ExecutableName, err)
	}

	api, err := projectAPI(global, local)
	if err != nil {
		return nil, err
	}

	return &pushContext{
		api:      api,
		local:    local,
		prompter: prompt.New(app.Flags().Force),
	}, nil
}

// change is one field that differs between the remote and the config.
type change struct {
	ID     string
	Key    string
	Remote string
	Local  string
}

// isEmpty reports whether a value counts as absent for comparison.
//
// Ports isEmpty(). Null, an all-whitespace string and an empty array are all
// "nothing", so a field the API omits does not read as a change against a
// config that leaves it blank.
func isEmpty(value any) bool {
	switch typed := value.(type) {
	case nil:
		return true
	case string:
		return strings.TrimSpace(typed) == ""
	case []any:
		return len(typed) == 0
	case []string:
		return len(typed) == 0
	}

	return false
}

// renderValue formats a value for the change table.
//
// Arrays are newline-joined rather than JSON-encoded: a permissions list is
// read by a human deciding whether to approve, and `["read(\"any\")"]` is
// harder to scan than the entry on its own line.
func renderValue(value any) string {
	switch typed := value.(type) {
	case nil:
		return ""
	case string:
		return typed
	case []any:
		parts := make([]string, 0, len(typed))
		for _, item := range typed {
			parts = append(parts, fmt.Sprint(scalarOf(item)))
		}

		return strings.Join(parts, "\n")
	}

	return fmt.Sprint(scalarOf(value))
}

// scalarOf unwraps a json.Number so a value prints as it was received rather
// than in Go's float formatting.
func scalarOf(value any) any {
	if number, ok := value.(json.Number); ok {
		return number.String()
	}

	return value
}

// equalValues compares a remote value with its local counterpart.
//
// Arrays compare ORDERED here, matching the JSON.stringify comparison in
// approveChanges. Some resources compare permissions unordered when deciding
// whether to send an update -- that is a different comparison, made later, and
// the two are deliberately not merged.
func equalValues(remote, local any) bool {
	remoteItems, remoteIsArray := remote.([]any)
	localItems, localIsArray := local.([]any)

	if remoteIsArray && localIsArray {
		if len(remoteItems) != len(localItems) {
			return false
		}
		for index := range remoteItems {
			if fmt.Sprint(scalarOf(remoteItems[index])) != fmt.Sprint(scalarOf(localItems[index])) {
				return false
			}
		}

		return true
	}
	if remoteIsArray != localIsArray {
		return false
	}

	return fmt.Sprint(scalarOf(remote)) == fmt.Sprint(scalarOf(local))
}

// approvalRequest describes one resource kind's change check.
type approvalRequest struct {
	// Resources are the config entries about to be pushed.
	Resources []*jsonx.Object
	// Fetch reads the remote counterpart. A 404 means the resource is new,
	// which is not a change to approve -- there is nothing to overwrite.
	Fetch func(local *jsonx.Object) (*jsonx.Object, error)
	// Keys limits the comparison to the fields the config owns, so a
	// server-managed $updatedAt never shows up as a change.
	Keys []string
	// SkipKeys are compared by the caller instead, or not at all.
	SkipKeys []string
	// Plural names the resource in the "pushed 0 X" line.
	Plural string
}

// approveChanges shows what would change and asks for approval.
//
// Returns true when the push should proceed: either nothing differs, or the
// user said yes. Ports approveChanges().
func (c *pushContext) approveChanges(command *cobra.Command, request approvalRequest) (bool, error) {
	out := command.OutOrStdout()
	output.Log(out, "Checking for changes ...")

	skip := make(map[string]bool, len(request.SkipKeys))
	for _, key := range request.SkipKeys {
		skip[key] = true
	}

	var changes []change

	for _, local := range request.Resources {
		remote, err := request.Fetch(local)
		if err != nil {
			if isNotFound(err) {
				// New resource. Nothing to overwrite, so nothing to approve.
				continue
			}

			return false, err
		}

		id := local.GetString("$id")

		// Iterating the KEYS rather than the response: the comparison is
		// scoped to what the config owns, and the order is stable so a change
		// table reads the same way twice.
		for _, key := range request.Keys {
			if skip[key] {
				continue
			}

			remoteValue, present := remote.Get(key)
			if !present {
				continue
			}
			localValue, _ := local.Get(key)

			if isEmpty(remoteValue) && isEmpty(localValue) {
				continue
			}
			if equalValues(remoteValue, localValue) {
				continue
			}

			changes = append(changes, change{
				ID:     id,
				Key:    key,
				Remote: renderValue(remoteValue),
				Local:  renderValue(localValue),
			})
		}
	}

	if len(changes) == 0 {
		return true, nil
	}

	printChanges(command, changes)

	approved, err := c.prompter.Confirm(prompt.Question{
		Message: "Would you like to apply these changes?",
		Default: true,
		Flag:    "--force",
	})
	if err != nil {
		return false, err
	}
	if approved {
		return true, nil
	}

	output.Warn(out, "Skipping push action. Changes were not applied.")
	output.Success(out, "Successfully pushed 0 %s.", request.Plural)

	return false, nil
}

// printChanges renders the change table.
func printChanges(command *cobra.Command, changes []change) {
	// Through the shared renderer, so this table carries the header rule and
	// column separators every other table has. It used to align its own
	// columns with spaces alone, which left the reader guessing where one
	// column ended -- especially with an empty cell in the last one.
	//
	// Multi-line cells -- a permissions list -- are handled by the renderer
	// rather than split here.
	data := make([][]string, 0, len(changes))
	for _, entry := range changes {
		data = append(data, []string{entry.ID, entry.Key, entry.Remote, entry.Local})
	}

	command.Println()
	for _, line := range strings.Split(
		output.RenderTable([]string{"id", "key", "remote", "local"}, data), "\n") {
		command.Printf("  %s\n", line)
	}
	command.Println()
}

// selectResources picks which configured resources to push.
//
// With --all every configured resource is pushed. Without it the user chooses,
// and a non-interactive shell is told to pass --all rather than left guessing.
func (c *pushContext) selectResources(
	resource string,
	singular string,
	entries []*jsonx.Object,
) ([]*jsonx.Object, error) {
	if app.Flags().All {
		return entries, nil
	}

	options := make([]prompt.Option, 0, len(entries))
	for _, entry := range entries {
		label := entry.GetString("name")
		if label == "" {
			label = entry.GetString("$id")
		}
		options = append(options, prompt.Option{
			Label: label + " (" + entry.GetString("$id") + ")",
			Value: entry.GetString("$id"),
		})
	}

	chosen, err := c.prompter.MultiChoice(prompt.MultiChoice{
		Message: "Which " + resource + " would you like to push?",
		Options: options,
		Filter:  true,
		Flag:    "--all",
		// Ports the checkbox's `validate: validateRequired(...)`
		// (questions.ts:999). A multi-select starts with nothing ticked and
		// Enter accepts that, so without this the prompt returns an empty
		// list -- and the caller then reports the resources as missing, which
		// sends the reader to `init` for something that already exists.
		Validate: prompt.RequiredSelection(singular),
	})
	if err != nil {
		return nil, err
	}

	selected := map[string]bool{}
	for _, id := range chosen {
		selected[id] = true
	}

	filtered := make([]*jsonx.Object, 0, len(chosen))
	for _, entry := range entries {
		if selected[entry.GetString("$id")] {
			filtered = append(filtered, entry)
		}
	}

	return filtered, nil
}

// arrayEqualsUnordered compares two arrays ignoring order.
//
// Ports arrayEqualsUnordered(). Used when DECIDING whether to send an update:
// the API returns permissions in its own order, and reordering alone is not a
// change worth a request.
func arrayEqualsUnordered(first, second any) bool {
	left, leftOK := stringsOf(first)
	right, rightOK := stringsOf(second)

	if !leftOK || !rightOK {
		return isEmpty(first) && isEmpty(second)
	}
	if len(left) != len(right) {
		return false
	}

	sort.Strings(left)
	sort.Strings(right)

	for index := range left {
		if left[index] != right[index] {
			return false
		}
	}

	return true
}

// stringsOf renders an array value as a comparable string slice.
func stringsOf(value any) ([]string, bool) {
	switch typed := value.(type) {
	case nil:
		return nil, true
	case []string:
		return append([]string(nil), typed...), true
	case []any:
		items := make([]string, 0, len(typed))
		for _, item := range typed {
			items = append(items, fmt.Sprint(scalarOf(item)))
		}

		return items, true
	}

	return nil, false
}

// newPushCommand builds the `push` tree.
//
// Every subcommand registers here rather than in the file that implements it:
// the tree is one thing, and three files each adding to it is how two of them
// end up disagreeing about the parent's description.
func newPushCommand() *cobra.Command {
	command := &cobra.Command{
		Use:   "push",
		Short: "Push your Appwrite project resources from appwrite.config.json",
		// Not Help(). The TypeScript's `push` has an action of its own
		// (push.ts:4280) that pushes -- prompting for one resource, or
		// everything under --all. Showing help instead made `push --all` a
		// no-op that looked like a usage error.
		RunE: func(command *cobra.Command, args []string) error {
			return runPush(command, app.Flags().All)
		},
	}

	command.AddCommand(newPushAllCommand())
	command.AddCommand(newPushSettingsCommand())
	for _, resource := range simpleResources() {
		command.AddCommand(newPushSimpleCommand(resource))
	}
	command.AddCommand(newPushTableCommand())
	command.AddCommand(newPushCollectionCommand())
	for _, resource := range deployables {
		command.AddCommand(newPushDeployableCommand(resource))
	}

	return command
}

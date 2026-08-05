package cmd

import (
	"encoding/json"
	"fmt"
	"io"
	"sort"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// pushTally is the closing count of a push.
//
// One policy, and it was written out three times -- in pushsimple, in
// `push tables` and in `push collections` -- with only the deploy variant ever
// covered by a test. The rule: zero pushed is INFORMATIONAL when nothing
// failed, because the commonest way to push nothing is that everything already
// matched, and reporting a good outcome as `✗ Error:` above a zero exit status
// contradicted itself. Zero pushed with a failure is the bad outcome, and each
// failure has already been printed with its own reason.
type pushTally struct {
	Pushed int
	Failed int
}

// report prints the closing line for a push of Label resources.
func (t pushTally) report(out io.Writer, plural string) {
	switch {
	case t.Pushed == 0 && t.Failed == 0:
		output.Log(out, "No %s were pushed. Everything is already up to date.", plural)
	case t.Pushed == 0:
		output.Failure(out, "No %s were pushed.", plural)
	default:
		output.Success(out, "Successfully pushed %d %s.", t.Pushed, plural)
	}
}

// Shared foundation for every `push` subcommand.
//
// Ports templates/cli/lib/commands/utils/change-approval.ts and the parts of
// push.ts every resource repeats: pick the resources, show what would change,
// get approval, then apply.
//
// The approval gate is the reason `push` is not simply "send the config". It
// exists because push OVERWRITES a live project, and a user who mistyped a name
// or pulled from the wrong project needs to see that before it happens.

// everyResourceArgument accepts the literal `all` as a positional, meaning what
// --all means.
//
// `appwrite push site all` reads as English, and both CLIs threw the word away:
// cobra and commander accept surplus positionals by default, so the command
// carried on and prompted for a selection anyway. `push function all` looked
// like it worked only because that project had no functions to prompt about --
// the word was ignored there too.
//
// `push all` is already a command, so `all` is a word the CLI has taught the
// user. Accepting it on the resource subcommands is cheaper than explaining why
// it means something one level up and nothing here.
//
// Anything else is rejected rather than ignored, which is the half of this that
// silently swallowing an argument cost: `push site my-site` looked like it
// pushed one site and pushed whatever the prompt was left on.
func everyResourceArgument() cobra.PositionalArgs {
	return func(command *cobra.Command, arguments []string) error {
		if len(arguments) == 0 {
			return nil
		}

		if len(arguments) == 1 && strings.EqualFold(arguments[0], "all") {
			// Exactly what --all sets, so everything downstream reads one flag
			// and does not need to know the word was typed instead.
			app.Flags().All = true

			return nil
		}

		return fmt.Errorf(
			"`%s` takes no arguments except `all`, and was given `%s`. "+
				"Run `%s all` to push every one, or `%s` on its own to choose from a list",
			command.CommandPath(), strings.Join(arguments, " "),
			command.CommandPath(), command.CommandPath())
	}
}

// pushContext is a project client plus the config being pushed.
type pushContext struct {
	api      *client.Client
	local    *config.Local
	prompter prompt.Prompter
	// screenshots is built on first use by pushpreview.go, and only by a site
	// push -- it needs a console session the other resources never ask for.
	screenshots *screenshots
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
			localValue, hasLocal := local.Get(key)

			// A key the config does not carry is never SENT. writeBody writes
			// only the keys that are present, deliberately, so that a push does
			// not clear a field the config is silent about -- which means
			// listing an absent key here promised an edit the push would not
			// make. A freshly inited site reported
			//
			//   id                   │ key                │ remote │ local
			//   6a729db1003e322cbfdb │ providerSilentMode │ false  │
			//
			// on every push, forever: the config has no such key, so there was
			// nothing to apply and the row never went away. Approving it did
			// nothing, which is the worst kind of prompt.
			if !hasLocal {
				continue
			}

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

	// The -a shorthand for --all lives here rather than on the root: see
	// app.RegisterGlobalFlags. Declared locally, so the persistent --all is not
	// merged over it and `push table`'s own -a stays free.
	command.Flags().BoolVarP(
		app.Flags().AllPointer(), "all", "a", false,
		"Push every resource in the project config")

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

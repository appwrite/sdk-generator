package cmd

import (
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// `pull all` is a fan-out: with --all it runs every pull in turn, and without
// it asks which single resource to pull. It is `pull all` rather than a bare
// `pull` because the parent command prints help.

// pullAction is one entry of the fan-out.
type pullAction struct {
	// Value is what the choice returns, and matches the TypeScript's.
	Value string
	// Label is shown in the prompt, with its owning service in parentheses.
	Label string
	// Run performs the pull.
	Run func(*cobra.Command) error
	// Deprecated resources are offered in the prompt but skipped by --all.
	Deprecated bool
}

// TWO ORDERS ARE IN PLAY AND THEY ARE NOT THE SAME, as they are not for push.
//
// pullActions is the EXECUTION order: the insertion order of the actions map
// (pull.ts:832), which is what `Object.values` walks under cliConfig.all.
// pullPromptOrder is what questionsPullResources offers (questions.ts:383),
// and it is missing `sites` upstream. That omission is faithful -- `pull all`
// still pulls sites, and a port that quietly added it to the picker would
// diverge from the CLI it is replacing.
func pullActions() []pullAction {
	byName := map[string]flatResource{}
	for _, resource := range flatResources {
		byName[resource.Name] = resource
	}

	databaseByName := map[string]databaseResource{}
	for _, resource := range databaseResources {
		databaseByName[resource.Name] = resource
	}

	codeByName := map[string]codeResource{}
	for _, resource := range codeResources {
		codeByName[resource.Name] = resource
	}

	flat := func(name string) func(*cobra.Command) error {
		resource := byName[name]

		return func(command *cobra.Command) error {
			return runPullResource(command, resource)
		}
	}

	database := func(name string) func(*cobra.Command) error {
		resource := databaseByName[name]

		return func(command *cobra.Command) error {
			return runPullDatabase(command, resource)
		}
	}

	code := func(name string) func(*cobra.Command) error {
		resource := codeByName[name]

		return func(command *cobra.Command) error {
			// --all implies pulling the code too; the per-command flags
			// default the same way.
			return runPullCode(command, resource, true, false)
		}
	}

	return []pullAction{
		{Value: "settings", Label: "Settings (Project)", Run: runPullSettings},
		{Value: "functions", Label: "Functions (Deployment)", Run: code("function")},
		{Value: "sites", Label: "Sites (Deployment)", Run: code("site")},
		{Value: "tables", Label: "Tables (TablesDB)", Run: database("table")},
		{Value: "buckets", Label: "Buckets (Storage)", Run: flat("bucket")},
		{Value: "teams", Label: "Teams (Auth)", Run: flat("team")},
		{Value: "webhooks", Label: "Webhooks (Project)", Run: flat("webhook")},
		{Value: "messages", Label: "Topics (Messaging)", Run: flat("topic")},
		{
			Value: "collections", Label: "Collections (Legacy Databases)",
			Run: database("collection"), Deprecated: true,
		},
	}
}

// pullPromptOrder is questionsPullResources' order. Listed as values so it
// cannot drift from the actions silently. `sites` is absent because it is
// absent upstream; see the note on pullActions.
var pullPromptOrder = []string{
	"settings", "functions", "tables", "buckets",
	"teams", "webhooks", "messages", "collections",
}

func newPullAllCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "all",
		Short: "Pull all resources from your Appwrite project",
		RunE: func(command *cobra.Command, args []string) error {
			// `pull all` means all, with or without --all. The TypeScript sets
			// cliConfig.all itself before delegating (pull.ts:1136), so it
			// never reaches the picker; gating on the flag here turned the
			// subcommand into a one-resource chooser.
			return runPull(command, pullActions(), true)
		},
	}
}

// runPull is the body of both `pull` and `pull all`.
//
// With everything selected it runs every non-deprecated
// action; otherwise it asks which one. Collections are always skipped in the
// everything case: they are the legacy databases API, and pulling both writes
// two representations of the same data into one config.
func runPull(command *cobra.Command, actions []pullAction, everything bool) error {
	if everything {
		// `cliConfig.all = true` (pull.ts:850). It is not only the fan-out's
		// own choice: each resource reads the same flag to decide whether to
		// ask WHICH functions, sites or buckets to pull. Running the fan-out
		// without setting it pulls every resource type and then stops to ask
		// about the contents of each one, which is not what `all` means.
		app.Flags().All = true

		for _, action := range actions {
			if action.Deprecated {
				continue
			}
			if err := action.Run(command); err != nil {
				return err
			}
		}

		return nil
	}

	byValue := make(map[string]pullAction, len(actions))
	for _, action := range actions {
		byValue[action.Value] = action
	}

	options := make([]prompt.Option, 0, len(pullPromptOrder))
	for _, value := range pullPromptOrder {
		if action, ok := byValue[value]; ok {
			options = append(options,
				prompt.Option{Label: action.Label, Value: action.Value})
		}
	}

	chosen, err := prompt.New(app.Flags().Force).Choice(prompt.Choice{
		Message: "Which resources would you like to pull?",
		Options: options,
		Flag:    "--all",
	})
	if err != nil {
		return err
	}

	for _, action := range actions {
		if action.Value == chosen {
			return action.Run(command)
		}
	}

	return nil
}

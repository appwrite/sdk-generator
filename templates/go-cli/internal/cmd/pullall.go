package cmd

import (
	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/prompt"
	"github.com/spf13/cobra"
)

// Ports pullResources (templates/cli/lib/commands/pull.ts:819).
//
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

// pullActions is ordered as questionsPullResources lists them, which is not
// the order they are declared elsewhere -- the prompt is user-visible.
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

func newPullAllCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "all",
		Short: "Pull all resources from your Appwrite project",
		RunE: func(command *cobra.Command, args []string) error {
			actions := pullActions()

			// --all runs everything. Collections are skipped: they are the
			// legacy databases API, and pulling both writes two
			// representations of the same data into one config.
			if app.Flags().All {
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

			options := make([]prompt.Option, 0, len(actions))
			for _, action := range actions {
				options = append(options,
					prompt.Option{Label: action.Label, Value: action.Value})
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
		},
	}
}

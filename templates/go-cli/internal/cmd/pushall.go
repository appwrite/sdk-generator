package cmd

import (
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Ports pushResources (templates/cli/lib/commands/push.ts:3257).
//
// `push all` is the fan-out. With --all it runs every resource in turn;
// without it, it asks which single resource to push.
//
// TWO ORDERS ARE IN PLAY AND THEY ARE NOT THE SAME. The execution order runs
// settings first and databases last, because a table cannot be created before
// its project settings allow the databases service and a function may depend on
// a bucket existing. The prompt order is the one questionsPushResources lists,
// which puts functions second because that is what people push most. Both are
// user-visible; neither is sorted.

// pushAction is one entry of the fan-out.
type pushAction struct {
	// Value is what the choice returns, matching the TypeScript's.
	Value string
	// Label is shown in the prompt, with its owning service in parentheses.
	Label string
	// Run performs the push.
	Run func(*cobra.Command) error
	// Deprecated resources are offered in the prompt but skipped by --all.
	Deprecated bool
}

// pushActions returns the fan-out entries in EXECUTION order.
//
// logs is the fan-out's --no-logs, which reaches the deployable resources and
// means nothing to the others.
func pushActions(logs bool) []pushAction {
	simple := func(name string) func(*cobra.Command) error {
		for _, resource := range simpleResources() {
			if resource.Name == name {
				return func(command *cobra.Command) error {
					return runPushSimple(command, resource)
				}
			}
		}

		return nil
	}

	deployableNamed := func(name string) func(*cobra.Command) error {
		for _, resource := range deployables {
			if resource.Name != name {
				continue
			}

			return func(command *cobra.Command) error {
				// --all implies pushing the code and activating it. The
				// TypeScript asks both questions once up front and applies the
				// answers to every resource; --force answers them true, which
				// is the only path a non-interactive run can take anyway.
				return runPushDeployable(command, resource, deployOptions{
					Code:        true,
					Activate:    true,
					ActivateSet: app.Flags().Force,
					Logs:        logs,
				})
			}
		}

		return nil
	}

	return []pushAction{
		{Value: "settings", Label: "Settings (Project)", Run: runPushSettings},
		{Value: "buckets", Label: "Buckets (Storage)", Run: simple("bucket")},
		{Value: "teams", Label: "Teams (Auth)", Run: simple("team")},
		{Value: "webhooks", Label: "Webhooks (Project)", Run: simple("webhook")},
		{Value: "messages", Label: "Topics (Messaging)", Run: simple("topic")},
		{Value: "functions", Label: "Functions (Deployment)", Run: deployableNamed("function")},
		{Value: "sites", Label: "Sites (Deployment)", Run: deployableNamed("site")},
		{
			Value: "tables", Label: "Tables (TablesDB)",
			Run: func(command *cobra.Command) error {
				return runPushTable(command, pushTable)
			},
		},
		{
			Value: "collections", Label: "Collections (Legacy Databases)",
			Run: func(command *cobra.Command) error {
				return runPushCollection(command, pushCollection)
			},
			Deprecated: true,
		},
	}
}

// promptOrder is questionsPushResources' order, which differs from the
// execution order above. Listed as values so the two cannot drift apart
// silently -- an action added without a prompt entry simply is not offered,
// and that shows up here rather than in a user's terminal.
var promptOrder = []string{
	"settings", "functions", "tables", "buckets",
	"teams", "webhooks", "messages", "collections",
}

func newPushAllCommand() *cobra.Command {
	logs := true

	command := &cobra.Command{
		Use:   "all",
		Short: "Push all resources in the current project",
		PreRunE: func(command *cobra.Command, args []string) error {
			return applyNegatedFlags(command)
		},
		RunE: func(command *cobra.Command, args []string) error {
			// `push all` means all, with or without --all. The TypeScript sets
			// cliConfig.all itself before delegating (push.ts:4288), so it
			// never reaches the picker; gating on the flag here turned the
			// subcommand into a one-resource chooser.
			return runPush(command, true)
		},
	}

	negatableBool(command.Flags(), &logs, "logs", "Stream deployment build logs")

	return command
}

// runPush is the body of both `push` and `push all`.
//
// Ports pushResources(). With everything selected it runs every non-deprecated
// action; otherwise it asks which one. Collections are always skipped in the
// everything case: they are the legacy databases API, and pushing both writes
// two representations of the same data to one project.
func runPush(command *cobra.Command, everything bool) error {
	// Only `push all` carries --no-logs; the bare `push` inherits the default,
	// as it does in the TypeScript where the flag is declared on the
	// subcommand alone (push.ts:4285).
	logs := true
	if command.Flags().Lookup("logs") != nil {
		parsed, err := command.Flags().GetBool("logs")
		if err != nil {
			return err
		}
		logs = parsed
	}

	return runPushActions(command, pushActions(logs), everything)
}

// runPushActions is the fan-out itself, separated from reading the flags so a
// test can hand it actions that record instead of calling the API.
func runPushActions(command *cobra.Command, actions []pushAction, everything bool) error {
	if everything {
		// `cliConfig.all = true` (push.ts:4288), for the same reason as pull:
		// each resource reads it to decide whether to ask which functions or
		// sites to push.
		app.Flags().All = true

		// Collections are skipped: they are the legacy databases API,
		// and pushing both writes two representations of the same
		// data to one project.
		for _, action := range actions {
			if action.Deprecated || action.Run == nil {
				continue
			}
			if err := action.Run(command); err != nil {
				return err
			}
		}

		return nil
	}

	byValue := make(map[string]pushAction, len(actions))
	for _, action := range actions {
		byValue[action.Value] = action
	}

	options := make([]prompt.Option, 0, len(promptOrder))
	for _, value := range promptOrder {
		if action, ok := byValue[value]; ok {
			options = append(options,
				prompt.Option{Label: action.Label, Value: action.Value})
		}
	}

	chosen, err := prompt.New(app.Flags().Force).Choice(prompt.Choice{
		Message: "Which resources would you like to push?",
		Options: options,
		Flag:    "--all",
	})
	if err != nil {
		return err
	}

	action, ok := byValue[chosen]
	if !ok || action.Run == nil {
		return nil
	}

	return action.Run(command)
}

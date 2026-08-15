//go:build browser

package cmd

import (
	"errors"

	"github.com/spf13/cobra"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
)

// registerHostCommands attaches guidance stubs for unavailable browser commands.
func registerHostCommands(root *cobra.Command) {
	root.AddCommand(newUnavailableCommand("login",
		"Sign in to an Appwrite endpoint",
		"Sign in through the Console instead -- this terminal already runs as the "+
			"signed-in user. `"+app.ExecutableName+" whoami` shows who that is."))

	root.AddCommand(newUnavailableCommand("update",
		"Update the CLI to the latest version",
		"This build ships with the Console and updates when the Console does. "+
			"Install the CLI locally to manage its version yourself: "+
			"https://appwrite.io/docs/tooling/command-line/installation"))

	root.AddCommand(newUnavailableCommand("types",
		"Generate types for your Appwrite project",
		"Generating types writes files into a project directory, which a browser "+
			"tab has no access to. Run `"+app.ExecutableName+" types` from a local "+
			"checkout of your project."))

	root.AddCommand(newUnavailableCommand("generate",
		"Generate a type-safe SDK from your Appwrite project configuration",
		"Generating an SDK writes files into a project directory, which a browser "+
			"tab has no access to. Run `"+app.ExecutableName+" generate` from a local "+
			"checkout of your project."))

	root.AddCommand(newUnavailableCommand("run",
		"Run project resources locally",
		"Running a function locally needs Docker, which a browser tab cannot reach. "+
			"Run `"+app.ExecutableName+" run` from a local checkout of your project."))

	root.AddCommand(newUnavailableCommand("init",
		"Init a new Appwrite project",
		"Initialising a project writes appwrite.config.json and a directory tree, "+
			"which a browser tab has no access to. Run `"+app.ExecutableName+" init` "+
			"from a local checkout, or create resources here with the service "+
			"commands -- `"+app.ExecutableName+" tablesdb create-table`, and so on."))

	root.AddCommand(newUnavailableCommand("pull",
		"Pull your Appwrite project resources into appwrite.config.json",
		"Pulling writes appwrite.config.json and resource files to disk, which a "+
			"browser tab has no access to. Run `"+app.ExecutableName+" pull` from a "+
			"local checkout of your project."))

	root.AddCommand(newUnavailableCommand("push",
		"Push your Appwrite project resources from appwrite.config.json",
		"Pushing reads appwrite.config.json and your source files from disk, which a "+
			"browser tab has no access to. Run `"+app.ExecutableName+" push` from a "+
			"local checkout of your project."))
}

// Accept all arguments so unavailable subcommands and flags show the same guidance.
func newUnavailableCommand(use, short, guidance string) *cobra.Command {
	return &cobra.Command{
		Use:                use,
		Short:              short,
		DisableFlagParsing: true,
		Args:               cobra.ArbitraryArgs,
		RunE: func(command *cobra.Command, args []string) error {
			return errors.New(guidance)
		},
	}
}

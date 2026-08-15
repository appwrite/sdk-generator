//go:build !browser

package cmd

import "github.com/spf13/cobra"

// registerHostCommands attaches commands that require native host capabilities.
func registerHostCommands(root *cobra.Command) {
	root.AddCommand(newUpdateCommand())
	root.AddCommand(newLoginCommand())
	root.AddCommand(newTypesCommand())
	root.AddCommand(newGenerateCommand())
	root.AddCommand(newRunCommand())
	root.AddCommand(newInitCommand())
	root.AddCommand(newPullCommand())
	root.AddCommand(newPushCommand())
}

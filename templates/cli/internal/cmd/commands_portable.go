package cmd

import "github.com/spf13/cobra"

// registerSessionCommands attaches portable commands and the host-specific surface.
func registerSessionCommands(root *cobra.Command) {
	root.AddCommand(newWhoamiCommand())
	root.AddCommand(newSessionsCommand())
	root.AddCommand(newClientCommand())
	root.AddCommand(newLogoutCommand())

	registerHostCommands(root)
}

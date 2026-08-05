package cmd

import (
	"strconv"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/spf13/cobra"
)

// Ports the `client` command from templates/cli/lib/commands/generic.ts.
//
// Configures an endpoint, project and API key without going through the
// browser login. Scripts and CI use it, and the conformance harness points the
// CLI at the mock API with it, so it is not optional.

func newClientCommand() *cobra.Command {
	var (
		endpoint   string
		projectID  string
		key        string
		selfSigned string
		debug      bool
		reset      bool
	)

	command := &cobra.Command{
		Use:   "client",
		Short: "Configure the CLI's endpoint, project and API key",
		RunE: func(command *cobra.Command, args []string) error {
			flags := command.Flags()

			// With no flags at all the TypeScript prints help rather than
			// silently succeeding, which is the useful behaviour when someone
			// types `client` to find out what it does.
			if !flags.Changed("endpoint") && !flags.Changed("project-id") &&
				!flags.Changed("key") && !flags.Changed("self-signed") &&
				!debug && !reset {
				return command.Help()
			}

			global, err := preferences()
			if err != nil {
				return err
			}

			if reset {
				for _, id := range global.SessionIDs() {
					global.DeleteSession(id)
				}
				if err := global.Write(); err != nil {
					return err
				}
				command.Println("Configuration reset.")

				return nil
			}

			if debug {
				return printClientDebug(command, global)
			}

			// `client` configures a session that may not exist yet -- this is
			// the path someone takes instead of `login`.
			sessionID := global.CurrentSessionID()
			if sessionID == "" {
				sessionID = "default"
				global.AddSession(sessionID, config.NewObject())
			}

			if flags.Changed("endpoint") {
				global.SetCurrentValue(config.PreferenceEndpoint, endpoint)
			}
			if flags.Changed("project-id") {
				global.SetCurrentValue(config.PreferenceProject, projectID)
			}
			if flags.Changed("key") {
				global.SetCurrentValue(config.PreferenceKey, key)
			}
			if flags.Changed("self-signed") {
				parsed, err := strconv.ParseBool(selfSigned)
				if err != nil {
					return err
				}
				global.SetCurrentValue(config.PreferenceSelfSigned, parsed)
			}

			return global.Write()
		},
	}

	flags := command.Flags()
	flags.StringVarP(&endpoint, "endpoint", "e", "", "Set your Appwrite server endpoint")
	flags.StringVarP(&projectID, "project-id", "p", "", "Set your Appwrite project ID")
	flags.StringVarP(&key, "key", "k", "", "Set your Appwrite server's API key")
	flags.StringVar(&selfSigned, "self-signed", "",
		"Configure the CLI to use a self-signed certificate ( true or false )")
	flags.BoolVarP(&debug, "debug", "d", false, "Print CLI debug information")
	flags.BoolVarP(&reset, "reset", "r", false, "Reset the CLI configuration")

	return command
}

// printClientDebug reports the active configuration with credentials masked.
//
// This is a diagnostic users paste into bug reports, so the key and access
// token are shortened rather than shown -- the same reason internal/output
// redacts them.
func printClientDebug(command *cobra.Command, global *config.Global) error {
	mask := func(value string) string {
		if value == "" {
			return ""
		}
		if len(value) > 16 {
			return value[:8] + "..." + value[len(value)-8:]
		}

		return "********"
	}

	command.Printf("endpoint     : %s\n", global.CurrentValue(config.PreferenceEndpoint))
	command.Printf("key          : %s\n", mask(global.CurrentValue(config.PreferenceKey)))
	command.Printf("accessToken  : %s\n", mask(global.CurrentValue(config.PreferenceAccessToken)))
	command.Printf("selfSigned   : %s\n", global.CurrentValue(config.PreferenceSelfSigned))
	command.Printf("projectId    : %s\n", global.CurrentValue(config.PreferenceProject))
	command.Printf("version      : %s\n", app.Version)

	return nil
}

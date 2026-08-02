package cmd

import (
	"errors"

	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/spf13/cobra"
)

// Ports the session half of templates/cli/lib/sdks.ts and the `whoami` command
// in templates/cli/lib/commands/generic.ts.

// ErrNotLoggedIn is returned when a command needs a session and none is stored.
var ErrNotLoggedIn = errors.New("no active session. Run `" + ExecutableName + " login` to sign in")

// preferences loads the user's global preferences.
func preferences() (*config.Global, error) {
	path, err := config.GlobalPath(ExecutableName)
	if err != nil {
		return nil, err
	}

	return config.LoadGlobal(path), nil
}

// consoleClient builds a client authenticated against the console project.
//
// Ports sdkForConsole(). Token refresh is deliberately not implemented here --
// that arrives with internal/auth in the rest of Phase 2; until then an expired
// token surfaces as a normal API error rather than being silently renewed.
func consoleClient() (*client.Client, *config.Global, error) {
	global, err := preferences()
	if err != nil {
		return nil, nil, err
	}

	session := global.Current()
	if session == nil {
		return nil, nil, ErrNotLoggedIn
	}

	endpoint := session.GetString(config.PreferenceEndpoint)
	if endpoint == "" {
		return nil, nil, ErrNotLoggedIn
	}

	api := client.New(endpoint, Version).
		SetProject(config.ProjectConsole).
		SetLocale("en-US")

	accessToken := session.GetString(config.PreferenceAccessToken)
	cookie := session.GetString(config.PreferenceCookie)

	switch {
	case accessToken != "":
		api.SetBearer(accessToken)
	case cookie != "":
		api.SetCookie(cookie)
	default:
		return nil, nil, ErrNotLoggedIn
	}

	return api, global, nil
}

func newWhoamiCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "whoami",
		Short: "Show the account the CLI is currently signed in as",
		RunE: func(command *cobra.Command, args []string) error {
			api, global, err := consoleClient()
			if err != nil {
				return err
			}

			var account map[string]any
			if err := api.Call("GET", "/account", nil, &account); err != nil {
				return err
			}

			session := global.Current()
			command.Printf("Endpoint : %s\n", session.GetString(config.PreferenceEndpoint))
			command.Printf("Email    : %v\n", account["email"])
			command.Printf("Name     : %v\n", account["name"])
			command.Printf("ID       : %v\n", account["$id"])

			return nil
		},
	}
}

func newSessionsCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "sessions",
		Short: "List the sessions stored in the CLI preferences",
		RunE: func(command *cobra.Command, args []string) error {
			global, err := preferences()
			if err != nil {
				return err
			}

			ids := global.SessionIDs()
			if len(ids) == 0 {
				command.Println("No stored sessions.")

				return nil
			}

			current := global.CurrentSessionID()
			for _, id := range ids {
				session, _ := global.Session(id)
				marker := " "
				if id == current {
					marker = "*"
				}
				command.Printf("%s %s  %s  %s\n", marker, id, session.Email, session.Endpoint)
			}

			return nil
		},
	}
}

// registerSessionCommands attaches the commands that do not come from the spec.
func registerSessionCommands(root *cobra.Command) {
	root.AddCommand(newWhoamiCommand())
	root.AddCommand(newSessionsCommand())
}

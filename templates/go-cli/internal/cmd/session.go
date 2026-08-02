package cmd

import (
	"errors"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/auth"
	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/spf13/cobra"
)

// Ports the session half of templates/cli/lib/sdks.ts and the `whoami` command
// in templates/cli/lib/commands/generic.ts.

// ErrNotLoggedIn is returned when a command needs a session and none is stored.
var ErrNotLoggedIn = errors.New("no active session. Run `" + app.ExecutableName + " login` to sign in")

// preferences loads the user's global preferences.
func preferences() (*config.Global, error) {
	path, err := config.GlobalPath(app.ExecutableName)
	if err != nil {
		return nil, err
	}

	return config.LoadGlobal(path), nil
}

// consoleClient builds a client authenticated against the console project.
//
// Ports sdkForConsole(). The access token is refreshed first when it is expired
// or within a minute of expiring.
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

	api := client.New(endpoint, app.Version).
		SetProject(config.ProjectConsole).
		SetLocale("en-US")

	hasAccessToken := session.GetString(config.PreferenceAccessToken) != ""
	cookie := session.GetString(config.PreferenceCookie)

	switch {
	case hasAccessToken:
		token, err := auth.NewAuthenticator(global, app.Version).AccessToken(false)
		if err != nil {
			return nil, nil, err
		}
		api.SetBearer(token)
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

func newLogoutCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "logout",
		Short: "Sign out and remove the stored session",
		RunE: func(command *cobra.Command, args []string) error {
			global, err := preferences()
			if err != nil {
				return err
			}

			// --all is a persistent root flag. Declaring a local one here would
			// shadow it: the local flag would be set and app.Flags().All would not,
			// which happens to work only while both mean the same thing.
			targets := []string{global.CurrentSessionID()}
			if app.Flags().All {
				targets = global.SessionIDs()
			}
			if len(targets) == 0 || targets[0] == "" {
				command.Println("No active session.")

				return nil
			}

			// Clear the refresh token before removing the session entry:
			// DeleteRefresh needs the session to still exist to drop the prefs
			// fallback copy.
			store := &auth.TokenStore{Global: global}
			for _, id := range targets {
				if err := store.DeleteRefresh(id); err != nil {
					return err
				}
				global.DeleteSession(id)
			}
			if err := global.Write(); err != nil {
				return err
			}

			command.Printf("Signed out of %d session(s).\n", len(targets))

			return nil
		},
	}
}

// registerSessionCommands attaches the commands that do not come from the spec.
func registerSessionCommands(root *cobra.Command) {
	root.AddCommand(newWhoamiCommand())
	root.AddCommand(newSessionsCommand())
	root.AddCommand(newLoginCommand())
	root.AddCommand(newLogoutCommand())
}

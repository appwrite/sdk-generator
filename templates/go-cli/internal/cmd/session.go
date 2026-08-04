package cmd

import (
	"errors"
	"fmt"
	"runtime"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/auth"
	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
	"github.com/spf13/cobra"
)

// Ports the session half of templates/cli/lib/sdks.ts and the `whoami` command
// in templates/cli/lib/commands/generic.ts.

// ErrNotLoggedIn is returned when a command needs a session and none is stored.
var ErrNotLoggedIn = errors.New("no active session. Run `" + app.ExecutableName + " login` to sign in")

// ErrConsoleNeedsSession is returned when only an API key is stored and the
// command needs the console. Ports the two-sentence message in sdkForConsole().
var ErrConsoleNeedsSession = errors.New(
	"session not found. Run `" + app.ExecutableName + " login`. API keys work for project commands " +
		"(e.g. `" + app.ExecutableName + " push functions`), not console-only commands " +
		"(e.g. `" + app.ExecutableName + " push settings`)")

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
		SetSelfSigned(global.CurrentBool(config.PreferenceSelfSigned)).
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
	case session.GetString(config.PreferenceKey) != "":
		// An API key is scoped to one project; the console endpoints are not.
		// Saying so is the difference between a user adding `login` and a user
		// re-checking a key that was never going to work here.
		return nil, nil, ErrConsoleNeedsSession
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

			account := jsonx.NewObject()
			if err := api.Call("GET", "/account", nil, account); err != nil {
				return err
			}

			// Rendered rather than printed, so --json and --raw work here as
			// they do on every generated command. Printing directly meant
			// `whoami --json` emitted the human table.
			session := global.Current()
			// The CLI identifies itself first, the way `vercel whoami` does.
			// Both lines are what a bug report needs and what someone chasing
			// "works on my machine" asks for, so they are fields rather than a
			// banner -- that way `--json` carries them too.
			//
			// MFA is deliberately absent: the OAuth login the CLI uses does not
			// exercise it, so reporting it invited a conclusion it cannot
			// support.
			report := jsonx.NewObject()
			report.Set("CLI version", app.Version)
			report.Set("Runtime", fmt.Sprintf("%s (%s/%s)",
				runtime.Version(), runtime.GOOS, runtime.GOARCH))
			report.Set("User ID", account.GetString("$id"))
			report.Set("Name", account.GetString("name"))
			report.Set("Email", account.GetString("email"))
			report.Set("Endpoint", session.GetString(config.PreferenceEndpoint))

			return app.Render(report)
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

			// Rendered like everything else so --json is machine-readable.
			// The active session is a field rather than a leading asterisk,
			// because a marker in a table cannot survive JSON.
			current := global.CurrentSessionID()
			rows := make([]any, 0, len(ids))
			for _, id := range ids {
				session, _ := global.Session(id)
				row := jsonx.NewObject()
				row.Set("ID", id)
				row.Set("Email", session.Email)
				row.Set("Endpoint", session.Endpoint)
				row.Set("Active", id == current)
				rows = append(rows, row)
			}

			report := jsonx.NewObject()
			report.Set("sessions", rows)

			return app.Render(report)
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

			// Revoked at the server first. Deleting the local entry alone
			// left the credential working until it expired on its own.
			result := logoutSessions(global, targets)
			if err := global.Write(); err != nil {
				return err
			}

			if len(result.SignedOut) > 0 {
				command.Printf("Signed out of %d session(s).\n", len(result.SignedOut))
			}
			if len(result.Failed) > 0 {
				// Kept, not removed: a live server session with no local record
				// of it is the one state the user cannot recover from.
				return fmt.Errorf(
					"could not sign out of %d session(s), which are still stored: %s",
					len(result.Failed), strings.Join(result.Errors, "; "))
			}

			return nil
		},
	}
}

// registerSessionCommands attaches the commands that do not come from the spec.
func registerSessionCommands(root *cobra.Command) {
	root.AddCommand(newWhoamiCommand())
	root.AddCommand(newSessionsCommand())
	root.AddCommand(newClientCommand())
	root.AddCommand(newUpdateCommand())
	root.AddCommand(newLoginCommand())
	root.AddCommand(newLogoutCommand())
	root.AddCommand(newTypesCommand())
	root.AddCommand(newGenerateCommand())
	root.AddCommand(newRunCommand())
	root.AddCommand(newInitCommand())
	root.AddCommand(newPullCommand())
	root.AddCommand(newPushCommand())
}

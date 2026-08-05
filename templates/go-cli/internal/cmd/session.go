package cmd

import (
	"errors"
	"fmt"
	"io"
	"runtime"
	"strings"

	"github.com/charmbracelet/lipgloss"
	"github.com/spf13/cobra"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/auth"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
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
// The access token is refreshed first when it is expired
// or within a minute of expiring.
func consoleClient() (*client.Client, *config.Global, error) {
	return consoleClientAt("")
}

// consoleClientAt is consoleClient against a given endpoint.
//
// The
// session's endpoint is the region-less one -- login normalises it, so a Cloud
// session is stored as cloud.appwrite.io however the user reached it -- and a
// resource that lives in ONE region is not there. The screenshot bucket is the
// case: the file exists in nyc.cloud.appwrite.io and the region-less console
// answers "The requested file could not be found."
//
// An empty endpoint keeps the session's, which is what console-wide routes such
// as /account and /projects want.
func consoleClientAt(override string) (*client.Client, *config.Global, error) {
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
	if override != "" {
		endpoint = override
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
			// A banner rather than a field, the way `vercel whoami` opens with
			// "Vercel CLI 58.4.4 (Node.js 22.19.0)". Dim, because it is context
			// for the answer rather than part of it.
			//
			// To stderr when the output is being parsed -- same rule as the
			// update notice, so `whoami --json | jq` still gets only JSON.
			fmt.Fprintln(bannerWriter(command), bannerStyle.Render(fmt.Sprintf(
				"Appwrite CLI %s (Go %s, %s/%s)", app.Version,
				strings.TrimPrefix(runtime.Version(), "go"),
				runtime.GOOS, runtime.GOARCH)))

			report := jsonx.NewObject()
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

// bannerStyle is the dim grey the CLI uses for context lines.
var bannerStyle = lipgloss.NewStyle().Faint(true)

// bannerWriter is stderr whenever stdout is being parsed.
//
// The banner is context, not data. On stdout under --json it would land inside
// the document and break `whoami --json | jq`.
func bannerWriter(command *cobra.Command) io.Writer {
	if app.Flags().JSON || app.Flags().Raw {
		return command.ErrOrStderr()
	}

	return command.OutOrStdout()
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

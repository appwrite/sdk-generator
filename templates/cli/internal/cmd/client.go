package cmd

import (
	"fmt"
	"io"
	"net/url"
	"os"
	"strconv"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
	"github.com/spf13/cobra"
)

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
				// Same server-side revocation as `logout`: resetting the
				// configuration is how someone signs out of everything, and
				// dropping the entries alone left every session live.
				result := logoutSessions(global, global.SessionIDs())
				if err := global.Write(); err != nil {
					return err
				}
				if len(result.Failed) > 0 {
					return fmt.Errorf(
						"could not sign out of %d session(s), which are still stored: %s",
						len(result.Failed), strings.Join(result.Errors, "; "))
				}
				command.Println("Configuration reset.")

				return nil
			}

			if debug {
				return printClientDebug(command, global)
			}

			// Captured before anything below can change it: the endpoint
			// branch decides what to do by comparing against the session that
			// was current when the command started.
			previous := global.CurrentSessionID()

			// Applied BEFORE the endpoint is verified. `client --endpoint
			// https://self-hosted/v1 --self-signed true` is one invocation, and
			// verifying the endpoint under the stored setting rather than the
			// given one rejected the certificate the user had just said to
			// accept -- so the first command someone with a self-signed
			// certificate runs could never succeed.
			trustSelfSigned := global.CurrentBool(config.PreferenceSelfSigned)
			if flags.Changed("self-signed") {
				parsed, err := strconv.ParseBool(selfSigned)
				if err != nil {
					return err
				}
				trustSelfSigned = parsed
			}

			if flags.Changed("endpoint") {
				// Checked before it is stored. Saving an unreachable endpoint
				// silently made every later command fail somewhere less
				// obvious than the typo that caused it, and the TypeScript
				// saves nothing at all when the check fails.
				if err := verifyEndpoint(endpoint, trustSelfSigned); err != nil {
					return err
				}
				selectSessionForEndpoint(command, global, previous, endpoint)
			}

			// `client --key` configures a session that may not exist yet --
			// this is the path someone takes instead of `login`. Created after
			// the endpoint branch, which makes its own session when it needs
			// one, so the two cannot both add a session for the same run.
			if global.CurrentSessionID() == "" {
				global.AddSession("default", config.NewObject())
			}
			// The project belongs to the DIRECTORY, not to the session: it is
			// the same value `init project` and `pull` write, and the same one
			// every project-scoped command reads. Writing it into the global
			// preferences instead left the flag inert -- `client --project-id X`
			// followed by any command answered "project is not set".
			if flags.Changed("project-id") {
				if err := setLocalProject(command, global, projectID); err != nil {
					return err
				}
			}
			if flags.Changed("key") {
				global.SetCurrentValue(config.PreferenceKey, key)
			}
			if flags.Changed("self-signed") {
				global.SetCurrentValue(config.PreferenceSelfSigned, trustSelfSigned)
			}

			if err := global.Write(); err != nil {
				return err
			}

			output.Success(command.OutOrStdout(), "Client configuration updated")

			return nil
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

// selectSessionForEndpoint points the CLI at the stored session belonging to a
// newly configured endpoint. Setting the endpoint on whatever session happened
// to be current re-pointed a signed-in session at a different instance, sending
// one server's credentials to another.
func selectSessionForEndpoint(
	command *cobra.Command,
	global *config.Global,
	previous string,
	endpoint string,
) {
	out := command.OutOrStdout()
	authenticated, endpointOnly := findSessionForEndpoint(global, endpoint)

	switch {
	// Already on the best session for this endpoint. Rewriting the value keeps
	// a regional host as the user typed it.
	case previous != "" &&
		config.EndpointsMatch(sessionEndpoint(global, previous), endpoint) &&
		(isAuthenticatedSession(global, previous) || authenticated == ""):

	case authenticated != "":
		global.SetCurrentSessionID(authenticated)
		if email := sessionEmail(global, authenticated); email != "" {
			output.Log(out, "Using signed-in account %s", email)
		}

	case endpointOnly != "":
		global.SetCurrentSessionID(endpointOnly)
		warnDetachedSession(out, global, previous)

	// An endpoint-only stub is repointed rather than multiplied.
	case previous != "" && !isAuthenticatedSession(global, previous):

	default:
		id := strconv.FormatInt(time.Now().UnixMilli(), 10)
		global.AddSession(id, config.NewObject())
		global.SetCurrentSessionID(id)
		warnDetachedSession(out, global, previous)
	}

	global.SetCurrentValue(config.PreferenceEndpoint, endpoint)
}

// findSessionForEndpoint returns the first signed-in session for an endpoint
// and the first endpoint-only one.
func findSessionForEndpoint(global *config.Global, endpoint string) (string, string) {
	authenticated, endpointOnly := "", ""

	for _, id := range global.SessionIDs() {
		stored := sessionEndpoint(global, id)
		if stored == "" || !config.EndpointsMatch(stored, endpoint) {
			continue
		}

		if isAuthenticatedSession(global, id) {
			if authenticated == "" {
				authenticated = id
			}

			continue
		}
		if endpointOnly == "" {
			endpointOnly = id
		}
	}

	return authenticated, endpointOnly
}

// isAuthenticatedSession reports whether a session carries login credentials
// rather than just an endpoint.
func isAuthenticatedSession(global *config.Global, id string) bool {
	session := global.SessionData(id)
	if session == nil {
		return false
	}

	return session.GetString(config.PreferenceAccessToken) != "" ||
		session.GetString(config.PreferenceCookie) != ""
}

func sessionEndpoint(global *config.Global, id string) string {
	session := global.SessionData(id)
	if session == nil {
		return ""
	}

	return session.GetString(config.PreferenceEndpoint)
}

func sessionEmail(global *config.Global, id string) string {
	session := global.SessionData(id)
	if session == nil {
		return ""
	}

	return session.GetString(config.PreferenceEmail)
}

// warnDetachedSession says that a signed-in account is still stored but is no
// longer the active one, so the user is not left wondering where it went.
func warnDetachedSession(out io.Writer, global *config.Global, previous string) {
	if previous == "" || !isAuthenticatedSession(global, previous) {
		return
	}

	email := ""
	if address := sessionEmail(global, previous); address != "" {
		email = " (" + address + ")"
	}

	output.Warn(out, "Signed-in account%s is still available but no longer "+
		"active. Run `%s login --switch` to return to it.", email, app.ExecutableName)
}

// setLocalProject records the project in appwrite.config.json. An existing
// config anywhere up the tree is updated in place rather than shadowed by a new
// one in the working directory.
//
// It also pins the project's regional endpoint, which the TypeScript does not:
// on Cloud a project is reachable only through its own region's host, so naming
// a project in another region and leaving the endpoint alone produced "Project
// is not accessible in this region" from the next command. `init project`
// already does this.
func setLocalProject(command *cobra.Command, global *config.Global, projectID string) error {
	local, err := config.LoadOrCreateLocal(config.FindLocalPath())
	if err != nil {
		return err
	}

	local.SetProject(projectID, "")

	// Regions only have hostnames of their own on Cloud; everywhere else one
	// endpoint serves every project, so there is nothing to look up and no
	// reason to spend a request finding that out.
	session := global.Current()
	if session != nil {
		endpoint := session.GetString(config.PreferenceEndpoint)
		if endpoint != "" && isCloudEndpoint(endpoint) {
			if api, _, err := consoleClient(); err == nil {
				if regional := regionalEndpointForProject(
					command.OutOrStdout(), api, endpoint, projectID,
				); regional != "" {
					local.SetEndpoint(regional)
				}
			}
		}
	}

	return local.Write()
}

// regionalEndpointForProject asks the API which region a project is in and
// returns the endpoint that serves it, or "" to leave the endpoint alone.
//
// Every failure returns "" rather than an error: setting the project is the job
// the user asked for, and it has to keep working offline and for a project id
// that does not exist yet.
func regionalEndpointForProject(
	out io.Writer,
	api *client.Client,
	endpoint string,
	projectID string,
) string {
	region, err := fetchProjectRegion(api, projectID)
	if err != nil || region == "" {
		return ""
	}

	regional := regionalEndpoint(endpoint, region)
	if strings.EqualFold(strings.TrimSuffix(regional, "/"),
		strings.TrimSuffix(endpoint, "/")) {
		// Already pointing at the region that serves it, so say nothing.
		return ""
	}

	output.Log(out, "Project %s is in %s, so this directory will use %s.",
		projectID, region, regional)

	return regional
}

// fetchProjectRegion reads the region off a project.
//
// `GET /projects/{projectId}` is not in the spec, so the call is issued by hand
// -- the same console-scoped read fetchOrganizationForProject uses, and for the
// same reason it must not carry an organization header: the caller has a project
// id and nothing else.
func fetchProjectRegion(api *client.Client, projectID string) (string, error) {
	var project jsonx.Object
	err := api.Clone().WithoutResponseFormat().Call(
		"GET", "/projects/"+url.PathEscape(projectID), nil, &project)
	if err != nil {
		return "", err
	}

	return project.GetString("region"), nil
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

	// The project and organization come from the PROJECT CONFIG, which is where
	// they live. Reading them off the global preferences reported an empty
	// projectId while sitting in a directory whose config named one -- the
	// least useful thing a diagnostic can do.
	projectID, projectName, organizationID := "", "", ""
	if local, err := config.LoadLocal(config.FindLocalPath()); err == nil {
		projectID = local.Data.GetString("projectId")
		projectName = local.Data.GetString("projectName")
		organizationID = local.Data.GetString("organizationId")
	}
	if environment := os.Getenv(sdk.EnvProjectID); environment != "" {
		projectID = environment
	}
	if environment := os.Getenv(sdk.EnvOrganizationID); environment != "" {
		organizationID = environment
	}

	// Rendered rather than printed, so --json and --raw work here as they do
	// everywhere else and the redaction hint comes from the same place.
	report := jsonx.NewObject()
	report.Set("endpoint", global.CurrentValue(config.PreferenceEndpoint))
	report.Set("key", mask(global.CurrentValue(config.PreferenceKey)))
	report.Set("accessToken", mask(global.CurrentValue(config.PreferenceAccessToken)))
	// A real boolean, not the string form: the renderer drops blank strings, so
	// an unset selfSigned would vanish from the report rather than read false.
	selfSignedValue := false
	if session := global.Current(); session != nil {
		if raw, ok := session.Get(config.PreferenceSelfSigned); ok {
			selfSignedValue, _ = raw.(bool)
		}
	}
	report.Set("selfSigned", selfSignedValue)
	report.Set("organizationId", organizationID)
	report.Set("projectId", projectID)
	report.Set("projectName", projectName)

	return app.Render(report)
}

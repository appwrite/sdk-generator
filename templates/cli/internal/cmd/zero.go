//go:build !browser

package cmd

import (
	"errors"
	"fmt"
	"io"
	"math"
	"net/url"
	"time"

	"github.com/spf13/cobra"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
)

// Appwrite Zero hands out a project without signup. The API key it returns is
// stored as its own session, the way `client --key` stores one, so every
// project command works against it without `login`. The claim code stays in
// prefs.json, never in appwrite.config.json, which is often committed.
//
// Until the project is claimed its domains open only for the owner token: a
// browser visits each domain's owner link once, an agent sends the token in
// the X-Appwrite-Zero-Token header.

const (
	pathZero  = "/zero"
	pathRules = "/proxy/rules"

	// zeroOwnerPath is where a domain of an unclaimed Zero takes the owner token.
	zeroOwnerPath = "/__appwrite/zero?token="

	// preferenceZero is the session field holding what `zero status` needs.
	preferenceZero = "zero"

	zeroSessionPrefix = "zero-"

	zeroUnavailable = "zero_unavailable"
	zeroDisabled    = "zero_disabled"
	zeroNotFound    = "zero_not_found"
	zeroActive      = "active"
	zeroExpired     = "expired"
)

func newZeroCommand() *cobra.Command {
	var endpoint string

	command := &cobra.Command{
		Use:   "zero",
		Short: "Get a working project instantly, no signup",
		Long: "Create an Appwrite project without an account and link this directory to it.\n\n" +
			"The project is yours to build on for a limited time. Claim it with the printed " +
			"link to keep it; unclaimed projects are deleted. Until then its domains open only " +
			"for you: visit a domain's owner link once, or send the access token in the " +
			"X-Appwrite-Zero-Token header. --json prints the manifest, including the claim " +
			"link and the access token, for agents to hand back.",
		Args: cobra.NoArgs,
		RunE: func(command *cobra.Command, args []string) error {
			return runZero(command, endpoint)
		},
	}

	command.Flags().StringVarP(&endpoint, "endpoint", "e", "",
		"Appwrite endpoint to create the project on (default "+config.DefaultEndpoint+")")
	command.AddCommand(newZeroStatusCommand())

	return command
}

func newZeroStatusCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "status",
		Short: "Show the time left to build on and claim this directory's Zero project",
		Args:  cobra.NoArgs,
		RunE: func(command *cobra.Command, args []string) error {
			return runZeroStatus(command)
		},
	}
}

func runZero(command *cobra.Command, endpoint string) error {
	if endpoint == "" {
		endpoint = osGetenv(sdk.EnvEndpoint)
	}
	// Used as given: a Zero project is created in the region of the server
	// that receives the request, so a regional endpoint must not be collapsed
	// onto the base host.
	if endpoint == "" {
		endpoint = config.DefaultEndpoint
	}

	// Loaded before the project is created, so a config that cannot be read
	// does not spend one of the day's few Zero projects.
	local, err := config.LoadOrCreateLocal(config.LocalPathOr(config.LocalPath(".")))
	if err != nil {
		return err
	}
	global, err := preferences()
	if err != nil {
		return err
	}

	// No session or cookie: the API refuses a console session here.
	var zero jsonx.Object
	err = client.New(endpoint, app.Version).
		WithoutResponseFormat().
		SetProject(config.ProjectConsole).
		SetLocale("en-US").
		Call("POST", pathZero, map[string]any{"source": "cli"}, &zero)
	if err != nil {
		return zeroError(err, endpoint)
	}

	projectID := zero.GetString("projectId")
	projectEndpoint := zero.GetString("endpoint")
	if projectEndpoint == "" {
		projectEndpoint = endpoint
	}

	stored := config.NewObject()
	for _, key := range []string{"$id", "secret", "projectId", "consoleUrl", "claimUrl", "accessToken", "expiresAt", "claimBy"} {
		stored.Set(key, zero.GetString(key))
	}
	session := config.NewObject()
	session.Set(config.PreferenceEndpoint, projectEndpoint)
	session.Set(config.PreferenceKey, zero.GetString("apiKey"))
	session.Set(preferenceZero, stored)

	previous := global.CurrentSessionID()
	global.AddSession(zeroSessionPrefix+projectID, session)
	if err := global.Write(); err != nil {
		return err
	}

	// Resource definitions are kept, so an existing config pushes straight
	// into the new project.
	messages := bannerWriter(command)
	if linked := local.Data.GetString("projectId"); linked != "" && linked != projectID {
		output.Warn(messages, "This directory was linked to project %s and now uses the Zero project.", linked)
	}
	local.SetProject(projectID, "")
	local.Data.Delete("projectName")
	local.SetOrganizationID(zero.GetString("organizationId"))
	local.SetEndpoint(projectEndpoint)
	if err := local.Write(); err != nil {
		return err
	}

	warnDetachedSession(messages, global, previous)

	if app.Flags().JSON || app.Flags().Raw {
		return renderZeroManifest(command.OutOrStdout(), &zero)
	}

	printZeroWelcome(command.OutOrStdout(), &zero, local.Path())

	return nil
}

// renderZeroManifest prints the manifest an agent hands back. The owner token
// is its point, and the welcome already prints it in each owner link, so the
// redactor must not mask it. The API key and the claim secret are kept in
// prefs.json for the CLI's own calls and leave it only with --show-secrets.
func renderZeroManifest(out io.Writer, zero *jsonx.Object) error {
	if !app.Flags().ShowSecrets {
		zero.Delete("apiKey")
		zero.Delete("secret")
	}

	renderer := app.Renderer()
	renderer.ShowSecrets = true
	renderer.Writer = out

	return renderer.Render(zero)
}

func printZeroWelcome(out io.Writer, zero *jsonx.Object, path string) {
	now := time.Now()
	name := app.ExecutableName

	output.Success(out, "Your Appwrite Zero project is live → %s", path)
	fmt.Fprintln(out)
	fmt.Fprintf(out, "  Project    %s\n", zero.GetString("projectId"))
	fmt.Fprintf(out, "  Endpoint   %s\n", zero.GetString("endpoint"))
	fmt.Fprintf(out, "  Console    %s\n", zero.GetString("consoleUrl"))
	fmt.Fprintln(out)
	fmt.Fprintf(out, "  You have %s to build. Claim it within %s to keep it:\n",
		timeLeft(zero.GetString("expiresAt"), now), timeLeft(zero.GetString("claimBy"), now))
	fmt.Fprintf(out, "    %s\n", zero.GetString("claimUrl"))
	fmt.Fprintln(out)
	fmt.Fprintln(out, "  Until then its domains open only for you, through each domain's owner link:")
	fmt.Fprintf(out, "    https://<domain>%s%s\n", zeroOwnerPath, zero.GetString("accessToken"))
	fmt.Fprintln(out)
	fmt.Fprintln(out, output.Heading("  Next steps"))
	fmt.Fprintf(out, "    %s tablesdb list       Call the project with its API key\n", name)
	fmt.Fprintf(out, "    %s push                Deploy this directory's resources\n", name)
	fmt.Fprintf(out, "    %s zero status         Check the time left and get each domain's owner link\n", name)
}

func runZeroStatus(command *cobra.Command) error {
	global, err := preferences()
	if err != nil {
		return err
	}

	session := zeroSession(global)
	if session == nil {
		return fmt.Errorf("no Appwrite Zero project found. Run `%s zero` to create one", app.ExecutableName)
	}
	stored := session.GetObject(preferenceZero)

	var zero jsonx.Object
	path := pathZero + "/" + url.PathEscape(stored.GetString("$id")) +
		"?secret=" + url.QueryEscape(stored.GetString("secret"))
	err = client.New(session.GetString(config.PreferenceEndpoint), app.Version).
		WithoutResponseFormat().
		SetProject(config.ProjectConsole).
		SetLocale("en-US").
		Call("GET", path, nil, &zero)
	var apiError *client.APIError
	if errors.As(err, &apiError) && apiError.Type == zeroNotFound {
		return zeroGone(global, session)
	}
	if err != nil {
		return err
	}

	now := time.Now()
	status := zero.GetString("status")
	report := jsonx.NewObject()
	report.Set("projectId", zero.GetString("projectId"))
	report.Set("status", status)
	report.Set("endpoint", zero.GetString("endpoint"))
	report.Set("consoleUrl", zero.GetString("consoleUrl"))
	report.Set("buildTimeLeft", timeLeft(zero.GetString("expiresAt"), now))
	report.Set("claimTimeLeft", timeLeft(zero.GetString("claimBy"), now))
	report.Set("claimUrl", stored.GetString("claimUrl"))
	// An expired project refuses API calls with zero_expired until it is
	// claimed, so its rules cannot be listed.
	if status == zeroActive {
		links, err := zeroOwnerLinks(session, zero.GetString("projectId"), stored.GetString("accessToken"))
		if err != nil {
			return err
		}
		report.Set("ownerLinks", links)
	}
	report.Set("expiresAt", zero.GetString("expiresAt"))
	report.Set("claimBy", zero.GetString("claimBy"))

	return app.RenderValue(report)
}

// zeroGone handles a project the API no longer knows as unclaimed: it answers
// zero_not_found alike for claimed, deleted, unknown and wrong-secret, so the
// CLI cannot tell which. The claim secret and owner token are dropped, since
// nothing accepts them any more. The endpoint and API key stay, because a
// claimed project may still answer to its key from this directory.
func zeroGone(global *config.Global, session *config.Object) error {
	stored := session.GetObject(preferenceZero)
	session.Delete(preferenceZero)
	if err := global.Write(); err != nil {
		return err
	}

	message := fmt.Sprintf("Appwrite Zero project %s is no longer unclaimed: it was claimed or deleted",
		stored.GetString("projectId"))
	if console := stored.GetString("consoleUrl"); console != "" {
		message += ". If you claimed it, open it at " + console
	}

	return errors.New(message)
}

// zeroOwnerLinks lists the link that opens each of the project's domains for
// the owner while the project is unclaimed.
func zeroOwnerLinks(session *config.Object, projectID, token string) ([]string, error) {
	links := []string{}
	if token == "" {
		return links, nil
	}

	var rules jsonx.Object
	err := client.New(session.GetString(config.PreferenceEndpoint), app.Version).
		WithoutResponseFormat().
		SetProject(projectID).
		SetKey(session.GetString(config.PreferenceKey)).
		Call("GET", pathRules, nil, &rules)
	if err != nil {
		return nil, err
	}

	for _, rule := range rules.GetObjects("rules") {
		links = append(links, "https://"+rule.GetString("domain")+zeroOwnerPath+url.QueryEscape(token))
	}

	return links, nil
}

// zeroSession finds the Zero session for this directory's project, falling
// back to the active session.
func zeroSession(global *config.Global) *config.Object {
	if local, err := config.LoadLocal(config.FindLocalPath()); err == nil {
		session := global.SessionData(zeroSessionPrefix + local.Data.GetString("projectId"))
		if session != nil && session.GetObject(preferenceZero) != nil {
			return session
		}
	}

	if session := global.Current(); session != nil && session.GetObject(preferenceZero) != nil {
		return session
	}

	return nil
}

func zeroError(err error, endpoint string) error {
	var apiError *client.APIError
	if !errors.As(err, &apiError) {
		return err
	}

	signup := consoleBaseURL(endpoint) + "/console/register"

	switch {
	case apiError.Type == zeroUnavailable:
		return fmt.Errorf("Appwrite Zero is busy, try again shortly or sign up: %s", signup)
	case apiError.Status == 429:
		return fmt.Errorf("Appwrite Zero's daily limit for this network is reached. Sign up to keep building: %s", signup)
	case apiError.Status == 404 && apiError.Type != zeroDisabled:
		return fmt.Errorf("Appwrite Zero is not available on %s", endpoint)
	}

	return err
}

// timeLeft renders the time until an ISO 8601 deadline, such as "60 min".
func timeLeft(deadline string, now time.Time) string {
	parsed, err := time.Parse(time.RFC3339, deadline)
	if err != nil {
		return ""
	}

	remaining := parsed.Sub(now).Round(time.Second)
	switch {
	case remaining <= 0:
		return ""
	case remaining >= time.Hour:
		return fmt.Sprintf("%dh", int(math.Round(remaining.Hours())))
	case remaining >= time.Minute:
		return fmt.Sprintf("%d min", int(math.Round(remaining.Minutes())))
	}

	return fmt.Sprintf("%ds", int(remaining.Seconds()))
}

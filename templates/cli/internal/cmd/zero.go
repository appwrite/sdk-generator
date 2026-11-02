//go:build !browser

package cmd

import (
	"errors"
	"fmt"
	"io"
	"math"
	"net/url"
	"os"
	"runtime"
	"strconv"
	"strings"
	"time"

	"github.com/spf13/cobra"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
)

// Appwrite Zero hands out a project without signup. The API key it returns is
// stored as its own session, the way `client --key` stores one, so every
// project command works against it without `login`. The claim code stays in
// prefs.json, never in appwrite.config.json, which is often committed.
//
// Until the project is claimed its sites open to anyone under a banner saying
// so. Deployment previews ask for a console session, which the owner token
// stands in for: a browser visits a domain's owner link once, an agent sends
// the token in the X-Appwrite-Zero-Token header.

const (
	pathZero   = "/zero"
	pathRules  = "/proxy/rules"
	pathTokens = "/tokens"

	// zeroOwnerPath is where a domain of an unclaimed Zero takes the owner token,
	// which opens its deployment previews without a console session.
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
	var endpoint, region string

	command := &cobra.Command{
		Use:   "zero [directory]",
		Short: "Get a working project instantly, no signup",
		Long: "Create an Appwrite project without an account and link this directory to it, " +
			"or a new one when a directory is given, such as `zero my-app`.\n\n" +
			"The project is yours to build on for a limited time. Claim it with the printed " +
			"link to keep it; unclaimed projects are deleted. Until then its sites are public " +
			"under a banner saying the project is unclaimed. Deployment previews open for " +
			"you through a domain's owner link, or the access token in the " +
			"X-Appwrite-Zero-Token header. --json prints the manifest, including the claim " +
			"link and the access token, for agents to hand back.\n\n" +
			"The project is created in the Cloud region closest to you. --region picks one, " +
			"such as `zero --region fra`; it cannot be combined with --endpoint.",
		Args: cobra.MaximumNArgs(1),
		RunE: func(command *cobra.Command, args []string) error {
			directory := ""
			if len(args) > 0 {
				directory = args[0]
			}

			return runZero(command, endpoint, region, directory)
		},
	}

	command.Flags().StringVarP(&endpoint, "endpoint", "e", "",
		"Appwrite endpoint to create the project on (default "+config.DefaultEndpoint+")")
	command.Flags().StringVar(&region, "region", "",
		"Cloud region to create the project in: "+strings.Join(config.CloudRegions(), ", "))
	command.AddCommand(newZeroStatusCommand())
	command.AddCommand(newZeroConsoleCommand())
	command.AddCommand(newZeroListCommand())
	command.AddCommand(newZeroDeleteCommand())

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

func newZeroConsoleCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "console",
		Short: "Open this directory's Zero project in the console",
		Long: "Sign in to the console as the owner of this directory's unclaimed Zero project " +
			"and open it. The link works once, for a few minutes. --json prints it without " +
			"opening a browser, for agents to hand back.",
		Args: cobra.NoArgs,
		RunE: func(command *cobra.Command, args []string) error {
			return runZeroConsole(command, openBrowser)
		},
	}
}

func newZeroListCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "list",
		Short: "List the Zero projects created on this machine",
		Long: "List the Zero projects this machine holds the claim codes for, with the time " +
			"left on each and its claim link. Read from the stored sessions, without calling the API.",
		Args: cobra.NoArgs,
		RunE: func(command *cobra.Command, args []string) error {
			return runZeroList(command)
		},
	}
}

func newZeroDeleteCommand() *cobra.Command {
	return &cobra.Command{
		Use:   "delete",
		Short: "Delete the Zero project linked to this directory now",
		Long: "Delete this directory's unclaimed Zero project instead of waiting for its claim " +
			"window to close, and forget its session. It cannot be undone. Asks first; --force " +
			"skips the question, which is how an agent or a script runs it.",
		Args: cobra.NoArgs,
		RunE: func(command *cobra.Command, args []string) error {
			return runZeroDelete(command)
		},
	}
}

func runZero(command *cobra.Command, endpoint, region, directory string) error {
	endpoint, err := zeroEndpoint(endpoint, region)
	if err != nil {
		return err
	}

	path := config.LocalPathOr(config.LocalPath("."))
	if directory != "" {
		if config.LocalFile != "" {
			return errors.New("--config and a directory both choose where the project is linked; pass only one")
		}
		if err := os.MkdirAll(directory, 0o755); err != nil {
			return err
		}
		path = config.LocalPath(directory)
	}

	// Loaded before the project is created, so a config that cannot be read
	// does not spend one of the day's few Zero projects.
	local, err := config.LoadOrCreateLocal(path)
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

	messages := bannerWriter(command)
	previous := global.CurrentSessionID()
	global.AddSession(zeroSessionPrefix+projectID, session)
	if err := global.Write(); err != nil {
		// The project exists, and this response is the only copy of its claim
		// link and key, so they are shown before the error.
		output.Warn(messages, "Appwrite Zero project %s was created but could not be saved. Keep these, they are not stored anywhere else:", projectID)
		fmt.Fprintf(messages, "  Claim link  %s\n", zero.GetString("claimUrl"))
		fmt.Fprintf(messages, "  Endpoint    %s\n", projectEndpoint)
		fmt.Fprintf(messages, "  API key     %s\n", zero.GetString("apiKey"))

		return err
	}

	// Resource definitions are kept, so an existing config pushes straight
	// into the new project.
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

	printZeroWelcome(command.OutOrStdout(), &zero, local.Path(), directory)

	return nil
}

// zeroEndpoint picks the host to create the project on. The API takes no
// region: a Zero project is created in the region of the server that receives
// the request, so --region selects that region's host, and an endpoint is used
// as given rather than collapsed onto the base host. With neither, the project
// goes to the nearest region's host: the base host would leave every user in
// Frankfurt, and store an endpoint that is not the project's region.
func zeroEndpoint(endpoint, region string) (string, error) {
	if region != "" && endpoint != "" {
		return "", errors.New("--region and --endpoint both choose where the project is created; pass only one")
	}
	if region != "" && !config.IsCloudRegion(region) {
		return "", fmt.Errorf("unknown region %q, expected one of: %s",
			region, strings.Join(config.CloudRegions(), ", "))
	}

	if endpoint == "" {
		endpoint = osGetenv(sdk.EnvEndpoint)
	}
	if endpoint == "" {
		endpoint = config.DefaultEndpoint
		if region == "" {
			region = nearestRegion(endpoint)
		}
	}
	if region == "" {
		return endpoint, nil
	}
	if !isCloudEndpoint(endpoint) {
		return "", fmt.Errorf("--region needs an Appwrite Cloud endpoint, not %s", endpoint)
	}

	return regionalEndpoint(endpoint, region), nil
}

// nearestRegion picks the region for a project nobody placed. Cloud answers
// GET /locale for anyone, from the caller's IP, including the time zone that
// splits the US between coasts; a remote machine often runs on UTC. When that
// fails, the local time zone still places most users and Frankfurt takes the
// rest.
func nearestRegion(endpoint string) string {
	var locale jsonx.Object
	_ = client.New(endpoint, app.Version).
		WithoutResponseFormat().
		SetProject(config.ProjectConsole).
		Call("GET", "/locale", nil, &locale)

	timeZone := locale.GetString("timeZone")
	if timeZone == "" {
		timeZone = localTimeZone()
	}

	return config.SuggestRegion(locale.GetString("countryCode"), locale.GetString("continentCode"),
		timeZone, config.CloudRegions())
}

// localTimeZone is the IANA name of the machine's zone, from TZ or the
// /etc/localtime link macOS and Linux keep; Go's time.Local only says "Local".
func localTimeZone() string {
	zone := strings.TrimPrefix(osGetenv("TZ"), ":")
	if zone == "" {
		zone, _ = os.Readlink("/etc/localtime")
	}
	if _, name, found := strings.Cut(zone, "zoneinfo/"); found {
		return name
	}
	if strings.HasPrefix(zone, "/") {
		return ""
	}

	return zone
}

// renderZeroManifest prints what an agent hands back: the manifest, whose point
// is the owner token the welcome already prints in each owner link, or the
// console sign-in link. The redactor must not mask those. The API key, the
// claim secret and the sign-in token's secret leave only with --show-secrets.
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

func printZeroWelcome(out io.Writer, zero *jsonx.Object, path, directory string) {
	now := time.Now()
	name := app.ExecutableName

	output.Success(out, "Your Appwrite Zero project is live → %s", path)
	fmt.Fprintln(out)
	fmt.Fprintf(out, "  Project    %s\n", zero.GetString("projectId"))
	fmt.Fprintf(out, "  Endpoint   %s\n", zero.GetString("endpoint"))
	fmt.Fprintln(out)
	fmt.Fprintf(out, "  You have %s to build. Claim it within %s to keep it:\n",
		timeLeft(zero.GetString("expiresAt"), now), timeLeft(zero.GetString("claimBy"), now))
	fmt.Fprintf(out, "    %s\n", zero.GetString("claimUrl"))
	fmt.Fprintln(out)
	fmt.Fprintln(out, "  Open it in the console, signed in:")
	fmt.Fprintf(out, "    %s zero console\n", name)
	fmt.Fprintln(out)
	fmt.Fprintln(out, output.Heading("  Next steps"))
	if directory != "" {
		fmt.Fprintf(out, "    %s\n", cdCommand(directory, runtime.GOOS))
	}
	fmt.Fprintf(out, "    %s tablesdb list       Call the project with its API key\n", name)
	fmt.Fprintf(out, "    %s push                Deploy this directory's resources\n", name)
	fmt.Fprintf(out, "    %s zero status         Check the time left and get the private links to your sites and functions\n", name)
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
		return zeroGone(session)
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
	// The console URL opens only for a signed-in owner, which an unclaimed
	// project has none of, so a person is pointed at `zero console` instead.
	machine := app.Flags().JSON || app.Flags().Raw
	if machine {
		report.Set("consoleUrl", zero.GetString("consoleUrl"))
	}
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

	if err := app.RenderValue(report); err != nil {
		return err
	}
	if !machine {
		output.Hint(command.OutOrStdout(), "Run `%s zero console` to open the project in the console", app.ExecutableName)
	}

	return nil
}

// runZeroConsole signs the browser in as the project's anonymous owner, whose
// console URL alone opens nothing. The link is single use, so --json prints it
// unopened for whoever it is handed to.
func runZeroConsole(command *cobra.Command, open func(string)) error {
	global, err := preferences()
	if err != nil {
		return err
	}

	session := zeroSession(global)
	if session == nil {
		return fmt.Errorf("no Appwrite Zero project found. Run `%s zero` to create one", app.ExecutableName)
	}
	stored := session.GetObject(preferenceZero)

	var token jsonx.Object
	path := pathZero + "/" + url.PathEscape(stored.GetString("$id")) + pathTokens
	err = client.New(session.GetString(config.PreferenceEndpoint), app.Version).
		WithoutResponseFormat().
		SetProject(config.ProjectConsole).
		SetLocale("en-US").
		Call("POST", path, map[string]any{"secret": stored.GetString("secret")}, &token)
	var apiError *client.APIError
	if errors.As(err, &apiError) && apiError.Type == zeroNotFound {
		return zeroGone(session)
	}
	if err != nil {
		return err
	}

	if app.Flags().JSON || app.Flags().Raw {
		return renderZeroManifest(command.OutOrStdout(), &token)
	}

	link := token.GetString("url")
	open(link)

	out := command.OutOrStdout()
	output.Success(out, "Opening Appwrite Zero project %s in the console", stored.GetString("projectId"))
	fmt.Fprintln(out)
	within := ""
	if left := timeLeft(token.GetString("expire"), time.Now()); left != "" {
		within = " within " + left
	}
	fmt.Fprintf(out, "  If the browser did not open, this link signs you in once%s:\n", within)
	fmt.Fprintf(out, "    %s\n", link)

	return nil
}

// runZeroList reads the stored sessions only: each holds the deadlines the
// project was created with, which no later call moves, so listing costs no
// request and works for a project the API no longer answers for.
func runZeroList(command *cobra.Command) error {
	global, err := preferences()
	if err != nil {
		return err
	}

	linked := ""
	if local, err := config.LoadLocal(config.FindLocalPath()); err == nil {
		linked = local.Data.GetString("projectId")
	}

	now := time.Now()
	projects := []any{}
	for _, id := range global.SessionIDs() {
		session := global.SessionData(id)
		if !strings.HasPrefix(id, zeroSessionPrefix) || session == nil || session.GetObject(preferenceZero) == nil {
			continue
		}
		stored := session.GetObject(preferenceZero)

		// Past its claim window a project is claimed or deleted, which the
		// stored dates cannot tell apart.
		status := zeroActive
		switch {
		case timeLeft(stored.GetString("claimBy"), now) == "":
			status = "closed"
		case timeLeft(stored.GetString("expiresAt"), now) == "":
			status = zeroExpired
		}

		project := jsonx.NewObject()
		project.Set("projectId", stored.GetString("projectId"))
		project.Set("status", status)
		project.Set("buildTimeLeft", timeLeft(stored.GetString("expiresAt"), now))
		project.Set("claimTimeLeft", timeLeft(stored.GetString("claimBy"), now))
		project.Set("claimUrl", stored.GetString("claimUrl"))
		project.Set("endpoint", session.GetString(config.PreferenceEndpoint))
		project.Set("linked", stored.GetString("projectId") == linked)
		projects = append(projects, project)
	}

	if len(projects) == 0 && !app.Flags().JSON && !app.Flags().Raw {
		output.Log(command.OutOrStdout(), "No Appwrite Zero projects on this machine. Run `%s zero` to create one.", app.ExecutableName)

		return nil
	}

	return app.RenderValue(projects)
}

// runZeroDelete deletes the project with its claim code, which the API checks
// as it does for `zero status`, then forgets the session and unlinks the
// directory so the next `zero` starts clean.
func runZeroDelete(command *cobra.Command) error {
	global, err := preferences()
	if err != nil {
		return err
	}

	// Only the project this directory is linked to: falling back to the active
	// session, as `zero status` does, could delete an unrelated project.
	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil {
		return fmt.Errorf("no Appwrite Zero project is linked here. Run `%s zero delete` in its directory", app.ExecutableName)
	}
	projectID := local.Data.GetString("projectId")
	session := global.SessionData(zeroSessionPrefix + projectID)
	if projectID == "" || session == nil || session.GetObject(preferenceZero) == nil {
		return fmt.Errorf("this directory is not linked to an Appwrite Zero project on this machine. Run `%s zero list` to see them", app.ExecutableName)
	}
	stored := session.GetObject(preferenceZero)

	confirmed, err := prompt.New(app.Flags().Force).Confirm(prompt.Question{
		Message: fmt.Sprintf("Delete Appwrite Zero project %s? Its data, sites and functions go with it, and it cannot be undone.", projectID),
		Default: false,
		Flag:    "--force",
	})
	if err != nil {
		return err
	}
	if !confirmed {
		output.Log(command.OutOrStdout(), "Delete cancelled.")

		return nil
	}

	err = client.New(session.GetString(config.PreferenceEndpoint), app.Version).
		WithoutResponseFormat().
		SetProject(config.ProjectConsole).
		SetLocale("en-US").
		Call("DELETE", pathZero+"/"+url.PathEscape(stored.GetString("$id")), map[string]any{"secret": stored.GetString("secret")}, nil)
	var apiError *client.APIError
	if errors.As(err, &apiError) && apiError.Type == zeroNotFound {
		return zeroGone(session)
	}
	if err != nil {
		return err
	}

	global.DeleteSession(zeroSessionPrefix + projectID)
	if err := global.Write(); err != nil {
		return err
	}
	local.Data.Delete("projectId")
	if err := local.Write(); err != nil {
		return err
	}

	output.Success(command.OutOrStdout(), "Deleted Appwrite Zero project %s", projectID)

	return nil
}

// zeroGone explains zero_not_found, which the API answers alike for a claimed,
// deleted or unknown project and for a wrong secret. The session is left as it
// is: the CLI cannot tell which case this is, and a project still unclaimed
// behind a stale secret may yet be claimed with the stored link.
func zeroGone(session *config.Object) error {
	stored := session.GetObject(preferenceZero)

	message := fmt.Sprintf("Appwrite Zero project %s is no longer unclaimed: it was claimed or deleted",
		stored.GetString("projectId"))
	if console := stored.GetString("consoleUrl"); console != "" {
		message += ". If you claimed it, open it at " + console
	}

	return errors.New(message)
}

// zeroOwnerLinks lists the link that lets the owner past each of the project's
// domains' preview sign-in while the project is unclaimed.
func zeroOwnerLinks(session *config.Object, projectID, token string) ([]string, error) {
	links := []string{}
	if token == "" {
		return links, nil
	}

	api := client.New(session.GetString(config.PreferenceEndpoint), app.Version).
		WithoutResponseFormat().
		SetProject(projectID).
		SetKey(session.GetString(config.PreferenceKey))

	// Every deployment adds a preview rule, so a busy project has more than a page
	const pageSize = 100
	for offset := 0; ; offset += pageSize {
		query := url.Values{"queries[]": []string{
			`{"method":"limit","values":[` + strconv.Itoa(pageSize) + `]}`,
			`{"method":"offset","values":[` + strconv.Itoa(offset) + `]}`,
		}}
		var rules jsonx.Object
		if err := api.Call("GET", pathRules+"?"+query.Encode(), nil, &rules); err != nil {
			return nil, err
		}

		page := rules.GetObjects("rules")
		for _, rule := range page {
			links = append(links, "https://"+rule.GetString("domain")+zeroOwnerPath+url.QueryEscape(token))
		}
		if len(page) < pageSize {
			return links, nil
		}
	}
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

// cdCommand is the line a user pastes to enter a new directory: quoted for
// PowerShell on Windows, where a single quote is doubled rather than escaped,
// and for zsh or bash elsewhere.
func cdCommand(directory, goos string) string {
	if goos != "windows" {
		return "cd " + shellQuote(directory)
	}
	if !strings.ContainsAny(directory, " \t'\"`$&|;()[]{}<>#@,") {
		return "cd " + directory
	}

	return "cd '" + strings.ReplaceAll(directory, "'", "''") + "'"
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

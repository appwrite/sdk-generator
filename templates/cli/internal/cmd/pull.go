//go:build !browser

package cmd

import (
	"fmt"
	"os"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/auth"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
	"github.com/spf13/cobra"
)

// `pull bucket|team|topic|webhook` each list a resource, shape it to the config
// schema, and REPLACE the local array. Replace, not merge: the remote is the
// source of truth, so a resource deleted remotely has to disappear locally --
// which is why a removal is confirmed before the write.
//
// `pull collection` and `pull table` live in pulldatabase.go.

// flatResource describes one list-and-replace pull.
type flatResource struct {
	resourceIdentity
	// Keys is the schema order the entry is written in, or nil to store the
	// API response unfiltered.
	Keys []string
}

var flatResources = []flatResource{
	{resourceIdentity: bucketIdentity, Keys: config.BucketKeys},
	{
		resourceIdentity: teamIdentity,
		// No Keys, and that is not an oversight. pullTeams() is the one pull
		// that does NOT call filterBySchema, so a pulled team carries
		// $createdAt, $updatedAt, total and prefs into the config. Confirmed
		// against the shipping CLI on a real project. It is noise in a
		// code-reviewed file and almost certainly unintended, but `pull` output
		// is the contract -- fix it in the TypeScript, not here.
	},
	{resourceIdentity: topicIdentity, Keys: config.TopicKeys},
	{resourceIdentity: webhookIdentity, Keys: config.WebhookKeys},
}

func newPullCommand() *cobra.Command {
	command := &cobra.Command{
		Use:   "pull",
		Short: "Pull your Appwrite project resources into appwrite.config.json",
		Args:  cobra.NoArgs,
		RunE: func(command *cobra.Command, args []string) error {
			// The TypeScript's bare `pull` runs pullResources (pull.ts:1129),
			// the same picker `pull all` used to reach -- it does not print
			// help. --all takes the everything path, as it does for `push`.
			return runPull(command, pullActions(), app.Flags().All)
		},
	}

	// See newPushCommand: the -a shorthand is local so it cannot collide with a
	// subcommand's own -a.
	command.Flags().BoolVarP(
		app.Flags().AllPointer(), "all", "a", false,
		"Pull every resource in the project")

	for _, resource := range flatResources {
		command.AddCommand(newPullResourceCommand(resource))
	}
	for _, resource := range databaseResources {
		command.AddCommand(newPullDatabaseCommand(resource))
	}
	command.AddCommand(newPullAllCommand())
	command.AddCommand(newPullSettingsCommand())
	for _, resource := range codeResources {
		command.AddCommand(newPullCodeCommand(resource))
	}

	return command
}

func newPullResourceCommand(resource flatResource) *cobra.Command {
	return &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short:   "Pull " + resource.Label + " from your Appwrite project",
		RunE: func(command *cobra.Command, args []string) error {
			return runPullResource(command, resource)
		},
	}
}

func runPullResource(command *cobra.Command, resource flatResource) error {
	out := command.OutOrStdout()

	context, err := newProjectPull()
	if err != nil {
		return err
	}

	output.Log(out, "Fetching %s ...", resource.Label)

	entries, err := context.list(resource)
	if err != nil {
		return err
	}

	// A resource present locally and absent remotely is about to be deleted
	// from the user's config. That is the one destructive part of a pull, so
	// it is named and confirmed.
	removed := missingLocally(context.local.ResourceEntries(resource.ConfigKey), entries)
	if len(removed) > 0 {
		output.Warn(out,
			"The following %s exist locally but not remotely and will be removed: %s",
			resource.Label, strings.Join(removed, ", "))

		confirmed, err := prompt.New(app.Flags().Force).Confirm(prompt.Question{
			Message: "Continue?",
			Default: false,
			Flag:    "--force",
		})
		if err != nil {
			return err
		}
		if !confirmed {
			output.Log(out, "Pull cancelled.")

			return nil
		}
	}

	for _, entry := range entries {
		output.Log(out, "Pulling %s %s ...", strings.TrimSuffix(resource.Label, "s"),
			entry.GetString("name"))
	}

	context.local.ReplaceResource(resource.ConfigKey, entries)
	if err := context.local.Write(); err != nil {
		return err
	}

	output.Success(out, "Successfully pulled %d %s.", len(entries), resource.Label)

	return nil
}

// projectAPI builds a project-scoped client from internal/client.
//
// internal/sdk is the constructor for everything the generated commands do;
// this exists only because `pull` reads console-shaped list endpoints through
// the raw client, and it deliberately applies the SAME rules -- environment
// override, the project's own endpoint, and the session-endpoint guard.
func projectAPI(global *config.Global, local *config.Local) (*client.Client, error) {
	projectID := local.Data.GetString("projectId")
	if value := osGetenv(sdk.EnvProjectID); value != "" {
		projectID = value
	}
	if projectID == "" {
		return nil, fmt.Errorf(
			"project is not set. Run `%s init project` first", app.ExecutableName)
	}

	session := global.Current()
	if session == nil {
		return nil, ErrNotLoggedIn
	}

	sessionEndpoint := session.GetString(config.PreferenceEndpoint)
	endpoint := sessionEndpoint
	if value := local.Data.GetString("endpoint"); value != "" {
		endpoint = value
	}
	if value := osGetenv(sdk.EnvEndpoint); value != "" {
		endpoint = value
	}
	if !config.EndpointsMatch(endpoint, sessionEndpoint) {
		return nil, fmt.Errorf(
			"endpoint %s does not match the current login session endpoint %s. "+
				"Switch to an account for this environment with `%s login --switch --endpoint %s`",
			endpoint, sessionEndpoint, app.ExecutableName, endpoint)
	}

	// WithoutResponseFormat is load bearing: with the header the API answers in
	// the pinned version's shape, and newer fields such as buildSpecification
	// and the project settings arrays are absent rather than wrong -- so the
	// output looks plausible and is quietly incomplete.
	api := client.New(endpoint, app.Version).
		WithoutResponseFormat().
		SetProject(projectID).
		SetLocale("en-US")

	switch {
	case session.GetString(config.PreferenceAccessToken) != "":
		token, err := auth.NewAuthenticator(global, app.Version).AccessToken(false)
		if err != nil {
			return nil, err
		}
		api.SetBearer(token).SetMode(config.ModeAdmin)
	case session.GetString(config.PreferenceCookie) != "":
		api.SetCookie(session.GetString(config.PreferenceCookie)).SetMode(config.ModeAdmin)
	case session.GetString(config.PreferenceKey) != "":
		api.SetKey(session.GetString(config.PreferenceKey)).SetMode(config.ModeDefault)
	default:
		return nil, fmt.Errorf(
			"authentication not found. Run `%s login` or `%s client --key <API_KEY>`",
			app.ExecutableName, app.ExecutableName)
	}

	return api, nil
}

// osGetenv keeps every environment read in this package greppable.
func osGetenv(name string) string { return os.Getenv(name) }

// projectPull is a project client plus the config it writes into.
type projectPull struct {
	api   *client.Client
	local *config.Local
}

func newProjectPull() (*projectPull, error) {
	global, err := preferences()
	if err != nil {
		return nil, err
	}

	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil {
		return nil, fmt.Errorf(
			"no %s found. Run `%s init project` first: %w",
			config.LocalFileName, app.ExecutableName, err)
	}

	api, err := projectAPI(global, local)
	if err != nil {
		return nil, err
	}

	return &projectPull{api: api, local: local}, nil
}

// probeEmpty issues the limit(1) request the TypeScript makes before paging.
//
// Redundant on its face -- the paginated walk would return the same emptiness
// -- but it is a request the CLI builds must both make, because a request trace
// is how they are compared. It also lets an empty resource report "No X found"
// without a full page.
func (p *projectPull) probeEmpty(path, wrapper string) (bool, error) {
	var response jsonx.Object
	err := p.api.Call("GET",
		path+"?"+client.EncodeQueries([]string{`{"method":"limit","values":[1]}`}),
		nil, &response)
	if err != nil {
		return false, err
	}

	items, _ := response.Get(wrapper)
	rows, _ := items.([]any)

	return len(rows) == 0, nil
}

// list pages a resource and shapes each entry to the config schema.
func (p *projectPull) list(resource flatResource) ([]*jsonx.Object, error) {
	empty, err := p.probeEmpty(resource.Path, resource.ConfigKey)
	if err != nil {
		return nil, err
	}
	if empty {
		return nil, nil
	}

	rows, err := p.page(resource.Path, resource.ConfigKey, nil)
	if err != nil {
		return nil, err
	}

	// A nil Keys means "store the API response unfiltered" -- see the teams
	// entry in flatResources.
	if resource.Keys == nil {
		return rows, nil
	}

	shaped := make([]*jsonx.Object, 0, len(rows))
	for _, row := range rows {
		shaped = append(shaped, config.FilterBySchema(row, resource.Keys))
	}

	return shaped, nil
}

// missingLocally names the local entries the remote no longer has.
//
// Reported by name where there is one, falling back to the id -- an entry with
// no name would otherwise be listed as an empty string.
func missingLocally(local, remote []*jsonx.Object) []string {
	present := make(map[string]bool, len(remote))
	for _, entry := range remote {
		present[entry.GetString("$id")] = true
	}

	var removed []string
	for _, entry := range local {
		id := entry.GetString("$id")
		if present[id] {
			continue
		}

		label := entry.GetString("name")
		if label == "" {
			label = id
		}
		removed = append(removed, label)
	}

	return removed
}

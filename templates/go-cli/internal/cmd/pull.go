package cmd

import (
	"fmt"
	"net/url"
	"os"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/auth"
	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
	"github.com/appwrite/appwrite-cli-go/internal/output"
	"github.com/appwrite/appwrite-cli-go/internal/prompt"
	"github.com/spf13/cobra"
)

// Ports templates/cli/lib/commands/pull.ts.
//
// `pull bucket|team|topic|webhook` each list a resource, shape it to the config
// schema, and REPLACE the local array. Replace, not merge: the remote is the
// source of truth, so a resource deleted remotely has to disappear locally --
// which is why a removal is confirmed before the write.
//
// `pull collection` and `pull table` live in pulldatabase.go.
//
// NOT YET PORTED: `pull all`, `settings`, `function` and `site`. All four need
// deployment download.

// flatResource describes one list-and-replace pull.
type flatResource struct {
	// Name is the subcommand and the config key.
	Name string
	// Aliases mirrors the TypeScript's plural forms.
	Aliases []string
	// Path is the API route.
	Path string
	// Wrapper is the array key in the response, which is not always the
	// config key -- topics live under "topics" locally and are listed from
	// /messaging/topics.
	Wrapper string
	// Keys is the schema order the entry is written in, or nil to store the
	// API response unfiltered.
	Keys []string
	// Label is the human word used in log lines.
	Label string
}

var flatResources = []flatResource{
	{
		Name: "bucket", Aliases: []string{"buckets"},
		Path: "/storage/buckets", Wrapper: "buckets",
		Keys: config.BucketKeys, Label: "buckets",
	},
	{
		Name: "team", Aliases: []string{"teams"},
		Path: "/teams", Wrapper: "teams",
		// No Keys, and that is not an oversight. pullTeams() is the one pull
		// that does NOT call filterBySchema, so a pulled team carries
		// $createdAt, $updatedAt, total and prefs into the config. Confirmed
		// against the shipping CLI on a real project. It is noise in a
		// code-reviewed file and almost certainly unintended, but `pull` output
		// is the contract -- fix it in the TypeScript, not here.
		Label: "teams",
	},
	{
		Name: "topic", Aliases: []string{"topics"},
		Path: "/messaging/topics", Wrapper: "topics",
		Keys: config.TopicKeys, Label: "topics",
	},
	{
		Name: "webhook", Aliases: []string{"webhooks"},
		Path: "/webhooks", Wrapper: "webhooks",
		Keys: config.WebhookKeys, Label: "webhooks",
	},
}

// configKey is the top-level array a resource is stored under.
func (r flatResource) configKey() string { return r.Wrapper }

func newPullCommand() *cobra.Command {
	command := &cobra.Command{
		Use:   "pull",
		Short: "Pull your Appwrite project resources into appwrite.config.json",
		RunE: func(command *cobra.Command, args []string) error {
			return command.Help()
		},
	}

	for _, resource := range flatResources {
		command.AddCommand(newPullResourceCommand(resource))
	}
	for _, resource := range databaseResources {
		command.AddCommand(newPullDatabaseCommand(resource))
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
	removed := missingLocally(context.local.ResourceEntries(resource.configKey()), entries)
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

	context.local.ReplaceResource(resource.configKey(), entries)
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
	if value := osGetenv("APPWRITE_PROJECT_ID"); value != "" {
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
	if value := osGetenv("APPWRITE_ENDPOINT"); value != "" {
		endpoint = value
	}
	if !config.EndpointsMatch(endpoint, sessionEndpoint) {
		return nil, fmt.Errorf(
			"endpoint %s does not match the current login session endpoint %s. "+
				"Switch to an account for this environment with `%s login --switch`",
			endpoint, sessionEndpoint, app.ExecutableName)
	}

	api := client.New(endpoint, app.Version).SetProject(projectID).SetLocale("en-US")

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

	local, err := config.LoadLocal(config.LocalPath("."))
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

// list pages a resource and shapes each entry to the config schema.
func (p *projectPull) list(resource flatResource) ([]*jsonx.Object, error) {
	rows, _, err := client.PaginateInto(func(queries []string) (*jsonx.Object, error) {
		values := url.Values{}
		for _, query := range queries {
			values.Add("queries[]", query)
		}

		var response jsonx.Object
		if err := p.api.Call("GET", resource.Path+"?"+values.Encode(), nil, &response); err != nil {
			return nil, err
		}

		return &response, nil
	}, resource.Wrapper, nil, 0)
	if err != nil {
		return nil, err
	}

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

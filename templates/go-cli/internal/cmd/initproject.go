package cmd

import (
	"errors"
	"fmt"
	"net/url"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Links the working directory to a project, creating one if asked. Everything
// here goes through the console client -- an API key cannot list organizations,
// which is why the session check is the first thing that happens.

// Console endpoints this command calls directly.
//
// These are console-only routes with no generated service behind them in the
// Go SDK, so they are issued through internal/client. Paths taken from the
// console SDK rather than guessed.
const (
	pathAccount       = "/account"
	pathOrganizations = "/organizations"
	pathTeams         = "/teams"
	pathProjects      = "/organization/projects"
	pathRegions       = "/console/regions"
)

func newInitProjectCommand() *cobra.Command {
	var organizationID, projectID, projectName string

	command := &cobra.Command{
		Use:   "project",
		Short: "Init a new Appwrite project",
		RunE: func(command *cobra.Command, args []string) error {
			return runInitProject(command, organizationID, projectID, projectName)
		},
	}

	command.Flags().StringVar(&organizationID, "organization-id", "", "Appwrite organization ID")
	command.Flags().StringVar(&projectID, "project-id", "", "Appwrite project ID")
	command.Flags().StringVar(&projectName, "project-name", "", "Appwrite project name")

	return command
}

func runInitProject(command *cobra.Command, organizationID, projectID, projectName string) error {
	out := command.OutOrStdout()

	api, global, err := consoleClient()
	if err != nil {
		return err
	}

	// The TypeScript calls account.get() before anything else. A stored
	// session can be expired or revoked, and finding that out here costs one
	// request instead of failing halfway through linking.
	var account jsonx.Object
	if err := api.Call("GET", pathAccount, nil, &account); err != nil {
		return fmt.Errorf(
			"session not found. Please run '%s login' to create a session: %w",
			app.ExecutableName, err)
	}

	// Absent is the normal case here: this is the command that creates it.
	local, err := config.LoadOrCreateLocal(config.LocalPath("."))
	if err != nil {
		return err
	}

	prompter := prompt.New(app.Flags().Force)

	// Linking over an existing link is destructive -- Clear() empties the
	// config -- so it is confirmed unless the user named everything.
	if local.Data.GetString("projectId") != "" && organizationID == "" && projectID == "" && projectName == "" {
		override, err := prompter.Confirm(prompt.Question{
			Message: "This directory is already linked to a project. Overwrite the configuration?",
			Default: true,
			Flag:    "--project-id",
		})
		if err != nil {
			return err
		}
		if !override {
			output.Log(out, "No changes made. Existing project configuration was kept.")

			return nil
		}
	}

	endpoint := global.CurrentValue(config.PreferenceEndpoint)
	cloud := isCloudEndpoint(endpoint)

	// Question order is user-visible, and it is the TypeScript's: the setup
	// method comes BEFORE the organization. Only the flags path skips it,
	// because naming a project id already answers it.
	creating := true
	if projectID == "" {
		method, err := prompter.Choice(prompt.Choice{
			Message: "Select a setup method:",
			Options: []prompt.Option{
				{Label: "Create a new project", Value: "new"},
				{Label: "Link this directory to an existing project", Value: "existing"},
			},
			Default: "new",
			Flag:    "--project-id",
		})
		if err != nil {
			return err
		}
		creating = method == "new"
	}

	if organizationID == "" {
		organizationID, err = chooseOrganization(api, prompter, endpoint, cloud)
		if err != nil {
			return err
		}
	}

	var linked *jsonx.Object

	// Naming a project id means "link to it if it exists, otherwise create
	// it" -- the TypeScript decides by trying to fetch it.
	if projectID != "" {
		existing, err := fetchProject(api, organizationID, projectID)
		switch {
		case err == nil:
			creating, linked = false, existing
		case isNotFound(err):
			creating = true
		default:
			return err
		}
	} else if !creating {
		chosen, err := chooseProject(api, prompter, organizationID)
		if err != nil {
			return err
		}
		linked, err = fetchProject(api, organizationID, chosen)
		if err != nil {
			return err
		}
	}

	region := ""
	if creating {
		if projectName == "" {
			projectName, err = prompter.Text(prompt.Text{
				Message: "What would you like to name your project?",
				Default: "My Awesome Project",
				Flag:    "--project-name",
			})
			if err != nil {
				return err
			}
		}
		if projectID == "" {
			projectID, err = prompter.Text(prompt.Text{
				Message: "What ID would you like to have for your project?",
				Default: appwrite.UniqueSentinel,
				Flag:    "--project-id",
			})
			if err != nil {
				return err
			}
		}
		projectID = appwrite.ResolveID(projectID)

		// Region is a Cloud concept; a self-hosted instance has exactly one.
		if cloud {
			region, err = chooseRegion(api, prompter)
			if err != nil {
				return err
			}
		}
	}

	// Cleared only once every question has been answered. Clearing earlier
	// would leave the directory unlinked if the user aborted at the last
	// prompt.
	if err := local.Clear(); err != nil {
		return err
	}

	if creating {
		created, err := createProject(api, organizationID, projectID, projectName, region)
		if err != nil {
			return err
		}
		linked = created
	}

	local.SetProject(linked.GetString("$id"), linked.GetString("name"))
	local.SetOrganizationID(organizationID)

	if cloud {
		if projectRegion := linked.GetString("region"); projectRegion != "" {
			local.SetEndpoint(regionalEndpoint(endpoint, projectRegion))
		}
	}

	// Asked BEFORE the success line, the way the TypeScript asks it. The answer
	// decides what happens in the seconds after that line, and a question
	// printed underneath "Project linked" reads as though the linking were not
	// finished yet.
	//
	// inquirer's confirm defaults to false when no default is given, which is
	// what this question has -- a pull overwrites local files, so the safe
	// answer is the default one.
	autopull := false
	if !creating {
		autopull, err = prompter.Confirm(prompt.Question{
			Message: "Pull all resources from this project?",
			Default: false,
		})
		if err != nil {
			return err
		}
	}

	if err := local.Write(); err != nil {
		return err
	}

	command.Println()
	if creating {
		output.Success(out, "Project created → %s", config.LocalFileName)
	} else {
		output.Success(out, "Project linked → %s", config.LocalFileName)
	}

	installInitProjectSkills(out, local.Dirname())

	// Only a LINKED project is offered the pull. A project created a moment ago
	// has nothing to fetch, and asking would be asking about an empty result.
	if !creating && autopull {
		command.Println()

		if err := pullEverything(command); err != nil {
			// The project is linked and its config is written, so this is not a
			// failed init -- it is a failed follow-up with its own command.
			output.Failure(out, "Failed to pull the project's resources: %s", err)
			output.Hint(out, "Run '%s pull all' to try again.", app.ExecutableName)
		}

		return nil
	}

	output.Log(out, "Run '%s pull all' to fetch this project's resources.", app.ExecutableName)

	return nil
}

// pullEverything runs `pull all` in place, the way the autopull answer does.
//
// --all and --force are forced on, as the TypeScript sets `cliConfig.all` and
// `cliConfig.force` before calling pullResources: the user has just answered the
// only question there was, so a pull that then asked which resources and whether
// to overwrite would be asking twice. The previous values are restored, because
// this process may go on to do something else that reads them.
func pullEverything(command *cobra.Command) error {
	flags := app.Flags()
	all, force := flags.All, flags.Force
	flags.All, flags.Force = true, true
	defer func() { flags.All, flags.Force = all, force }()

	return runPull(command, pullActions(), true)
}

// chooseOrganization lists the organizations the account belongs to.
//
// Cloud serves them at /organizations; a self-hosted instance has no such
// concept and uses /teams instead. The TypeScript branches the same way.
func chooseOrganization(api *client.Client, prompter prompt.Prompter, endpoint string, cloud bool) (string, error) {
	path := pathTeams
	if cloud {
		path = pathOrganizations
	}

	teams, _, err := client.PaginateInto(listing(api, path), "teams", nil, 0)
	if err != nil {
		return "", err
	}
	if len(teams) == 0 {
		return "", fmt.Errorf(
			"no organizations found. Please create a new organization at %s/console/onboarding",
			consoleBaseURL(endpoint))
	}

	options := make([]prompt.Option, 0, len(teams))
	for _, team := range teams {
		options = append(options, prompt.Option{
			Label: selectionLabel(team.GetString("name"), team.GetString("$id")),
			Value: team.GetString("$id"),
		})
	}

	return prompter.Choice(prompt.Choice{
		Message: "Choose your organization:",
		Options: options,
		Filter:  true,
		Flag:    "--organization-id",
	})
}

// chooseProject lists the projects in an organization, newest first.
func chooseProject(api *client.Client, prompter prompt.Prompter, organizationID string) (string, error) {
	queries := []string{
		fmt.Sprintf(`{"method":"equal","attribute":"teamId","values":[%q]}`, organizationID),
		`{"method":"orderDesc","attribute":"$id"}`,
	}

	projects, _, err := client.PaginateInto(
		listing(api, pathProjects, organizationID), "projects", queries, 0)
	if err != nil {
		return "", err
	}
	if len(projects) == 0 {
		return "", fmt.Errorf("no projects found. Please create a new project")
	}

	options := make([]prompt.Option, 0, len(projects))
	for _, project := range projects {
		options = append(options, prompt.Option{
			Label: selectionLabel(project.GetString("name"), project.GetString("$id")),
			Value: project.GetString("$id"),
		})
	}

	return prompter.Choice(prompt.Choice{
		Message: "Choose your project:",
		Options: options,
		Filter:  true,
		Flag:    "--project-id",
	})
}

// chooseRegion lists the Cloud regions a new project can be created in.
func chooseRegion(api *client.Client, prompter prompt.Prompter) (string, error) {
	var response struct {
		Regions []struct {
			ID       string `json:"$id"`
			Name     string `json:"name"`
			Disabled bool   `json:"disabled"`
		} `json:"regions"`
	}
	if err := api.Call("GET", pathRegions, nil, &response); err != nil {
		return "", err
	}

	options := make([]prompt.Option, 0, len(response.Regions))
	for _, region := range response.Regions {
		if region.Disabled {
			continue
		}
		options = append(options, prompt.Option{
			Label: fmt.Sprintf("%s (%s)", region.Name, region.ID),
			Value: region.ID,
		})
	}
	if len(options) == 0 {
		return "", fmt.Errorf(
			"no regions found. Please check your network or Appwrite Cloud availability")
	}

	return prompter.Choice(prompt.Choice{
		Message: "Select your Appwrite Cloud region",
		Options: options,
	})
}

// listing adapts a console list endpoint to the paginator.
//
// The organization header is set per request rather than on the client: the
// same client lists organizations first, and scoping it early would filter that
// call to an organization not yet chosen.
func listing(api *client.Client, path string, organizationID ...string) client.Lister {
	return func(queries []string) (*jsonx.Object, error) {
		request := path
		if encoded := client.EncodeQueries(queries); encoded != "" {
			request += "?" + encoded
		}

		scoped := api
		if len(organizationID) > 0 && organizationID[0] != "" {
			scoped = api.Clone().WithoutResponseFormat().SetOrganization(organizationID[0])
		}

		var response jsonx.Object
		if err := scoped.Call("GET", request, nil, &response); err != nil {
			return nil, err
		}

		return &response, nil
	}
}

// fetchProject reads one project through its organization.
func fetchProject(api *client.Client, organizationID, projectID string) (*jsonx.Object, error) {
	var project jsonx.Object
	err := api.Clone().WithoutResponseFormat().SetOrganization(organizationID).
		Call("GET", pathProjects+"/"+url.PathEscape(projectID), nil, &project)
	if err != nil {
		return nil, err
	}

	return &project, nil
}

// createProject creates a project in an organization.
func createProject(api *client.Client, organizationID, projectID, projectName, region string) (*jsonx.Object, error) {
	body := map[string]any{"projectId": projectID, "name": projectName}
	if region != "" {
		body["region"] = region
	}

	var created jsonx.Object
	err := api.Clone().WithoutResponseFormat().SetOrganization(organizationID).
		Call("POST", pathProjects, body, &created)
	if err != nil {
		return nil, err
	}

	return &created, nil
}

// isNotFound reports whether an error is the API's 404.
func isNotFound(err error) bool {
	var apiError *client.APIError
	if errors.As(err, &apiError) {
		return apiError.Status == 404
	}

	return false
}

// selectionLabel renders "Name (id)", or just the id when there is no name.
//
// A project created by the API with no name would
// otherwise render as " (id)".
func selectionLabel(name, id string) string {
	if strings.TrimSpace(name) == "" {
		return id
	}

	return name + " (" + id + ")"
}

// consoleBaseURL strips the /v1 suffix so a console link can be built.
func consoleBaseURL(endpoint string) string {
	return strings.TrimSuffix(strings.TrimSuffix(endpoint, "/"), "/v1")
}

// regionalEndpoint prefixes the endpoint's host with a region.
//
// Built from the NORMALISED host, not from whatever is configured: the session
// endpoint may already be regional, and prefixing again would produce
// `fra.sgp.cloud.appwrite.io`.
func regionalEndpoint(endpoint, region string) string {
	normalized := config.NormalizeCloudConsoleEndpoint(endpoint)

	parsed, err := url.Parse(normalized)
	if err != nil {
		return normalized
	}
	parsed.Host = region + "." + parsed.Host

	return strings.TrimSuffix(parsed.String(), "/")
}

// isCloudEndpoint reports whether an endpoint is Appwrite Cloud.
func isCloudEndpoint(endpoint string) bool {
	_, ok := config.CloudBaseHost(endpoint)

	return ok
}

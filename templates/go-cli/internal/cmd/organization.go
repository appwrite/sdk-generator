package cmd

import (
	"fmt"
	"io"
	"net/url"
	"sync"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
)

// The console endpoints that take no organization in their path read it from a
// header instead, and a config written by `pull` does not carry one -- `pull`
// records projectId and projectName, not organizationId. Sending the header
// empty makes the API resolve it to nothing and answer "Team with the requested
// ID could not be found", which names a team the user never mentioned.
//
// So when the config has no organization, it is looked up from the project.

// derivedOrganizationWarned keeps the "resolved from project" notice to once
// per run, matching the TypeScript. A `push all` resolves it for several
// resources and repeating the same advice each time is noise.
var derivedOrganizationWarned sync.Once

// resolveOrganizationID returns configured when it is set, and otherwise asks
// the API which organization owns the project.
func resolveOrganizationID(
	out io.Writer,
	api *client.Client,
	configured string,
	projectID string,
) (string, error) {
	if configured != "" {
		return configured, nil
	}

	if projectID == "" {
		return "", fmt.Errorf(
			"organization is not set. Pass --organization-id <id>, or run " +
				"`appwrite init project` to link this directory to a project")
	}

	organizationID, err := fetchOrganizationForProject(api, projectID)
	if err != nil {
		return "", err
	}

	derivedOrganizationWarned.Do(func() {
		output.Warn(out, "Resolved the organization for this command from "+
			"project %s. Run `appwrite init project` to persist organizationId "+
			"in appwrite.config.json.", projectID)
	})

	return organizationID, nil
}

// fetchOrganizationForProject reads the owning organization off the project.
//
// `GET /projects/{projectId}` is not in the spec, so there is no generated
// method for it and the call is issued by hand. It is a console-scoped read:
// the console client already sends `X-Appwrite-Project: console`, and it must
// NOT carry an organization header, which is the thing being resolved.
func fetchOrganizationForProject(api *client.Client, projectID string) (string, error) {
	var project jsonx.Object
	err := api.Clone().WithoutResponseFormat().Call(
		"GET", "/projects/"+url.PathEscape(projectID), nil, &project)
	if err != nil {
		return "", err
	}

	organizationID := project.GetString("teamId")
	if organizationID == "" {
		return "", fmt.Errorf(
			"unable to resolve the organization for project %s. Pass "+
				"--organization-id <id>, or run `appwrite init project` to "+
				"relink this directory", projectID)
	}

	return organizationID, nil
}

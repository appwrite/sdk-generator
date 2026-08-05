package cmd

import (
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
)

// A config written by `pull` carries projectId but no organizationId, and the
// console endpoints read the organization from a header. Sending it empty made
// the API answer "Team with the requested ID could not be found", so
// `pull settings` failed on any project the user had not run `init project` in.
func TestOrganizationIsResolvedFromTheProject(t *testing.T) {
	var paths []string

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		paths = append(paths, r.URL.Path)
		w.Write([]byte(`{"$id":"proj","teamId":"team-42"}`))
	}))
	defer server.Close()

	written := &strings.Builder{}
	organizationID, err := resolveOrganizationID(
		written, client.New(server.URL, "test"), "", "proj")
	if err != nil {
		t.Fatal(err)
	}

	if organizationID != "team-42" {
		t.Errorf("organization = %q, want the project's team", organizationID)
	}
	if len(paths) != 1 || paths[0] != "/projects/proj" {
		t.Errorf("requests = %v, want one project read", paths)
	}
}

// A configured organization is authoritative: looking it up anyway would spend
// a request to confirm what the config already says, and would override a
// deliberate --organization-id.
func TestConfiguredOrganizationIsNotLookedUp(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		t.Errorf("unexpected request to %s", r.URL.Path)
	}))
	defer server.Close()

	written := &strings.Builder{}
	organizationID, err := resolveOrganizationID(
		written, client.New(server.URL, "test"), "configured", "proj")
	if err != nil {
		t.Fatal(err)
	}

	if organizationID != "configured" {
		t.Errorf("organization = %q, want the configured one", organizationID)
	}
	if written.Len() != 0 {
		t.Errorf("warned about a configured organization: %q", written.String())
	}
}

// A project that reports no owning team cannot be guessed at. The message has
// to name the two ways out rather than surfacing an empty header downstream.
func TestUnresolvableOrganizationIsReported(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte(`{"$id":"proj"}`))
	}))
	defer server.Close()

	_, err := resolveOrganizationID(
		&strings.Builder{}, client.New(server.URL, "test"), "", "proj")
	if err == nil {
		t.Fatal("a project with no team resolved anyway")
	}
	if !strings.Contains(err.Error(), "--organization-id") {
		t.Errorf("error %q does not say how to fix it", err)
	}
}

// The project id goes in the path, so one containing a slash must not be able
// to reach a different route.
func TestOrganizationLookupEscapesTheProjectID(t *testing.T) {
	var paths []string

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		paths = append(paths, r.URL.EscapedPath())
		w.Write([]byte(`{"teamId":"t"}`))
	}))
	defer server.Close()

	_, err := resolveOrganizationID(
		&strings.Builder{}, client.New(server.URL, "test"), "", "a/../b")
	if err != nil {
		t.Fatal(err)
	}

	for _, path := range paths {
		if strings.Contains(path, "a/../b") {
			t.Errorf("path %q carries an unescaped project id", path)
		}
	}
}

// The lookup must not send an organization header -- that is the value being
// resolved, and an empty one is what caused the original failure.
func TestOrganizationLookupSendsNoOrganizationHeader(t *testing.T) {
	var header string
	var present bool

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		header, present = r.Header.Get("X-Appwrite-Organization"),
			r.Header.Values("X-Appwrite-Organization") != nil
		w.Write([]byte(`{"teamId":"t"}`))
	}))
	defer server.Close()

	api := client.New(server.URL, "test")
	api.SetOrganization("")

	if _, err := resolveOrganizationID(&strings.Builder{}, api, "", "proj"); err != nil {
		t.Fatal(err)
	}

	if present && header != "" {
		t.Errorf("sent X-Appwrite-Organization: %q", header)
	}
}

// Decoding guard: the resolved id has to come from `teamId`, not from whatever
// other id the project payload happens to carry first.
func TestOrganizationComesFromTeamID(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		payload, _ := json.Marshal(map[string]string{
			"$id": "proj", "name": "Project", "teamId": "the-team",
		})
		w.Write(payload)
	}))
	defer server.Close()

	organizationID, err := resolveOrganizationID(
		&strings.Builder{}, client.New(server.URL, "test"), "", "proj")
	if err != nil {
		t.Fatal(err)
	}

	if organizationID != "the-team" {
		t.Errorf("organization = %q, want the-team", organizationID)
	}
}

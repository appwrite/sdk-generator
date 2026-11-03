//go:build !browser

package cmd

import (
	"errors"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"strings"
	"sync"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
	"github.com/spf13/cobra"
)

// A project environment is reached with the project's ID in X-Appwrite-Project
// and the environment's in X-Appwrite-Environment, and --reason travels on
// every request as X-Appwrite-Reason. These run the real commands against a
// recording server, because a header that one of the CLI's two clients forgets
// is only visible on the wire.

// recordedRequest is what the recording server saw of one request.
type recordedRequest struct {
	Path        string
	Project     string
	Environment string
	Reason      string
}

// recordingServer answers every request with an empty list and records it.
func recordingServer(t *testing.T) (*httptest.Server, func() []recordedRequest) {
	t.Helper()

	var mutex sync.Mutex
	var requests []recordedRequest
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		mutex.Lock()
		requests = append(requests, recordedRequest{
			Path:        r.URL.Path,
			Project:     r.Header.Get("X-Appwrite-Project"),
			Environment: r.Header.Get("X-Appwrite-Environment"),
			Reason:      r.Header.Get(client.HeaderReason),
		})
		mutex.Unlock()

		w.Header().Set("content-type", "application/json")
		_, _ = w.Write([]byte(`{"total":0,"teams":[]}`))
	}))
	t.Cleanup(server.Close)

	return server, func() []recordedRequest {
		mutex.Lock()
		defer mutex.Unlock()

		return append([]recordedRequest(nil), requests...)
	}
}

// inLinkedProject signs in with an API key against the server and links the
// working directory with the given config.
func inLinkedProject(t *testing.T, server *httptest.Server, contents string) string {
	t.Helper()

	preferencesWith(t, `{"current":"ci","ci":{"endpoint":"`+server.URL+`","key":"secret"}}`)
	t.Setenv("XDG_CONFIG_HOME", "")
	t.Setenv(sdk.EnvProjectID, "")
	t.Setenv(sdk.EnvEnvironmentID, "")
	t.Setenv(sdk.EnvReason, "")

	directory := t.TempDir()
	inDirectory(t, directory)
	path := filepath.Join(directory, config.LocalFileName)
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}

	return path
}

// runRoot runs the CLI with the given arguments and returns what it printed.
func runRoot(t *testing.T, args ...string) (string, error) {
	t.Helper()

	// The flag writes the client package's Reason; registering it again in the
	// next test resets it, and this keeps it from leaking past the last one.
	previous := client.Reason
	t.Cleanup(func() { client.Reason = previous })

	root := NewRootCommand()
	root.AddCommand(sdkProbe())
	root.SetArgs(args)
	out := &strings.Builder{}
	root.SetOut(out)
	root.SetErr(out)
	err := root.Execute()

	return out.String(), err
}

// sdkProbe makes one project request through the SDK client, as every
// generated service command does, without depending on which services the
// spec this CLI is generated from has.
func sdkProbe() *cobra.Command {
	return &cobra.Command{
		Use: "sdk-probe",
		RunE: func(*cobra.Command, []string) error {
			api, err := app.ClientForProject("")
			if err != nil {
				return err
			}
			_, err = api.Call(http.MethodGet, "/health", nil, nil)

			return err
		},
	}
}

// Both clients -- the raw one `pull` uses and the SDK one the generated
// commands use -- send the environment from the config and the reason from
// the flag, on every request.
func TestProjectCommandsActInTheConfiguredEnvironment(t *testing.T) {
	for _, args := range [][]string{
		{"pull", "teams", "--force"},
		{"sdk-probe"},
	} {
		t.Run(strings.Join(args, " "), func(t *testing.T) {
			server, requests := recordingServer(t)
			inLinkedProject(t, server, `{"projectId":"my-project","environmentId":"staging"}`)

			output, err := runRoot(t, append(args, "--reason", "Nasazení opravy")...)
			if err != nil {
				t.Fatalf("%v\n%s", err, output)
			}

			seen := requests()
			if len(seen) == 0 {
				t.Fatal("no request was made")
			}
			for _, request := range seen {
				if request.Project != "my-project" || request.Environment != "staging" {
					t.Errorf("%s went to project %q environment %q, want my-project / staging",
						request.Path, request.Project, request.Environment)
				}
				if request.Reason != "Nasazen%C3%AD%20opravy" {
					t.Errorf("%s carried reason %q", request.Path, request.Reason)
				}
			}
		})
	}
}

// --reason wins over APPWRITE_REASON, which applies on its own when no flag is
// given -- the way --config-file and APPWRITE_CONFIG_FILE relate.
func TestReasonFlagBeatsTheEnvironmentVariable(t *testing.T) {
	for _, test := range []struct {
		name string
		args []string
		want string
	}{
		{"variable", nil, "From%20CI"},
		{"flag", []string{"--reason", "From flag"}, "From%20flag"},
	} {
		t.Run(test.name, func(t *testing.T) {
			server, requests := recordingServer(t)
			inLinkedProject(t, server, `{"projectId":"my-project"}`)
			t.Setenv(sdk.EnvReason, "From CI")

			output, err := runRoot(t, append([]string{"sdk-probe"}, test.args...)...)
			if err != nil {
				t.Fatalf("%v\n%s", err, output)
			}
			for _, request := range requests() {
				if request.Reason != test.want {
					t.Errorf("reason = %q, want %q", request.Reason, test.want)
				}
			}
		})
	}
}

// The config's environment belongs to the config's project. A CI job that
// points APPWRITE_PROJECT_ID at another project must not carry it there;
// APPWRITE_ENVIRONMENT_ID applies to whichever project is in use.
func TestEnvironmentResolution(t *testing.T) {
	for _, test := range []struct {
		name            string
		projectID       string
		environmentID   string
		wantProject     string
		wantEnvironment string
	}{
		{"config", "", "", "my-project", "staging"},
		{"another project", "other-project", "", "other-project", ""},
		{"variable", "", "preview", "my-project", "preview"},
		{"variable in another project", "other-project", "preview", "other-project", "preview"},
	} {
		t.Run(test.name, func(t *testing.T) {
			server, requests := recordingServer(t)
			inLinkedProject(t, server, `{"projectId":"my-project","environmentId":"staging"}`)
			t.Setenv(sdk.EnvProjectID, test.projectID)
			t.Setenv(sdk.EnvEnvironmentID, test.environmentID)

			if output, err := runRoot(t, "sdk-probe"); err != nil {
				t.Fatalf("%v\n%s", err, output)
			}
			for _, request := range requests() {
				if request.Project != test.wantProject || request.Environment != test.wantEnvironment {
					t.Errorf("project %q environment %q, want %q / %q",
						request.Project, request.Environment, test.wantProject, test.wantEnvironment)
				}
			}
		})
	}
}

// Without a reason or an environment neither header is sent at all.
func TestNoEnvironmentOrReasonSendsNeitherHeader(t *testing.T) {
	server, requests := recordingServer(t)
	inLinkedProject(t, server, `{"projectId":"my-project"}`)

	if output, err := runRoot(t, "pull", "teams", "--force"); err != nil {
		t.Fatalf("%v\n%s", err, output)
	}
	for _, request := range requests() {
		if request.Environment != "" || request.Reason != "" {
			t.Errorf("%s sent environment %q reason %q", request.Path, request.Environment, request.Reason)
		}
	}
}

// Settings are read and written through the console's project routes, which
// would reach the project rather than the environment. The commands refuse
// before making a single request, and the fan-outs skip settings and go on.
func TestSettingsRefuseToRunInAnEnvironment(t *testing.T) {
	for _, verb := range []string{"push", "pull"} {
		t.Run(verb, func(t *testing.T) {
			server, requests := recordingServer(t)
			inLinkedProject(t, server, `{"projectId":"my-project","environmentId":"staging"}`)

			output, err := runRoot(t, verb, "settings", "--force")
			if !errors.Is(err, errSettingsInEnvironment) {
				t.Fatalf("err = %v, want the environment refusal\n%s", err, output)
			}
			if !strings.Contains(err.Error(), "staging") || !strings.Contains(err.Error(), sdk.EnvEnvironmentID) {
				t.Errorf("the refusal does not say which environment or how to leave it: %v", err)
			}
			if seen := requests(); len(seen) != 0 {
				t.Errorf("made %d requests before refusing", len(seen))
			}
		})
	}
}

func TestEverythingSkipsSettingsInAnEnvironment(t *testing.T) {
	for _, verb := range []string{"push", "pull"} {
		t.Run(verb, func(t *testing.T) {
			isolateGlobalAll(t)

			server, _ := recordingServer(t)
			inLinkedProject(t, server, `{"projectId":"my-project","environmentId":"staging"}`)

			output, err := runRoot(t, verb, "all", "--force")
			if err != nil {
				t.Fatalf("`%s all` stopped at settings: %v\n%s", verb, err, output)
			}
			if !strings.Contains(output, "Skipping settings") {
				t.Errorf("the skipped settings were not reported:\n%s", output)
			}
		})
	}
}

// Settings still run on the project itself.
func TestSettingsRunWithoutAnEnvironment(t *testing.T) {
	directory := t.TempDir()
	inDirectory(t, directory)
	t.Setenv(sdk.EnvProjectID, "")
	t.Setenv(sdk.EnvEnvironmentID, "")

	local, err := config.LoadOrCreateLocal(filepath.Join(directory, config.LocalFileName))
	if err != nil {
		t.Fatal(err)
	}
	local.SetProject("my-project", "")

	if err := refuseSettingsInEnvironment("push", local); err != nil {
		t.Errorf("refused without an environment: %v", err)
	}
}

// `client --environment-id` writes where project commands read it, and an
// empty one clears it.
func TestClientEnvironmentIDIsWrittenAndCleared(t *testing.T) {
	directory := t.TempDir()
	inDirectory(t, directory)
	path := filepath.Join(directory, config.LocalFileName)
	if err := os.WriteFile(path, []byte(`{"projectId":"my-project"}`), 0o600); err != nil {
		t.Fatal(err)
	}

	if err := setLocalEnvironment("staging"); err != nil {
		t.Fatal(err)
	}
	if got := readConfigValue(t, path, "environmentId"); got != "staging" {
		t.Errorf("environmentId = %q, want staging", got)
	}

	if err := setLocalEnvironment(""); err != nil {
		t.Fatal(err)
	}
	contents, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if strings.Contains(string(contents), "environmentId") {
		t.Errorf("the environment was not cleared:\n%s", contents)
	}
}

// An environment means nothing without the project it belongs to.
func TestClientEnvironmentIDNeedsAProject(t *testing.T) {
	inDirectory(t, t.TempDir())

	if err := setLocalEnvironment("staging"); err == nil {
		t.Error("an environment was linked with no project")
	}
}

// Naming a different project drops the old project's environment, so
// `client --project-id other` cannot silently land in other's "staging".
func TestClientProjectIDDropsAnotherProjectsEnvironment(t *testing.T) {
	directory := t.TempDir()
	inDirectory(t, directory)
	path := filepath.Join(directory, config.LocalFileName)
	if err := os.WriteFile(path,
		[]byte(`{"projectId":"my-project","environmentId":"staging"}`), 0o600); err != nil {
		t.Fatal(err)
	}

	command := &cobra.Command{Use: "client"}
	if err := setLocalProject(command, preferencesWith(t, `{}`), "my-project"); err != nil {
		t.Fatal(err)
	}
	if got := readConfigValue(t, path, "environmentId"); got != "staging" {
		t.Errorf("relinking the same project dropped its environment: %q", got)
	}

	if err := setLocalProject(command, preferencesWith(t, `{}`), "other-project"); err != nil {
		t.Fatal(err)
	}
	if got := readConfigValue(t, path, "environmentId"); got != "" {
		t.Errorf("environmentId = %q after linking another project", got)
	}
}

// `init project` lists a project's environments behind the project itself,
// which is the default answer; a project without environments, or an instance
// without the route, asks nothing.
func TestChooseEnvironment(t *testing.T) {
	var body string
	var requested recordedRequest
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		requested = recordedRequest{Path: r.URL.Path, Project: r.Header.Get("X-Appwrite-Project")}
		if body == "" {
			w.WriteHeader(http.StatusNotFound)
			_, _ = w.Write([]byte(`{"message":"Route not found.","code":404,"type":"general_route_not_found"}`))

			return
		}
		_, _ = w.Write([]byte(body))
	}))
	t.Cleanup(server.Close)
	api := client.New(server.URL, "test").SetProject("my-project")

	body = `{"total":2,"environments":[{"$id":"staging","name":"Staging"},{"$id":"preview","name":""}]}`
	prompter := &prompt.Scripted{Choices: map[string]string{"Choose an environment:": "staging"}}
	chosen, err := chooseEnvironment(api, prompter)
	if err != nil {
		t.Fatal(err)
	}
	if chosen != "staging" {
		t.Errorf("chose %q, want staging", chosen)
	}
	if requested.Path != pathEnvironments || requested.Project != "my-project" {
		t.Errorf("listed %s in project %q", requested.Path, requested.Project)
	}

	defaulted, err := chooseEnvironment(api, &prompt.Scripted{})
	if err != nil || defaulted != "" {
		t.Errorf("the default was %q (%v), want the project itself", defaulted, err)
	}

	for _, response := range []string{`{"total":0,"environments":[]}`, ""} {
		body = response
		silent := &prompt.Scripted{}
		if chosen, err := chooseEnvironment(api, silent); err != nil || chosen != "" || len(silent.Asked) != 0 {
			t.Errorf("with %q: chose %q (%v) after asking %v", response, chosen, err, silent.Asked)
		}
	}
}

// A named environment that the project does not have is refused before it is
// written; anything else the API answers leaves the name as given.
func TestCheckEnvironment(t *testing.T) {
	var status int
	var body string
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(status)
		_, _ = w.Write([]byte(body))
	}))
	t.Cleanup(server.Close)
	api := client.New(server.URL, "test").SetProject("my-project")

	status, body = http.StatusNotFound,
		`{"message":"Environment not found.","code":404,"type":"project_environment_not_found"}`
	if err := checkEnvironment(api, "stagign"); err == nil {
		t.Error("an environment the project does not have was accepted")
	}

	status, body = http.StatusNotFound, `{"message":"Route not found.","code":404,"type":"general_route_not_found"}`
	if err := checkEnvironment(api, "staging"); err != nil {
		t.Errorf("an instance without environments refused the name: %v", err)
	}

	status, body = http.StatusOK, `{"$id":"staging","name":"Staging"}`
	if err := checkEnvironment(api, "staging"); err != nil {
		t.Errorf("an existing environment was refused: %v", err)
	}
}

func readConfigValue(t *testing.T, path, key string) string {
	t.Helper()

	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}

	return local.Data.GetString(key)
}

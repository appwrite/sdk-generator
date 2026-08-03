package cmd

import (
	"bytes"
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/spf13/cobra"
)

// `client --project-id X` is the non-interactive setup path CI uses. Writing
// the project into the global preferences left it inert: nothing reads it
// there, so the very next command answered "project is not set".
func TestClientProjectIDIsWrittenWhereCommandsReadIt(t *testing.T) {
	directory := t.TempDir()
	inDirectory(t, directory)

	if err := setLocalProject("chosen-project"); err != nil {
		t.Fatal(err)
	}

	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil {
		t.Fatalf("no config a command could read: %v", err)
	}
	if got := local.Data.GetString("projectId"); got != "chosen-project" {
		t.Errorf("projectId = %q, want chosen-project", got)
	}
}

// An existing config is updated in place. Creating a second one in the working
// directory would shadow the project's own and silently split the config in
// two.
func TestClientProjectIDUpdatesTheExistingConfig(t *testing.T) {
	root := t.TempDir()
	existing := filepath.Join(root, config.LocalFileName)
	if err := os.WriteFile(existing,
		[]byte(`{"projectId":"old","projectName":"Kept"}`), 0o600); err != nil {
		t.Fatal(err)
	}

	nested := filepath.Join(root, "functions", "one")
	if err := os.MkdirAll(nested, 0o755); err != nil {
		t.Fatal(err)
	}
	inDirectory(t, nested)

	if err := setLocalProject("new"); err != nil {
		t.Fatal(err)
	}

	if _, err := os.Stat(filepath.Join(nested, config.LocalFileName)); err == nil {
		t.Error("wrote a second config into the working directory")
	}

	local, err := config.LoadLocal(existing)
	if err != nil {
		t.Fatal(err)
	}
	if got := local.Data.GetString("projectId"); got != "new" {
		t.Errorf("projectId = %q, want new", got)
	}
	// Setting the id must not discard the rest of the file.
	if got := local.Data.GetString("projectName"); got != "Kept" {
		t.Errorf("projectName = %q, want it preserved", got)
	}
}

func inDirectory(t *testing.T, directory string) {
	t.Helper()

	previous, err := os.Getwd()
	if err != nil {
		t.Fatal(err)
	}
	if err := os.Chdir(directory); err != nil {
		t.Fatal(err)
	}
	t.Cleanup(func() { _ = os.Chdir(previous) })
}

// Pointing the CLI at a different endpoint must select the session that
// belongs to it. Setting the endpoint on whatever session happened to be
// current re-pointed a signed-in session at another instance, so one server's
// credentials were sent to the other until the next login.
func TestEndpointChangeSelectsTheSignedInSessionForIt(t *testing.T) {
	global := preferencesWith(t, `{
		"current": "cloud",
		"cloud": {"endpoint": "https://cloud.appwrite.io/v1", "cookie": "a=1", "email": "me@cloud"},
		"selfhosted": {"endpoint": "https://self.hosted/v1", "cookie": "b=2", "email": "me@self"}
	}`)

	command, out := captureCommand()
	selectSessionForEndpoint(command, global, "cloud", "https://self.hosted/v1")

	if got := global.CurrentSessionID(); got != "selfhosted" {
		t.Errorf("current session = %q, want selfhosted", got)
	}
	if !strings.Contains(out.String(), "me@self") {
		t.Errorf("did not report the account it switched to:\n%s", out)
	}
}

// A signed-in session for the endpoint beats an endpoint-only stub for it.
func TestEndpointChangePrefersASignedInSession(t *testing.T) {
	global := preferencesWith(t, `{
		"current": "other",
		"other": {"endpoint": "https://elsewhere/v1"},
		"stub": {"endpoint": "https://self.hosted/v1"},
		"real": {"endpoint": "https://self.hosted/v1", "cookie": "b=2", "email": "me@self"}
	}`)

	command, _ := captureCommand()
	selectSessionForEndpoint(command, global, "other", "https://self.hosted/v1")

	if got := global.CurrentSessionID(); got != "real" {
		t.Errorf("current session = %q, want the signed-in one", got)
	}
}

// Leaving a signed-in account behind is worth saying: it is still stored, and
// the user needs to know how to get back to it.
func TestEndpointChangeWarnsWhenItLeavesAnAccount(t *testing.T) {
	global := preferencesWith(t, `{
		"current": "cloud",
		"cloud": {"endpoint": "https://cloud.appwrite.io/v1", "cookie": "a=1", "email": "me@cloud"}
	}`)

	command, out := captureCommand()
	selectSessionForEndpoint(command, global, "cloud", "https://brand.new/v1")

	if !strings.Contains(out.String(), "me@cloud") {
		t.Errorf("did not name the account left behind:\n%s", out)
	}
	if !strings.Contains(out.String(), "login --switch") {
		t.Errorf("did not say how to return to it:\n%s", out)
	}
	if global.CurrentSessionID() == "cloud" {
		t.Error("stayed on the old session")
	}
	if _, ok := global.Session("cloud"); !ok {
		t.Error("the signed-in session was discarded rather than detached")
	}
}

// Already on the right session: nothing to switch, nothing to announce.
func TestEndpointChangeStaysOnAMatchingSession(t *testing.T) {
	global := preferencesWith(t, `{
		"current": "cloud",
		"cloud": {"endpoint": "https://cloud.appwrite.io/v1", "cookie": "a=1", "email": "me@cloud"}
	}`)

	command, out := captureCommand()
	selectSessionForEndpoint(command, global, "cloud", "https://fra.cloud.appwrite.io/v1")

	if got := global.CurrentSessionID(); got != "cloud" {
		t.Errorf("current session = %q, want to stay on cloud", got)
	}
	if out.Len() != 0 {
		t.Errorf("announced a switch that did not happen:\n%s", out)
	}
	if got := sessionEndpoint(global, "cloud"); got != "https://fra.cloud.appwrite.io/v1" {
		t.Errorf("endpoint = %q, want the regional host as typed", got)
	}
}

// An endpoint-only stub is repointed, not multiplied: `client --endpoint` run
// twice must not leave two sessions behind.
func TestEndpointChangeReusesAnEndpointOnlyStub(t *testing.T) {
	global := preferencesWith(t, `{"current": "default", "default": {"endpoint": "https://one/v1"}}`)

	command, _ := captureCommand()
	selectSessionForEndpoint(command, global, "default", "https://two/v1")

	if got := len(global.SessionIDs()); got != 1 {
		t.Errorf("%d sessions stored, want the stub reused", got)
	}
	if got := sessionEndpoint(global, "default"); got != "https://two/v1" {
		t.Errorf("endpoint = %q, want it repointed", got)
	}
}

func captureCommand() (*cobra.Command, *bytes.Buffer) {
	out := &bytes.Buffer{}
	command := &cobra.Command{Use: "client"}
	command.SetOut(out)

	return command, out
}

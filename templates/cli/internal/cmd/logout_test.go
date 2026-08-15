package cmd

import (
	"bytes"
	"io"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/spf13/cobra"
)

// Signing out deleted the stored session and stopped there, so the credential
// the user asked to destroy kept working until it expired on its own.
func TestLogoutRevokesACookieSessionAtTheServer(t *testing.T) {
	var calls []string

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		calls = append(calls, r.Method+" "+r.URL.Path)
		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	global := preferencesWith(t, `{
		"current": "s1",
		"s1": {"endpoint": "`+server.URL+`", "cookie": "a_session_console=abc"}
	}`)

	result := logoutSessions(global, []string{"s1"})

	if len(result.Failed) != 0 {
		t.Fatalf("failed to sign out: %v", result.Errors)
	}
	if len(calls) != 1 || calls[0] != "DELETE /account/sessions/current" {
		t.Errorf("requests = %v, want the session deleted at the server", calls)
	}
	if _, ok := global.Session("s1"); ok {
		t.Error("the session is still stored after signing out")
	}
}

// A session the server never issued has nothing to revoke. `client --endpoint`
// creates these, and reaching out for them would fail against an endpoint the
// user has not signed in to.
func TestLogoutSkipsTheServerForALocalOnlySession(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		t.Errorf("unexpected request to %s", r.URL.Path)
	}))
	defer server.Close()

	global := preferencesWith(t, `{
		"current": "s1",
		"s1": {"endpoint": "`+server.URL+`"}
	}`)

	result := logoutSessions(global, []string{"s1"})

	if len(result.Failed) != 0 {
		t.Fatalf("failed: %v", result.Errors)
	}
	if _, ok := global.Session("s1"); ok {
		t.Error("the local-only session was not removed")
	}
}

// A session that cannot be revoked is KEPT. Removing it anyway would leave a
// live server session with no local record of it -- the one state from which
// the user cannot recover.
func TestLogoutKeepsASessionItCouldNotRevoke(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusInternalServerError)
		w.Write([]byte(`{"message":"boom"}`))
	}))
	defer server.Close()

	global := preferencesWith(t, `{
		"current": "s1",
		"s1": {"endpoint": "`+server.URL+`", "cookie": "a_session_console=abc"}
	}`)

	result := logoutSessions(global, []string{"s1"})

	if len(result.Failed) != 1 {
		t.Fatalf("failed = %v, want the session reported", result.Failed)
	}
	if _, ok := global.Session("s1"); !ok {
		t.Error("a session that could not be revoked was removed anyway")
	}
}

// Signing out of everything must reach each instance the user signed in to,
// not whichever one happens to be current.
func TestLogoutRevokesEachSessionAtItsOwnEndpoint(t *testing.T) {
	var first, second int

	serverOne := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		first++
		w.Write([]byte(`{}`))
	}))
	defer serverOne.Close()
	serverTwo := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		second++
		w.Write([]byte(`{}`))
	}))
	defer serverTwo.Close()

	global := preferencesWith(t, `{
		"current": "s1",
		"s1": {"endpoint": "`+serverOne.URL+`", "cookie": "a=1"},
		"s2": {"endpoint": "`+serverTwo.URL+`", "cookie": "b=2"}
	}`)

	result := logoutSessions(global, []string{"s1", "s2"})

	if len(result.Failed) != 0 {
		t.Fatalf("failed: %v", result.Errors)
	}
	if first != 1 || second != 1 {
		t.Errorf("requests: first=%d second=%d, want one each", first, second)
	}
}

func TestLogoutSessionLabelsShowAccountAndEndpoint(t *testing.T) {
	session := config.Session{
		ID:       "session-id",
		Email:    "person@example.com",
		Endpoint: "https://cloud.appwrite.io/v1",
	}

	if got := formatSessionLabel(session); got != "person@example.com (https://cloud.appwrite.io/v1)" {
		t.Errorf("label = %q", got)
	}
}

func TestLogoutSessionLabelsFallbackToID(t *testing.T) {
	session := config.Session{ID: "session-id"}

	if got := formatSessionLabel(session); got != "session-id" {
		t.Errorf("label = %q", got)
	}
}

// preferencesWith writes a prefs file and loads it, with HOME pointed at a
// temporary directory so the real one is never touched.
func preferencesWith(t *testing.T, contents string) *config.Global {
	t.Helper()

	home := t.TempDir()
	t.Setenv("HOME", home)
	t.Setenv("USERPROFILE", home)

	directory := filepath.Join(home, ".appwrite")
	if err := os.MkdirAll(directory, 0o755); err != nil {
		t.Fatal(err)
	}
	path := filepath.Join(directory, "prefs.json")
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}

	return config.LoadGlobal(path)
}

// The version banner is context, not data. On stdout it would land inside the
// JSON and break `whoami --json | jq`, so under --json it goes to stderr --
// the same rule the update notice follows.
func TestWhoamiBannerAvoidsStdoutWhenParsed(t *testing.T) {
	stdout, stderr := &bytes.Buffer{}, &bytes.Buffer{}
	command := &cobra.Command{Use: "whoami"}
	command.SetOut(stdout)
	command.SetErr(stderr)

	restore := app.Flags().JSON
	defer func() { app.Flags().JSON = restore }()

	app.Flags().JSON = false
	if bannerWriter(command) != io.Writer(stdout) {
		t.Error("the banner avoided stdout when nothing was parsing it")
	}

	app.Flags().JSON = true
	if bannerWriter(command) != io.Writer(stderr) {
		t.Error("the banner went to stdout under --json, where it breaks jq")
	}
}

package cmd

import (
	"encoding/json"
	"io"
	"net/http"
	"net/http/httptest"
	"strings"
	"sync"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
)

// A function running locally still calls the real API, and it authenticates
// with an ephemeral key the CLI mints for it. `run` sent an empty
// x-appwrite-key, so every such call went out unauthenticated.
func TestRunMintsAFunctionKey(t *testing.T) {
	var paths []string
	var body map[string]any

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		paths = append(paths, r.Method+" "+r.URL.Path)
		payload, _ := io.ReadAll(r.Body)
		_ = json.Unmarshal(payload, &body)
		w.Write([]byte(`{"secret":"ephemeral-secret"}`))
	}))
	defer server.Close()

	credentials, err := mintRunCredentials(
		client.New(server.URL, "test"), "", []string{"users.read", "databases.write"})
	if err != nil {
		t.Fatal(err)
	}

	if credentials.FunctionKey != "ephemeral-secret" {
		t.Errorf("FunctionKey = %q, want the minted secret", credentials.FunctionKey)
	}
	if len(paths) != 1 || paths[0] != "POST /project/keys/ephemeral" {
		t.Errorf("requests = %v, want one ephemeral key request", paths)
	}

	// The key must carry the function's OWN scopes. A key minted with the
	// wrong set either cannot do what the function does, or can do more.
	scopes, _ := body["scopes"].([]any)
	if len(scopes) != 2 || scopes[0] != "users.read" {
		t.Errorf("scopes = %v, want the function's scopes", body["scopes"])
	}
	if body["duration"] != float64(credentialDuration) {
		t.Errorf("duration = %v, want %d", body["duration"], credentialDuration)
	}
}

// No user, no user JWT: the run is anonymous and there is nobody to mint one
// for.
func TestRunMintsNoUserJWTWithoutAUser(t *testing.T) {
	var paths []string

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		paths = append(paths, r.URL.Path)
		w.Write([]byte(`{"secret":"s"}`))
	}))
	defer server.Close()

	credentials, err := mintRunCredentials(client.New(server.URL, "test"), "", nil)
	if err != nil {
		t.Fatal(err)
	}

	if credentials.UserJWT != "" {
		t.Errorf("UserJWT = %q, want none", credentials.UserJWT)
	}
	for _, path := range paths {
		if strings.Contains(path, "/users/") {
			t.Errorf("asked for %s without a user id", path)
		}
	}
}

// With a user, the run also carries a JWT for them, so the function sees the
// caller it would see in production.
func TestRunMintsAUserJWT(t *testing.T) {
	var paths []string

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		paths = append(paths, r.Method+" "+r.URL.Path)

		switch {
		case strings.HasSuffix(r.URL.Path, "/jwts"):
			w.Write([]byte(`{"jwt":"user-token"}`))
		case strings.HasPrefix(r.URL.Path, "/users/"):
			w.Write([]byte(`{"$id":"someone"}`))
		default:
			w.Write([]byte(`{"secret":"ephemeral-secret"}`))
		}
	}))
	defer server.Close()

	credentials, err := mintRunCredentials(client.New(server.URL, "test"), "someone", nil)
	if err != nil {
		t.Fatal(err)
	}

	if credentials.UserJWT != "user-token" {
		t.Errorf("UserJWT = %q, want the minted token", credentials.UserJWT)
	}
	if credentials.FunctionKey != "ephemeral-secret" {
		t.Errorf("FunctionKey = %q, want the key as well", credentials.FunctionKey)
	}

	// The user is read FIRST, so a mistyped id fails as "user not found"
	// rather than as whatever minting against a missing user returns.
	if len(paths) != 3 || paths[0] != "GET /users/someone" {
		t.Errorf("requests = %v, want the user read before the JWT", paths)
	}
}

// A user id with a slash or a space in it must not escape the path.
func TestRunEscapesTheUserIDInThePath(t *testing.T) {
	var paths []string

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		paths = append(paths, r.URL.EscapedPath())
		w.Write([]byte(`{"jwt":"t","secret":"s"}`))
	}))
	defer server.Close()

	_, err := mintRunCredentials(client.New(server.URL, "test"), "a/../b", nil)
	if err != nil {
		t.Fatal(err)
	}

	for _, path := range paths {
		if strings.Contains(path, "a/../b") {
			t.Errorf("path %q carries an unescaped user id", path)
		}
	}
}

// A run that cannot mint must say which step failed rather than starting the
// function with a half-filled credential set.
func TestRunReportsAFailedMint(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"message":"missing scope","code":403}`))
	}))
	defer server.Close()

	_, err := mintRunCredentials(client.New(server.URL, "test"), "", nil)
	if err == nil {
		t.Error("a rejected mint was reported as success")
	}
}

// The expiry warnings must not outlive the run, and stopping them twice or
// stopping a run that ended immediately must not panic.
func TestCredentialExpiryWarningStops(t *testing.T) {
	var mutex sync.Mutex
	written := &strings.Builder{}

	stop := warnOnCredentialExpiry(writerFunc(func(p []byte) (int, error) {
		mutex.Lock()
		defer mutex.Unlock()

		return written.Write(p)
	}))
	stop()

	mutex.Lock()
	defer mutex.Unlock()
	if written.Len() != 0 {
		t.Errorf("a run that ended immediately warned: %q", written.String())
	}
}

type writerFunc func([]byte) (int, error)

func (f writerFunc) Write(p []byte) (int, error) { return f(p) }

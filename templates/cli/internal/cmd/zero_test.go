//go:build !browser

package cmd

import (
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"os"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
)

// zeroServer answers the Zero endpoints the way the API does and records what
// the CLI sent.
func zeroServer(t *testing.T, requests *[]*http.Request, status string) *httptest.Server {
	t.Helper()

	var server *httptest.Server
	server = httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		*requests = append(*requests, r)

		switch {
		case r.Method == http.MethodPost && r.URL.Path == "/zero":
			json.NewEncoder(w).Encode(map[string]any{
				"$id":            "zero-row",
				"secret":         "claim-secret",
				"projectId":      "zero-project",
				"organizationId": "zero-org",
				"endpoint":       server.URL,
				"apiKey":         "standard_zero-api-key",
				"accessToken":    "owner-token",
				"consoleUrl":     "https://console.example/project",
				"claimUrl":       "https://console.example/claim/code",
				"expiresAt":      "2099-01-01T00:00:00Z",
				"claimBy":        "2099-01-02T00:00:00Z",
			})
		case r.Method == http.MethodGet && r.URL.Path == "/zero/zero-row":
			json.NewEncoder(w).Encode(map[string]any{
				"projectId": "zero-project",
				"status":    status,
				"endpoint":  server.URL,
				"expiresAt": "2099-01-01T00:00:00Z",
				"claimBy":   "2099-01-02T00:00:00Z",
			})
		case r.Method == http.MethodGet && r.URL.Path == "/proxy/rules" && status == zeroExpired:
			w.WriteHeader(http.StatusForbidden)
			w.Write([]byte(`{"message":"Build window over","code":403,"type":"zero_expired"}`))
		case r.Method == http.MethodGet && r.URL.Path == "/proxy/rules":
			w.Write([]byte(`{"total":1,"rules":[{"domain":"app.example"}]}`))
		default:
			w.WriteHeader(http.StatusNotFound)
		}
	}))
	t.Cleanup(server.Close)

	return server
}

func withZeroFlags(t *testing.T, json, showSecrets bool) {
	t.Helper()

	previousJSON, previousSecrets := app.Flags().JSON, app.Flags().ShowSecrets
	app.Flags().JSON, app.Flags().ShowSecrets = json, showSecrets
	t.Cleanup(func() {
		app.Flags().JSON, app.Flags().ShowSecrets = previousJSON, previousSecrets
	})
}

// `zero` leaves a directory every project command can use without login: the
// API key is the session's credential, and the claim secret stays in the
// private prefs file rather than in appwrite.config.json, which is committed.
func TestZeroLinksTheDirectoryAndStoresTheKeyAsASession(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	var requests []*http.Request
	server := zeroServer(t, &requests, zeroActive)

	command, out := captureCommand()
	if err := runZero(command, server.URL); err != nil {
		t.Fatal(err)
	}

	if len(requests) != 1 || requests[0].Header.Get("X-Appwrite-Key") != "" {
		t.Fatalf("expected one unauthenticated POST /zero, got %d requests", len(requests))
	}

	global, err := preferences()
	if err != nil {
		t.Fatal(err)
	}
	session := global.SessionData(zeroSessionPrefix + "zero-project")
	if session == nil {
		t.Fatal("no session stored for the Zero project")
	}
	if global.CurrentSessionID() != zeroSessionPrefix+"zero-project" {
		t.Errorf("current session = %q, want the Zero session", global.CurrentSessionID())
	}
	if got := session.GetString(config.PreferenceKey); got != "standard_zero-api-key" {
		t.Errorf("session key = %q", got)
	}
	if got := session.GetString(config.PreferenceEndpoint); got != server.URL {
		t.Errorf("session endpoint = %q", got)
	}
	if got := session.GetObject(preferenceZero).GetString("secret"); got != "claim-secret" {
		t.Errorf("stored claim secret = %q", got)
	}

	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil {
		t.Fatal(err)
	}
	if got := local.Data.GetString("projectId"); got != "zero-project" {
		t.Errorf("linked project = %q", got)
	}
	contents, err := os.ReadFile(local.Path())
	if err != nil {
		t.Fatal(err)
	}
	for _, secret := range []string{"claim-secret", "standard_zero-api-key", "owner-token"} {
		if strings.Contains(string(contents), secret) {
			t.Errorf("%s leaked into %s:\n%s", secret, config.LocalFileName, contents)
		}
	}

	if !strings.Contains(out.String(), "https://console.example/claim/code") {
		t.Errorf("welcome does not print the claim link:\n%s", out)
	}
}

// The --json manifest is what an agent hands back, so the credentials it exists
// to carry must survive redaction, with or without --show-secrets.
func TestZeroJSONManifestIsUnredacted(t *testing.T) {
	for _, test := range []struct {
		name        string
		showSecrets bool
	}{
		{"default", false},
		{"show secrets", true},
	} {
		t.Run(test.name, func(t *testing.T) {
			preferencesWith(t, `{}`)
			inDirectory(t, t.TempDir())
			withZeroFlags(t, true, test.showSecrets)

			var requests []*http.Request
			server := zeroServer(t, &requests, zeroActive)

			command, out := captureCommand()
			if err := runZero(command, server.URL); err != nil {
				t.Fatal(err)
			}

			var manifest map[string]any
			if err := json.Unmarshal(out.Bytes(), &manifest); err != nil {
				t.Fatalf("manifest is not JSON: %v\n%s", err, out)
			}
			for key, value := range map[string]string{
				"accessToken": "owner-token",
				"apiKey":      "standard_zero-api-key",
				"secret":      "claim-secret",
				"claimUrl":    "https://console.example/claim/code",
			} {
				if manifest[key] != value {
					t.Errorf("%s = %v, want %s", key, manifest[key], value)
				}
			}
		})
	}
}

// `zero status` reads the project with the stored claim secret and lists an
// owner link per domain, fetched with the stored API key.
func TestZeroStatusUsesTheStoredSession(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	var requests []*http.Request
	server := zeroServer(t, &requests, zeroActive)

	command, _ := captureCommand()
	if err := runZero(command, server.URL); err != nil {
		t.Fatal(err)
	}

	global, err := preferences()
	if err != nil {
		t.Fatal(err)
	}
	session := zeroSession(global)
	if session == nil {
		t.Fatal("zero status cannot find the session zero stored")
	}

	links, err := zeroOwnerLinks(session, "zero-project", "owner-token")
	if err != nil {
		t.Fatal(err)
	}
	if len(links) != 1 || links[0] != "https://app.example"+zeroOwnerPath+"owner-token" {
		t.Errorf("owner links = %v", links)
	}

	rules := requests[len(requests)-1]
	if got := rules.Header.Get("X-Appwrite-Key"); got != "standard_zero-api-key" {
		t.Errorf("rules were listed with key %q", got)
	}
	if got := rules.Header.Get("X-Appwrite-Project"); got != "zero-project" {
		t.Errorf("rules were listed for project %q", got)
	}

	requests = requests[:0]
	if err := runZeroStatus(command); err != nil {
		t.Fatal(err)
	}
	if len(requests) == 0 || requests[0].URL.Query().Get("secret") != "claim-secret" {
		t.Error("status did not read the project with the stored claim secret")
	}
}

// After the build window the project refuses API calls with zero_expired, but
// `zero status` is how the user learns the claim deadline, so it must still
// answer without asking the project for its domains.
func TestZeroStatusReportsAnExpiredProject(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	var requests []*http.Request
	server := zeroServer(t, &requests, zeroExpired)

	command, _ := captureCommand()
	if err := runZero(command, server.URL); err != nil {
		t.Fatal(err)
	}

	requests = requests[:0]
	if err := runZeroStatus(command); err != nil {
		t.Fatalf("status failed for an expired project: %v", err)
	}
	for _, request := range requests {
		if request.URL.Path == "/proxy/rules" {
			t.Error("asked an expired project for its rules")
		}
	}
}

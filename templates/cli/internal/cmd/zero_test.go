//go:build !browser

package cmd

import (
	"bytes"
	"context"
	"crypto/tls"
	"encoding/json"
	"io"
	"net"
	"net/http"
	"net/http/httptest"
	"os"
	"strings"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
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
				"$id":            "zero-project",
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
		case r.URL.Path == "/zero/zero-project/tokens" && status == zeroNotFound:
			w.WriteHeader(http.StatusNotFound)
			w.Write([]byte(`{"message":"Zero project not found","code":404,"type":"zero_not_found"}`))
		case r.Method == http.MethodPost && r.URL.Path == "/zero/zero-project/tokens":
			body, _ := io.ReadAll(r.Body)
			r.Body = io.NopCloser(bytes.NewReader(body))
			w.WriteHeader(http.StatusCreated)
			json.NewEncoder(w).Encode(map[string]any{
				"userId": "zero-owner",
				"secret": "sign-in-secret",
				"expire": time.Now().Add(5 * time.Minute).UTC().Format(time.RFC3339),
				"url":    "https://console.example/sign-in?secret=sign-in-secret",
			})
		case r.Method == http.MethodGet && r.URL.Path == "/zero/zero-project" && status == zeroNotFound:
			w.WriteHeader(http.StatusNotFound)
			w.Write([]byte(`{"message":"Zero project not found","code":404,"type":"zero_not_found"}`))
		case r.Method == http.MethodGet && r.URL.Path == "/zero/zero-project":
			json.NewEncoder(w).Encode(map[string]any{
				"projectId":  "zero-project",
				"status":     status,
				"endpoint":   server.URL,
				"consoleUrl": "https://console.example/project",
				"expiresAt":  "2099-01-01T00:00:00Z",
				"claimBy":    "2099-01-02T00:00:00Z",
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

// regionalZeroServer answers POST /zero for any host: every host dials the
// test server, so a regional name is never resolved. It records the host and
// path the CLI called.
func regionalZeroServer(t *testing.T, endpoint string, host, path *string) {
	t.Helper()

	server := httptest.NewTLSServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		*host, *path = r.Host, r.URL.Path
		json.NewEncoder(w).Encode(map[string]any{
			"$id":         "zero-row",
			"projectId":   "zero-project",
			"endpoint":    endpoint,
			"apiKey":      "standard_zero-api-key",
			"accessToken": "owner-token",
		})
	}))
	t.Cleanup(server.Close)

	previous := http.DefaultTransport
	transport := previous.(*http.Transport).Clone()
	transport.DialContext = func(ctx context.Context, network, _ string) (net.Conn, error) {
		return (&net.Dialer{}).DialContext(ctx, network, server.Listener.Addr().String())
	}
	transport.TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	http.DefaultTransport = transport
	t.Cleanup(func() { http.DefaultTransport = previous })
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
	if err := runZero(command, server.URL, ""); err != nil {
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
	// The console URL opens nothing for an owner with no session, so the
	// welcome points at the command that signs the browser in instead.
	if strings.Contains(out.String(), "https://console.example/project") {
		t.Errorf("welcome prints the console URL, which needs a session:\n%s", out)
	}
	if !strings.Contains(out.String(), "zero console") {
		t.Errorf("welcome does not point at zero console:\n%s", out)
	}
}

// stdout captures what the renderer writes, which goes to os.Stdout rather
// than to the command's writer.
func stdout(t *testing.T, run func()) string {
	t.Helper()

	reader, writer, err := os.Pipe()
	if err != nil {
		t.Fatal(err)
	}
	previous := os.Stdout
	os.Stdout = writer
	defer func() { os.Stdout = previous }()

	run()
	writer.Close()
	contents, err := io.ReadAll(reader)
	if err != nil {
		t.Fatal(err)
	}

	return string(contents)
}

// `zero status` keeps consoleUrl in --json, whose schema agents read, but a
// person is pointed at `zero console`: the URL alone opens nothing until the
// project is claimed.
func TestZeroStatusPointsAtZeroConsole(t *testing.T) {
	for _, test := range []struct {
		name string
		json bool
	}{
		{"table", false},
		{"json", true},
	} {
		t.Run(test.name, func(t *testing.T) {
			preferencesWith(t, `{}`)
			inDirectory(t, t.TempDir())
			withZeroFlags(t, false, false)

			var requests []*http.Request
			server := zeroServer(t, &requests, zeroActive)

			command, out := captureCommand()
			if err := runZero(command, server.URL, ""); err != nil {
				t.Fatal(err)
			}

			withZeroFlags(t, test.json, false)
			out.Reset()
			var err error
			rendered := stdout(t, func() { err = runZeroStatus(command) })
			if err != nil {
				t.Fatal(err)
			}
			printed := rendered + out.String()

			if got := strings.Contains(printed, "https://console.example/project"); got != test.json {
				t.Errorf("console URL printed = %v, want %v:\n%s", got, test.json, printed)
			}
			if got := strings.Contains(printed, "zero console"); got == test.json {
				t.Errorf("zero console hint printed = %v, want %v:\n%s", got, !test.json, printed)
			}
		})
	}
}

// `zero console` signs the browser in as the project's owner with the stored
// claim secret, the same unauthenticated way `zero status` reads the project,
// and opens the link it gets back.
func TestZeroConsoleOpensASignInLink(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	var requests []*http.Request
	server := zeroServer(t, &requests, zeroActive)

	command, out := captureCommand()
	if err := runZero(command, server.URL, ""); err != nil {
		t.Fatal(err)
	}

	requests = requests[:0]
	out.Reset()
	var opened []string
	if err := runZeroConsole(command, func(link string) { opened = append(opened, link) }); err != nil {
		t.Fatal(err)
	}

	const link = "https://console.example/sign-in?secret=sign-in-secret"
	if len(requests) != 1 {
		t.Fatalf("expected one request, got %d", len(requests))
	}
	request := requests[0]
	if request.Method != http.MethodPost || request.URL.Path != "/zero/zero-project/tokens" {
		t.Errorf("called %s %s", request.Method, request.URL.Path)
	}
	if got := request.Header.Get("X-Appwrite-Project"); got != "console" {
		t.Errorf("project header = %q, want console", got)
	}
	if got := request.Header.Get("X-Appwrite-Key"); got != "" {
		t.Errorf("sent the API key %q to an unauthenticated endpoint", got)
	}
	var body map[string]any
	if err := json.NewDecoder(request.Body).Decode(&body); err != nil || body["secret"] != "claim-secret" {
		t.Errorf("body = %v (%v), want the stored claim secret", body, err)
	}

	if len(opened) != 1 || opened[0] != link {
		t.Errorf("opened %v, want the sign-in link", opened)
	}
	if !strings.Contains(out.String(), link) {
		t.Errorf("does not print the link for a browser that did not open:\n%s", out)
	}
}

// The link signs in once, so --json hands it back unopened: a browser opening
// it would spend it. The session secret behind it stays out without
// --show-secrets.
func TestZeroConsoleJSONPrintsTheLinkUnopened(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	var requests []*http.Request
	server := zeroServer(t, &requests, zeroActive)

	command, out := captureCommand()
	if err := runZero(command, server.URL, ""); err != nil {
		t.Fatal(err)
	}

	withZeroFlags(t, true, false)
	out.Reset()
	opened := false
	if err := runZeroConsole(command, func(string) { opened = true }); err != nil {
		t.Fatal(err)
	}

	if opened {
		t.Error("opened the single-use link with --json")
	}
	var token map[string]any
	if err := json.Unmarshal(out.Bytes(), &token); err != nil {
		t.Fatalf("output is not JSON: %v\n%s", err, out)
	}
	if token["url"] != "https://console.example/sign-in?secret=sign-in-secret" {
		t.Errorf("url = %v", token["url"])
	}
	if secret, present := token["secret"]; present {
		t.Errorf("secret = %v printed without --show-secrets", secret)
	}
}

// Once the project is claimed or deleted the API answers zero_not_found, and
// `zero console` says so the way `zero status` does rather than echo the API.
func TestZeroConsoleReportsAProjectNoLongerUnclaimed(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	var requests []*http.Request
	server := zeroServer(t, &requests, zeroNotFound)

	command, _ := captureCommand()
	if err := runZero(command, server.URL, ""); err != nil {
		t.Fatal(err)
	}

	opened := false
	err := runZeroConsole(command, func(string) { opened = true })
	if err == nil {
		t.Fatal("zero console succeeded for a project the API no longer knows")
	}
	for _, want := range []string{"zero-project", "claimed or deleted", "https://console.example/project"} {
		if !strings.Contains(err.Error(), want) {
			t.Errorf("message %q does not mention %q", err, want)
		}
	}
	if opened {
		t.Error("opened a browser for a project it could not sign in to")
	}
}

// The --json manifest is what an agent hands back, so the owner token it
// exists to carry must survive redaction. The API key and claim secret are the
// CLI's own and stay out of it unless --show-secrets asks for them.
func TestZeroJSONManifestCarriesTheOwnerToken(t *testing.T) {
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
			if err := runZero(command, server.URL, ""); err != nil {
				t.Fatal(err)
			}

			var manifest map[string]any
			if err := json.Unmarshal(out.Bytes(), &manifest); err != nil {
				t.Fatalf("manifest is not JSON: %v\n%s", err, out)
			}
			if manifest["accessToken"] != "owner-token" {
				t.Errorf("accessToken = %v, want the owner token", manifest["accessToken"])
			}
			if manifest["claimUrl"] != "https://console.example/claim/code" {
				t.Errorf("claimUrl = %v", manifest["claimUrl"])
			}

			for key, value := range map[string]string{"apiKey": "standard_zero-api-key", "secret": "claim-secret"} {
				got, present := manifest[key]
				if test.showSecrets && got != value {
					t.Errorf("%s = %v with --show-secrets, want %s", key, got, value)
				}
				if !test.showSecrets && present {
					t.Errorf("%s = %v printed without --show-secrets", key, got)
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
	if err := runZero(command, server.URL, ""); err != nil {
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
	if err := runZero(command, server.URL, ""); err != nil {
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

// A Zero project is created in the region of the server that receives the
// request, and the API takes no region. Collapsing a regional endpoint onto the
// base host created the project in another region, or met zero_disabled where
// the asked-for region had Zero. The request has to reach the host as given.
func TestZeroUsesARegionalEndpointAsGiven(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	const regional = "https://nyc.cloud.appwrite.io/v1"

	var host, path string
	regionalZeroServer(t, regional, &host, &path)

	command, _ := captureCommand()
	if err := runZero(command, regional, ""); err != nil {
		t.Fatal(err)
	}

	if host != "nyc.cloud.appwrite.io" || path != "/v1/zero" {
		t.Errorf("created the project through %s%s, want nyc.cloud.appwrite.io/v1/zero", host, path)
	}

	global, err := preferences()
	if err != nil {
		t.Fatal(err)
	}
	session := global.SessionData(zeroSessionPrefix + "zero-project")
	if session == nil || session.GetString(config.PreferenceEndpoint) != regional {
		t.Errorf("session endpoint is not the regional one the manifest returned")
	}
}

// Once a project is claimed or deleted the API answers zero_not_found, the same
// as for a wrong secret. status has to say what that means rather than echo the
// API. It keeps the session, because the CLI cannot tell the cases apart and a
// still-unclaimed project's claim link must not be thrown away.
func TestZeroStatusReportsAProjectNoLongerUnclaimed(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)

	var requests []*http.Request
	server := zeroServer(t, &requests, zeroNotFound)

	command, _ := captureCommand()
	if err := runZero(command, server.URL, ""); err != nil {
		t.Fatal(err)
	}

	err := runZeroStatus(command)
	if err == nil {
		t.Fatal("status succeeded for a project the API no longer knows")
	}
	for _, want := range []string{"zero-project", "claimed or deleted", "https://console.example/project"} {
		if !strings.Contains(err.Error(), want) {
			t.Errorf("message %q does not mention %q", err, want)
		}
	}

	global, err := preferences()
	if err != nil {
		t.Fatal(err)
	}
	session := global.SessionData(zeroSessionPrefix + "zero-project")
	if session == nil || session.GetString(config.PreferenceKey) != "standard_zero-api-key" {
		t.Fatal("dropped the API key session")
	}
	if got := session.GetObject(preferenceZero).GetString("claimUrl"); got != "https://console.example/claim/code" {
		t.Errorf("claim link = %q, want it kept", got)
	}
}

// The API takes no region, so --region has to send the create to that region's
// host, and keep it as the session's endpoint for every later command.
func TestZeroRegionCreatesOnTheRegionalHost(t *testing.T) {
	preferencesWith(t, `{}`)
	inDirectory(t, t.TempDir())
	withZeroFlags(t, false, false)
	t.Setenv(sdk.EnvEndpoint, "")

	const regional = "https://syd.cloud.appwrite.io/v1"

	var host, path string
	regionalZeroServer(t, regional, &host, &path)

	command, _ := captureCommand()
	if err := runZero(command, "", "syd"); err != nil {
		t.Fatal(err)
	}

	if host != "syd.cloud.appwrite.io" || path != "/v1/zero" {
		t.Errorf("created the project through %s%s, want syd.cloud.appwrite.io/v1/zero", host, path)
	}

	global, err := preferences()
	if err != nil {
		t.Fatal(err)
	}
	session := global.SessionData(zeroSessionPrefix + "zero-project")
	if session == nil || session.GetString(config.PreferenceEndpoint) != regional {
		t.Errorf("session endpoint is not %s", regional)
	}
}

// --region and --endpoint both say where the project goes. Honouring either one
// silently would create it somewhere the user did not ask for, and spend one of
// the network's few Zero projects doing so.
func TestZeroRefusesARegionOrEndpointItCannotUse(t *testing.T) {
	cases := map[string]struct{ endpoint, region, want string }{
		"region with endpoint": {"https://fra.cloud.appwrite.io/v1", "nyc", "--region and --endpoint"},
		"unknown region":       {"", "mars", `unknown region "mars"`},
		"upper-case region":    {"", "FRA", `unknown region "FRA"`},
	}

	for name, test := range cases {
		t.Run(name, func(t *testing.T) {
			preferencesWith(t, `{}`)
			directory := t.TempDir()
			inDirectory(t, directory)
			withZeroFlags(t, false, false)
			t.Setenv(sdk.EnvEndpoint, "")

			var host, path string
			regionalZeroServer(t, "", &host, &path)

			command, _ := captureCommand()
			err := runZero(command, test.endpoint, test.region)
			if err == nil || !strings.Contains(err.Error(), test.want) {
				t.Fatalf("error = %v, want one mentioning %s", err, test.want)
			}

			if host != "" {
				t.Errorf("called %s%s before refusing", host, path)
			}
			global, err := preferences()
			if err != nil {
				t.Fatal(err)
			}
			if ids := global.SessionIDs(); len(ids) != 0 {
				t.Errorf("stored sessions %v before refusing", ids)
			}
			if _, err := os.Stat(config.LocalPath(directory)); !os.IsNotExist(err) {
				t.Errorf("wrote %s before refusing", config.LocalPath(directory))
			}
		})
	}
}

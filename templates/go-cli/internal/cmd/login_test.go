package cmd

import (
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
)

// `login --switch` was reported as "unknown flag". It was not alone: the port
// implemented only the cloud browser flow, so six of the TypeScript's seven
// options did not exist (generic.ts:121).
func TestLoginOffersEveryOption(t *testing.T) {
	command := newLoginCommand()

	for _, name := range []string{
		"endpoint", "email", "password", "mfa", "code", "switch", "new",
	} {
		if command.Flags().Lookup(name) == nil {
			t.Errorf("--%s is missing", name)
		}
	}
}

// Two ways to spell "not the current account" that mean different things:
// --switch moves to one already signed in, --new signs in again. Asking for
// both is a contradiction, not a preference.
func TestLoginRejectsSwitchWithNew(t *testing.T) {
	err := runLogin(newLoginCommand(), loginOptions{Switch: true, New: true})

	if err == nil || !strings.Contains(err.Error(), "not both") {
		t.Errorf("err = %v, want a complaint about using both", err)
	}
}

func TestLoginRejectsCredentialsWithSwitch(t *testing.T) {
	preferencesWith(t, `{}`)

	err := runLogin(newLoginCommand(), loginOptions{Switch: true, Password: "secret"})
	if err == nil || !strings.Contains(err.Error(), "cannot be used with --switch") {
		t.Errorf("err = %v, want incompatible switch credentials rejected", err)
	}
}

func TestSwitchSelectorsMatchRegionalEndpointAndEmail(t *testing.T) {
	global := preferencesWith(t, `{
  "prod-a": {
    "endpoint": "https://cloud.appwrite.io/v1",
    "email": "a@example.com",
    "accessToken": "one"
  },
  "prod-b": {
    "endpoint": "https://cloud.appwrite.io/v1",
    "email": "b@example.com",
    "accessToken": "two"
  },
  "staging-a": {
    "endpoint": "https://cloud.staging.appwrite.io/v1",
    "email": "a@example.com",
    "accessToken": "three"
  },
  "stub": {
    "endpoint": "https://cloud.appwrite.io/v1",
    "email": "stub@example.com"
  }
}`)

	regional := matchingSwitchAccounts(global, "https://sgp.cloud.appwrite.io/v1", "")
	if len(regional) != 2 {
		t.Fatalf("regional matches = %v, want the two production accounts", regional)
	}

	selected := matchingSwitchAccounts(
		global, "https://sgp.cloud.appwrite.io/v1", "B@EXAMPLE.COM")
	if len(selected) != 1 || selected[0].ID != "prod-b" {
		t.Errorf("selected = %v, want prod-b", selected)
	}

	byEmail := matchingSwitchAccounts(global, "", "a@example.com")
	if len(byEmail) != 2 {
		t.Errorf("email matches = %v, want production and staging", byEmail)
	}
}

func TestSwitchSelectorsDeduplicateStoredAccount(t *testing.T) {
	global := preferencesWith(t, `{
  "current": "new",
  "old": {
    "endpoint": "https://cloud.appwrite.io/v1",
    "email": "a@example.com",
    "accessToken": "old"
  },
  "new": {
    "endpoint": "https://fra.cloud.appwrite.io/v1",
    "email": "A@example.com",
    "accessToken": "new"
  }
}`)

	accounts := matchingSwitchAccounts(global, "", "")
	if len(accounts) != 1 || accounts[0].ID != "new" {
		t.Errorf("accounts = %v, want only the current duplicate", accounts)
	}
}

func TestSwitchSelectorsReportAmbiguityBeforeSwitching(t *testing.T) {
	global := preferencesWith(t, `{
  "one": {
    "endpoint": "https://cloud.appwrite.io/v1",
    "email": "a@example.com",
    "accessToken": "one"
  },
  "two": {
    "endpoint": "https://cloud.appwrite.io/v1",
    "email": "b@example.com",
    "accessToken": "two"
  }
}`)
	command, _ := captureCommand()

	err := switchAccount(command, global, "", "https://sgp.cloud.appwrite.io/v1", "")
	if err == nil || !strings.Contains(err.Error(), "Add --email") {
		t.Errorf("err = %v, want an actionable ambiguity error", err)
	}
}

func TestSwitchWithoutSelectorsIsActionableWhenHeadless(t *testing.T) {
	global := preferencesWith(t, `{
  "one": {
    "endpoint": "https://cloud.appwrite.io/v1",
    "email": "a@example.com",
    "accessToken": "one"
  }
}`)
	command, _ := captureCommand()

	err := switchAccount(command, global, "", "", "")
	if err == nil || !strings.Contains(err.Error(), "--endpoint and/or --email") {
		t.Errorf("err = %v, want selector flags named for a headless invocation", err)
	}
}

// Cloud sign-in happens in a browser, so an email and password given for it
// would be silently ignored. Saying so beats appearing to accept them.
func TestLoginDefaultsToProductionCloudEvenWhenAnotherEndpointIsCurrent(t *testing.T) {
	preferencesWith(t, `{
  "current": "staging",
  "staging": {
    "endpoint": "https://cloud.staging.appwrite.io/v1",
    "email": "a@example.com",
    "accessToken": "token"
  }
}`)

	if got := resolveLoginEndpoint(""); got != "https://cloud.appwrite.io/v1" {
		t.Errorf("endpoint = %q, want production Cloud", got)
	}
}

func TestLoginHonorsExplicitEndpoint(t *testing.T) {
	if got := resolveLoginEndpoint("https://cloud.staging.appwrite.io/v1"); got != "https://cloud.staging.appwrite.io/v1" {
		t.Errorf("endpoint = %q, want the explicit endpoint", got)
	}
}

func TestLoginRejectsPasswordOptionsAgainstCloud(t *testing.T) {
	err := runLogin(newLoginCommand(), loginOptions{
		Endpoint: "https://cloud.appwrite.io/v1",
		Email:    "someone@example.com",
		Password: "hunter2",
	})

	if err == nil || !strings.Contains(err.Error(), "browser") {
		t.Errorf("err = %v, want it to explain that Cloud signs in via the browser", err)
	}
}

// The endpoint is checked BEFORE anything is prompted for, so a typo fails
// immediately rather than after an email and a password have been typed.
func TestVerifyEndpointRejectsAServerThatIsNotAppwrite(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	if err := verifyEndpoint(server.URL, false); err == nil {
		t.Error("a server with no version was accepted as an Appwrite instance")
	}
}

func TestVerifyEndpointAcceptsAnAppwriteServer(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/health/version" {
			t.Errorf("asked for %s, want /health/version", r.URL.Path)
		}
		w.Write([]byte(`{"version":"1.8.0"}`))
	}))
	defer server.Close()

	if err := verifyEndpoint(server.URL, false); err != nil {
		t.Errorf("a healthy server was rejected: %v", err)
	}
}

func TestVerifyEndpointRejectsAMalformedURL(t *testing.T) {
	for _, endpoint := range []string{"not a url", "ftp://example.com/v1", ""} {
		if err := verifyEndpoint(endpoint, false); err == nil {
			t.Errorf("%q was accepted as an endpoint", endpoint)
		}
	}
}

// The email-and-password flow has no token: the session cookie IS the
// credential, and it arrives only on the Set-Cookie header of the sign-in
// response. Dropping it leaves a session that cannot authenticate anything.
func TestClientCapturesTheConsoleSessionCookie(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		// The session cookie comes FIRST and an unrelated one after it, so a
		// capture that does not filter ends up holding the wrong one. With the
		// order reversed, last-write-wins would pass either way.
		w.Header().Add("Set-Cookie", "a_session_console=secret; Path=/; HttpOnly")
		w.Header().Add("Set-Cookie", "unrelated=value; Path=/")
		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	api := client.New(server.URL, "test")
	if err := api.Call("POST", "/account/sessions/email", nil, nil); err != nil {
		t.Fatal(err)
	}

	if !strings.HasPrefix(api.SessionCookie(), "a_session_console=") {
		t.Errorf("SessionCookie = %q, want the console session cookie", api.SessionCookie())
	}
	if strings.Contains(api.SessionCookie(), "unrelated") {
		t.Error("an unrelated cookie was stored as the session")
	}
}

// A response that sets no cookie must not clear one already held, or a later
// request in the same flow would go out unauthenticated.
//
// Asserted on the request the server actually receives. `SetCookie` writes the
// outbound credential while `SessionCookie` holds what a response last set --
// two different fields, so checking `SessionCookie` here would pass whether or
// not the credential survived.
func TestClientKeepsTheCookieWhenAResponseSetsNone(t *testing.T) {
	var sent []string
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		sent = append(sent, r.Header.Get("Cookie"))
		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	api := client.New(server.URL, "test").SetCookie("a_session_console=kept")
	for range 2 {
		if err := api.Call("GET", "/account", nil, nil); err != nil {
			t.Fatal(err)
		}
	}

	for i, cookie := range sent {
		if !strings.Contains(cookie, "a_session_console=kept") {
			t.Errorf("request %d went out with Cookie %q, want the held credential", i+1, cookie)
		}
	}
}

// Every `*.appwrite.io` host takes the browser flow, and a staging deployment
// seeded from a production dump hands out the same account IDs. Keying a
// session on the subject alone let the second sign-in overwrite the first
// endpoint's session -- and its keyring refresh token, which shares the key.
func TestCloudSessionIDSeparatesEndpointsSharingASubject(t *testing.T) {
	production := cloudSessionID("https://cloud.appwrite.io/v1", "68a1b2c3d4e5f6")
	staging := cloudSessionID("https://stage.appwrite.io/v1", "68a1b2c3d4e5f6")

	if production == staging {
		t.Errorf("both endpoints keyed to %q, so one sign-in overwrites the other", production)
	}
}

// Signing in again to an account already signed in on the same endpoint has to
// land on the existing entry rather than accumulate a new one each time.
func TestCloudSessionIDIsStableForOneAccountAndEndpoint(t *testing.T) {
	first := cloudSessionID("https://cloud.appwrite.io/v1", "68a1b2c3d4e5f6")
	second := cloudSessionID("https://cloud.appwrite.io/v1", "68a1b2c3d4e5f6")

	if first != second {
		t.Errorf("%q then %q, want one stable key per account and endpoint", first, second)
	}
}

// Two self-hosted instances can share a host and differ only by scheme or base
// path -- one reverse-proxied at /staging/v1 and another at /prod/v1, or an http
// and an https URL for the same box during a migration. Keying on the host alone
// collapsed those onto one session.
func TestCloudSessionIDSeparatesEndpointsSharingAHost(t *testing.T) {
	for _, pair := range [][2]string{
		{"http://appwrite.example/v1", "https://appwrite.example/v1"},
		{"https://appwrite.example/staging/v1", "https://appwrite.example/prod/v1"},
		{"https://appwrite.example:8080/v1", "https://appwrite.example:9090/v1"},
	} {
		first := cloudSessionID(pair[0], "68a1b2c3d4e5f6")
		second := cloudSessionID(pair[1], "68a1b2c3d4e5f6")

		if first == second {
			t.Errorf("%s and %s both keyed to %q", pair[0], pair[1], first)
		}
	}
}

// The same instance typed differently must still be one session, or a trailing
// slash would strand the credentials of the session it already has.
func TestCloudSessionIDIgnoresSpellingOfTheSameEndpoint(t *testing.T) {
	for _, pair := range [][2]string{
		{"https://cloud.appwrite.io/v1", "https://cloud.appwrite.io/v1/"},
		{"https://Cloud.Appwrite.IO/v1", "https://cloud.appwrite.io/v1"},
	} {
		first := cloudSessionID(pair[0], "68a1b2c3d4e5f6")
		second := cloudSessionID(pair[1], "68a1b2c3d4e5f6")

		if first != second {
			t.Errorf("%s keyed to %q but %s keyed to %q", pair[0], first, pair[1], second)
		}
	}
}

// No subject claim leaves nothing stable to key on. Two such sign-ins must
// still not collide, so the fallback has to vary.
func TestCloudSessionIDFallsBackWithoutASubject(t *testing.T) {
	id := cloudSessionID("https://cloud.appwrite.io/v1", "")

	if id == "" || strings.HasPrefix(id, "@") {
		t.Errorf("id = %q, want a usable fallback key", id)
	}
}

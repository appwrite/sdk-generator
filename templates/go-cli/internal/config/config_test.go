package config

import (
	"os"
	"path/filepath"
	"testing"
)

// A real prefs.json, shaped exactly as the TypeScript CLI writes one. Key order
// here is the order the TS CLI produces, which is what the round-trip test
// pins.
const realPrefs = `{
    "current": "6a6f6194001a17532db8",
    "6a6f6194001a17532db8": {
        "endpoint": "https://cloud.staging.appwrite.io/v1",
        "clientId": "appwrite-cli",
        "accessToken": "header.payload.signature",
        "tokenExpiry": 1785687988066,
        "email": "someone@example.com",
        "cookie": "a_session_console=deleted"
    }
}`

// Invariant 2 and 6: reading a config and writing it back must not change the
// bytes. Go maps marshal with sorted keys, so a naive port would silently
// reorder every user's file on first write.
func TestGlobalRoundTripIsByteIdentical(t *testing.T) {
	path := filepath.Join(t.TempDir(), "prefs.json")
	if err := os.WriteFile(path, []byte(realPrefs), 0o600); err != nil {
		t.Fatal(err)
	}

	global := LoadGlobal(path)
	if err := global.Write(); err != nil {
		t.Fatal(err)
	}

	written, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}

	if string(written) != realPrefs {
		t.Errorf("round-trip changed the file.\n--- want ---\n%s\n--- got ---\n%s", realPrefs, written)
	}
}

// A millisecond timestamp exceeds float64's exact integer range in the general
// case; json.Number keeps every digit.
func TestGlobalPreservesLargeIntegers(t *testing.T) {
	path := filepath.Join(t.TempDir(), "prefs.json")
	if err := os.WriteFile(path, []byte(realPrefs), 0o600); err != nil {
		t.Fatal(err)
	}

	global := LoadGlobal(path)
	session := global.Current()
	if session == nil {
		t.Fatal("no current session")
	}

	if got := session.GetInt64(PreferenceTokenExpiry); got != 1785687988066 {
		t.Errorf("tokenExpiry = %d, want 1785687988066", got)
	}
}

func TestGlobalSessionsIgnoreSettingKeys(t *testing.T) {
	path := filepath.Join(t.TempDir(), "prefs.json")
	if err := os.WriteFile(path, []byte(realPrefs), 0o600); err != nil {
		t.Fatal(err)
	}

	global := LoadGlobal(path)
	ids := global.SessionIDs()
	if len(ids) != 1 || ids[0] != "6a6f6194001a17532db8" {
		t.Errorf("SessionIDs() = %v, want one session ID", ids)
	}

	session, ok := global.Session(ids[0])
	if !ok {
		t.Fatal("session not found")
	}
	if session.Email != "someone@example.com" {
		t.Errorf("email = %q", session.Email)
	}
	if session.Endpoint != "https://cloud.staging.appwrite.io/v1" {
		t.Errorf("endpoint = %q", session.Endpoint)
	}
}

// Deleting the active session must move `current`, never leave it dangling.
func TestDeleteCurrentSessionRepointsCurrent(t *testing.T) {
	global := &Global{path: filepath.Join(t.TempDir(), "prefs.json"), data: NewObject()}

	first, second := NewObject(), NewObject()
	first.Set(PreferenceEmail, "a@example.com")
	second.Set(PreferenceEmail, "b@example.com")
	global.AddSession("one", first)
	global.AddSession("two", second)

	global.DeleteSession("two")

	if got := global.CurrentSessionID(); got != "one" {
		t.Errorf("current = %q, want %q", got, "one")
	}

	global.DeleteSession("one")
	if got := global.CurrentSessionID(); got != "" {
		t.Errorf("current = %q, want empty after deleting the last session", got)
	}
}

func TestNormalizeCloudConsoleEndpoint(t *testing.T) {
	cases := []struct{ in, want string }{
		{"https://cloud.appwrite.io/v1", "https://cloud.appwrite.io/v1"},
		{"https://fra.cloud.appwrite.io/v1", "https://cloud.appwrite.io/v1"},
		{"https://syd.cloud.staging.appwrite.io/v1", "https://cloud.staging.appwrite.io/v1"},
		// Self-hosted endpoints pass through untouched.
		{"https://appwrite.example.com/v1", "https://appwrite.example.com/v1"},
		// Not a known region code, so not Cloud -- must not be normalised, or a
		// session stored for real Cloud could be selected for this host.
		{"https://evil.cloud.appwrite.io/v1", "https://evil.cloud.appwrite.io/v1"},
		// Multi-label prefixes are not regions either.
		{"https://a.b.cloud.appwrite.io/v1", "https://a.b.cloud.appwrite.io/v1"},
		{"not a url", "not a url"},
	}

	for _, tc := range cases {
		if got := NormalizeCloudConsoleEndpoint(tc.in); got != tc.want {
			t.Errorf("NormalizeCloudConsoleEndpoint(%q) = %q, want %q", tc.in, got, tc.want)
		}
	}
}

func TestEndpointsMatch(t *testing.T) {
	if !EndpointsMatch("https://fra.cloud.appwrite.io/v1", "https://cloud.appwrite.io/v1/") {
		t.Error("regional and base Cloud endpoints should match")
	}
	if EndpointsMatch("https://cloud.appwrite.io/v1", "https://appwrite.example.com/v1") {
		t.Error("Cloud and self-hosted endpoints should not match")
	}
	if EndpointsMatch("https://evil.cloud.appwrite.io/v1", "https://cloud.appwrite.io/v1") {
		t.Error("an unknown subdomain must not match Cloud")
	}
}

// A missing or corrupt file must yield empty preferences, not an error -- a
// broken prefs.json should never block `login`.
func TestLoadGlobalToleratesMissingAndCorruptFiles(t *testing.T) {
	missing := LoadGlobal(filepath.Join(t.TempDir(), "absent.json"))
	if missing.CurrentSessionID() != "" || len(missing.SessionIDs()) != 0 {
		t.Error("missing file should produce empty preferences")
	}

	path := filepath.Join(t.TempDir(), "prefs.json")
	if err := os.WriteFile(path, []byte("{not json"), 0o600); err != nil {
		t.Fatal(err)
	}
	corrupt := LoadGlobal(path)
	if corrupt.CurrentSessionID() != "" {
		t.Error("corrupt file should produce empty preferences")
	}
}

func TestWriteCreatesPrivateFile(t *testing.T) {
	path := filepath.Join(t.TempDir(), "nested", "prefs.json")
	global := LoadGlobal(path)
	global.SetCurrentSessionID("abc")
	if err := global.Write(); err != nil {
		t.Fatal(err)
	}

	info, err := os.Stat(path)
	if err != nil {
		t.Fatal(err)
	}
	if perm := info.Mode().Perm(); perm != 0o600 {
		t.Errorf("prefs.json mode = %o, want 600 -- it holds access tokens", perm)
	}
}

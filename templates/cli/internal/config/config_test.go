package config

import (
	"os"
	"path/filepath"
	"runtime"
	"testing"
)

// A real prefs.json. The key order here is the order the file is written in,
// which is what the round-trip test pins.
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
	// Windows has no Unix permission bits and reports 0666 for every file. The
	// token still needs protecting there; the OS keyring is what provides it,
	// and prefs.json is only the fallback.
	if perm := info.Mode().Perm(); runtime.GOOS != "windows" && perm != 0o600 {
		t.Errorf("prefs.json mode = %o, want 600 -- it holds access tokens", perm)
	}
}

// prefs.json holds access and refresh tokens, so neither it nor the directory
// around it may be readable by other accounts.
//
// Both cases matter, and the second is the common one: on most machines the
// directory and the file already exist -- and neither MkdirAll nor WriteFile
// changes the mode of something that is already there. Creating them correctly
// only protects a fresh install.
func TestGlobalWriteTightensPermissions(t *testing.T) {
	if runtime.GOOS == "windows" {
		t.Skip("POSIX permission bits")
	}

	t.Run("fresh install", func(t *testing.T) {
		directory := filepath.Join(t.TempDir(), ".appwrite")
		path := filepath.Join(directory, "prefs.json")

		if err := LoadGlobal(path).Write(); err != nil {
			t.Fatal(err)
		}

		assertPermissions(t, directory, 0o700)
		assertPermissions(t, path, 0o600)
	})

	t.Run("already created with looser modes", func(t *testing.T) {
		directory := filepath.Join(t.TempDir(), ".appwrite")
		if err := os.MkdirAll(directory, 0o755); err != nil {
			t.Fatal(err)
		}
		path := filepath.Join(directory, "prefs.json")
		if err := os.WriteFile(path, []byte(realPrefs), 0o644); err != nil {
			t.Fatal(err)
		}

		if err := LoadGlobal(path).Write(); err != nil {
			t.Fatal(err)
		}

		assertPermissions(t, directory, 0o700)
		assertPermissions(t, path, 0o600)
	})
}

func assertPermissions(t *testing.T, path string, want os.FileMode) {
	t.Helper()

	info, err := os.Stat(path)
	if err != nil {
		t.Fatal(err)
	}
	if got := info.Mode().Perm(); got != want {
		t.Errorf("%s has mode %04o, want %04o", path, got, want)
	}
}

// A write that cannot complete must leave the previous file exactly as it was.
// prefs.json holds every stored session, and LoadGlobal reads an unparseable one
// as empty preferences -- so a half-written file does not report an error, it
// silently logs the user out of everything.
func TestWriteFileAtomicallyKeepsTheOldFileWhenItFails(t *testing.T) {
	if runtime.GOOS == "windows" || os.Geteuid() == 0 {
		t.Skip("relies on directory permissions being enforced")
	}

	directory := t.TempDir()
	path := filepath.Join(directory, "prefs.json")
	if err := os.WriteFile(path, []byte(realPrefs), 0o600); err != nil {
		t.Fatal(err)
	}

	// No temporary file can be created here, so the write fails before it could
	// have replaced anything.
	if err := os.Chmod(directory, 0o500); err != nil {
		t.Fatal(err)
	}
	t.Cleanup(func() { _ = os.Chmod(directory, 0o700) })

	if err := writeFileAtomically(path, []byte(`{"current":"replaced"}`), 0o600); err == nil {
		t.Error("a write into an unwritable directory reported success")
	}

	contents, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if string(contents) != realPrefs {
		t.Errorf("a failed write damaged the previous preferences:\n%s", contents)
	}
}

// The temporary file must never outlive the call, on either path. Left behind in
// ~/.appwrite it would be a stray copy of the user's tokens.
func TestWriteFileAtomicallyLeavesNoTemporaryFile(t *testing.T) {
	directory := t.TempDir()
	path := filepath.Join(directory, "prefs.json")

	if err := writeFileAtomically(path, []byte(realPrefs), 0o600); err != nil {
		t.Fatal(err)
	}

	entries, err := os.ReadDir(directory)
	if err != nil {
		t.Fatal(err)
	}
	if len(entries) != 1 || entries[0].Name() != "prefs.json" {
		names := make([]string, 0, len(entries))
		for _, entry := range entries {
			names = append(names, entry.Name())
		}
		t.Errorf("directory holds %v, want only prefs.json", names)
	}
}

// Replacing an existing file must carry the requested mode rather than inherit
// the old one -- the rename installs a new inode, which is what lets an
// existing 0644 prefs.json become 0600 without a chmod.
func TestWriteFileAtomicallyReplacesContentsAndMode(t *testing.T) {
	if runtime.GOOS == "windows" {
		t.Skip("POSIX permission bits")
	}

	directory := t.TempDir()
	path := filepath.Join(directory, "prefs.json")
	if err := os.WriteFile(path, []byte("stale"), 0o644); err != nil {
		t.Fatal(err)
	}

	if err := writeFileAtomically(path, []byte(realPrefs), 0o600); err != nil {
		t.Fatal(err)
	}

	contents, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if string(contents) != realPrefs {
		t.Errorf("contents = %q", contents)
	}
	assertPermissions(t, path, 0o600)
}

// prefs.json used to hold one endpoint and cookie at the top level, before
// sessions replaced that with a keyed array. Reading a legacy file correctly is
// not enough: without lifting it into the new shape, an upgrading user's cookie
// sits somewhere nothing looks, and the first command tells them to log in.
func TestMigrateLegacySessionLiftsTheOldShape(t *testing.T) {
	const legacy = `{
    "endpoint": "https://selfhosted.example/v1",
    "cookie": "a_session_console=legacycookie"
}`

	path := filepath.Join(t.TempDir(), "prefs.json")
	if err := os.WriteFile(path, []byte(legacy), 0o600); err != nil {
		t.Fatal(err)
	}

	global := LoadGlobal(path)
	if !global.MigrateLegacySession("session1") {
		t.Fatal("a legacy prefs.json was not migrated")
	}

	if got := global.CurrentSessionID(); got != "session1" {
		t.Errorf("current session = %q, want the migrated one", got)
	}
	session := global.SessionData("session1")
	if session == nil {
		t.Fatal("the migrated session was not stored")
	}
	if got := session.GetString(PreferenceCookie); got != "a_session_console=legacycookie" {
		t.Errorf("cookie = %q, the credential was lost", got)
	}
	if got := session.GetString(PreferenceEndpoint); got != "https://selfhosted.example/v1" {
		t.Errorf("endpoint = %q", got)
	}
	if got := session.GetString(PreferenceEmail); got != LegacyEmail {
		t.Errorf("email = %q, want %q", got, LegacyEmail)
	}

	// The old keys have to go, or the migration runs again on every command and
	// adds a further session each time.
	if global.MigrateLegacySession("session2") {
		t.Error("the migration ran a second time")
	}
	if got := global.CurrentSessionID(); got != "session1" {
		t.Errorf("a second run moved the current session to %q", got)
	}
}

// Either key alone is not a legacy session. A bare endpoint is what
// `client --endpoint` writes before anyone signs in, and inventing a session
// from it would claim a login that never happened.
func TestMigrateLegacySessionIgnoresAPartialOrModernFile(t *testing.T) {
	for name, contents := range map[string]string{
		"endpoint only": `{"endpoint":"https://selfhosted.example/v1"}`,
		"cookie only":   `{"cookie":"a_session_console=x"}`,
		"empty":         `{}`,
		"already migrated": `{"current":"abc","abc":{` +
			`"endpoint":"https://cloud.appwrite.io/v1","email":"someone@example.com"}}`,
	} {
		t.Run(name, func(t *testing.T) {
			path := filepath.Join(t.TempDir(), "prefs.json")
			if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
				t.Fatal(err)
			}

			global := LoadGlobal(path)
			if global.MigrateLegacySession("new") {
				t.Errorf("migrated a file that is not a legacy session: %s", contents)
			}
		})
	}
}

package auth

import (
	"runtime"
	"testing"

	"github.com/zalando/go-keyring"
)

// Does the platform's own credential store actually hold a refresh token?
//
// This is the claim the README could not make about Windows. The suite already
// round-tripped a token through TokenStore, but TokenStore falls back to
// prefs.json when the keyring is unavailable and says nothing about which
// backend answered -- so a green Windows run proved the fallback worked, not the
// credential manager.
//
// So the store is exercised DIRECTLY here, with no fallback to hide behind. A
// platform with no reachable store skips, which is the honest outcome for a
// headless Linux runner and a container; a platform with one must round-trip.
func TestTheOSCredentialStoreRoundTripsAToken(t *testing.T) {
	// Not the real service name: this writes to the developer's own keychain
	// when run locally, and it must not collide with the entry their CLI uses.
	const service = "appwrite-cli-keyring-selftest"
	const account = "session-selftest"
	const secret = "round-trip-me"

	if err := keyring.Set(service, account, secret); err != nil {
		if runtime.GOOS == "windows" || runtime.GOOS == "darwin" {
			// Both ship a credential store that is always present, so a failure
			// here is a real finding rather than an absent dependency.
			t.Fatalf("%s has a credential store and it refused a write: %s", runtime.GOOS, err)
		}
		t.Skipf("no reachable credential store on %s: %s", runtime.GOOS, err)
	}
	t.Cleanup(func() { _ = keyring.Delete(service, account) })

	stored, err := keyring.Get(service, account)
	if err != nil {
		t.Fatalf("the credential store accepted a write and then could not read it back: %s", err)
	}
	if stored != secret {
		t.Fatalf("read back %q, want %q", stored, secret)
	}

	// Deleting has to work too, or `logout` leaves the credential behind on a
	// machine the user believes they signed out of.
	if err := keyring.Delete(service, account); err != nil {
		t.Fatalf("the credential could not be deleted: %s", err)
	}
	if _, err := keyring.Get(service, account); err == nil {
		t.Error("the credential survived its own deletion")
	}
}

// And the same round-trip through TokenStore, which is what the CLI actually
// calls -- including the prefs fallback, so this one passes everywhere.
func TestTokenStoreRoundTripsWhicheverBackendItLandsOn(t *testing.T) {
	global := newTestGlobal(t, "https://cloud.appwrite.io/v1", 0, "token")
	store := &TokenStore{Global: global}

	if err := store.SetRefresh("session-1", "stored-refresh"); err != nil {
		t.Fatal(err)
	}
	t.Cleanup(func() { _ = store.DeleteRefresh("session-1") })

	token, err := store.Refresh("session-1")
	if err != nil {
		t.Fatalf("a token that was just written could not be read: %v", err)
	}
	if token != "stored-refresh" {
		t.Errorf("token = %q, want stored-refresh", token)
	}

	if err := store.DeleteRefresh("session-1"); err != nil {
		t.Fatal(err)
	}
	if token, _ := store.Refresh("session-1"); token != "" {
		t.Errorf("the token survived deletion: %q", token)
	}
}

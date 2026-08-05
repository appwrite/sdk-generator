package auth

import (
	"errors"
	"fmt"
	"strings"
	"testing"
	"time"

	"github.com/zalando/go-keyring"
)

// The whole point of the message is the WHERE. A CLI that looked in the wrong
// keychain and a CLI whose session really is gone produce the same empty
// lookup, so naming what was searched is the only thing that tells them apart.
func TestMissingRefreshTokenNamesWhereItLooked(t *testing.T) {
	keyring.MockInit()

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(-time.Hour).UnixMilli(), "expired")
	store := &TokenStore{Global: global}

	token, err := store.Refresh("session-1")
	if token != "" {
		t.Fatalf("token = %q, want empty", token)
	}

	message := err.Error()
	if !strings.Contains(message, "session-1") {
		t.Errorf("the session id is missing: %s", message)
	}
	if !strings.Contains(message, global.Path()) {
		t.Errorf("the prefs file is not named: %s", message)
	}
	if !strings.Contains(message, storeName()) {
		t.Errorf("the credential store is not named: %s", message)
	}
}

// A store that FAILS reads differently from a store that answered "nothing
// here", because unlocking a keyring and signing in again are different
// actions.
func TestStoreFailureIsWordedAsAFailure(t *testing.T) {
	keyring.MockInitWithError(
		errors.New("The name org.freedesktop.secrets was not provided by any .service files"))

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(-time.Hour).UnixMilli(), "expired")

	_, err := (&TokenStore{Global: global}).Refresh("session-1")
	if err == nil {
		t.Fatal("a store that would not answer was reported as success")
	}

	if !strings.Contains(err.Error(), "could not be read") {
		t.Errorf("a store failure reads as an absent entry: %s", err)
	}
	if !strings.Contains(err.Error(), "org.freedesktop.secrets") {
		t.Errorf("the store's own reason was dropped: %s", err)
	}
}

// A found token is not an error, whichever store held it -- and the prefs
// fallback wins, because that is where SetRefresh writes when the keyring is
// unavailable.
func TestRefreshPrefersAnyStoreThatHasTheToken(t *testing.T) {
	keyring.MockInitWithError(errors.New("no secret service"))

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(-time.Hour).UnixMilli(), "expired")
	seedPrefsRefreshToken(t, global, "session-1", "from-prefs")

	token, err := (&TokenStore{Global: global}).Refresh("session-1")
	if err != nil {
		t.Fatalf("a token in prefs was still reported as missing: %v", err)
	}
	if token != "from-prefs" {
		t.Errorf("token = %q, want from-prefs", token)
	}
}

// --verbose has to say which store answered. It printed nothing at all before,
// which is what turned a one-line diagnosis into a long one.
func TestRefreshTracesTheStoreItConsulted(t *testing.T) {
	keyring.MockInit()

	var traced []string
	Trace = func(format string, arguments ...any) {
		traced = append(traced, format)
	}
	t.Cleanup(func() { Trace = nil })

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(-time.Hour).UnixMilli(), "expired")

	if _, err := (&TokenStore{Global: global}).Refresh("session-1"); err == nil {
		t.Fatal("expected a missing token")
	}
	if len(traced) == 0 {
		t.Fatal("a failed store read was not traced")
	}

	// And on the way through, so a working read is visible too.
	traced = nil
	if err := (&TokenStore{Global: global}).SetRefresh("session-1", "fresh"); err != nil {
		t.Fatal(err)
	}
	if _, err := (&TokenStore{Global: global}).Refresh("session-1"); err != nil {
		t.Fatal(err)
	}
	if len(traced) == 0 {
		t.Error("a successful store read was not traced")
	}
}

// Tracing is opt-in: an unset sink must not panic, because every command that
// is not --verbose takes that path.
func TestRefreshWithoutATraceSink(t *testing.T) {
	keyring.MockInit()
	Trace = nil

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(-time.Hour).UnixMilli(), "expired")

	if _, err := (&TokenStore{Global: global}).Refresh("session-1"); err == nil {
		t.Fatal("expected a missing token")
	}
}

// The fallback to plaintext is deliberate -- headless Linux and CI have no
// secret service, and a CLI that refuses to hold a session there cannot be
// scripted. What was wrong is that it happened in silence: the keyring error was
// discarded, and Trace was wired only on the read path, so even --verbose said
// nothing. Where a long-lived refresh token ends up is the user's to know.
func TestSetRefreshWarnsWhenItFallsBackToPlaintext(t *testing.T) {
	keyring.MockInitWithError(errors.New("no secret service"))

	var warnings []string
	Warn = func(format string, arguments ...any) {
		warnings = append(warnings, fmt.Sprintf(format, arguments...))
	}
	t.Cleanup(func() { Warn = nil })

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(time.Hour).UnixMilli(), "token")

	if err := (&TokenStore{Global: global}).SetRefresh("session-1", "rotated"); err != nil {
		t.Fatalf("the fallback must still store the token: %v", err)
	}

	if len(warnings) != 1 {
		t.Fatalf("got %d warnings, want exactly one: %v", len(warnings), warnings)
	}
	// Naming the file is the point: the user has to know which one now holds a
	// credential.
	if !strings.Contains(warnings[0], global.Path()) {
		t.Errorf("the warning does not name the file it wrote to: %q", warnings[0])
	}

	// And it genuinely fell back, rather than warning and losing the token.
	token, err := (&TokenStore{Global: global}).Refresh("session-1")
	if err != nil || token != "rotated" {
		t.Errorf("Refresh() = %q, %v -- the token was not stored", token, err)
	}
}

// A working keyring must stay quiet, or the warning becomes noise on every
// machine and stops meaning anything.
func TestSetRefreshIsQuietWhenTheKeyringWorks(t *testing.T) {
	keyring.MockInit()

	var warnings []string
	Warn = func(format string, arguments ...any) {
		warnings = append(warnings, fmt.Sprintf(format, arguments...))
	}
	t.Cleanup(func() { Warn = nil })

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(time.Hour).UnixMilli(), "token")

	if err := (&TokenStore{Global: global}).SetRefresh("session-1", "rotated"); err != nil {
		t.Fatal(err)
	}

	if len(warnings) != 0 {
		t.Errorf("warned about a keyring that worked: %v", warnings)
	}
}

// With no keyring and no session to fall back into, the token has nowhere to go.
// Reporting success there is a lie with consequences: the caller has just
// rotated the token, so the old one is already dead server-side, and the next
// command finds no credential and cannot say why.
func TestSetRefreshFailsWhenThereIsNowhereToStoreTheToken(t *testing.T) {
	keyring.MockInitWithError(errors.New("no secret service"))

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(time.Hour).UnixMilli(), "token")

	err := (&TokenStore{Global: global}).SetRefresh("session-that-does-not-exist", "rotated")
	if err == nil {
		t.Fatal("storing a token with nowhere to put it reported success")
	}
	if !strings.Contains(err.Error(), "session-that-does-not-exist") {
		t.Errorf("the error does not name the session: %v", err)
	}
}

// Writing an empty token is a delete, not a fallback, so it must not warn.
func TestSetRefreshWithAnEmptyTokenDoesNotWarn(t *testing.T) {
	keyring.MockInitWithError(errors.New("no secret service"))

	var warnings []string
	Warn = func(format string, arguments ...any) {
		warnings = append(warnings, fmt.Sprintf(format, arguments...))
	}
	t.Cleanup(func() { Warn = nil })

	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(time.Hour).UnixMilli(), "token")

	if err := (&TokenStore{Global: global}).SetRefresh("session-1", ""); err != nil {
		t.Fatal(err)
	}
	if len(warnings) != 0 {
		t.Errorf("clearing a token warned about plaintext: %v", warnings)
	}
}

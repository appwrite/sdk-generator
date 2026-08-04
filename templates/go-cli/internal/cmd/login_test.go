package cmd

import (
	"strings"
	"testing"
)

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

// No subject claim leaves nothing stable to key on. The fallback still has to
// produce a usable key rather than one that starts at the separator.
func TestCloudSessionIDFallsBackWithoutASubject(t *testing.T) {
	id := cloudSessionID("https://cloud.appwrite.io/v1", "")

	if id == "" || strings.HasPrefix(id, "@") {
		t.Errorf("id = %q, want a usable fallback key", id)
	}
}

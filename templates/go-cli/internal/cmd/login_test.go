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

// No subject claim leaves nothing stable to key on. The fallback still has to
// produce a usable key rather than one that starts at the separator.
func TestCloudSessionIDFallsBackWithoutASubject(t *testing.T) {
	id := cloudSessionID("https://cloud.appwrite.io/v1", "")

	if id == "" || strings.HasPrefix(id, "@") {
		t.Errorf("id = %q, want a usable fallback key", id)
	}
}

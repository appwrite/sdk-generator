package auth

import (
	"encoding/base64"
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"testing"
	"time"
)

// deviceServer stands in for the token endpoint, replying with the queued
// responses in order.
func deviceServer(t *testing.T, responses []func(w http.ResponseWriter)) (*httptest.Server, *int) {
	t.Helper()

	calls := 0
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		index := calls
		calls++
		if index >= len(responses) {
			t.Errorf("unexpected extra poll %d", index)

			return
		}
		responses[index](w)
	}))
	t.Cleanup(server.Close)

	return server, &calls
}

func pendingResponse(w http.ResponseWriter) {
	w.WriteHeader(http.StatusBadRequest)
	_ = json.NewEncoder(w).Encode(map[string]any{"error": "authorization_pending"})
}

func slowDownResponse(w http.ResponseWriter) {
	w.WriteHeader(http.StatusBadRequest)
	_ = json.NewEncoder(w).Encode(map[string]any{"error": "slow_down"})
}

func legacyPendingTypeResponse(w http.ResponseWriter) {
	w.WriteHeader(http.StatusBadRequest)
	_ = json.NewEncoder(w).Encode(map[string]any{"type": "authorization_pending"})
}

func legacyPendingMessageResponse(w http.ResponseWriter) {
	w.WriteHeader(http.StatusBadRequest)
	_ = json.NewEncoder(w).Encode(map[string]any{"message": "authorization_pending"})
}

func tokenGrantedResponse(w http.ResponseWriter) {
	_ = json.NewEncoder(w).Encode(map[string]any{
		"access_token":  "granted",
		"refresh_token": "refresh",
		"expires_in":    3600,
	})
}

func newTestFlow(t *testing.T, endpoint string) (*DeviceFlow, *[]time.Duration) {
	t.Helper()

	slept := []time.Duration{}
	flow := NewDeviceFlow(endpoint, "0.0.1")
	flow.Sleep = func(d time.Duration) { slept = append(slept, d) }
	flow.Now = func() time.Time { return fixedNow }

	return flow, &slept
}

func testAuthorization() DeviceAuthorization {
	return DeviceAuthorization{
		DeviceCode: "device-code",
		UserCode:   "ABCD-EFGH",
		ExpiresIn:  json.Number("600"),
		Interval:   json.Number("5"),
	}
}

// Polling continues through pending responses and stops on the granted token.
func TestPollSucceedsAfterPendingResponses(t *testing.T) {
	server, calls := deviceServer(t, []func(http.ResponseWriter){
		pendingResponse, pendingResponse, tokenGrantedResponse,
	})

	flow, slept := newTestFlow(t, server.URL)
	token, err := flow.Poll(testAuthorization())
	if err != nil {
		t.Fatal(err)
	}
	if token.AccessToken != "granted" {
		t.Errorf("access token = %q", token.AccessToken)
	}
	if *calls != 3 {
		t.Errorf("polls = %d, want 3", *calls)
	}
	for _, interval := range *slept {
		if interval != 5*time.Second {
			t.Errorf("poll interval = %s, want 5s", interval)
		}
	}
}

// Older endpoints used Appwrite's type/message error envelope. Keep accepting
// both shapes while preferring the standard OAuth error field.
func TestPollAcceptsLegacyPendingErrorShapes(t *testing.T) {
	for _, test := range []struct {
		name     string
		response func(http.ResponseWriter)
	}{
		{name: "type", response: legacyPendingTypeResponse},
		{name: "message", response: legacyPendingMessageResponse},
	} {
		t.Run(test.name, func(t *testing.T) {
			server, calls := deviceServer(t, []func(http.ResponseWriter){test.response, tokenGrantedResponse})
			flow, _ := newTestFlow(t, server.URL)

			if _, err := flow.Poll(testAuthorization()); err != nil {
				t.Fatal(err)
			}
			if *calls != 2 {
				t.Errorf("polls = %d, want 2", *calls)
			}
		})
	}
}

// RFC 8628 section 3.5: `slow_down` widens the interval by five seconds, and
// the widened interval persists for subsequent polls.
func TestPollWidensIntervalOnSlowDown(t *testing.T) {
	server, _ := deviceServer(t, []func(http.ResponseWriter){
		slowDownResponse, pendingResponse, tokenGrantedResponse,
	})

	flow, slept := newTestFlow(t, server.URL)
	if _, err := flow.Poll(testAuthorization()); err != nil {
		t.Fatal(err)
	}

	want := []time.Duration{5 * time.Second, 10 * time.Second, 10 * time.Second}
	if len(*slept) != len(want) {
		t.Fatalf("slept = %v, want %v", *slept, want)
	}
	for i, interval := range *slept {
		if interval != want[i] {
			t.Errorf("poll %d slept %s, want %s", i, interval, want[i])
		}
	}
}

// An empty error body -- a 400 with no type and no message -- is transient.
// Aborting the whole login on one blank response is a worse failure than one
// more round trip.
func TestPollTreatsEmptyErrorBodyAsPending(t *testing.T) {
	empty := func(w http.ResponseWriter) {
		w.WriteHeader(http.StatusBadRequest)
		_, _ = w.Write([]byte(`{}`))
	}

	server, calls := deviceServer(t, []func(http.ResponseWriter){empty, tokenGrantedResponse})

	flow, _ := newTestFlow(t, server.URL)
	if _, err := flow.Poll(testAuthorization()); err != nil {
		t.Fatal(err)
	}
	if *calls != 2 {
		t.Errorf("polls = %d, want 2", *calls)
	}
}

// A real error -- access_denied -- must abort rather than spin until the
// authorization expires.
func TestPollAbortsOnRealError(t *testing.T) {
	denied := func(w http.ResponseWriter) {
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]any{"error": "access_denied"})
	}

	server, calls := deviceServer(t, []func(http.ResponseWriter){denied})

	flow, _ := newTestFlow(t, server.URL)
	if _, err := flow.Poll(testAuthorization()); err == nil {
		t.Fatal("expected an error")
	}
	if *calls != 1 {
		t.Errorf("polls = %d, want 1", *calls)
	}
}

// When the window closes the caller gets a distinguishable error rather than a
// nil token.
func TestPollExpires(t *testing.T) {
	server, _ := deviceServer(t, []func(http.ResponseWriter){})

	flow, _ := newTestFlow(t, server.URL)
	authorization := testAuthorization()
	authorization.ExpiresIn = json.Number("0")

	if _, err := flow.Poll(authorization); err != ErrDeviceAuthorizationExpired {
		t.Errorf("err = %v, want ErrDeviceAuthorizationExpired", err)
	}
}

// A server that omits the interval must not produce a busy loop.
func TestPollIntervalDefaultsWhenMissing(t *testing.T) {
	authorization := DeviceAuthorization{}
	if got := authorization.PollInterval(); got != defaultPollInterval {
		t.Errorf("interval = %s, want %s", got, defaultPollInterval)
	}

	authorization.Interval = json.Number("0")
	if got := authorization.PollInterval(); got != defaultPollInterval {
		t.Errorf("zero interval = %s, want %s", got, defaultPollInterval)
	}
}

// The URL with the code embedded is preferred so the user does not retype it.
func TestVerificationURLPrefersCompleteForm(t *testing.T) {
	authorization := DeviceAuthorization{
		VerificationURI:         "https://example.com/device",
		VerificationURIComplete: "https://example.com/device?code=ABCD",
	}
	if got := authorization.VerificationURL(); got != authorization.VerificationURIComplete {
		t.Errorf("URL = %q, want the complete form", got)
	}

	authorization.VerificationURIComplete = ""
	if got := authorization.VerificationURL(); got != authorization.VerificationURI {
		t.Errorf("URL = %q, want the plain form", got)
	}
}

func TestDecodeIDToken(t *testing.T) {
	claims := map[string]any{"email": "a@b.c", "name": "Test User", "sub": "user-1"}
	payload, err := json.Marshal(claims)
	if err != nil {
		t.Fatal(err)
	}
	idToken := "header." + base64.RawURLEncoding.EncodeToString(payload) + ".signature"

	email, name, subject := DecodeIDToken(idToken)
	if email != "a@b.c" || name != "Test User" || subject != "user-1" {
		t.Errorf("claims = %q, %q, %q", email, name, subject)
	}
}

// A malformed token must return empty claims rather than panic -- it is only
// used to label the stored session, so it is never worth failing a login over.
func TestDecodeIDTokenToleratesGarbage(t *testing.T) {
	for _, idToken := range []string{"", "onlyonepart", "a.!!!notbase64!!!.c", "a.e30.c"} {
		email, name, subject := DecodeIDToken(idToken)
		if idToken == "a.e30.c" {
			continue // valid but empty claims
		}
		if email != "" || name != "" || subject != "" {
			t.Errorf("DecodeIDToken(%q) = %q, %q, %q, want empty", idToken, email, name, subject)
		}
	}
}

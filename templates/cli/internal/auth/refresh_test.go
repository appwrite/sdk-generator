package auth

import (
	"encoding/json"
	"errors"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
)

// fixedNow keeps expiry arithmetic deterministic.
var fixedNow = time.Date(2026, 8, 2, 12, 0, 0, 0, time.UTC)

// newTestGlobal writes a prefs file with one session and returns it loaded.
func newTestGlobal(t *testing.T, endpoint string, expiry int64, accessToken string) *config.Global {
	t.Helper()

	prefs := map[string]any{
		"current": "session-1",
		"session-1": map[string]any{
			"endpoint":    endpoint,
			"accessToken": accessToken,
			"tokenExpiry": expiry,
			"email":       "someone@example.com",
		},
	}
	encoded, err := json.Marshal(prefs)
	if err != nil {
		t.Fatal(err)
	}

	path := filepath.Join(t.TempDir(), "prefs.json")
	if err := os.WriteFile(path, encoded, 0o600); err != nil {
		t.Fatal(err)
	}

	return config.LoadGlobal(path)
}

// A token comfortably inside its lifetime must be returned untouched -- no
// network call, no rewrite of the config.
func TestAccessTokenReturnsValidTokenWithoutRefreshing(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		t.Errorf("unexpected refresh request to %s", r.URL.Path)
	}))
	defer server.Close()

	expiry := fixedNow.Add(time.Hour).UnixMilli()
	global := newTestGlobal(t, server.URL, expiry, "still-good")

	authenticator := NewAuthenticator(global, "0.0.1")
	authenticator.Now = func() time.Time { return fixedNow }

	token, err := authenticator.AccessToken(false)
	if err != nil {
		t.Fatal(err)
	}
	if token != "still-good" {
		t.Errorf("token = %q, want %q", token, "still-good")
	}
}

// A token inside the skew window is treated as stale: it would otherwise expire
// mid-request.
func TestAccessTokenRefreshesInsideSkewWindow(t *testing.T) {
	var requests int
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		requests++

		var body map[string]any
		_ = json.NewDecoder(r.Body).Decode(&body)
		if body["grant_type"] != "refresh_token" {
			t.Errorf("grant_type = %v", body["grant_type"])
		}
		if body["refresh_token"] != "stored-refresh" {
			t.Errorf("refresh_token = %v", body["refresh_token"])
		}
		if body["client_id"] != DefaultClientID {
			t.Errorf("client_id = %v", body["client_id"])
		}

		_ = json.NewEncoder(w).Encode(map[string]any{
			"access_token": "fresh-token",
			"expires_in":   3600,
		})
	}))
	defer server.Close()

	// Expires in 30s: still valid, but inside the 60s skew.
	expiry := fixedNow.Add(30 * time.Second).UnixMilli()
	global := newTestGlobal(t, server.URL, expiry, "about-to-expire")
	seedPrefsRefreshToken(t, global, "session-1", "stored-refresh")

	authenticator := NewAuthenticator(global, "0.0.1")
	authenticator.Now = func() time.Time { return fixedNow }

	token, err := authenticator.AccessToken(false)
	if err != nil {
		t.Fatal(err)
	}
	if token != "fresh-token" {
		t.Errorf("token = %q, want %q", token, "fresh-token")
	}
	if requests != 1 {
		t.Errorf("refresh requests = %d, want 1", requests)
	}

	// The new token and its expiry must be persisted, or every command refreshes.
	reloaded := config.LoadGlobal(global.Path())
	session := reloaded.Current()
	if got := session.GetString(config.PreferenceAccessToken); got != "fresh-token" {
		t.Errorf("persisted access token = %q", got)
	}
	wantExpiry := fixedNow.Add(3600 * time.Second).UnixMilli()
	if got := session.GetInt64(config.PreferenceTokenExpiry); got != wantExpiry {
		t.Errorf("persisted expiry = %d, want %d", got, wantExpiry)
	}
}

// An invalid refresh grant means the server refused the stored session. The
// sentence names that session and the recovery action; the server's description
// stays in the unwrap chain, where --verbose reads it.
func TestAccessTokenReportsRejectedSession(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]any{
			"error":             "invalid_grant",
			"error_description": "Invalid refresh token provided.",
		})
	}))
	defer server.Close()

	global := newTestGlobal(t, server.URL, 0, "expired")
	seedPrefsRefreshToken(t, global, "session-1", "invalid-refresh")

	_, err := NewAuthenticator(global, "0.0.1").AccessToken(true)
	if err == nil {
		t.Fatal("expected refresh to fail")
	}

	message := err.Error()
	if !strings.Contains(message, "someone@example.com") {
		t.Errorf("rejection = %q, does not name the session", message)
	}
	if !strings.Contains(message, "`"+client.ExecutableName+" login`") {
		t.Errorf("rejection = %q, does not name the recovery command", message)
	}
	// The description restates the rejection, so it belongs to --verbose rather
	// than to a sentence that has to fit on one line.
	if strings.Contains(message, "Invalid refresh token provided.") {
		t.Errorf("rejection = %q, repeats the server description", message)
	}

	if !errors.Is(err, ErrSessionExpired) {
		t.Errorf("errors.Is(err, ErrSessionExpired) = false: %v", err)
	}

	var apiError *client.APIError
	if !errors.As(err, &apiError) {
		t.Fatalf("refresh error does not retain *client.APIError: %v", err)
	}
	if apiError.OAuthDescription != "Invalid refresh token provided." {
		t.Errorf("retained description = %q", apiError.OAuthDescription)
	}
}

// The server may rotate the refresh token. Failing to persist the new one would
// leave the CLI holding a token that no longer works.
func TestAccessTokenPersistsRotatedRefreshToken(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		_ = json.NewEncoder(w).Encode(map[string]any{
			"access_token":  "fresh-token",
			"refresh_token": "rotated-refresh",
			"expires_in":    3600,
		})
	}))
	defer server.Close()

	global := newTestGlobal(t, server.URL, 0, "expired")
	seedPrefsRefreshToken(t, global, "session-1", "stored-refresh")

	authenticator := NewAuthenticator(global, "0.0.1")
	authenticator.Now = func() time.Time { return fixedNow }

	// Whichever backend SetRefresh lands on -- the keyring where one exists,
	// prefs otherwise -- Refresh must read the rotated value back.
	t.Cleanup(func() { _ = authenticator.Store.DeleteRefresh("session-1") })

	if _, err := authenticator.AccessToken(true); err != nil {
		t.Fatal(err)
	}

	if stored, _ := authenticator.Store.Refresh("session-1"); stored != "rotated-refresh" {
		t.Errorf("stored refresh token = %q, want %q", stored, "rotated-refresh")
	}
}

// No refresh token and an expired access token means the session cannot be
// renewed. The message has to say WHICH session and WHERE the CLI looked, not
// merely assert that it expired -- see cannotRefresh.
func TestAccessTokenWithoutRefreshTokenFailsCleanly(t *testing.T) {
	global := newTestGlobal(t, "https://cloud.appwrite.io/v1",
		fixedNow.Add(-time.Hour).UnixMilli(), "expired")

	authenticator := NewAuthenticator(global, "0.0.1")
	authenticator.Now = func() time.Time { return fixedNow }

	_, err := authenticator.AccessToken(false)
	if err == nil {
		t.Fatal("expected an error")
	}

	message := err.Error()
	for _, want := range []string{
		"someone@example.com",          // which account
		"https://cloud.appwrite.io/v1", // and which environment
		"session-1",                    // the id the store was asked for
		"login",                        // and what to do about it
	} {
		if !strings.Contains(message, want) {
			t.Errorf("the message does not mention %q: %s", want, message)
		}
	}

	// The lookup is recoverable by a caller, not just printed.
	var missing *MissingRefreshToken
	if !errors.As(err, &missing) {
		t.Errorf("err does not carry a MissingRefreshToken: %v", err)
	}
}

// An expiry of zero means the issuing flow reported none. With no refresh token
// available, the access token is used as-is rather than rejected outright.
func TestAccessTokenWithZeroExpiryAndNoRefreshTokenIsUsedAsIs(t *testing.T) {
	global := newTestGlobal(t, "https://cloud.appwrite.io/v1", 0, "no-expiry-token")

	authenticator := NewAuthenticator(global, "0.0.1")
	authenticator.Now = func() time.Time { return fixedNow }

	token, err := authenticator.AccessToken(false)
	if err != nil {
		t.Fatal(err)
	}
	if token != "no-expiry-token" {
		t.Errorf("token = %q", token)
	}
}

func TestAccessTokenWithoutSessionFails(t *testing.T) {
	global := config.LoadGlobal(filepath.Join(t.TempDir(), "absent.json"))

	// No session at all is the one case with no lookup behind it, so the
	// sentinel is still what a caller gets.
	if _, err := NewAuthenticator(global, "0.0.1").AccessToken(false); !errors.Is(err, ErrSessionExpired) {
		t.Errorf("err = %v, want ErrSessionExpired", err)
	}
}

// seedPrefsRefreshToken writes a refresh token into the prefs fallback, which
// is the path headless environments without a keyring take.
func seedPrefsRefreshToken(t *testing.T, global *config.Global, sessionID, token string) {
	t.Helper()

	session := global.SessionData(sessionID)
	if session == nil {
		t.Fatalf("session %q not found", sessionID)
	}
	session.Set(config.PreferenceRefreshToken, token)
	if err := global.Write(); err != nil {
		t.Fatal(err)
	}
}

// Timestamps must be written as integer literals; scientific notation would not
// parse back as an integer.
func TestRefreshWritesIntegerTimestamp(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		_ = json.NewEncoder(w).Encode(map[string]any{
			"access_token": "fresh-token",
			"expires_in":   3600,
		})
	}))
	defer server.Close()

	global := newTestGlobal(t, server.URL, 0, "expired")
	seedPrefsRefreshToken(t, global, "session-1", "stored-refresh")

	authenticator := NewAuthenticator(global, "0.0.1")
	authenticator.Now = func() time.Time { return fixedNow }
	if _, err := authenticator.AccessToken(true); err != nil {
		t.Fatal(err)
	}

	raw, err := os.ReadFile(global.Path())
	if err != nil {
		t.Fatal(err)
	}
	want := strconv.FormatInt(fixedNow.Add(3600*time.Second).UnixMilli(), 10)
	if !strings.Contains(string(raw), want) {
		t.Errorf("expiry %s not written as an integer literal:\n%s", want, raw)
	}
}

//go:build !js

package auth

import (
	"errors"
	"fmt"
	"runtime"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/zalando/go-keyring"
)

// refreshTokenService is the keyring service refresh tokens are filed under.
//
// `${EXECUTABLE_NAME}-oauth-refresh-token`, kept stable across releases.
// Interop is not an invariant -- re-authenticating once on upgrade is
// acceptable -- but a stable name costs nothing and spares users a login if the
// platform keyring cooperates.
const refreshTokenService = "appwrite-oauth-refresh-token"

// prefsRefreshTokenKey is where a refresh token lands when no keyring is
// available.
const prefsRefreshTokenKey = config.PreferenceRefreshToken

// TokenStore reads and writes refresh tokens, preferring the OS keyring and
// falling back to the preferences file.
//
// The fallback is not a convenience: headless Linux and CI containers have no
// secret service, and a CLI that refuses to hold a session there is a CLI that
// cannot be scripted.
type TokenStore struct {
	Global *config.Global
}

// Trace receives one line per credential-store read when --verbose is on.
//
// A package variable for the same reason client.RequestLog is one: the store is
// reached deep inside the auth path and diagnostics should not have to be
// threaded through every caller. Set during start-up, before any read.
var Trace func(format string, arguments ...any)

func trace(format string, arguments ...any) {
	if Trace != nil {
		Trace(format, arguments...)
	}
}

// Warn receives a message the user should see whether or not --verbose is on.
//
// Separate from Trace because falling back to plaintext is not diagnostics: it
// changes where the user's refresh token is stored, and staying quiet about it
// meant a headless Linux box or a locked keychain wrote a long-lived credential
// to disk with nothing said. Wired the same way Trace is, for the same reason.
var Warn func(format string, arguments ...any)

func warn(format string, arguments ...any) {
	if Warn != nil {
		Warn(format, arguments...)
	}
}

// storeName is what the platform calls its credential store, so a message about
// one names the thing the user would go and look at.
func storeName() string {
	switch runtime.GOOS {
	case "darwin":
		return "the macOS keychain"
	case "windows":
		return "the Windows credential manager"
	default:
		return "the system keyring"
	}
}

// MissingRefreshToken reports that a session has no refresh token, and says
// where the CLI looked.
//
// The where is the point: "session expired" is a conclusion, and it is wrong
// whenever the CLI is looking somewhere other than where the token is -- a
// redirected HOME points macOS at a different login keychain and a good session
// reads as expired. That case cannot be told apart from a genuinely absent entry
// by the error alone, since `security` exits 44 for both, so naming the store
// and session id is the only honest thing available.
type MissingRefreshToken struct {
	SessionID string
	Store     string
	// StoreErr is what the credential store said. A not-found error means it
	// answered; anything else means it failed, which is worth wording
	// differently because unlocking a keyring and signing in again are
	// different actions.
	StoreErr  error
	PrefsPath string
}

func (e *MissingRefreshToken) Error() string {
	if e.StoreErr != nil && !errors.Is(e.StoreErr, keyring.ErrNotFound) {
		return fmt.Sprintf("%s could not be read (%s), and %s holds no token for session %s",
			e.Store, e.StoreErr, e.PrefsPath, e.SessionID)
	}

	return fmt.Sprintf("%s has no entry for session %s, and neither does %s",
		e.Store, e.SessionID, e.PrefsPath)
}

// Unwrap exposes the store's own error, so a caller can still match on it.
func (e *MissingRefreshToken) Unwrap() error { return e.StoreErr }

// Refresh returns the stored refresh token for a session.
//
// The failed lookup is described rather than reduced to "": "no token here" and
// "the store would not answer" are different advice. See MissingRefreshToken.
//
// A token in prefs wins over the store's opinion -- it is the fallback SetRefresh
// writes to when the keyring is unavailable, so the store not answering does not
// matter.
func (s *TokenStore) Refresh(sessionID string) (string, error) {
	if sessionID == "" {
		return "", &MissingRefreshToken{Store: storeName(), PrefsPath: s.prefsPath()}
	}

	token, storeErr := keyring.Get(refreshTokenService, sessionID)
	if storeErr == nil && token != "" {
		trace("read the refresh token for %s from %s", sessionID, storeName())

		return token, nil
	}

	if storeErr != nil {
		trace("%s returned no refresh token for %s: %s", storeName(), sessionID, storeErr)
	}

	if session := s.Global.SessionData(sessionID); session != nil {
		if fallback := session.GetString(prefsRefreshTokenKey); fallback != "" {
			trace("read the refresh token for %s from %s", sessionID, s.prefsPath())

			return fallback, nil
		}
	}

	return "", &MissingRefreshToken{
		SessionID: sessionID,
		Store:     storeName(),
		StoreErr:  storeErr,
		PrefsPath: s.prefsPath(),
	}
}

// prefsPath names the fallback store in a message, and falls back to the file's
// own name when the path is not known.
func (s *TokenStore) prefsPath() string {
	if s.Global != nil {
		if path := s.Global.Path(); path != "" {
			return path
		}
	}

	return "prefs.json"
}

// SetRefresh stores a refresh token, preferring the keyring.
//
// On success the prefs copy is removed, so a token never lingers in plaintext
// after the keyring starts working. On failure it is written to prefs instead,
// and the user is told -- the fallback is the documented behaviour for headless
// Linux and CI, but which store holds a long-lived credential is theirs to know.
func (s *TokenStore) SetRefresh(sessionID, token string) error {
	if sessionID == "" {
		return nil
	}
	if token == "" {
		return s.DeleteRefresh(sessionID)
	}

	storeErr := keyring.Set(refreshTokenService, sessionID, token)
	if storeErr == nil {
		trace("stored the refresh token for %s in %s", sessionID, storeName())
		s.clearPrefsToken(sessionID)

		return s.Global.Write()
	}

	trace("%s would not store the refresh token for %s: %s", storeName(), sessionID, storeErr)

	session := s.Global.SessionData(sessionID)
	if session == nil {
		// Nothing to write the token into, so reporting success would be a lie:
		// the caller has just rotated the token, the old one is already dead
		// server-side, and silence here means the next command finds no
		// credential and cannot say why.
		return fmt.Errorf(
			"%s would not store the refresh token and session %s is not in %s: %w",
			storeName(), sessionID, s.prefsPath(), storeErr)
	}

	warn("%s is unavailable, so the refresh token was written to %s instead.",
		storeName(), s.prefsPath())

	session.Set(prefsRefreshTokenKey, token)

	return s.Global.Write()
}

// DeleteRefresh removes a refresh token from both stores.
//
// A missing or unavailable keyring must not block local cleanup, so the keyring
// error is ignored and the prefs copy is removed regardless.
func (s *TokenStore) DeleteRefresh(sessionID string) error {
	_ = keyring.Delete(refreshTokenService, sessionID)
	s.clearPrefsToken(sessionID)

	return s.Global.Write()
}

func (s *TokenStore) clearPrefsToken(sessionID string) {
	if session := s.Global.SessionData(sessionID); session != nil {
		session.Delete(prefsRefreshTokenKey)
	}
}

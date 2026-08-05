package auth

import (
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/zalando/go-keyring"
)

// Ports templates/cli/lib/auth/refresh-token.ts.

// refreshTokenService is the keyring service refresh tokens are filed under.
//
// Matches the TypeScript CLI's `${EXECUTABLE_NAME}-oauth-refresh-token` exactly.
// Interop is not an invariant -- docs/go-cli/PLAN.md §3 says re-authenticating
// once on upgrade is acceptable -- but matching costs nothing and spares users
// a login if the platform keyring cooperates.
const refreshTokenService = "appwrite-oauth-refresh-token"

// prefsRefreshTokenKey is where a refresh token lands when no keyring is
// available.
const prefsRefreshTokenKey = config.PreferenceRefreshToken

// TokenStore reads and writes refresh tokens, preferring the OS keyring and
// falling back to the preferences file.
//
// The fallback is not a convenience: headless Linux and CI containers have no
// secret service, and a CLI that refuses to hold a session there is a CLI that
// cannot be scripted. The TypeScript CLI makes the same trade.
type TokenStore struct {
	Global *config.Global
}

// Refresh returns the stored refresh token for a session, or "" when there is
// none.
//
// Keyring errors are swallowed rather than surfaced: an unavailable keyring is
// indistinguishable from an absent entry as far as the caller is concerned, and
// both mean "fall back to prefs".
func (s *TokenStore) Refresh(sessionID string) string {
	if sessionID == "" {
		return ""
	}

	if token, err := keyring.Get(refreshTokenService, sessionID); err == nil && token != "" {
		return token
	}

	session := s.Global.SessionData(sessionID)
	if session == nil {
		return ""
	}

	return session.GetString(prefsRefreshTokenKey)
}

// SetRefresh stores a refresh token, preferring the keyring.
//
// On success the prefs copy is removed, so a token never lingers in plaintext
// after the keyring starts working. On failure it is written to prefs instead.
func (s *TokenStore) SetRefresh(sessionID, token string) error {
	if sessionID == "" {
		return nil
	}
	if token == "" {
		return s.DeleteRefresh(sessionID)
	}

	if err := keyring.Set(refreshTokenService, sessionID, token); err == nil {
		s.clearPrefsToken(sessionID)

		return s.Global.Write()
	}

	if session := s.Global.SessionData(sessionID); session != nil {
		session.Set(prefsRefreshTokenKey, token)
	}

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

//go:build js

package auth

import (
	"fmt"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
)

// Browsers store refresh tokens in preferences because they have no keyring.
const prefsRefreshTokenKey = config.PreferenceRefreshToken

// TokenStore reads and writes browser refresh tokens.
type TokenStore struct {
	Global *config.Global
}

// Trace receives credential-store diagnostics.
var Trace func(format string, arguments ...any)

func trace(format string, arguments ...any) {
	if Trace != nil {
		Trace(format, arguments...)
	}
}

// Warn matches the native variant's exported surface.
var Warn func(format string, arguments ...any)

func storeName() string {
	return "the browser session store"
}

// MissingRefreshToken reports where a session token was expected.
type MissingRefreshToken struct {
	SessionID string
	Store     string
	StoreErr  error
	PrefsPath string
}

func (e *MissingRefreshToken) Error() string {
	if e.StoreErr != nil {
		return fmt.Sprintf("%s could not be read (%s), and %s holds no token for session %s",
			e.Store, e.StoreErr, e.PrefsPath, e.SessionID)
	}

	return fmt.Sprintf("%s has no entry for session %s",
		e.Store, e.SessionID)
}

func (e *MissingRefreshToken) Unwrap() error { return e.StoreErr }

// Refresh returns the stored refresh token for a session.
func (s *TokenStore) Refresh(sessionID string) (string, error) {
	if sessionID == "" {
		return "", &MissingRefreshToken{Store: storeName(), PrefsPath: s.prefsPath()}
	}

	if session := s.Global.SessionData(sessionID); session != nil {
		if token := session.GetString(prefsRefreshTokenKey); token != "" {
			trace("read the refresh token for %s from %s", sessionID, s.prefsPath())

			return token, nil
		}
	}

	return "", &MissingRefreshToken{
		SessionID: sessionID,
		Store:     storeName(),
		PrefsPath: s.prefsPath(),
	}
}

func (s *TokenStore) prefsPath() string {
	if s.Global != nil {
		if path := s.Global.Path(); path != "" {
			return path
		}
	}

	return "prefs.json"
}

// SetRefresh stores a refresh token.
func (s *TokenStore) SetRefresh(sessionID, token string) error {
	if sessionID == "" {
		return nil
	}
	if token == "" {
		return s.DeleteRefresh(sessionID)
	}

	session := s.Global.SessionData(sessionID)
	if session == nil {
		return fmt.Errorf("session %s is not in %s, so the refresh token could not be stored",
			sessionID, s.prefsPath())
	}

	trace("stored the refresh token for %s in %s", sessionID, s.prefsPath())
	session.Set(prefsRefreshTokenKey, token)

	return s.Global.Write()
}

// DeleteRefresh removes a refresh token.
func (s *TokenStore) DeleteRefresh(sessionID string) error {
	s.clearPrefsToken(sessionID)

	return s.Global.Write()
}

func (s *TokenStore) clearPrefsToken(sessionID string) {
	if session := s.Global.SessionData(sessionID); session != nil {
		session.Delete(prefsRefreshTokenKey)
	}
}

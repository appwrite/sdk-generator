package cmd

import (
	"net/url"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/auth"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// Signing out deleted the stored session and stopped there, so the session
// stayed alive on the server: the credential the user asked to destroy kept
// working until it expired on its own. `client --reset` had the same hole.
//
// A session that cannot be revoked is KEPT locally and reported. Removing it
// anyway would leave a live server session with no local record of it, which
// is the one state from which the user cannot recover.

// consoleClientID is the OAuth2 client the CLI registers as, used when a
// session does not record its own.
const consoleClientID = "appwrite-cli"

// logoutResult reports what could not be signed out.
type logoutResult struct {
	SignedOut []string
	Failed    []string
	Errors    []string
}

// logoutSessions revokes each session server-side, then forgets it locally.
func logoutSessions(global *config.Global, ids []string) logoutResult {
	result := logoutResult{}
	store := &auth.TokenStore{Global: global}

	for _, id := range ids {
		forget := func() error {
			if err := store.DeleteRefresh(id); err != nil {
				return err
			}
			global.DeleteSession(id)

			return nil
		}

		// Nothing to revoke: a session with neither credential only ever
		// existed in this file. `client --endpoint` creates these.
		if !hasServerCredential(global, store, id) {
			if err := forget(); err != nil {
				result.Failed = append(result.Failed, id)
				result.Errors = append(result.Errors, err.Error())

				continue
			}
			result.SignedOut = append(result.SignedOut, id)

			continue
		}

		if err := revokeSession(global, store, id); err != nil {
			result.Failed = append(result.Failed, id)
			result.Errors = append(result.Errors, err.Error())

			continue
		}
		if err := forget(); err != nil {
			result.Failed = append(result.Failed, id)
			result.Errors = append(result.Errors, err.Error())

			continue
		}
		result.SignedOut = append(result.SignedOut, id)
	}

	return result
}

// hasServerCredential reports whether a session holds something the server
// would still honour.
func hasServerCredential(global *config.Global, store *auth.TokenStore, id string) bool {
	// Why there is no token does not change the answer here, so the failed
	// lookup is dropped rather than reported: logout is about removing local
	// state, and it must work whether or not the keyring cooperates.
	if token, _ := store.Refresh(id); token != "" {
		return true
	}

	session := global.SessionData(id)

	return session != nil && session.GetString(config.PreferenceCookie) != ""
}

// revokeSession destroys one session at the endpoint that issued it.
//
// The session's OWN endpoint and client id, not the current session's: signing
// out of everything must reach each instance the user signed in to.
func revokeSession(global *config.Global, store *auth.TokenStore, id string) error {
	session := global.SessionData(id)
	if session == nil {
		return nil
	}

	endpoint := session.GetString(config.PreferenceEndpoint)
	if endpoint == "" {
		return nil
	}

	api := client.New(endpoint, app.Version)

	// As in hasServerCredential: a token that cannot be read cannot be revoked
	// at the server either, and the local session is removed regardless.
	if refresh, _ := store.Refresh(id); refresh != "" {
		clientID := session.GetString(config.PreferenceClientID)
		if clientID == "" {
			clientID = consoleClientID
		}

		request := jsonx.NewObject()
		request.Set("token", refresh)
		request.Set("token_type_hint", "refresh_token")
		request.Set("client_id", clientID)

		return api.Call("POST",
			"/oauth2/"+url.PathEscape(config.ProjectConsole)+"/revoke", request, nil)
	}

	cookie := session.GetString(config.PreferenceCookie)
	if cookie == "" {
		return nil
	}

	return api.Clone().SetCookie(cookie).
		Call("DELETE", "/account/sessions/current", nil, nil)
}

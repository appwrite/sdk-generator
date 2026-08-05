package auth

import (
	"encoding/json"
	"errors"
	"strconv"
	"time"

	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/config"
)

// Ports getValidAccessToken() from templates/cli/lib/sdks.ts:33.

// DefaultClientID is the OAuth2 client the CLI identifies as.
const DefaultClientID = "appwrite-cli"

// refreshSkew is how far before expiry a token is treated as stale.
//
// A token that expires during the request it authenticates is a confusing
// failure, so renew a minute early. Matches the TypeScript `60_000`.
const refreshSkew = 60 * time.Second

// ErrSessionExpired is returned when no usable credential remains.
var ErrSessionExpired = errors.New("session expired. Run `appwrite login` to create a new session")

// tokenResponse is the subset of the OAuth2 token payload the CLI uses.
type tokenResponse struct {
	AccessToken  string      `json:"access_token"`
	RefreshToken string      `json:"refresh_token"`
	IDToken      string      `json:"id_token"`
	ExpiresIn    json.Number `json:"expires_in"`
}

// Authenticator resolves a usable access token for the active session.
type Authenticator struct {
	Global     *config.Global
	Store      *TokenStore
	SDKVersion string

	// Now is injectable so expiry logic is testable without sleeping.
	Now func() time.Time
}

// NewAuthenticator wires an authenticator over the given preferences.
func NewAuthenticator(global *config.Global, sdkVersion string) *Authenticator {
	return &Authenticator{
		Global:     global,
		Store:      &TokenStore{Global: global},
		SDKVersion: sdkVersion,
		Now:        time.Now,
	}
}

func (a *Authenticator) now() time.Time {
	if a.Now == nil {
		return time.Now()
	}

	return a.Now()
}

// AccessToken returns a valid access token, refreshing it when necessary.
//
// forceRefresh renews even a token that still looks valid, which the caller
// uses after the API rejects one.
func (a *Authenticator) AccessToken(forceRefresh bool) (string, error) {
	session := a.Global.Current()
	if session == nil {
		return "", ErrSessionExpired
	}

	accessToken := session.GetString(config.PreferenceAccessToken)
	expiry := session.GetInt64(config.PreferenceTokenExpiry)
	sessionID := a.Global.CurrentSessionID()

	deadline := a.now().Add(refreshSkew).UnixMilli()
	if !forceRefresh && accessToken != "" && expiry > deadline {
		return accessToken, nil
	}

	refreshToken := a.Store.Refresh(sessionID)

	// An expiry of zero means the token came from a flow that does not report
	// one. Without a refresh token there is nothing better to do than use it and
	// let the API decide.
	if accessToken != "" && expiry == 0 && refreshToken == "" {
		return accessToken, nil
	}

	if refreshToken == "" {
		return "", ErrSessionExpired
	}

	return a.refresh(session, sessionID, refreshToken)
}

func (a *Authenticator) refresh(session *config.Object, sessionID, refreshToken string) (string, error) {
	endpoint := session.GetString(config.PreferenceEndpoint)
	if endpoint == "" {
		endpoint = config.DefaultEndpoint
	}

	clientID := session.GetString(config.PreferenceClientID)
	if clientID == "" {
		clientID = DefaultClientID
	}

	// The token endpoint is served by the console project and is not regional,
	// so the endpoint is normalised before use.
	api := client.New(config.NormalizeCloudConsoleEndpoint(endpoint), a.SDKVersion).
		SetProject(config.ProjectConsole)

	var token tokenResponse
	err := api.Call("POST", "/oauth2/"+config.ProjectConsole+"/token", map[string]any{
		"grant_type":    "refresh_token",
		"refresh_token": refreshToken,
		"client_id":     clientID,
	}, &token)
	if err != nil {
		return "", err
	}
	if token.AccessToken == "" {
		return "", ErrSessionExpired
	}

	expiresIn, _ := token.ExpiresIn.Int64()
	session.Set(config.PreferenceAccessToken, token.AccessToken)
	newExpiry := a.now().Add(time.Duration(expiresIn) * time.Second).UnixMilli()
	// json.Number so the timestamp is written as an integer literal rather than
	// in scientific notation, matching how the TypeScript CLI stores it.
	session.Set(config.PreferenceTokenExpiry, json.Number(strconv.FormatInt(newExpiry, 10)))

	// The server may rotate the refresh token; when it does, the old one stops
	// working, so persisting the new one is not optional.
	if token.RefreshToken != "" {
		if err := a.Store.SetRefresh(sessionID, token.RefreshToken); err != nil {
			return "", err
		}
	}

	if err := a.Global.Write(); err != nil {
		return "", err
	}

	return token.AccessToken, nil
}

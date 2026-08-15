package auth

import (
	"encoding/base64"
	"encoding/json"
	"errors"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
)

// Implements RFC 8628 device authorization.

// OAuth2Scopes are the scopes the CLI requests.
const OAuth2Scopes = "openid email profile all"

// defaultPollInterval is used when the server omits one.
//
// RFC 8628 section 3.5. The TypeScript guards against a missing interval
// becoming NaN and busy-polling the endpoint; the Go equivalent is guarding
// against zero.
const defaultPollInterval = 5 * time.Second

// slowDownIncrement is how much a `slow_down` response adds to the interval.
const slowDownIncrement = 5 * time.Second

// ErrDeviceAuthorizationExpired means the user did not approve in time.
var ErrDeviceAuthorizationExpired = errors.New("device authorization expired before it was approved")

// DeviceAuthorization is the server's response to a device authorization
// request.
type DeviceAuthorization struct {
	DeviceCode              string      `json:"device_code"`
	UserCode                string      `json:"user_code"`
	VerificationURI         string      `json:"verification_uri"`
	VerificationURIComplete string      `json:"verification_uri_complete"`
	ExpiresIn               json.Number `json:"expires_in"`
	Interval                json.Number `json:"interval"`
}

// VerificationURL is the URL to show the user, preferring the one that embeds
// the code so they do not have to type it.
func (d DeviceAuthorization) VerificationURL() string {
	if d.VerificationURIComplete != "" {
		return d.VerificationURIComplete
	}

	return d.VerificationURI
}

// PollInterval is how long to wait between token requests.
func (d DeviceAuthorization) PollInterval() time.Duration {
	seconds, err := d.Interval.Int64()
	if err != nil || seconds <= 0 {
		return defaultPollInterval
	}

	return time.Duration(seconds) * time.Second
}

// Lifetime is how long the authorization is valid for.
//
// An explicit zero means already expired and is honoured as such -- the
// TypeScript computes `Date.now() + expires_in * 1000` directly, so a zero
// there ends the loop immediately. Only a missing or unparseable value falls
// back to a default, which stops a malformed response becoming an instant
// failure.
func (d DeviceAuthorization) Lifetime() time.Duration {
	seconds, err := d.ExpiresIn.Int64()
	if err != nil {
		return 10 * time.Minute
	}
	if seconds < 0 {
		return 0
	}

	return time.Duration(seconds) * time.Second
}

// DeviceFlow runs RFC 8628 device authorization against one endpoint.
type DeviceFlow struct {
	Endpoint   string
	ClientID   string
	SDKVersion string
	// SelfSigned accepts a self-signed certificate on the endpoint, for a
	// self-hosted instance behind its own. Set from the stored preference by the
	// caller, which is the only place that reads preferences.
	SelfSigned bool

	// Sleep and Now are injectable so the poll loop is testable without
	// waiting real seconds.
	Sleep func(time.Duration)
	Now   func() time.Time
}

// NewDeviceFlow wires a device flow against an endpoint.
func NewDeviceFlow(endpoint, sdkVersion string) *DeviceFlow {
	return &DeviceFlow{
		Endpoint:   config.NormalizeCloudConsoleEndpoint(endpoint),
		ClientID:   DefaultClientID,
		SDKVersion: sdkVersion,
		Sleep:      time.Sleep,
		Now:        time.Now,
	}
}

func (f *DeviceFlow) api() *client.Client {
	return client.New(f.Endpoint, f.SDKVersion).
		SetProject(config.ProjectConsole).
		SetSelfSigned(f.SelfSigned)
}

// Authorize requests a device code and user code.
func (f *DeviceFlow) Authorize() (DeviceAuthorization, error) {
	var authorization DeviceAuthorization
	err := f.api().Call("POST",
		"/oauth2/"+config.ProjectConsole+"/device_authorization",
		map[string]any{
			"client_id": f.ClientID,
			"scope":     OAuth2Scopes,
		},
		&authorization)

	return authorization, err
}

// Poll requests a token until the user approves, the authorization expires, or
// the server returns a real error.
//
// Returns ErrDeviceAuthorizationExpired when the window closes. Pending and
// empty-body responses are retried; `slow_down` additionally widens the
// interval per RFC 8628 section 3.5.
func (f *DeviceFlow) Poll(authorization DeviceAuthorization) (*TokenSet, error) {
	interval := authorization.PollInterval()
	deadline := f.now().Add(authorization.Lifetime())

	for f.now().Before(deadline) {
		f.sleep(interval)

		var token tokenResponse
		err := f.api().Call("POST", "/oauth2/"+config.ProjectConsole+"/token", map[string]any{
			"grant_type":  "urn:ietf:params:oauth:grant-type:device_code",
			"device_code": authorization.DeviceCode,
			"client_id":   f.ClientID,
		}, &token)

		if err == nil && token.AccessToken != "" {
			expiresIn, _ := token.ExpiresIn.Int64()

			return &TokenSet{
				AccessToken:  token.AccessToken,
				RefreshToken: token.RefreshToken,
				IDToken:      token.IDToken,
				ExpiresAt:    f.now().Add(time.Duration(expiresIn) * time.Second),
			}, nil
		}
		if err == nil {
			// A 2xx with no token is not something the spec describes; treat it
			// like a pending response rather than looping instantly.
			continue
		}

		if isSlowDown(err) {
			interval += slowDownIncrement

			continue
		}
		if isAuthorizationPending(err) {
			continue
		}

		return nil, err
	}

	return nil, ErrDeviceAuthorizationExpired
}

// TokenSet is a completed device authorization.
type TokenSet struct {
	AccessToken  string
	RefreshToken string
	IDToken      string
	ExpiresAt    time.Time
}

func (f *DeviceFlow) sleep(duration time.Duration) {
	if f.Sleep == nil {
		time.Sleep(duration)

		return
	}
	f.Sleep(duration)
}

func (f *DeviceFlow) now() time.Time {
	if f.Now == nil {
		return time.Now()
	}

	return f.Now()
}

// matchesOAuthError reports whether an API error carries an OAuth error code.
//
// The code can arrive as the error type or the message depending on how the
// endpoint failed, so both are checked -- ports matchesOAuthError().
func matchesOAuthError(err error, code string) bool {
	var apiError *client.APIError
	if !errors.As(err, &apiError) {
		return false
	}

	return apiError.Type == code ||
		apiError.Message == code ||
		strings.Contains(apiError.Message, code)
}

func isSlowDown(err error) bool {
	return matchesOAuthError(err, "slow_down")
}

// isAuthorizationPending reports whether polling should continue.
//
// An empty error body -- a 400 with no type and no message -- is treated as
// pending rather than fatal. Ports isEmptyDevicePollError(): aborting the whole
// login because one poll came back blank would be a worse failure than one more
// round trip.
func isAuthorizationPending(err error) bool {
	if matchesOAuthError(err, "authorization_pending") || matchesOAuthError(err, "slow_down") {
		return true
	}

	var apiError *client.APIError
	if !errors.As(err, &apiError) {
		return false
	}

	return strings.TrimSpace(apiError.Type) == "" && strings.TrimSpace(apiError.Message) == ""
}

// DecodeIDToken pulls the profile claims out of an OIDC ID token.
//
// The signature is not verified: the token arrived over TLS from the endpoint
// the user just authenticated against, and it is used only to label the stored
// session. Ports decodeIdToken().
func DecodeIDToken(idToken string) (email, name, subject string) {
	parts := strings.Split(idToken, ".")
	if len(parts) < 2 {
		return "", "", ""
	}

	payload, err := base64.RawURLEncoding.DecodeString(parts[1])
	if err != nil {
		return "", "", ""
	}

	var claims struct {
		Email   string `json:"email"`
		Name    string `json:"name"`
		Subject string `json:"sub"`
	}
	if err := json.Unmarshal(payload, &claims); err != nil {
		return "", "", ""
	}

	return claims.Email, claims.Name, claims.Subject
}

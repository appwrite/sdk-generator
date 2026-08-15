//go:build !browser

package cmd

import (
	"io"
	"net/url"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
)

// A function running locally still talks to the real API, and it needs two
// credentials to do it: an ephemeral API key scoped to the function's own
// scopes, which the runtime sends as `x-appwrite-key`, and -- when the run
// names a user -- a JWT for that user, sent as `x-appwrite-user-jwt`. Without
// them the function starts and every API call it makes is unauthenticated.

const (
	// credentialDuration is the hour asked for on both credentials.
	credentialDuration = 60 * 60

	// credentialWarning and credentialExpiry are when the run warns that the
	// hour is nearly up and then that it is up. The function keeps running
	// either way; what stops working is its access to the API, and a silent
	// change from working to 401 is worse than a line saying so.
	credentialWarning = 55 * time.Minute
	credentialExpiry  = 60 * time.Minute
)

// runCredentials are the tokens a local run injects.
type runCredentials struct {
	// FunctionKey is the ephemeral API key, `x-appwrite-key`.
	FunctionKey string
	// UserJWT is set only when the run names a user.
	UserJWT string
}

// mintRunCredentials creates the credentials for one local run.
//
// A failure is the CALLER's to report and not fatal: it warns and runs the
// function anyway, because a function that does not touch the API runs
// perfectly well without either credential.
func mintRunCredentials(
	api *client.Client,
	userID string,
	scopes []string,
) (runCredentials, error) {
	credentials := runCredentials{}

	if userID != "" {
		// The user is read first so a mistyped id fails with "user not found"
		// rather than with whatever minting a JWT for a missing user returns.
		user := jsonx.NewObject()
		err := api.Call("GET", "/users/"+url.PathEscape(userID), nil, user)
		if err != nil {
			return credentials, err
		}

		request := jsonx.NewObject()
		request.Set("duration", credentialDuration)

		token := jsonx.NewObject()
		err = api.Call("POST",
			"/users/"+url.PathEscape(userID)+"/jwts", request, token)
		if err != nil {
			return credentials, err
		}
		credentials.UserJWT = token.GetString("jwt")
	}

	request := jsonx.NewObject()
	// Scopes are sent even when empty: an ephemeral key with no scopes is a
	// valid key that can do nothing, which is what a function declaring no
	// scopes should get.
	request.Set("scopes", scopes)
	request.Set("duration", credentialDuration)

	key := jsonx.NewObject()
	if err := api.Call("POST", "/project/keys/ephemeral", request, key); err != nil {
		return credentials, err
	}
	credentials.FunctionKey = key.GetString("secret")

	return credentials, nil
}

// runProjectAPI builds the project client a local run uses.
//
// Separate from projectAPI so the caller gets ONE error to report rather than
// two: `run` treats an unreachable API as a warning, and both callers here
// need the same client.
func runProjectAPI(local *config.Local) (*client.Client, error) {
	global, err := preferences()
	if err != nil {
		return nil, err
	}

	return projectAPI(global, local)
}

// listFunctionVariables reads a function's production variables.
func listFunctionVariables(api *client.Client, functionID string) ([]*jsonx.Object, error) {
	listing := jsonx.NewObject()
	err := api.Call("GET",
		"/functions/"+url.PathEscape(functionID)+"/variables", nil, listing)
	if err != nil {
		return nil, err
	}

	value, _ := listing.Get("variables")
	items, _ := value.([]any)

	variables := make([]*jsonx.Object, 0, len(items))
	for _, item := range items {
		if variable, ok := item.(*jsonx.Object); ok {
			variables = append(variables, variable)
		}
	}

	return variables, nil
}

// warnNoVariables reports that the run will not see production variables.
func warnNoVariables(out io.Writer, err error) {
	output.Warn(out, "Remote variables not fetched. Production environment "+
		"variables will not be available. Reason: %s", err)
}

// warnOnCredentialExpiry announces the end of the credentials' hour.
//
// The timers are goroutines on a stop channel rather than stored handles: a run
// that ends first simply closes the channel.
func warnOnCredentialExpiry(out io.Writer) (stop func()) {
	done := make(chan struct{})

	go func() {
		warning := time.NewTimer(credentialWarning)
		defer warning.Stop()
		expiry := time.NewTimer(credentialExpiry)
		defer expiry.Stop()

		for {
			select {
			case <-done:
				return
			case <-warning.C:
				output.Warn(out, "Authorized JWT will expire in 5 minutes. "+
					"Please stop and re-run the command to refresh tokens for 1 hour.")
			case <-expiry.C:
				output.Warn(out, "Authorized JWT just expired. Please stop and "+
					"re-run the command to obtain new tokens with 1 hour validity.")
				output.Warn(out, "Some Appwrite API communication is not authorized now.")

				return
			}
		}
	}()

	return func() { close(done) }
}

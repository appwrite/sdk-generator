package cmd

import (
	"encoding/json"
	"errors"
	"fmt"
	"net/url"
	"os/exec"
	"runtime"
	"strconv"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/auth"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// TWO SIGN-IN FLOWS, chosen by the endpoint. Cloud signs in through the
// browser with a device code; a self-hosted instance signs in with an email
// and a password, and may then ask for a second factor. isCloudLoginEndpoint
// (utils.ts:503) is what decides, so the same `login` command does either.

// loginOptions are the flags `login` was given. Mirrors loginCommand's
// parameter object (login.ts:452).
type loginOptions struct {
	Endpoint string
	Email    string
	Password string
	MFA      string
	Code     string
	// Switch changes which signed-in account is current, without signing in.
	Switch bool
	// New signs in again even when an account is already signed in.
	New bool
}

func newLoginCommand() *cobra.Command {
	options := loginOptions{}

	command := &cobra.Command{
		Use:   "login",
		Short: "Sign in to an Appwrite endpoint",
		RunE: func(command *cobra.Command, args []string) error {
			return runLogin(command, options)
		},
	}

	flags := command.Flags()
	flags.StringVar(&options.Endpoint, "endpoint", "",
		"Appwrite endpoint to sign in to. Defaults to "+config.DefaultEndpoint+".")
	flags.StringVar(&options.Email, "email", "", "Email, for self hosted instances")
	flags.StringVar(&options.Password, "password", "", "Password, for self hosted instances")
	flags.StringVar(&options.MFA, "mfa", "",
		"Factor used for MFA on self hosted instances. "+
			"Must be one of: email, phone, totp, recoveryCode")
	flags.StringVar(&options.Code, "code", "", "Code used for MFA on self hosted instances")
	flags.BoolVar(&options.Switch, "switch", false, "Switch to another signed-in account")
	flags.BoolVar(&options.New, "new", false, "Sign in to another account")

	return command
}

// runLogin ports loginCommand (login.ts:452).
func runLogin(command *cobra.Command, options loginOptions) error {
	out := command.OutOrStdout()

	if options.Switch && options.New {
		return errors.New("use either --switch or --new, not both")
	}

	global, err := preferences()
	if err != nil {
		return err
	}

	endpoint := options.Endpoint
	if endpoint == "" {
		endpoint = global.CurrentValue(config.PreferenceEndpoint)
	}
	if endpoint == "" {
		endpoint = config.DefaultEndpoint
	}
	configEndpoint := config.NormalizeCloudConsoleEndpoint(endpoint)

	if options.Endpoint != "" && config.IsRegionalCloudEndpoint(options.Endpoint) {
		output.Warn(out, "Regional Cloud endpoints are for project API calls, so "+
			"signing in to %s instead. Set the regional endpoint in %s.config.json.",
			configEndpoint, app.ExecutableName)
	}

	cloud := config.IsCloudLoginEndpoint(configEndpoint)

	// Checked BEFORE anything is prompted for, so a wrong endpoint fails
	// immediately rather than after the email and password are typed.
	if options.Endpoint != "" && !cloud {
		if err := verifyEndpoint(configEndpoint,
			global.CurrentBool(config.PreferenceSelfSigned)); err != nil {
			return err
		}
	}

	if cloud && (options.Email != "" || options.Password != "" ||
		options.MFA != "" || options.Code != "") {
		return fmt.Errorf("cloud sign-in happens in your browser. Run '%s login' "+
			"without --email, --password, --mfa or --code -- those options are "+
			"for self-hosted instances", app.ExecutableName)
	}

	previous := global.CurrentSessionID()

	if previous != "" && !options.New {
		// The error is deliberately dropped: the question here is only whether
		// someone is already signed in, and every failure answers it with no.
		if account, _ := currentAccount(); account != nil {
			// Nothing was asked for and someone is already signed in, so say so
			// rather than starting a flow they did not ask for.
			if options.Email == "" && options.Password == "" &&
				options.Endpoint == "" && !options.Switch {
				output.Success(out, "Already logged in as %s", account.GetString("email"))
				output.Hint(out, "Use '%s login --new' to add another account",
					app.ExecutableName)

				return nil
			}
		}
	}

	if options.Switch {
		return switchAccount(command, global, previous)
	}

	if !cloud {
		return loginWithPassword(command, global, configEndpoint, previous, options)
	}

	return loginWithDevice(command, configEndpoint)
}

// currentAccount reads the signed-in account.
//
// The error is RETURNED rather than
// folded into a nil account, because the two callers ask different questions of
// it. `login` asks "is someone signed in already?", where every failure means
// no and the reason does not matter. `login --switch` asks "does this stored
// session work?", and there the reason is the whole answer -- the TypeScript
// rethrows it (login.ts:318) for exactly that reason, and reporting a locked
// keyring or an unreachable endpoint as a dead session sends the user to
// re-authenticate against a problem that re-authenticating does not fix.
func currentAccount() (*jsonx.Object, error) {
	api, _, err := consoleClient()
	if err != nil {
		return nil, err
	}

	account := jsonx.NewObject()
	if err := api.Call("GET", "/account", nil, account); err != nil {
		return nil, err
	}

	return account, nil
}

// verifyEndpoint checks that an endpoint is an Appwrite server before a
// password is typed into it.
func verifyEndpoint(endpoint string, selfSigned bool) error {
	parsed, err := url.Parse(endpoint)
	if err != nil || (parsed.Scheme != "http" && parsed.Scheme != "https") {
		return fmt.Errorf("invalid endpoint URL: %s", endpoint)
	}

	version := jsonx.NewObject()
	// selfSigned is threaded in rather than read from preferences: `client
	// --endpoint X --self-signed true` has to verify X under the setting given in
	// the same invocation, not the one stored from a previous run.
	err = client.New(endpoint, app.Version).SetSelfSigned(selfSigned).
		Call("GET", "/health/version", nil, version)
	if err == nil && version.GetString("version") != "" {
		return nil
	}

	return fmt.Errorf(
		"invalid endpoint or your Appwrite server is not running as expected: %s",
		endpoint)
}

// switchAccount makes a different signed-in account current.
//
// The switch is applied first and rolled
// back if the account turns out to be unusable, because whether a stored
// session still works can only be answered by using it.
func switchAccount(command *cobra.Command, global *config.Global, previous string) error {
	out := command.OutOrStdout()

	options := make([]prompt.Option, 0)
	for _, id := range global.SessionIDs() {
		session, _ := global.Session(id)
		if session.Email == "" {
			continue
		}
		options = append(options, prompt.Option{
			Label: session.Email + " (" + session.Endpoint + ")",
			Value: id,
		})
	}

	if len(options) == 0 {
		return fmt.Errorf("no signed-in accounts found. Run '%s login' to sign in",
			app.ExecutableName)
	}

	chosen, err := prompt.New(app.Flags().Force).Choice(prompt.Choice{
		Message: "Select an account to use",
		Options: options,
	})
	if err != nil {
		return err
	}

	if chosen == previous {
		account, err := currentAccount()
		if account != nil {
			output.Success(out, "Already using %s", account.GetString("email"))

			return nil
		}

		return unusableSessionError(err)
	}

	global.SetCurrentSessionID(chosen)
	if err := global.Write(); err != nil {
		return err
	}

	account, accountErr := currentAccount()
	if account == nil {
		global.SetCurrentSessionID(previous)
		if err := global.Write(); err != nil {
			return err
		}

		return unusableSessionError(accountErr)
	}

	output.Success(out, "Switched to %s", account.GetString("email"))

	return nil
}

// unusableSessionError explains why the selected session could not be used.
//
// The reason is carried when there is one. Without it the only thing this could
// say was "run `login --switch` again", which is the command that just failed --
// so a locked keyring, an unreachable endpoint and a genuinely dead session all
// pointed at the same dead end. The TypeScript rethrows the underlying error
// here (login.ts:318) and keeps the generic wording for the one case that has no
// error behind it.
func unusableSessionError(reason error) error {
	if reason != nil {
		return fmt.Errorf("selected account session cannot be used: %w", reason)
	}

	return fmt.Errorf(
		"selected account session is no longer valid. Run '%s login --switch' again",
		app.ExecutableName)
}

// loginWithPassword signs in to a self-hosted instance.
//
// The session is written BEFORE
// the request and removed if it fails, because the client that makes the
// request reads its endpoint from the session -- and a half-written session
// left behind on a bad password would make the next command think it was
// signed in.
func loginWithPassword(
	command *cobra.Command,
	global *config.Global,
	endpoint, previous string,
	options loginOptions,
) error {
	out := command.OutOrStdout()

	email, password := options.Email, options.Password
	if email == "" || password == "" {
		prompter := prompt.New(app.Flags().Force)

		answer, err := prompter.Text(prompt.Text{Message: "Enter your email", Flag: "--email"})
		if err != nil {
			return err
		}
		email = answer

		answer, err = prompter.Text(prompt.Text{
			Message: "Enter your password", Secret: true, Flag: "--password",
		})
		if err != nil {
			return err
		}
		password = answer
	}

	sessionID := strconv.FormatInt(time.Now().UnixMilli(), 10)
	session := config.NewObject()
	session.Set(config.PreferenceEndpoint, endpoint)
	session.Set(config.PreferenceEmail, email)
	global.AddSession(sessionID, session)
	global.SetCurrentSessionID(sessionID)
	if err := global.Write(); err != nil {
		return err
	}

	abandon := func() {
		global.DeleteSession(sessionID)
		global.SetCurrentSessionID(previous)
		_ = global.Write()
	}

	api := client.New(endpoint, app.Version).
		SetProject(config.ProjectConsole).
		SetLocale("en-US")

	credentials := jsonx.NewObject()
	credentials.Set("email", email)
	credentials.Set("password", password)

	err := api.Call("POST", "/account/sessions/email", credentials, nil)
	if err != nil {
		if !isMFARequired(err) {
			abandon()

			if endpoint != config.DefaultEndpoint && isInvalidCredentials(err) {
				output.Log(out, "Use the --endpoint option for self-hosted instances")
			}

			return err
		}

		if err := completeMFA(command, api, options); err != nil {
			abandon()

			return err
		}
	}

	// The cookie IS the credential for this flow, so a sign-in that does not
	// produce one has not signed anyone in.
	sessionCookie := api.SessionCookie()
	if sessionCookie == "" {
		abandon()

		return errors.New("sign-in did not return a session")
	}
	session.Set(config.PreferenceCookie, sessionCookie)

	account := jsonx.NewObject()
	if err := api.Call("GET", "/account", nil, account); err != nil {
		abandon()

		return err
	}

	if actual := account.GetString("email"); actual != "" {
		session.Set(config.PreferenceEmail, actual)
		email = actual
	}
	global.AddSession(sessionID, session)
	if err := global.Write(); err != nil {
		return err
	}

	output.Success(out, "Successfully signed in as %s", email)
	output.Hint(out, "Next you can create or link to your project using '%s init project'",
		app.ExecutableName)

	return nil
}

// completeMFA answers a second-factor challenge.
//
// The challenge is created against the
// half-authenticated session the password already established, which is why it
// reuses the same client and its cookie.
func completeMFA(command *cobra.Command, api *client.Client, options loginOptions) error {
	prompter := prompt.New(app.Flags().Force)

	factor := options.MFA
	if factor == "" {
		chosen, err := prompter.Choice(prompt.Choice{
			Message: "Choose the factor to be used to complete the MFA challenge",
			Options: []prompt.Option{
				{Label: "Email", Value: "email"},
				{Label: "Phone", Value: "phone"},
				{Label: "Authenticator app", Value: "totp"},
				{Label: "Recovery code", Value: "recoveryCode"},
			},
			Flag: "--mfa",
		})
		if err != nil {
			return err
		}
		factor = chosen
	}

	request := jsonx.NewObject()
	request.Set("factor", factor)

	challenge := jsonx.NewObject()
	if err := api.Call("POST", "/account/mfa/challenges", request, challenge); err != nil {
		return err
	}

	code := options.Code
	if code == "" {
		answer, err := prompter.Text(prompt.Text{
			Message: "Enter the code from your authentication factor",
			Flag:    "--code",
		})
		if err != nil {
			return err
		}
		code = answer
	}

	answer := jsonx.NewObject()
	answer.Set("challengeId", challenge.GetString("$id"))
	answer.Set("otp", code)

	return api.Call("PUT", "/account/mfa/challenges", answer, nil)
}

// isMFARequired reports whether a sign-in stopped to ask for a second factor.
func isMFARequired(err error) bool {
	var apiError *client.APIError
	if !errors.As(err, &apiError) {
		return false
	}

	return apiError.Type == "user_more_factors_required"
}

// isInvalidCredentials reports a wrong email or password, which on a
// non-default endpoint usually means the endpoint is wrong rather than the
// password.
func isInvalidCredentials(err error) bool {
	var apiError *client.APIError
	if !errors.As(err, &apiError) {
		return false
	}

	return apiError.Type == "user_invalid_credentials"
}

// loginWithDevice signs in through the browser. Ports loginWithOAuthDevice
// (login.ts:333).
func loginWithDevice(command *cobra.Command, endpoint string) error {
	global, err := preferences()
	if err != nil {
		return err
	}

	flow := auth.NewDeviceFlow(endpoint, app.Version)
	authorization, err := flow.Authorize()
	if err != nil {
		return err
	}

	url := authorization.VerificationURL()
	command.Printf("\nTo sign in, confirm the code below in your browser:\n\n")
	command.Printf("  Code: %s\n", authorization.UserCode)
	command.Printf("  URL:  %s\n\n", url)
	openBrowser(url)

	token, err := flow.Poll(authorization)
	if err != nil {
		return err
	}

	email, name, subject := auth.DecodeIDToken(token.IDToken)
	sessionID := cloudSessionID(endpoint, subject)

	session := config.NewObject()
	// Key order matches what the TypeScript CLI writes, so a prefs.json
	// touched by both binaries does not churn.
	session.Set(config.PreferenceEndpoint, endpoint)
	session.Set(config.PreferenceClientID, auth.DefaultClientID)
	session.Set(config.PreferenceAccessToken, token.AccessToken)
	// json.Number so the timestamp is written as an integer literal, not a
	// float in scientific notation.
	session.Set(config.PreferenceTokenExpiry,
		json.Number(strconv.FormatInt(token.ExpiresAt.UnixMilli(), 10)))
	if email != "" {
		session.Set(config.PreferenceEmail, email)
	}
	global.AddSession(sessionID, session)

	if err := global.Write(); err != nil {
		return err
	}

	// Written after the session exists: the store needs the entry to
	// fall back to prefs when no keyring is available.
	if token.RefreshToken != "" {
		store := &auth.TokenStore{Global: global}
		if err := store.SetRefresh(sessionID, token.RefreshToken); err != nil {
			return err
		}
	}

	who := email
	if who == "" {
		who = name
	}
	command.Printf("Signed in as %s on %s\n", who, endpoint)

	return nil
}

// cloudSessionID keys a browser-flow session on the endpoint as well as the
// account.
//
// The subject alone is not unique. Every `*.appwrite.io` host takes this flow,
// and a staging deployment seeded from a production dump hands out the very
// same account IDs, so keying on the subject alone lets a second sign-in
// overwrite the first endpoint's session -- and, because the same string names
// the keyring entry, its refresh token with it.
//
// The TypeScript avoids this by keying on `ID.unique()` (login.ts:548), which
// is collision-free but accumulates a fresh entry every time you sign in to an
// account you are already signed in to. Composing the two keeps the
// deduplication and drops the collision. The key is internal -- `session list`
// and `login --switch` label sessions by email and endpoint -- so its spelling
// is free.
//
// The WHOLE endpoint goes into the key, not just its host. Two self-hosted
// instances can share a host and differ only by scheme or base path -- one
// reverse-proxied at /staging/v1 and another at /prod/v1, or an http and an https
// URL for the same box during a migration -- and reducing them to the host would
// reintroduce exactly the collision this function exists to remove.
func cloudSessionID(endpoint, subject string) string {
	if subject == "" {
		// No subject claim leaves nothing stable to key on, so fall back to the
		// sign-in time. Repeated sign-ins then accumulate entries rather than
		// overwrite one, which is noisy but never wrong.
		return strconv.FormatInt(time.Now().UnixMilli(), 10)
	}

	return subject + "@" + canonicalEndpoint(endpoint)
}

// canonicalEndpoint reduces an endpoint to one spelling per instance, so the same
// instance keys to the same session however it was typed.
//
// Hostnames are case-insensitive and a trailing slash is not meaningful; paths
// are case-sensitive, so only the host is folded.
func canonicalEndpoint(endpoint string) string {
	trimmed := strings.TrimRight(endpoint, "/")

	parsed, err := url.Parse(trimmed)
	if err != nil || parsed.Host == "" {
		return trimmed
	}
	parsed.Host = strings.ToLower(parsed.Host)
	parsed.Scheme = strings.ToLower(parsed.Scheme)

	return parsed.String()
}

// openBrowser is best-effort: a headless or locked-down environment simply
// leaves the user to open the printed URL themselves, which is why the error is
// discarded rather than surfaced.
func openBrowser(url string) {
	var command string
	var args []string

	switch runtime.GOOS {
	case "darwin":
		command, args = "open", []string{url}
	case "windows":
		command, args = "rundll32", []string{"url.dll,FileProtocolHandler", url}
	default:
		command, args = "xdg-open", []string{url}
	}

	_ = exec.Command(command, args...).Start()
}

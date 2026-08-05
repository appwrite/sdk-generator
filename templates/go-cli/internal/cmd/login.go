package cmd

import (
	"encoding/json"
	"net/url"
	"os/exec"
	"runtime"
	"strconv"
	"strings"
	"time"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/auth"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/spf13/cobra"
)

// Ports the device-code path of templates/cli/lib/auth/login.ts.

func newLoginCommand() *cobra.Command {
	var endpoint string

	command := &cobra.Command{
		Use:   "login",
		Short: "Sign in to an Appwrite endpoint",
		RunE: func(command *cobra.Command, args []string) error {
			global, err := preferences()
			if err != nil {
				return err
			}

			if endpoint == "" {
				endpoint = config.DefaultEndpoint
			}

			flow := auth.NewDeviceFlow(endpoint, app.Version)
			flow.SelfSigned = global.CurrentBool(config.PreferenceSelfSigned)
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
		},
	}
	command.Flags().StringVar(&endpoint, "endpoint", "",
		"Appwrite endpoint to sign in to. Defaults to "+config.DefaultEndpoint+".")

	return command
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
// deduplication and drops the collision. The key is internal, so its spelling
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

//go:build js

package sdk

import (
	"os"

	sdkclient "github.com/appwrite/sdk-for-go/v6/client"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
)

// In a browser the session is the page's, not the CLI's.
//
// Every other build authenticates from prefs.json, which `login` wrote. A
// browser build has neither: it cannot open a browser to sign in, it usually
// cannot write a file, and it does not need to -- the tab it runs in is already
// signed in, and the credential is an httpOnly cookie that JavaScript cannot
// read and a header cannot carry. The browser attaches it, on a request the CLI
// made, because internal/client asks fetch for the origin's credentials.
//
// Without this the fetch decoration is unreachable: `endpoint()` and
// `authenticate()` both refuse a call that has no stored session, so the CLI
// fails locally and never issues the request the cookie would have
// authenticated.

// ambientEndpoint is the endpoint to use when no session is stored.
//
// The environment rather than window.location: an embedder that serves the page
// from one origin and the API from another -- which the Console does, once a
// project is in a region -- would otherwise be told to call itself. It is the
// same variable a CI job sets, and it means the page states its endpoint rather
// than the artifact guessing.
func ambientEndpoint() string { return os.Getenv(EnvEndpoint) }

// ambientCredentials reports whether the environment authenticates the request
// on the CLI's behalf.
//
// Nothing is attached here, deliberately -- the whole point is that the CLI
// cannot see the credential. It only stops refusing, and marks the call the way
// a console session is marked.
func ambientCredentials(client *sdkclient.Client, allowAPIKey bool) bool {
	if ambientEndpoint() == "" {
		// No endpoint means nothing was configured at all, and answering "the
		// browser has this" would turn a missing configuration into an
		// unauthenticated request against whatever endpoint was guessed.
		return false
	}

	if allowAPIKey {
		// admin mode, as for a stored console cookie. A browser build runs in a
		// console, so its ambient session is a console session, and a console
		// session acting on a project without this is treated as an end user of
		// that project -- which is not what the terminal in the Console is for.
		client.AddHeader("X-Appwrite-Mode", config.ModeAdmin)
	}

	return true
}

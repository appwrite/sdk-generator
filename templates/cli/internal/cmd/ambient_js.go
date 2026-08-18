//go:build js

package cmd

import (
	"os"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
)

// The console half of what internal/sdk/ambient_js.go does for project calls.
//
// `whoami` and `sessions` do not go through the SDK context -- they build a
// client directly, in session.go -- so they need the same permission to run on
// a session the CLI cannot read. Same reasoning, same environment variable, and
// deliberately the same name so the two are found together.

// ambientEndpoint is the endpoint to use when no session is stored.
func ambientEndpoint() string { return os.Getenv(sdk.EnvEndpoint) }

// ambientSession reports whether the environment carries a session the CLI
// cannot see but can still make requests with.
//
// False without an endpoint: nothing was configured, and treating that as a
// session would send an unauthenticated request somewhere no one named.
func ambientSession() bool { return ambientEndpoint() != "" }

//go:build !js

package sdk

import sdkclient "github.com/appwrite/sdk-for-go/v7/client"

// The native half of the pair in ambient_js.go. A host has no session of its
// own to lend the CLI: the credential is whatever `login` stored, and a command
// that finds none has to say so rather than send an unauthenticated request and
// report whatever the API says about it.

// ambientEndpoint is the endpoint to use when no session is stored.
func ambientEndpoint() string { return "" }

// ambientCredentials reports whether the environment authenticates the request
// on the CLI's behalf.
func ambientCredentials(client *sdkclient.Client, allowAPIKey bool) bool { return false }

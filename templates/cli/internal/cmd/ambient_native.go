//go:build !js

package cmd

// The native half of the pair in ambient_js.go. Nothing lends this process a
// session; the credential is whatever `login` stored.

// ambientEndpoint is the endpoint to use when no session is stored.
func ambientEndpoint() string { return "" }

// ambientSession reports whether the environment carries a session the CLI
// cannot see but can still make requests with.
func ambientSession() bool { return false }

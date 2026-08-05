package client

import (
	"net/http"
	"net/http/httptest"
	"testing"
)

// `client --self-signed true` exists for a self-hosted instance behind its own
// certificate. The setting was stored in the preferences and never reached a
// transport, so every request to such an instance failed certificate validation
// and the flag looked like it did nothing.
//
// Asserted against a real TLS server with a self-signed certificate, because the
// only thing worth checking is whether the request completes.
func TestSelfSignedAcceptsAnUntrustedCertificate(t *testing.T) {
	server := httptest.NewTLSServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte(`{"version":"1.8.0"}`))
	}))
	defer server.Close()

	refused := New(server.URL, "test")
	if err := refused.Call("GET", "/health/version", nil, nil); err == nil {
		t.Fatal("an untrusted certificate was accepted without --self-signed")
	}

	accepted := New(server.URL, "test").SetSelfSigned(true)
	if err := accepted.Call("GET", "/health/version", nil, nil); err != nil {
		t.Errorf("--self-signed did not accept the certificate: %v", err)
	}
}

// Turning it off must leave verification alone rather than disabling it, and it
// must not reach into the shared default transport: mutating that would switch
// verification off for every later request in the process, including ones to
// Appwrite Cloud.
func TestSelfSignedFalseLeavesVerificationOn(t *testing.T) {
	server := httptest.NewTLSServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	if err := New(server.URL, "test").SetSelfSigned(false).
		Call("GET", "/health/version", nil, nil); err == nil {
		t.Error("SetSelfSigned(false) accepted an untrusted certificate")
	}

	// The one client that opted in must not have changed the process default.
	_ = New(server.URL, "test").SetSelfSigned(true)

	transport, ok := http.DefaultTransport.(*http.Transport)
	if !ok {
		t.Fatal("http.DefaultTransport is not an *http.Transport")
	}
	if transport.TLSClientConfig != nil && transport.TLSClientConfig.InsecureSkipVerify {
		t.Error("the shared default transport had certificate verification turned off")
	}
	// Belt and braces: a fresh client still refuses the same certificate.
	if err := New(server.URL, "test").Call("GET", "/health/version", nil, nil); err == nil {
		t.Error("a later client accepted an untrusted certificate")
	}
}

// Clone copies the *http.Client by pointer, so opting one clone into a
// self-signed certificate used to reach through to its parent and every sibling
// -- including the ones talking to Appwrite Cloud. `--self-signed` is per
// endpoint, and turning verification off for a self-hosted instance must not
// turn it off for anything else.
func TestSelfSignedOnACloneLeavesTheParentVerifying(t *testing.T) {
	server := httptest.NewTLSServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/json")
		_, _ = w.Write([]byte(`{"version":"1.0.0"}`))
	}))
	defer server.Close()

	parent := New(server.URL, "test")
	sibling := parent.Clone()

	_ = parent.Clone().SetSelfSigned(true)

	if err := parent.Call("GET", "/health/version", nil, nil); err == nil {
		t.Error("a clone opting in disabled verification for its parent")
	}
	if err := sibling.Call("GET", "/health/version", nil, nil); err == nil {
		t.Error("a clone opting in disabled verification for a sibling")
	}
}

// The opted-in client itself still has to work, and a clone taken from it
// afterwards should inherit the decision rather than silently re-enable
// verification against an endpoint that cannot satisfy it.
func TestSelfSignedSurvivesCloning(t *testing.T) {
	server := httptest.NewTLSServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/json")
		_, _ = w.Write([]byte(`{"version":"1.0.0"}`))
	}))
	defer server.Close()

	accepted := New(server.URL, "test").SetSelfSigned(true)

	if err := accepted.Call("GET", "/health/version", nil, nil); err != nil {
		t.Errorf("the opted-in client rejected the certificate: %v", err)
	}
	if err := accepted.Clone().Call("GET", "/health/version", nil, nil); err != nil {
		t.Errorf("a clone of the opted-in client rejected the certificate: %v", err)
	}
}

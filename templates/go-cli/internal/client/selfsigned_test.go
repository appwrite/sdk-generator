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

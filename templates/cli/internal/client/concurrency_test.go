package client

import (
	"net/http"
	"net/http/httptest"
	"sync"
	"testing"
)

// `push` shares one client across UploadConcurrency chunk uploads, and every
// response may carry a Set-Cookie that the client records. So one goroutine
// writes the cookie while seven others read it to build their next request.
//
// The race is invisible without -race and does not need a console cookie to be
// present in practice: the read and the write are what matter, not the value.
// Guarding the assertion below is secondary -- the point of this test is that it
// is run with -race, which the CLI CI job now does.
func TestConcurrentCallsDoNotRaceOnTheSessionCookie(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Add("Set-Cookie", "a_session_console=abc123; Path=/; HttpOnly")
		w.Header().Set("content-type", "application/json")
		_, _ = w.Write([]byte(`{}`))
	}))
	defer server.Close()

	api := New(server.URL, "test")

	var group sync.WaitGroup
	for range 32 {
		group.Add(1)
		go func() {
			defer group.Done()

			var out map[string]any
			_ = api.Call(http.MethodGet, "/v1/health", nil, &out)
		}()
	}
	group.Wait()

	if api.SessionCookie() == "" {
		t.Error("the console session cookie was never recorded")
	}
}

// Clone exists so that scoping one call does not scope the next, so a clone must
// not share the mutable cookie state it was copied from either.
func TestCloneDoesNotShareCookieState(t *testing.T) {
	api := New("https://example.invalid", "test").SetCookie("a_session_console=parent")

	clone := api.Clone().SetCookie("a_session_console=clone")

	if got := api.outboundCookie(); got != "a_session_console=parent" {
		t.Errorf("the clone overwrote its parent's cookie: %q", got)
	}
	if got := clone.outboundCookie(); got != "a_session_console=clone" {
		t.Errorf("clone cookie = %q", got)
	}
}

// A clone taken while uploads are in flight must not race with them either.
func TestCloneWhileCallsAreInFlight(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Add("Set-Cookie", "a_session_console=abc123")
		w.Header().Set("content-type", "application/json")
		_, _ = w.Write([]byte(`{}`))
	}))
	defer server.Close()

	api := New(server.URL, "test")

	var group sync.WaitGroup
	for range 16 {
		group.Add(1)
		go func() {
			defer group.Done()

			var out map[string]any
			_ = api.Call(http.MethodGet, "/v1/health", nil, &out)
		}()
		group.Add(1)
		go func() {
			defer group.Done()

			_ = api.Clone()
		}()
	}
	group.Wait()
}

// One http.Client.Timeout covers connect, write, read and body streaming
// together, so the old 60 second ceiling was also the budget for uploading a
// 5 MB chunk: below roughly 90 KB/s of upstream every chunk failed, reported as
// a server error with no retry.
//
// Asserted as configuration rather than by timing. Demonstrating the fault takes
// a minute of wall clock by definition, which is not something to put in a unit
// suite -- but the property that broke uploads is exactly "a whole-request
// deadline exists", so that is what this pins.
func TestNoWholeRequestDeadline(t *testing.T) {
	api := New("https://example.invalid", "test")

	if api.HTTP.Timeout != 0 {
		t.Errorf("http.Client.Timeout = %s, which caps how long an upload may take",
			api.HTTP.Timeout)
	}

	transport, ok := api.HTTP.Transport.(*http.Transport)
	if !ok {
		t.Fatalf("transport is %T, want *http.Transport", api.HTTP.Transport)
	}
	if transport.ResponseHeaderTimeout <= 0 {
		t.Error("no ResponseHeaderTimeout: a server that goes quiet would hang the CLI")
	}
}

// Opting into a self-signed certificate replaces the transport, which must not
// take the response-header bound with it.
func TestSelfSignedKeepsTheResponseHeaderBound(t *testing.T) {
	api := New("https://example.invalid", "test").SetSelfSigned(true)

	transport, ok := api.HTTP.Transport.(*http.Transport)
	if !ok {
		t.Fatalf("transport is %T, want *http.Transport", api.HTTP.Transport)
	}
	if transport.ResponseHeaderTimeout <= 0 {
		t.Error("--self-signed discarded the ResponseHeaderTimeout")
	}
	if api.HTTP.Timeout != 0 {
		t.Errorf("--self-signed reintroduced a whole-request deadline: %s", api.HTTP.Timeout)
	}
}

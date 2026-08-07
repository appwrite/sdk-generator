package sdk

import (
	"context"
	"errors"
	"io"
	"net/http"
	"net/http/httptest"
	"net/url"
	"strings"
	"testing"
	"time"
)

func testRecordingHTTPClient() *http.Client {
	return recordingHTTPClient(&http.Client{Transport: http.DefaultTransport})
}

// --raw promises the full response. Rendering the typed struct instead dropped
// every field the generated model does not declare, so a field the API added
// was invisible until the SDK was regenerated and the CLI re-pinned.
func TestResponseBodyIsCaptured(t *testing.T) {
	body := `{"total":42,"undeclaredByTheModel":"kept"}`

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/json")
		w.Write([]byte(body))
	}))
	defer server.Close()

	LastResponse.Take()
	if _, err := testRecordingHTTPClient().Get(server.URL); err != nil {
		t.Fatal(err)
	}

	if got := string(LastResponse.Take()); got != body {
		t.Errorf("captured %q, want %q", got, body)
	}
}

// The SDK parses the same body afterwards. Reading it to capture it must not
// consume it.
func TestCapturedBodyIsStillReadable(t *testing.T) {
	body := `{"$id":"abc"}`

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/json")
		w.Write([]byte(body))
	}))
	defer server.Close()

	response, err := testRecordingHTTPClient().Get(server.URL)
	if err != nil {
		t.Fatal(err)
	}
	defer response.Body.Close()

	read, err := io.ReadAll(response.Body)
	if err != nil {
		t.Fatal(err)
	}
	if string(read) != body {
		t.Errorf("body read back as %q, want %q", read, body)
	}
}

// Take clears, so a second render with no request behind it falls back to the
// typed struct rather than reprinting the previous command's response.
func TestTakeClearsTheCapturedBody(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/json")
		w.Write([]byte(`{"a":1}`))
	}))
	defer server.Close()

	if _, err := testRecordingHTTPClient().Get(server.URL); err != nil {
		t.Fatal(err)
	}

	if len(LastResponse.Take()) == 0 {
		t.Fatal("nothing captured")
	}
	if got := LastResponse.Take(); got != nil {
		t.Errorf("second Take returned %q, want nothing", got)
	}
}

// A file download is not JSON and must not be held in memory or mistaken for a
// renderable response.
func TestNonJSONResponsesAreNotCaptured(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/octet-stream")
		w.Write([]byte("binary"))
	}))
	defer server.Close()

	LastResponse.Take()
	if _, err := testRecordingHTTPClient().Get(server.URL); err != nil {
		t.Fatal(err)
	}

	if got := LastResponse.Take(); got != nil {
		t.Errorf("captured a non-JSON body: %q", got)
	}
}

// A chunked upload makes one request per chunk and the command renders the file
// the last one returns, so the newest body wins.
func TestTheNewestResponseWins(t *testing.T) {
	responses := []string{`{"chunk":1}`, `{"chunk":2}`, `{"chunk":3}`}
	index := 0

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("content-type", "application/json")
		w.Write([]byte(responses[index]))
		index++
	}))
	defer server.Close()

	client := testRecordingHTTPClient()
	for range responses {
		if _, err := client.Get(server.URL); err != nil {
			t.Fatal(err)
		}
	}

	if got := string(LastResponse.Take()); got != `{"chunk":3}` {
		t.Errorf("captured %q, want the last response", got)
	}
}

func TestRecordingClientUsesThePhaseBoundedTransport(t *testing.T) {
	baseTransport := &http.Transport{ResponseHeaderTimeout: 60 * time.Second}
	client := recordingHTTPClient(&http.Client{
		Timeout:   10 * time.Second,
		Transport: baseTransport,
	})

	if client.Timeout != 0 {
		t.Errorf("http.Client.Timeout = %s, want no whole-request deadline", client.Timeout)
	}
	recorder, ok := client.Transport.(*recordingTransport)
	if !ok {
		t.Fatalf("transport is %T, want *recordingTransport", client.Transport)
	}
	transport, ok := recorder.next.(*http.Transport)
	if !ok {
		t.Fatalf("recorded transport is %T, want *http.Transport", recorder.next)
	}
	if transport.ResponseHeaderTimeout != 60*time.Second {
		t.Errorf("ResponseHeaderTimeout = %s, want 60s", transport.ResponseHeaderTimeout)
	}
}

func TestMutationTimeoutHasAnUnknownOutcome(t *testing.T) {
	cause := &url.Error{Op: "Post", URL: "https://example.invalid", Err: context.DeadlineExceeded}
	err := WrapMutationError(http.MethodPost, cause)

	var unknown *UnknownMutationOutcome
	if !errors.As(err, &unknown) {
		t.Fatalf("error is %T, want *UnknownMutationOutcome", err)
	}
	if !errors.Is(err, context.DeadlineExceeded) {
		t.Error("the original deadline error is not preserved")
	}
	if !strings.Contains(err.Error(), "outcome is unknown") ||
		!strings.Contains(err.Error(), "before retrying") {
		t.Errorf("error does not explain the uncertain mutation: %q", err)
	}
}

func TestReadTimeoutIsUnchanged(t *testing.T) {
	cause := &url.Error{Op: "Get", URL: "https://example.invalid", Err: context.DeadlineExceeded}

	if got := WrapMutationError(http.MethodGet, cause); got != cause {
		t.Errorf("read timeout was wrapped as %T", got)
	}
}

func TestNonTimeoutMutationErrorIsUnchanged(t *testing.T) {
	cause := errors.New("server rejected the request")

	if got := WrapMutationError(http.MethodDelete, cause); got != cause {
		t.Errorf("ordinary mutation error was wrapped as %T", got)
	}
	if got := WrapMutationError(http.MethodPost, nil); got != nil {
		t.Errorf("nil error became %v", got)
	}
}

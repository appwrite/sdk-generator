package sdk

import (
	"bytes"
	"io"
	"net/http"
	"net/http/cookiejar"
	"strings"
	"sync"
	"time"
)

// --raw is documented as "the full raw JSON response", and --json filters that
// response. Both were rendering the SDK's typed struct re-encoded instead, so
// any field the API returned that the generated model does not declare was
// dropped from both -- invisible until sdk-for-go is regenerated and the CLI
// re-pinned.
//
// The body is captured at the transport rather than by changing the SDK: the
// service methods return typed structs and nothing else, and widening every
// generated signature to carry the bytes alongside would be a large change to
// a published SDK for one consumer's benefit.

// LastResponse holds the most recent JSON response body.
//
// Last-wins is deliberate. A chunked upload makes one request per chunk and the
// command renders the file the final chunk returns, so the final body is the
// one to keep.
var LastResponse = &responseRecorder{}

type responseRecorder struct {
	mutex sync.Mutex
	body  []byte
}

// Take returns the captured body and clears it.
//
// Clearing matters: a command that renders twice, or a later render with no
// request behind it, must not reprint a stale response.
func (r *responseRecorder) Take() []byte {
	r.mutex.Lock()
	defer r.mutex.Unlock()

	body := r.body
	r.body = nil

	return body
}

// Record stores a body. Exported for tests that stage a response without a
// server; the transport is the only production caller.
func (r *responseRecorder) Record(body []byte) {
	r.mutex.Lock()
	defer r.mutex.Unlock()

	r.body = body
}

// recordingTransport copies each JSON response body on its way past.
type recordingTransport struct {
	next http.RoundTripper
}

func (t *recordingTransport) RoundTrip(request *http.Request) (*http.Response, error) {
	response, err := t.next.RoundTrip(request)
	if err != nil || response == nil || response.Body == nil {
		return response, err
	}

	if !strings.HasPrefix(response.Header.Get("content-type"), "application/json") {
		return response, nil
	}

	body, readErr := io.ReadAll(response.Body)
	_ = response.Body.Close()
	if readErr != nil {
		return response, readErr
	}

	// The SDK still has to read the body, so hand back an equivalent one.
	response.Body = io.NopCloser(bytes.NewReader(body))
	LastResponse.Record(body)

	return response, nil
}

// recordingHTTPClient is the http.Client the SDK client is given.
//
// It reproduces GetDefaultClient's cookie jar and timeout, because setting
// Client on the SDK's struct skips that constructor entirely.
func recordingHTTPClient(timeout time.Duration) *http.Client {
	jar, err := cookiejar.New(nil)
	if err != nil {
		jar = nil
	}

	return &http.Client{
		Jar:       jar,
		Timeout:   timeout,
		Transport: &recordingTransport{next: http.DefaultTransport},
	}
}

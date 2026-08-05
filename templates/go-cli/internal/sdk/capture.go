package sdk

import (
	"bytes"
	"io"
	"net/http"
	"net/http/cookiejar"
	"strings"
	"sync"
	"sync/atomic"
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

// requestWasMade records that the process reached the API.
var requestWasMade atomic.Bool

// RequestWasMade reports whether any request has been sent.
//
// It answers whether --verbose has anything to say. That flag prints the status
// code, the unwrap chain and the captured response body, all of which come from
// a request; a command that fails before it sends one -- a missing argument, an
// unsupported -l value, a config file with no tables in it -- has none of them,
// so advising the user to re-run with it promised detail that did not exist.
//
// Set before the round trip rather than after, so a connection that never
// completed still counts. `dial tcp: connection refused` is a request the user
// made, and the chain that wraps it is worth printing.
func RequestWasMade() bool {
	return requestWasMade.Load()
}

// SetRequestWasMade overrides the flag, and exists for tests: the difference
// between a failure that reached the API and one that did not is what decides
// whether the CLI advises --verbose, and nothing in a test sends a request.
//
// Returns the previous value, so a caller can restore it.
func SetRequestWasMade(made bool) bool {
	return requestWasMade.Swap(made)
}

// recordingTransport copies each JSON response body on its way past.
type recordingTransport struct {
	next http.RoundTripper
}

func (t *recordingTransport) RoundTrip(request *http.Request) (*http.Response, error) {
	requestWasMade.Store(true)

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

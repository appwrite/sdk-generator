package sdk

import (
	"bytes"
	"context"
	"encoding/json"
	"errors"
	"io"
	"net/http"
	"net/http/cookiejar"
	"strings"
	"sync"
	"sync/atomic"
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

// LastResponse holds the most recent JSON response body, except that a chunk
// reporting an unfinished upload never replaces one reporting the finished
// file: the SDK uploads chunks concurrently, so they do not arrive in order.
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

	if uploadState(r.body) == uploadFinished && uploadState(body) == uploadUnfinished {
		return
	}

	r.body = body
}

type upload int

const (
	uploadNone upload = iota
	uploadUnfinished
	uploadFinished
)

func uploadState(body []byte) upload {
	var progress struct {
		Total    *float64 `json:"chunksTotal"`
		Uploaded *float64 `json:"chunksUploaded"`
	}
	if json.Unmarshal(body, &progress) != nil || progress.Total == nil || progress.Uploaded == nil {
		return uploadNone
	}
	if *progress.Uploaded >= *progress.Total {
		return uploadFinished
	}

	return uploadUnfinished
}

// requestWasMade records that the process reached the API.
var requestWasMade atomic.Bool

// UnknownMutationOutcome is a timeout returned after a mutating request may
// already have reached the server. Retrying it as an ordinary failure can
// duplicate a create or repeat another side effect.
type UnknownMutationOutcome struct {
	err error
}

func (e *UnknownMutationOutcome) Error() string {
	return "the request timed out after it may have reached the server; its outcome is unknown. " +
		"Check the resource before retrying to avoid duplicate changes"
}

// Unwrap keeps the transport error available to --verbose and errors.Is/As.
func (e *UnknownMutationOutcome) Unwrap() error { return e.err }

// WrapMutationError marks a timeout from an unsafe HTTP method as an unknown
// outcome. Read-only requests and ordinary failures keep their original error.
func WrapMutationError(method string, err error) error {
	if err == nil || isReadMethod(method) || !isTimeout(err) {
		return err
	}

	return &UnknownMutationOutcome{err: err}
}

func isReadMethod(method string) bool {
	switch strings.ToUpper(method) {
	case http.MethodGet, http.MethodHead, http.MethodOptions:
		return true
	default:
		return false
	}
}

func isTimeout(err error) bool {
	if errors.Is(err, context.DeadlineExceeded) {
		return true
	}

	var timeout interface{ Timeout() bool }

	return errors.As(err, &timeout) && timeout.Timeout()
}

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

// recordingHTTPClient adds response capture and the SDK's cookie jar to the
// CLI's shared HTTP policy. In particular, it removes any whole-request
// deadline instead of inheriting the SDK's ten-second timeout.
func recordingHTTPClient(base *http.Client) *http.Client {
	jar, err := cookiejar.New(nil)
	if err != nil {
		jar = nil
	}
	if base == nil {
		base = &http.Client{}
	}

	next := base.Transport
	if next == nil {
		next = http.DefaultTransport
	}
	recorded := *base
	recorded.Jar = jar
	recorded.Timeout = 0
	recorded.Transport = &recordingTransport{next: next}

	return &recorded
}

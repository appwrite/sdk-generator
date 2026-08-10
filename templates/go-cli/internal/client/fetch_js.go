//go:build js

package client

import "net/http"

// Go's js transport consumes this header as a fetch option.
const fetchCredentials = "js.fetch:credentials"

// browserCredentials swaps the CLI's own Cookie header for the browser's.
type browserCredentials struct {
	next http.RoundTripper
}

// RoundTrip replaces the forbidden Cookie header with browser credentials.
func (c browserCredentials) RoundTrip(request *http.Request) (*http.Response, error) {
	outbound := request.Clone(request.Context())
	outbound.Header.Del("Cookie")
	outbound.Header.Set(fetchCredentials, "include")

	return c.next.RoundTrip(outbound)
}

func decorate(client *http.Client) *http.Client {
	next := client.Transport
	if next == nil {
		next = http.DefaultTransport
	}
	client.Transport = browserCredentials{next: next}

	return client
}

// Browsers own certificate verification, so --self-signed cannot be honoured.
func noteSelfSigned() {
	if RequestLog != nil {
		RequestLog("--self-signed does not apply in a browser: " +
			"the browser verifies certificates and wasm cannot override it")
	}
}

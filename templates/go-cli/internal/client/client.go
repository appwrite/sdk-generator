package client

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"runtime"
	"strings"
	"time"
)

// Ports templates/cli/lib/client.ts.

// ResponseFormat pins the API response shape the CLI is written against.
//
// Hard-coded in the TypeScript client too (client.ts:40). It tracks the API
// version the CLI was generated for, not the CLI's own version, so it must not
// be wired to sdk.version.
const ResponseFormat = "1.8.1"

// Header names. The API is case-insensitive, but these match the TypeScript
// spelling so the two CLIs are directly comparable on the wire.
const (
	headerProject      = "X-Appwrite-Project"
	headerKey          = "X-Appwrite-Key"
	headerJWT          = "X-Appwrite-JWT"
	headerLocale       = "X-Appwrite-Locale"
	headerMode         = "X-Appwrite-Mode"
	headerOrganization = "X-Appwrite-Organization"
	headerFormat       = "X-Appwrite-Response-Format"
)

// Client is a thin HTTP client for the Appwrite API.
type Client struct {
	Endpoint   string
	HTTP       *http.Client
	headers    map[string]string
	cookie     string
	SDKVersion string
}

// New returns a client with the headers every request carries.
func New(endpoint, sdkVersion string) *Client {
	return &Client{
		Endpoint:   strings.TrimRight(endpoint, "/"),
		HTTP:       &http.Client{Timeout: 60 * time.Second},
		SDKVersion: sdkVersion,
		headers: map[string]string{
			"content-type":   "application/json",
			headerFormat:     ResponseFormat,
			"x-sdk-name":     "Command Line",
			"x-sdk-platform": "console",
			"x-sdk-language": "cli",
			"x-sdk-version":  sdkVersion,
			"user-agent": fmt.Sprintf("AppwriteCLI/%s (%s; %s)",
				sdkVersion, runtime.GOOS, runtime.GOARCH),
		},
	}
}

// SetHeader sets one header.
func (c *Client) SetHeader(name, value string) *Client {
	c.headers[name] = value

	return c
}

// SetProject sets the project the request acts on.
func (c *Client) SetProject(project string) *Client { return c.SetHeader(headerProject, project) }

// SetKey authenticates with an API key.
func (c *Client) SetKey(key string) *Client { return c.SetHeader(headerKey, key) }

// SetJWT authenticates with a JWT.
func (c *Client) SetJWT(jwt string) *Client { return c.SetHeader(headerJWT, jwt) }

// SetLocale sets the response locale.
func (c *Client) SetLocale(locale string) *Client { return c.SetHeader(headerLocale, locale) }

// SetMode selects admin or default scope resolution.
func (c *Client) SetMode(mode string) *Client { return c.SetHeader(headerMode, mode) }

// SetOrganization names the organization for endpoints that take no ID in the
// path.
func (c *Client) SetOrganization(id string) *Client { return c.SetHeader(headerOrganization, id) }

// SetBearer authenticates with an OAuth2 access token.
func (c *Client) SetBearer(token string) *Client {
	return c.SetHeader("Authorization", "Bearer "+token)
}

// SetSelfSigned accepts a self-signed TLS certificate.
//
// Ports `rejectUnauthorized: !this.selfSigned` on the TypeScript client's HTTPS
// agent (client.ts:236). A self-hosted instance behind its own certificate is
// the whole reason `client --self-signed` exists, and without this the flag was
// stored and never acted on.
//
// The transport is this client's own, not http.DefaultTransport: mutating the
// shared default would turn verification off for every request the process makes
// afterwards, including ones to Appwrite Cloud.
func (c *Client) SetSelfSigned(selfSigned bool) *Client {
	if !selfSigned {
		return c
	}

	transport := http.DefaultTransport.(*http.Transport).Clone()
	transport.TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	c.HTTP.Transport = transport

	return c
}

// SetCookie authenticates with a legacy session cookie.
func (c *Client) SetCookie(cookie string) *Client {
	c.cookie = cookie

	return c
}

// APIError is a non-2xx response from the API.
type APIError struct {
	Status  int
	Code    int    `json:"code"`
	Message string `json:"message"`
	Type    string `json:"type"`
}

func (e *APIError) Error() string {
	if e.Message != "" {
		return e.Message
	}

	return fmt.Sprintf("request failed with status %d", e.Status)
}

// Call performs a request and decodes the JSON response into out.
//
// out may be nil for endpoints whose body is not needed. Numbers decode as
// json.Number so large integers survive, matching the json-bigint parsing the
// TypeScript CLI uses.
func (c *Client) Call(method, path string, body any, out any) error {
	var reader io.Reader
	if body != nil {
		encoded, err := json.Marshal(body)
		if err != nil {
			return err
		}
		reader = bytes.NewReader(encoded)
	}

	request, err := http.NewRequest(method, c.Endpoint+path, reader)
	if err != nil {
		return err
	}
	for name, value := range c.headers {
		request.Header.Set(name, value)
	}
	if c.cookie != "" {
		request.Header.Set("Cookie", c.cookie)
	}

	response, err := c.HTTP.Do(request)
	if err != nil {
		return err
	}
	defer response.Body.Close()

	payload, err := io.ReadAll(response.Body)
	if err != nil {
		return err
	}

	if response.StatusCode < 200 || response.StatusCode >= 300 {
		apiError := &APIError{Status: response.StatusCode}
		// A non-JSON error body (a proxy error page, say) leaves Message empty
		// and Error() falls back to the status line.
		_ = json.Unmarshal(payload, apiError)

		return apiError
	}

	if out == nil {
		return nil
	}

	decoder := json.NewDecoder(bytes.NewReader(payload))
	decoder.UseNumber()

	return decoder.Decode(out)
}

package client

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"io"
	"mime/multipart"
	"net/http"
	"net/textproto"
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
	headerUploadID     = "x-appwrite-id"
	headerRange        = "content-range"
	headerContentType  = "content-type"
)

// Client is a thin HTTP client for the Appwrite API.
type Client struct {
	Endpoint   string
	HTTP       *http.Client
	headers    map[string]string
	cookie     string
	SDKVersion string
	// SessionCookie is the console session cookie the server last set, which
	// an email-and-password sign-in has to persist.
	SessionCookie string
}

// RequestLog receives one line per HTTP request when --verbose is on.
//
// A package variable rather than a field on Client because clients are built
// in a dozen places and diagnostics should not have to be threaded through
// every one of them. Set once during start-up, before any request is made, and
// only read afterwards -- concurrent requests read it, none of them write it.
var RequestLog func(format string, arguments ...any)

func logRequest(method, path string, status int, elapsed time.Duration) {
	if RequestLog == nil {
		return
	}

	if status == 0 {
		RequestLog("%s %s failed after %s", method, path, elapsed.Round(time.Millisecond))

		return
	}

	RequestLog("%s %s %d in %s", method, path, status, elapsed.Round(time.Millisecond))
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

// Download fetches a path and returns the raw body.
//
// Separate from Call because a deployment archive is not JSON, and decoding a
// gzip stream as JSON fails with a message about the first byte rather than
// about the archive.
func (c *Client) Download(path string) ([]byte, error) {
	request, err := http.NewRequest("GET", c.Endpoint+path, nil)
	if err != nil {
		return nil, err
	}
	for name, value := range c.headers {
		request.Header.Set(name, value)
	}
	if c.cookie != "" {
		request.Header.Set("Cookie", c.cookie)
	}

	response, err := c.HTTP.Do(request)
	if err != nil {
		return nil, err
	}
	defer response.Body.Close()

	payload, err := io.ReadAll(response.Body)
	if err != nil {
		return nil, err
	}

	if response.StatusCode < 200 || response.StatusCode >= 300 {
		apiError := &APIError{Status: response.StatusCode}
		// A failed download still answers with JSON, so the message is
		// recoverable even though the success path is binary.
		_ = json.Unmarshal(payload, apiError)

		return nil, apiError
	}

	return payload, nil
}

// WithoutResponseFormat drops the x-appwrite-response-format header.
//
// That header does not merely declare a version -- it asks the API for THAT
// version's response shape. The console routes still answer it with a legacy
// flat project (serviceStatusForAccount, authEmailPassword, ...) instead of the
// `services`/`protocols`/`authMethods` arrays the config is built from.
//
// The TypeScript never hits this because its console calls go through
// @appwrite.io/console, which sends no such header; only its own client.ts
// sends one. This is how a call reproduces the console SDK.
func (c *Client) WithoutResponseFormat() *Client {
	delete(c.headers, headerFormat)

	return c
}

// Clone returns a copy with its own header map.
//
// Needed because one console client lists organizations and then acts within
// one: setting X-Appwrite-Organization on the shared client would scope the
// next unrelated call as well.
func (c *Client) Clone() *Client {
	copied := *c
	copied.headers = make(map[string]string, len(c.headers))
	for name, value := range c.headers {
		copied.headers[name] = value
	}

	return &copied
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
//
// Deliberately unreachable for now: it completes the setter surface of
// templates/cli/lib/client.ts:95, and the only caller will be the JwtManager
// that `run` still lacks. Kept so that port is a wiring change, not a rewrite.
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

// consoleSessionCookie is the cookie an email-and-password sign-in returns.
// Only this one is kept; a response may also set unrelated cookies, and
// storing those would send them back on every later request.
const consoleSessionCookie = "a_session_console="

// captureSessionCookie remembers a console session cookie the server set.
//
// Ports the Set-Cookie handling in the TypeScript client (client.ts:327). The
// email-and-password flow has no other way to learn its session: the cookie IS
// the credential, and it arrives on the response to POST /account/sessions/email.
func (c *Client) captureSessionCookie(response *http.Response) {
	for _, cookie := range response.Header.Values("Set-Cookie") {
		if strings.HasPrefix(cookie, consoleSessionCookie) {
			c.cookie = cookie
			c.SessionCookie = cookie
		}
	}
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

	return c.send(request, out)
}

// FormField is one text field of a multipart upload.
//
// Ordered rather than a map: the request body is what a recorded trace
// compares, and Go map iteration would reorder the parts on every run.
type FormField struct {
	Name  string
	Value string
}

// UploadPart is one multipart request of a chunked upload.
//
// Content is read as the request body rather than buffered, so the caller
// decides how much of a file is in memory at once -- for a deployment archive
// that is a section reader over the file and the answer is "none of it".
type UploadPart struct {
	Path   string
	Fields []FormField
	// FileField is the form field the file is sent under, `code` for a
	// deployment.
	FileField     string
	FileName      string
	ContentType   string
	Content       io.Reader
	ContentLength int64
	// Range is the content-range header. Empty for an upload that fits in one
	// request, which the API answers without minting an upload id.
	Range string
	// UploadID pins this part to the upload the first chunk created.
	UploadID string
}

// Upload POSTs one multipart part and decodes the JSON response into out.
//
// The body is assembled as three readers -- the form prefix, the file content,
// the closing boundary -- so its exact length is known without holding it.
// Content-Length matters here: without it Go falls back to chunked
// transfer-encoding, and the API sizes an upload from the header.
func (c *Client) Upload(part UploadPart, out any) error {
	var prefix bytes.Buffer
	writer := multipart.NewWriter(&prefix)

	for _, field := range part.Fields {
		if err := writer.WriteField(field.Name, field.Value); err != nil {
			return err
		}
	}

	header := make(textproto.MIMEHeader)
	header.Set("Content-Disposition", fmt.Sprintf(
		`form-data; name="%s"; filename="%s"`,
		escapeQuotes(part.FileField), escapeQuotes(part.FileName)))
	header.Set("Content-Type", part.ContentType)
	if _, err := writer.CreatePart(header); err != nil {
		return err
	}

	// Written by hand rather than by writer.Close(), which would append it to
	// the prefix ahead of the content.
	closing := "\r\n--" + writer.Boundary() + "--\r\n"

	body := io.MultiReader(
		bytes.NewReader(prefix.Bytes()), part.Content, strings.NewReader(closing))

	request, err := http.NewRequest("POST", c.Endpoint+part.Path, body)
	if err != nil {
		return err
	}
	for name, value := range c.headers {
		if strings.EqualFold(name, headerContentType) {
			continue
		}
		request.Header.Set(name, value)
	}
	if c.cookie != "" {
		request.Header.Set("Cookie", c.cookie)
	}
	request.Header.Set(headerContentType, writer.FormDataContentType())
	if part.Range != "" {
		request.Header.Set(headerRange, part.Range)
	}
	if part.UploadID != "" {
		request.Header.Set(headerUploadID, part.UploadID)
	}
	request.ContentLength = int64(prefix.Len()) + part.ContentLength + int64(len(closing))

	return c.send(request, out)
}

// escapeQuotes protects a form field name or filename in a header.
func escapeQuotes(value string) string {
	return strings.NewReplacer(`\`, `\\`, `"`, `\"`).Replace(value)
}

// send performs a prepared request and decodes the JSON response into out.
func (c *Client) send(request *http.Request, out any) error {
	started := time.Now()

	response, err := c.HTTP.Do(request)
	if err != nil {
		logRequest(request.Method, request.URL.Path, 0, time.Since(started))

		return err
	}
	defer response.Body.Close()

	logRequest(request.Method, request.URL.Path, response.StatusCode, time.Since(started))
	c.captureSessionCookie(response)

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

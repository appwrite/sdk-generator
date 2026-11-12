// Package appwrite is a small client of the Appwrite REST API: what the terminal interface reads
// and changes, for a project on Appwrite Cloud or on a self-hosted instance. It speaks plain
// net/http rather than the Go SDK, whose calls take no context and decode rows without their
// columns.
//
// docs: https://appwrite.io/docs/apis/rest
package appwrite

import (
	"bytes"
	"context"
	"crypto/tls"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"net/url"
	"strings"
	"time"
)

// DefaultEndpoint is Appwrite Cloud's endpoint, which routes a project to its region.
const DefaultEndpoint = "https://cloud.appwrite.io/v1"

// ConsoleProject is the project id the console's own routes answer to, as organisations and the
// projects in them.
const ConsoleProject = "console"

// Options says which instance and project to talk to, and as whom: an API key of the project, or
// the session cookie of a console login.
type Options struct {
	Endpoint string
	Project  string
	Key      string
	// Cookie is the a_session_console cookie of a console login, as the appwrite cli keeps it.
	Cookie string
	// Token returns the access token of a console login made with the device flow, renewed when
	// it has expired, so a session open longer than a token lives keeps working.
	Token      func() (string, error)
	SelfSigned bool
	// Cloud says the endpoint is Appwrite Cloud, which lists organisations at a route of its own.
	// The caller decides it, as the cli knows Cloud's hosts.
	Cloud bool
	// UserAgent names the program in every request.
	UserAgent string
}

// Client calls one instance as one project.
type Client struct {
	endpoint string
	project  string
	key      string
	cookie   string
	token    func() (string, error)
	agent    string
	cloud    bool
	http     *http.Client
}

// New makes a client. The endpoint is cleaned of a trailing slash, and given /v1 when it has no
// path, as a self-hosted instance is often written as its host alone.
func New(opts Options) (*Client, error) {
	endpoint, err := NormalizeEndpoint(opts.Endpoint)
	if err != nil {
		return nil, err
	}
	return &Client{
		endpoint: endpoint,
		project:  opts.Project,
		key:      opts.Key,
		cookie:   opts.Cookie,
		token:    opts.Token,
		agent:    opts.UserAgent,
		cloud:    opts.Cloud,
		http:     &http.Client{Transport: transport(opts.SelfSigned), CheckRedirect: sameOrigin},
	}, nil
}

// maxRedirects is how many redirects a request follows, as many as net/http follows by default.
const maxRedirects = 10

// sameOrigin follows a redirect only to the origin the request was sent to. net/http forwards a
// request's headers on a redirect and drops only Authorization and Cookie when the host changes,
// so X-Appwrite-Key would otherwise reach whatever host a redirect names.
func sameOrigin(req *http.Request, via []*http.Request) error {
	if len(via) >= maxRedirects {
		return fmt.Errorf("stopped after %d redirects", maxRedirects)
	}
	from := via[0].URL
	if req.URL.Scheme != from.Scheme || !strings.EqualFold(req.URL.Host, from.Host) {
		return fmt.Errorf("not following a redirect from %s://%s to %s://%s, so the credential stays with the endpoint: sign in to %s://%s if it is the instance",
			from.Scheme, from.Host, req.URL.Scheme, req.URL.Host, req.URL.Scheme, req.URL.Host)
	}
	return nil
}

// transport is the default transport, which with selfSigned trusts any certificate: the flag
// says the instance's certificate is its own, which the system does not trust.
//
//nolint:gosec
func transport(selfSigned bool) *http.Transport {
	t := http.DefaultTransport.(*http.Transport).Clone()
	if selfSigned {
		t.TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	}
	return t
}

// NormalizeEndpoint checks an endpoint is an http or https url, cuts a trailing slash, and adds
// /v1 to a bare host.
func NormalizeEndpoint(endpoint string) (string, error) {
	if endpoint == "" {
		endpoint = DefaultEndpoint
	}
	u, err := url.Parse(strings.TrimRight(endpoint, "/"))
	if err != nil || (u.Scheme != "http" && u.Scheme != "https") || u.Host == "" {
		return "", fmt.Errorf("endpoint %q is not an http or https url, as https://cloud.appwrite.io/v1", endpoint)
	}
	if u.Path == "" {
		u.Path = "/v1"
	}
	return u.String(), nil
}

// Endpoint is the endpoint the client calls.
func (c *Client) Endpoint() string { return c.endpoint }

// ProjectID is the project the client calls as.
func (c *Client) ProjectID() string { return c.project }

// AsProject is a client of the same instance and login, for another project, as the console
// opens a project of an organisation.
func (c *Client) AsProject(project string) *Client {
	other := *c
	other.project = project
	return &other
}

// Cloud reports whether the endpoint is Appwrite Cloud, as the options said.
func (c *Client) Cloud() bool { return c.cloud }

// Console reports whether the client calls with a console session rather than a project's key.
func (c *Client) Console() bool { return c.key == "" && (c.cookie != "" || c.token != nil) }

// Error is what the API answers a request it refuses or fails with.
//
// docs: https://appwrite.io/docs/advanced/platform/response-codes
type Error struct {
	Code    int    `json:"code"`
	Type    string `json:"type"`
	Message string `json:"message"`
}

func (e *Error) Error() string {
	msg := strings.TrimSuffix(e.Message, ".")
	if msg == "" {
		msg = http.StatusText(e.Code)
	}
	return fmt.Sprintf("%s (%d %s)", msg, e.Code, e.Type)
}

// IsCode reports whether err is an answer of the API with the http status code.
func IsCode(err error, code int) bool {
	var e *Error
	return errors.As(err, &e) && e.Code == code
}

// IsType reports whether err is an answer of the API of the type, as project_not_found.
func IsType(err error, typ string) bool {
	var e *Error
	return errors.As(err, &e) && e.Type == typ
}

// maxBody bounds what is read of an answer, so a misbehaving server cannot fill the memory.
const maxBody = 64 << 20

// do sends a request and decodes the answer into out, when out is not nil. A body is sent as json.
func (c *Client) do(ctx context.Context, method, path string, query url.Values, body, out any) error {
	data, err := c.raw(ctx, method, path, query, body)
	if err != nil {
		return err
	}
	if out == nil || len(data) == 0 {
		return nil
	}
	if err := json.Unmarshal(data, out); err != nil {
		return fmt.Errorf("reading the answer to %s %s: %w", method, path, err)
	}
	return nil
}

// raw sends a request and returns the body of a successful answer.
func (c *Client) raw(ctx context.Context, method, path string, query url.Values, body any) ([]byte, error) {
	u := c.endpoint + path
	if len(query) > 0 {
		u += "?" + query.Encode()
	}
	var reader io.Reader
	if body != nil {
		data, err := json.Marshal(body)
		if err != nil {
			return nil, err
		}
		reader = bytes.NewReader(data)
	}
	req, err := http.NewRequestWithContext(ctx, method, u, reader)
	if err != nil {
		return nil, err
	}
	if body != nil {
		req.Header.Set("Content-Type", "application/json")
	}
	req.Header.Set("Accept", "application/json")
	if err := c.authorise(req); err != nil {
		return nil, err
	}
	start := time.Now()
	resp, err := c.http.Do(req)
	if err != nil {
		slog.Debug("request failed", "method", method, "path", path, "project", c.project, "err", err, "took", time.Since(start))
		return nil, err
	}
	defer func() { _ = resp.Body.Close() }()
	slog.Debug("request", "method", method, "path", path, "project", c.project, "status", resp.StatusCode, "took", time.Since(start))
	if resp.StatusCode >= 300 {
		return nil, decodeError(resp)
	}
	data, err := io.ReadAll(io.LimitReader(resp.Body, maxBody))
	if err != nil {
		return nil, fmt.Errorf("reading the answer to %s %s: %w", method, path, err)
	}
	return data, nil
}

// authorise names the project and who calls on a request. A console session calls a project's
// routes in admin mode, as the console and the appwrite cli do; the console's own routes refuse
// admin mode, and a key does not need it.
func (c *Client) authorise(req *http.Request) error {
	req.Header.Set("X-Appwrite-Project", c.project)
	switch {
	case c.key != "":
		req.Header.Set("X-Appwrite-Key", c.key)
	case c.token != nil:
		token, err := c.token()
		if err != nil {
			return err
		}
		req.Header.Set("Authorization", "Bearer "+token)
	case c.cookie != "":
		req.Header.Set("Cookie", c.cookie)
	}
	if c.Console() && c.project != ConsoleProject {
		req.Header.Set("X-Appwrite-Mode", "admin")
	}
	if c.agent != "" {
		req.Header.Set("User-Agent", c.agent)
	}
	return nil
}

// decodeError reads the error of an answer that is not a success.
func decodeError(resp *http.Response) error {
	data, _ := io.ReadAll(io.LimitReader(resp.Body, 1<<20))
	e := &Error{}
	if json.Unmarshal(data, e) != nil || e.Code == 0 {
		e.Code = resp.StatusCode
	}
	if e.Message == "" && e.Type == "" {
		e.Type = "http"
	}
	return e
}

// segment escapes an id for a path, so it stays the one segment it is put in.
func segment(id string) string { return url.PathEscape(id) }

// Version is the version of the instance, which answers without a key.
//
// docs: https://appwrite.io/docs/references/cloud/server-rest/health#getVersion
func (c *Client) Version(ctx context.Context) (string, error) {
	var v struct {
		Version string `json:"version"`
	}
	if err := c.do(ctx, http.MethodGet, "/health/version", nil, nil, &v); err != nil {
		return "", err
	}
	return v.Version, nil
}

// Ping measures one round trip to the instance.
func (c *Client) Ping(ctx context.Context) (time.Duration, error) {
	start := time.Now()
	_, err := c.Version(ctx)
	return time.Since(start), err
}

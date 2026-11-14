package client

import (
	"net/http"
	"net/http/httptest"
	"net/url"
	"strings"
	"testing"
)

// The API percent-decodes X-Appwrite-Reason, so what goes on the wire is the
// reason as encodeURIComponent would encode it: a space as %20, never +, which
// the API keeps as a plus sign.
func TestReasonHeaderEncodesLikeEncodeURIComponent(t *testing.T) {
	for _, test := range []struct {
		reason string
		want   string
	}{
		{"Rollback", "Rollback"},
		{"Roll back checkout", "Roll%20back%20checkout"},
		{"100% done", "100%25%20done"},
		{"Nasazení opravy", "Nasazen%C3%AD%20opravy"},
		{"a+b", "a+b"},
		{"  padded  ", "padded"},
		{"   ", ""},
		{"", ""},
	} {
		withReason(t, test.reason)

		got := ReasonHeader()
		if got != test.want {
			t.Errorf("ReasonHeader(%q) = %q, want %q", test.reason, got, test.want)
		}
		// Whatever is sent has to decode back to the reason the user gave.
		decoded, err := url.PathUnescape(got)
		if err != nil || decoded != strings.TrimSpace(test.reason) {
			t.Errorf("%q decodes to %q (%v)", got, decoded, err)
		}
	}
}

// Every request the client makes carries the reason, not just the first.
func TestEveryRequestCarriesTheReason(t *testing.T) {
	var received []string
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		received = append(received, r.Header.Get(HeaderReason))
		w.Header().Set("content-type", "application/json")
		_, _ = w.Write([]byte(`{}`))
	}))
	t.Cleanup(server.Close)

	withReason(t, "Nasazení")
	api := New(server.URL, "test")

	var out map[string]any
	for _, path := range []string{"/v1/health", "/v1/account"} {
		if err := api.Clone().Call(http.MethodGet, path, nil, &out); err != nil {
			t.Fatal(err)
		}
	}

	for _, header := range received {
		if header != "Nasazen%C3%AD" {
			t.Errorf("X-Appwrite-Reason = %q, want Nasazen%%C3%%AD", header)
		}
	}
	if len(received) != 2 {
		t.Errorf("made %d requests, want 2", len(received))
	}
}

// Without a reason the header is left out rather than sent empty.
func TestNoReasonSendsNoHeader(t *testing.T) {
	withReason(t, "")

	if _, ok := New("https://example.invalid", "test").headers[HeaderReason]; ok {
		t.Error("an empty reason was sent")
	}
}

// An environment is addressed alongside its project, and an empty one is the
// project itself, which sends no header at all.
func TestSetEnvironment(t *testing.T) {
	api := New("https://example.invalid", "test").SetProject("my-project").SetEnvironment("staging")
	if got := api.headers[headerEnvironment]; got != "staging" {
		t.Errorf("X-Appwrite-Environment = %q, want staging", got)
	}
	if got := api.headers[headerProject]; got != "my-project" {
		t.Errorf("X-Appwrite-Project = %q, want my-project", got)
	}

	api.SetEnvironment("")
	if _, ok := api.headers[headerEnvironment]; ok {
		t.Error("an empty environment was sent")
	}
}

func withReason(t *testing.T, reason string) {
	t.Helper()

	previous := Reason
	Reason = reason
	t.Cleanup(func() { Reason = previous })
}

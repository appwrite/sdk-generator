package cmd

import (
	"errors"
	"net/url"
	"strings"
	"testing"
)

// A proxy 502 or a maintenance page is an HTML document, and the SDK puts a
// non-JSON body straight into the error message. The CLI printed all 8,946
// bytes of it where the TypeScript printed one line.
func TestHTMLErrorPagesAreSummarised(t *testing.T) {
	page := "<!DOCTYPE html><html><head><title>Error</title></head>" +
		strings.Repeat("<div>padding</div>", 400) + "</html>"

	got := FormatError(appwriteError(404, page))

	if strings.Contains(got, "<div>") {
		t.Errorf("printed the page body:\n%s", got)
	}
	if !strings.Contains(got, "404") {
		t.Errorf("summary %q does not name the status", got)
	}
	if !strings.Contains(got, "HTML") {
		t.Errorf("summary %q does not say what came back", got)
	}
	if !strings.Contains(got, "--verbose") {
		t.Errorf("summary %q does not say how to see the body", got)
	}
}

// The API's own messages are the useful case and must survive untouched.
func TestAPIMessagesArePrintedAsIs(t *testing.T) {
	message := "Invalid `userId` param: UID must contain at most 36 chars."

	if got := FormatError(appwriteError(400, message)); got != message {
		t.Errorf("FormatError() = %q, want the message unchanged", got)
	}
}

// Errors that are not from the API are printed as they are.
func TestPlainErrorsArePrintedAsIs(t *testing.T) {
	if got := FormatError(errors.New("project is not set")); got != "project is not set" {
		t.Errorf("FormatError() = %q, want the error unchanged", got)
	}
}

// Not an error, nothing to print. Guards the nil path rather than panicking on
// a command that returns a typed nil.
func TestNoErrorFormatsToNothing(t *testing.T) {
	if got := FormatError(nil); got != "" {
		t.Errorf("FormatError(nil) = %q, want empty", got)
	}
}

// A long message that is not markup is still not a message. The threshold
// catches a plain-text error page too.
func TestOverlongMessagesAreSummarised(t *testing.T) {
	got := FormatError(appwriteError(500, strings.Repeat("x", maximumMessageLength+1)))

	if strings.Contains(got, strings.Repeat("x", 50)) {
		t.Errorf("printed the body:\n%s", got)
	}
	if !strings.Contains(got, "long text") {
		t.Errorf("summary %q does not describe the body", got)
	}
}

// A message right at the limit is a message, not a document. Guards the
// boundary so a legitimately detailed API error is not swallowed.
func TestMessagesAtTheLimitArePrinted(t *testing.T) {
	message := strings.Repeat("x", maximumMessageLength)

	if got := FormatError(appwriteError(400, message)); got != message {
		t.Errorf("a %d-character message was summarised", len(message))
	}
}

// stubAPIError stands in for the SDK's AppwriteError, whose fields are
// unexported.
type stubAPIError struct {
	status  int
	message string
}

func (e *stubAPIError) Error() string      { return e.message }
func (e *stubAPIError) GetMessage() string { return e.message }
func (e *stubAPIError) GetStatusCode() int { return e.status }

func appwriteError(status int, message string) error {
	return &stubAPIError{status: status, message: message}
}

// Both CLIs' error output tells the user to "pass the --verbose or --report
// flag". In Go the second half of that sentence was a lie -- the flag did not
// exist -- so an error offered help it could not give.
func TestReportURLCarriesTheFailure(t *testing.T) {
	link := ReportURL(appwriteError(404, "user_not_found"))

	parsed, err := url.Parse(link)
	if err != nil {
		t.Fatalf("not a URL: %v", err)
	}
	if parsed.Host != "github.com" {
		t.Errorf("host = %q, want github.com", parsed.Host)
	}

	query := parsed.Query()
	for field, want := range map[string]string{
		"labels":   "bug",
		"template": "bug.yaml",
	} {
		if query.Get(field) != want {
			t.Errorf("%s = %q, want %q", field, query.Get(field), want)
		}
	}
	if !strings.Contains(query.Get("title"), "user_not_found") {
		t.Errorf("title %q does not name the failure", query.Get("title"))
	}
	if !strings.Contains(query.Get("actual-behavior"), "user_not_found") {
		t.Errorf("body %q does not carry the error", query.Get("actual-behavior"))
	}
	if query.Get("environment") == "" {
		t.Error("no environment reported")
	}
}

// An 8,000-byte error page must not be pasted whole into a URL: GitHub rejects
// the request and the user gets nothing.
func TestReportURLIsBounded(t *testing.T) {
	page := "<!DOCTYPE html>" + strings.Repeat("<div>padding</div>", 2000)

	link := ReportURL(appwriteError(502, page))

	if len(link) > 8000 {
		t.Errorf("report URL is %d bytes, too long to open", len(link))
	}
	if strings.Contains(link, "padding") {
		t.Error("the page body was pasted into the URL")
	}
}

// Nothing failed, nothing to report.
func TestReportURLIsEmptyWithoutAnError(t *testing.T) {
	if got := ReportURL(nil); got != "" {
		t.Errorf("ReportURL(nil) = %q, want empty", got)
	}
}

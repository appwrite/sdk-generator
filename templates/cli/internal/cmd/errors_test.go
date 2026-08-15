package cmd

import (
	"bytes"
	"errors"
	"fmt"
	"net/url"
	"os"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
)

// requestWasMade makes a failure look like it came from the API, or like it did
// not, and restores whichever it was.
func requestWasMade(t *testing.T, made bool) {
	t.Helper()

	restore := sdk.SetRequestWasMade(made)
	t.Cleanup(func() { sdk.SetRequestWasMade(restore) })
}

// A proxy 502 or a maintenance page is an HTML document, and the SDK puts a
// non-JSON body straight into the error message. All 8,946 bytes of it reached
// the terminal where one line would do.
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

// CLI releases' error output tells the user to "pass the --verbose or --report
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

// A cancelled prompt used to reach the same path as a failed request: the bare
// error string "prompt cancelled" on stderr, under the same advice about
// --verbose and --report. Nothing had gone wrong -- the user had pressed
// Ctrl-C -- so the CLI was suggesting they file a bug about their own decision.
func TestCancellationIsToldApartFromAFailure(t *testing.T) {
	if !IsCancelled(fmt.Errorf("switching accounts: %w", prompt.ErrAborted)) {
		t.Error("a wrapped ErrAborted is not recognised as a cancellation")
	}
	if IsCancelled(errors.New("network unreachable")) {
		t.Error("an ordinary error is being treated as a cancellation")
	}
	if IsCancelled(appwriteError(500, "internal error")) {
		t.Error("an API error is being treated as a cancellation")
	}
}

// 130 is 128 + SIGINT: what a shell reports for an interrupted program, and
// distinct from the 1 a real failure exits with.
func TestCancellationExitsWithTheInterruptStatus(t *testing.T) {
	if ExitCancelled != 130 {
		t.Errorf("ExitCancelled = %d, want 130", ExitCancelled)
	}
}

// The notice names the command but must never echo the arguments: `client
// --key <secret>` is a prompt away from printing an API key into whatever is
// capturing stderr.
func TestCancellationNoticeNamesTheCommandWithoutItsArguments(t *testing.T) {
	root := NewRootCommand()
	login := resolveCommand(root, "login")
	if login == nil {
		t.Fatal("`login` is missing")
	}

	notice := CancellationNotice(login)

	if !strings.Contains(notice, "appwrite login") {
		t.Errorf("notice %q does not name the command", notice)
	}
	if !strings.Contains(notice, "Cancelled") {
		t.Errorf("notice %q does not say it was cancelled", notice)
	}

	// Nothing from the invocation itself.
	for _, argument := range os.Args[1:] {
		if argument != "" && strings.Contains(notice, argument) {
			t.Errorf("notice %q echoes the argument %q", notice, argument)
		}
	}

	if fallback := CancellationNotice(nil); !strings.Contains(fallback, "Cancelled") {
		t.Errorf("notice for an unknown command = %q", fallback)
	}
}

// The whole point of Report is that these two outcomes do not look alike.
func TestReportDistinguishesCancellationFromFailure(t *testing.T) {
	// `network unreachable` below is a request that never completed, which is
	// the case --verbose still has something to say about.
	requestWasMade(t, true)

	root := NewRootCommand()
	login := resolveCommand(root, "login")

	cancelled := &bytes.Buffer{}
	if status := Report(cancelled, login, prompt.ErrAborted); status != ExitCancelled {
		t.Errorf("cancellation exited %d, want %d", status, ExitCancelled)
	}
	// Nothing that reads as a diagnostic: no advice about flags, no "Error",
	// and no "Warning" either -- the user cancelled on purpose.
	for _, unwanted := range []string{"--verbose", "--report", "Error", "Warning", "Info"} {
		if strings.Contains(cancelled.String(), unwanted) {
			t.Errorf("a cancelled prompt reads as a problem (%q):\n%s",
				unwanted, cancelled.String())
		}
	}
	if !strings.Contains(cancelled.String(), "Cancelled") {
		t.Errorf("cancellation notice missing:\n%s", cancelled.String())
	}

	failed := &bytes.Buffer{}
	if status := Report(failed, login, errors.New("network unreachable")); status != 1 {
		t.Errorf("failure exited %d, want 1", status)
	}
	got := failed.String()
	if !strings.Contains(got, "✗ Error:") {
		t.Errorf("a failure is missing the error prefix:\n%s", got)
	}
	if !strings.Contains(got, "network unreachable") {
		t.Errorf("a failure does not say what went wrong:\n%s", got)
	}
	if !strings.Contains(got, "--verbose") {
		t.Errorf("a failure does not point at --verbose:\n%s", got)
	}

	if status := Report(&bytes.Buffer{}, login, nil); status != 0 {
		t.Errorf("no error exited %d, want 0", status)
	}
}

// A command that fails DURING parsing never reaches the later flags, so the
// parsed globals cannot answer "did the user ask for detail". `users get
// --bogus --verbose` stopped at --bogus and then advised passing --verbose.
func TestDetailIsRecognisedFromTheArgumentsWhenParsingFailed(t *testing.T) {
	cases := map[string]bool{
		"users get --bogus --verbose": true,
		"users get --bogus --report":  true,
		"users get --bogus -V":        true,
		"users get --bogus -jV":       true,
		"users get --bogus":           false,
		"users get --bogus -j":        false,
		// After `--` it is a value, not a flag.
		"users get -- --verbose": false,
		"users get -":            false,
	}

	for invocation, want := range cases {
		restore := os.Args
		os.Args = append([]string{"appwrite"}, strings.Fields(invocation)...)

		got := detailWasRequested()
		os.Args = restore

		if got != want {
			t.Errorf("detailWasRequested() = %v for `appwrite %s`, want %v",
				got, invocation, want)
		}
	}
}

// And the hint really does disappear from the rendered output.
func TestTheHintIsNotPrintedWhenTheUserAlreadyAskedForDetail(t *testing.T) {
	requestWasMade(t, true)

	restore := os.Args
	os.Args = []string{"appwrite", "users", "get", "--bogus", "--verbose"}
	t.Cleanup(func() { os.Args = restore })

	buffer := &bytes.Buffer{}
	Report(buffer, nil, errors.New("unknown flag: --bogus"))

	if strings.Contains(buffer.String(), "For detailed error") {
		t.Errorf("advised passing a flag that is already in the invocation:\n%s", buffer.String())
	}
}

// The advice is only true of a failed request: a command that never sends one
// has no status, response or chain to print, so the offer of detail was empty.
func TestTheHintIsNotPrintedWhenNothingWasSent(t *testing.T) {
	requestWasMade(t, false)

	restore := os.Args
	os.Args = []string{"appwrite", "types", ".", "-l", "typescript"}
	t.Cleanup(func() { os.Args = restore })

	buffer := &bytes.Buffer{}
	Report(buffer, nil, errors.New("language 'typescript' is not supported"))

	if strings.Contains(buffer.String(), "For detailed error") {
		t.Errorf("offered detail for a failure with none:\n%s", buffer.String())
	}
	if !strings.Contains(buffer.String(), "is not supported") {
		t.Errorf("lost the error itself:\n%s", buffer.String())
	}
}

func TestTheHintIsPrintedForAFailedRequest(t *testing.T) {
	requestWasMade(t, true)

	restore := os.Args
	os.Args = []string{"appwrite", "users", "get", "--user-id", "missing"}
	t.Cleanup(func() { os.Args = restore })

	buffer := &bytes.Buffer{}
	Report(buffer, nil, appwriteError(404, "User with the requested ID could not be found."))

	if !strings.Contains(buffer.String(), "For detailed error") {
		t.Errorf("withheld the advice from the failure it is for:\n%s", buffer.String())
	}
}

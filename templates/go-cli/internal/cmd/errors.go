package cmd

import (
	"errors"
	"fmt"
	"net/url"
	"os"
	"runtime"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/app"
)

// apiError is what the SDK's AppwriteError provides.
//
// Matched as an interface rather than as *client.AppwriteError because the
// SDK's fields are unexported, so nothing outside that package can build one --
// including a test for this function.
type apiError interface {
	error
	GetMessage() string
	GetStatusCode() int
}

// When a response is not JSON the SDK puts the whole body in the error message,
// and the CLI printed it. A proxy 502, a WAF block or a maintenance page is an
// HTML document, so `users get` answered with 8,946 bytes of markup where the
// TypeScript printed one line.
//
// The body is still available -- it is what --verbose is for -- but it is not
// the first thing a user should see.

// maximumMessageLength is where a "message" stops being one.
//
// Comfortably longer than any message the API sends and far shorter than a
// rendered error page.
const maximumMessageLength = 400

// FormatError renders a command failure for the terminal.
func FormatError(err error) string {
	if err == nil {
		return ""
	}

	var appwrite apiError
	if !errors.As(err, &appwrite) {
		return err.Error()
	}

	message := appwrite.GetMessage()
	if !isDocument(message) {
		return message
	}

	kind := documentKind(message)
	article := "a"
	if strings.ContainsRune("AEIOU", rune(kind[0])) {
		article = "an"
	}

	summary := fmt.Sprintf(
		"the server returned %d with %s %s response instead of JSON",
		appwrite.GetStatusCode(), article, kind)

	if app.Flags().Verbose {
		return summary + "\n\n" + message
	}

	return summary + ". Re-run with --verbose to see it"
}

// isDocument reports whether a message is really a response body.
func isDocument(message string) bool {
	trimmed := strings.TrimSpace(message)

	return strings.HasPrefix(trimmed, "<") || len(trimmed) > maximumMessageLength
}

// documentKind names the body in the summary, so the line says something
// useful about what came back.
func documentKind(message string) string {
	lowered := strings.ToLower(strings.TrimSpace(message))
	if strings.HasPrefix(lowered, "<!doctype html") || strings.HasPrefix(lowered, "<html") {
		return "HTML"
	}
	if strings.HasPrefix(lowered, "<") {
		return "markup"
	}

	return "long text"
}

// maximumReportTitleLength keeps a summarised message from dominating the
// issue title. Ports MAX_REPORT_TITLE_LENGTH.
const maximumReportTitleLength = 100

// ReportURL builds a prefilled GitHub issue for a failed command.
//
// Ports the --report branch of parseError (parser.ts:878). Both CLIs tell the
// user to "pass the --verbose or --report flag" on every error; in Go the
// second half of that sentence was a lie, because the flag did not exist.
func ReportURL(err error) string {
	if err == nil {
		return ""
	}

	message := FormatError(err)

	environment := fmt.Sprintf("CLI version: %s\nOperation System: %s\nArchitecture: %s",
		app.Version, runtime.GOOS, runtime.GOARCH)

	query := url.Values{}
	query.Set("labels", "bug")
	query.Set("template", "bug.yaml")
	query.Set("title", "🐛 Bug Report: "+truncate(message, maximumReportTitleLength))
	query.Set("actual-behavior", "CLI Error:\n```\n"+truncate(message, 2000)+"\n```")
	query.Set("steps-to-reproduce",
		"Running `"+app.ExecutableName+" "+strings.Join(commandArguments(), " ")+"`")
	query.Set("environment", environment)

	return "https://github.com/appwrite/appwrite/issues/new?" + query.Encode()
}

// ReportBlock is what the CLI prints under an error when --report is given.
func ReportBlock(err error) string {
	return "To report this error you can:\n" +
		" - Create a support ticket in our Discord server https://appwrite.io/discord\n" +
		" - Create an issue in our Github\n   " + ReportURL(err)
}

// commandArguments is the invocation, minus the flag that asked for the report.
func commandArguments() []string {
	arguments := make([]string, 0, len(os.Args))
	for _, argument := range os.Args[1:] {
		if argument == "--report" {
			continue
		}
		arguments = append(arguments, argument)
	}

	return arguments
}

// truncate shortens text to a limit, on a rune boundary.
func truncate(text string, limit int) string {
	runes := []rune(text)
	if len(runes) <= limit {
		return text
	}

	return string(runes[:limit]) + "..."
}

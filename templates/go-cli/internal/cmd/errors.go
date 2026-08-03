package cmd

import (
	"errors"
	"fmt"
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

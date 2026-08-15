package cmd

import (
	"bytes"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

func TestScreenshotPathAsksForAPngAtThePreviewSize(t *testing.T) {
	want := "/storage/buckets/screenshots/files/abc123/preview?width=480&height=270&output=png"

	if got := screenshotPath("abc123"); got != want {
		t.Errorf("screenshotPath = %q, want %q", got, want)
	}
}

// A file id comes from the API, and a path is built from it. Escaping it is the
// cheap half of not trusting that.
func TestScreenshotPathEscapesTheFileID(t *testing.T) {
	if got := screenshotPath("a/b"); strings.Contains(got, "a/b") {
		t.Errorf("screenshotPath did not escape the id: %q", got)
	}
}

// The deployment is live whether or not its picture is. Saying so is the
// difference between a user waiting for something and a user who knows the
// deploy finished.
func TestReportScreenshotSaysWhyThereIsNoPicture(t *testing.T) {
	var out bytes.Buffer
	context := &pushContext{}

	context.reportScreenshot(&out, jsonx.NewObject())

	if !strings.Contains(out.String(), "screenshot generation is still finalizing") {
		t.Errorf("the pending hint is missing:\n%s", out.String())
	}
}

// Off a terminal there is no picture to draw, so there must be no heading
// either -- and no request for a screenshot that nothing will display. The test
// binary's stdout is never a terminal, which is what makes this the path taken
// here.
func TestReportScreenshotDrawsNothingOffATerminal(t *testing.T) {
	var out bytes.Buffer
	deployment := jsonx.NewObject()
	deployment.Set("screenshotDark", "abc123")

	// A nil api would panic if this reached the fetch, which is the assertion
	// that matters more than the empty buffer.
	(&pushContext{}).reportScreenshot(&out, deployment)

	if out.String() != "" {
		t.Errorf("wrote something with no terminal to write it to:\n%s", out.String())
	}
}

func TestHasScreenshots(t *testing.T) {
	cases := []struct {
		key   string
		value string
		want  bool
	}{
		{"screenshotDark", "abc", true},
		{"screenshotLight", "abc", true},
		// Whitespace is not a file id. The API has answered with one.
		{"screenshotLight", "   ", false},
		{"screenshotLight", "", false},
		{"buildLogs", "abc", false},
	}

	for _, testCase := range cases {
		deployment := jsonx.NewObject()
		deployment.Set(testCase.key, testCase.value)

		if got := hasScreenshots(deployment); got != testCase.want {
			t.Errorf("hasScreenshots(%s=%q) = %t, want %t",
				testCase.key, testCase.value, got, testCase.want)
		}
	}
}

// Eight sites past an unreachable console should say so once.
func TestWarnOnceRepeatsNothing(t *testing.T) {
	var out bytes.Buffer
	context := &pushContext{}

	context.warnOnce(&out, "Screenshot preview unavailable: nope")
	context.warnOnce(&out, "Screenshot preview unavailable: nope")
	context.warnOnce(&out, "Screenshot preview unavailable: something else")

	if got := strings.Count(out.String(), "nope"); got != 1 {
		t.Errorf("the same warning was printed %d times", got)
	}
	if !strings.Contains(out.String(), "something else") {
		t.Errorf("a different warning was suppressed:\n%s", out.String())
	}
}

package cmd

import (
	"fmt"
	"io"
	"net/url"
	"os"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/preview"
	"golang.org/x/term"
)

// Screenshot previews for a deployed site.
//
// Ports the preview half of Push.pushSites: renderSiteTerminalPreview
// (push.ts:420), fetchSiteScreenshotPreview (:356) and the block at :2755 that
// prints them under the deployment summary.
//
// Why the CLI shows a picture at all: a site deploy succeeds whenever the build
// does, and a build succeeds for a page that renders blank, renders an error, or
// renders the previous version because an asset path was wrong. The deployment
// page has the screenshot on it, and the point of pushing from a terminal is not
// to open a browser to find that out.

// screenshots is the state a push carries for its previews.
//
// The console client is built at most once per push and only when a preview is
// actually going to be drawn -- it needs a session, and a push authenticated
// with an API key has none. Warnings are deduplicated for the same reason the
// TypeScript keeps a Set of them: pushing eight sites past an unreachable
// console should say so once.
type screenshots struct {
	console  *client.Client
	setupErr error
	built    bool
	warned   map[string]bool
}

// reportScreenshot prints a site deployment's preview, or says why there is none.
//
// Ordering matches the TypeScript summary: the pending hint, then the picture,
// then the links reportDeployment prints -- so the last line on screen is
// always the one to click.
func (c *pushContext) reportScreenshot(out io.Writer, deployment *jsonx.Object) {
	if !hasScreenshots(deployment) {
		// The deployment IS live; only its picture is missing. Saying so is the
		// difference between a user waiting for something and a user who knows
		// the deploy finished.
		output.Hint(out, "Deployment is ready, but screenshot generation is still "+
			"finalizing. Open the deployment page to view it once it is available.")

		return
	}

	if !previewRenderable() {
		return
	}

	art, err := c.screenshotArt(deployment)
	if err != nil {
		c.warnOnce(out, fmt.Sprintf("Screenshot preview unavailable: %s", err))

		return
	}
	if art == "" {
		return
	}

	fmt.Fprintf(out, "\n%s\n\n%s\n\n", output.Heading("Screenshot preview"), art)
}

// previewRenderable reports whether ANSI art belongs on this stdout.
//
// Ports shouldRenderSiteTerminalPreview. --json and --raw are machine-readable
// contracts, and half a megabyte of colour escapes in the middle of one is not
// a preview -- it is a broken pipe to whatever was parsing it. A redirected
// stdout is excluded for the same reason.
func previewRenderable() bool {
	if app.Flags().JSON || app.Flags().Raw {
		return false
	}

	return term.IsTerminal(int(os.Stdout.Fd()))
}

// screenshotArt fetches and renders the first screenshot that renders.
//
// Dark before light, matching the TypeScript's candidate order: a terminal is
// dark far more often than not, so the dark screenshot is the one that sits in
// it without glaring.
func (c *pushContext) screenshotArt(deployment *jsonx.Object) (string, error) {
	console, err := c.consoleForScreenshots()
	if err != nil {
		return "", err
	}

	columns, rows := previewSize()

	var firstErr error
	for _, key := range []string{"screenshotDark", "screenshotLight"} {
		fileID := deployment.GetString(key)
		if fileID == "" {
			continue
		}

		encoded, err := console.Download(screenshotPath(fileID))
		if err == nil {
			var art string
			art, err = preview.Render(encoded, columns, rows)
			if err == nil {
				return preview.Frame(art), nil
			}
		}

		if firstErr == nil {
			firstErr = err
		}
	}

	return "", firstErr
}

// screenshotPath is the preview endpoint for one screenshot file.
func screenshotPath(fileID string) string {
	return fmt.Sprintf(
		"/storage/buckets/%s/files/%s/preview?width=%d&height=%d&output=png",
		preview.Bucket, url.PathEscape(fileID), preview.Width, preview.Height)
}

// previewSize is the cell grid the terminal has room for.
func previewSize() (int, int) {
	width, height, err := term.GetSize(int(os.Stdout.Fd()))
	if err != nil {
		// Columns and Rows substitute 80x24 for a size they cannot read, which
		// is the size a terminal that will not answer is assumed to be.
		width, height = 0, 0
	}

	return preview.Columns(width), preview.Rows(height)
}

// consoleForScreenshots builds the console client the screenshot bucket needs.
//
// The bucket belongs to the CONSOLE project, not to the project being pushed, so
// the project client that ran the deployment cannot read it -- but it does have
// the right endpoint. A screenshot lives in the region its deployment ran in,
// and the session's endpoint has had the region normalised out of it, so the
// project client's endpoint is passed through: `preserveRegion: true` in the
// TypeScript, and the difference between a preview and "The requested file could
// not be found."
//
// Built lazily and cached, failure included: a push with no session must not
// retry a login error once per site.
func (c *pushContext) consoleForScreenshots() (*client.Client, error) {
	if c.screenshots == nil {
		c.screenshots = &screenshots{}
	}

	if !c.screenshots.built {
		c.screenshots.built = true
		c.screenshots.console, _, c.screenshots.setupErr = consoleClientAt(c.api.Endpoint)
	}

	return c.screenshots.console, c.screenshots.setupErr
}

// warnOnce prints a hint the first time that exact message comes up.
func (c *pushContext) warnOnce(out io.Writer, message string) {
	if c.screenshots == nil {
		c.screenshots = &screenshots{}
	}
	if c.screenshots.warned == nil {
		c.screenshots.warned = map[string]bool{}
	}
	if c.screenshots.warned[message] {
		return
	}

	c.screenshots.warned[message] = true
	output.Hint(out, "%s", message)
}

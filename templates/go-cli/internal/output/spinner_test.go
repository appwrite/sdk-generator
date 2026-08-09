package output

import (
	"bytes"
	"strings"
	"testing"

	"github.com/charmbracelet/lipgloss"
)

// The middle column is fixed width so the trailing note does not jitter left
// and right as the resource name changes.
func TestSpinnerLinePadsTheMiddleColumn(t *testing.T) {
	line := spinnerLine("⠋", "Deploying   ", "api (abc)", "Status: building", spinnerTrailingStyle, 120)

	if !strings.Contains(line, "api (abc)"+strings.Repeat(" ", middleWidth-len("api (abc)"))) {
		t.Errorf("line = %q, want the middle column padded to %d", line, middleWidth)
	}
}

// A long name must not push the status off the right edge and wrap the line,
// which on a redrawn line leaves the previous frame's tail on screen.
func TestSpinnerLineTruncatesALongMiddleColumn(t *testing.T) {
	long := strings.Repeat("a", 80)
	line := spinnerLine("⠋", "Deploying   ", long, "Status: building", spinnerTrailingStyle, 120)

	if strings.Contains(line, long) {
		t.Error("the full name survived, so the line will wrap")
	}
	if !strings.Contains(line, "…") {
		t.Errorf("line = %q, want it marked as truncated", line)
	}
}

// A narrow terminal still gets a line rather than a wrapped mess.
func TestSpinnerLineSurvivesANarrowTerminal(t *testing.T) {
	line := spinnerLine("⠋", "Deploying   ", "some-function (abc123)", "waiting", spinnerTrailingStyle, 20)

	if strings.Contains(line, "\n") {
		t.Error("the line broke across rows")
	}
}

// Nothing trails a line with no note -- an empty end would leave a dangling
// separator.
func TestSpinnerLineOmitsAnEmptyTrailer(t *testing.T) {
	line := spinnerLine("✓", "Deployed    ", "api (abc)", "", spinnerTrailingStyle, 120)

	if strings.HasSuffix(strings.TrimSpace(line), separator) {
		t.Errorf("line = %q, want no dangling separator", line)
	}
}

// Off a terminal the spinner must not animate: a progress line written to a
// pipe or a CI log is a smear of escape codes. It falls back to the plain
// status lines this printed before there was a spinner.
func TestSpinnerWritesPlainLinesOffATerminal(t *testing.T) {
	buffer := &bytes.Buffer{}
	spinner := NewSpinner(buffer, SpinnerState{
		Status: "Deploying", Resource: "api", ID: "abc",
	})

	spinner.Update("Deploying", "Status: building")
	spinner.Succeed("Deployed", "")

	output := buffer.String()
	if strings.Contains(output, "\033[") || strings.Contains(output, "\r") {
		t.Errorf("output carries terminal control codes: %q", output)
	}
	if !strings.Contains(output, "Status: building") {
		t.Errorf("output = %q, want the status transition", output)
	}
}

// Two seconds apart for ten minutes, the poll mostly reports what it reported
// last time. On a terminal that is a still frame; in a log file it would be
// three hundred identical lines.
func TestSpinnerOffATerminalSkipsRepeatedStatuses(t *testing.T) {
	buffer := &bytes.Buffer{}
	spinner := NewSpinner(buffer, SpinnerState{
		Status: "Deploying", Resource: "api", ID: "abc",
	})

	spinner.Update("Deploying", "Status: building")
	before := buffer.Len()
	spinner.Update("Deploying", "Status: building")

	if buffer.Len() != before {
		t.Errorf("an unchanged status printed again: %q", buffer.String())
	}
}

// Build logs go through the spinner so they land above the status row. Off a
// terminal that is just the line.
func TestSpinnerLogsALineVerbatim(t *testing.T) {
	buffer := &bytes.Buffer{}
	spinner := NewSpinner(buffer, SpinnerState{Resource: "api", ID: "abc"})
	buffer.Reset()

	spinner.Log("npm install")

	if got, want := buffer.String(), "npm install\n"; got != want {
		t.Errorf("logged %q, want %q", got, want)
	}
}

// clear() erases only the row the cursor is on, so a line that reaches the
// terminal's width wraps and leaves its remainder on screen as debris under
// the spinner. The line must always fit.
func TestSpinnerLineNeverReachesTheTerminalWidth(t *testing.T) {
	long := strings.Repeat("very-long-resource-name-", 8)

	for _, width := range []int{10, 20, 40, 80, 120} {
		line := spinnerLine("⠋", Pad("building", statusWidth),
			long+" ("+long+")", "Checking deployment status...",
			spinnerTrailingStyle, width)

		if got := lipgloss.Width(line); got >= width {
			t.Errorf("width %d: line is %d columns, so it wraps:\n%s", width, got, line)
		}
	}
}

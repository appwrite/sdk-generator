package output

import (
	"bytes"
	"strings"
	"testing"
)

// The build log arrives as a growing string. Each read must print only the part
// that is new, or a build that logs twenty lines prints them twenty times over.
func TestBuildLogPrintsOnlyWhatIsNew(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("cloning\n")
	printer.Ingest("cloning\ninstalling\n")
	printer.Ingest("cloning\ninstalling\nbuilding\n")

	if got, want := body(buffer), "cloning\ninstalling\nbuilding\n"; got != want {
		t.Errorf("logs =\n%q\nwant\n%q", got, want)
	}
	if count := strings.Count(buffer.String(), "Build logs"); count != 1 {
		t.Errorf("heading printed %d times, want once", count)
	}
}

// A read can land mid-line. Printing the half that arrived and the rest on the
// next read would split one log line across two lines of terminal.
func TestBuildLogHoldsBackAPartialLine(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("done\ninstalling depend")
	if got, want := body(buffer), "done\n"; got != want {
		t.Errorf("logs = %q, want the complete line only", got)
	}

	printer.Ingest("done\ninstalling dependencies\n")
	if got, want := body(buffer), "done\ninstalling dependencies\n"; got != want {
		t.Errorf("logs = %q, want the line once it completed", got)
	}
}

// There is no next read after the deployment ends, so the line being held back
// is all of it there will ever be.
func TestBuildLogCompleteFlushesThePartialLine(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("built in 3s\nno newline at the end")
	printer.Complete()

	// Complete also closes the block, hence the trailing blank line.
	if got, want := body(buffer), "built in 3s\nno newline at the end\n\n"; got != want {
		t.Errorf("logs = %q, want the trailing fragment flushed", got)
	}
}

// Polling every two seconds through a ten-minute build means most reads carry
// nothing new. None of them may print anything -- not even the heading.
func TestBuildLogStaysQuietWithoutProgress(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("")
	printer.Complete()
	if buffer.Len() != 0 {
		t.Fatalf("a deployment that logged nothing printed %q", buffer.String())
	}

	printer.Ingest("compiling\n")
	before := buffer.String()
	printer.Ingest("compiling\n")

	if buffer.String() != before {
		t.Errorf("an unchanged log printed again: %q", buffer.String())
	}
}

// A log can come back SHORTER than it was -- a rebuild restarts it, or a read
// lands on a truncated copy. Nothing is printed for the shrink itself, but the
// bookmark rewinds with it, so text between the new end and the old one prints
// a second time as the log regrows.
//
// That is the TypeScript's behaviour (deployment.ts:212) and it is pinned here
// rather than improved on: repeating a line is cheap, and the alternative --
// keeping the bookmark ahead of the log -- silently swallows a genuine rebuild.
func TestBuildLogRewindsWhenTheLogShrinks(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("step one\nstep two\n")
	buffer.Reset()

	printer.Ingest("step one\n")
	if buffer.Len() != 0 {
		t.Errorf("a shrinking log printed %q, want nothing", buffer.String())
	}

	printer.Ingest("step one\nstep two\nstep three\n")
	if got, want := buffer.String(), "step two\nstep three\n"; got != want {
		t.Errorf("logs = %q, want everything past the rewound bookmark", got)
	}
}

// A log that is not an extension of what was printed did not grow, it was
// REPLACED. There is no shared prefix to skip, so all of it is new.
func TestBuildLogReprintsALogThatWasReplaced(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("first attempt\n")
	buffer.Reset()

	printer.Ingest("retrying\nsecond attempt\n")
	if got, want := buffer.String(), "retrying\nsecond attempt\n"; got != want {
		t.Errorf("logs = %q, want the replacement in full", got)
	}
}

// Two deployments pushed together interleave their logs, and an unlabelled line
// belongs to either. One deployment needs no label and the width is better
// spent on the log.
func TestBuildLogLabelsLinesOnlyWhenAsked(t *testing.T) {
	labelled := &bytes.Buffer{}
	NewBuildLogPrinter(lines(labelled), "function:api", true).Ingest("compiling\n")
	if got, want := body(labelled), "[function:api] compiling\n"; got != want {
		t.Errorf("logs = %q, want the label", got)
	}

	plain := &bytes.Buffer{}
	NewBuildLogPrinter(lines(plain), "function:api", false).Ingest("compiling\n")
	if got, want := body(plain), "compiling\n"; got != want {
		t.Errorf("logs = %q, want no label", got)
	}
}

// Build logs come from containers that may write CRLF. Left alone, the carriage
// return returns the cursor to the start of the line and the next line
// overwrites it.
func TestBuildLogNormalisesCarriageReturns(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("first\r\nsecond\r\n")

	if got, want := body(buffer), "first\nsecond\n"; got != want {
		t.Errorf("logs = %q, want the carriage returns dropped", got)
	}
}

// The block closes the way it opened. Without the trailing blank line the
// deployment's verdict butts against the last line of build output and reads
// as part of it.
func TestBuildLogClosesTheBlockWithABlankLine(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Ingest("Build finished.\n")
	printer.Complete()

	if got, want := body(buffer), "Build finished.\n\n"; got != want {
		t.Errorf("logs = %q, want a blank line after the last one", got)
	}

	// Complete is reached from several exits; the spacing must not accumulate.
	printer.Complete()
	if got, want := body(buffer), "Build finished.\n\n"; got != want {
		t.Errorf("logs = %q after a second Complete, want one blank line", got)
	}
}

// A deployment that logged nothing has no block to close.
func TestBuildLogAddsNoBlankLineWithoutLogs(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "function:api", false)

	printer.Complete()

	if buffer.Len() != 0 {
		t.Errorf("printed %q for a deployment with no logs", buffer.String())
	}
}

// body drops the heading so a test can assert on the log text alone.
func body(buffer *bytes.Buffer) string {
	_, text, found := strings.Cut(buffer.String(), "Build logs\n\n")
	if !found {
		return buffer.String()
	}

	return text
}

// lines is the sink a test reads back through. Production always prints via
// the spinner, so this lives here rather than in the package.
func lines(buffer *bytes.Buffer) func(string) {
	return func(line string) {
		buffer.WriteString(line + "\n")
	}
}

// Build tools redraw progress in place with a bare carriage return -- `vite
// build`, npm and docker all do. Passing one through moves the terminal cursor
// to column zero, so the rest of the line overwrites whatever was already
// there. That is how a half-erased progress line ended up superimposed on the
// spinner's status row.
func TestBuildLogCollapsesCarriageReturnProgress(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "site:web", false)

	printer.Ingest("transforming (1) index.js\rtransforming (57) app.js\rdone in 3s\n")

	got := body(buffer)
	if strings.Contains(got, "\r") {
		t.Errorf("a carriage return reached the terminal: %q", got)
	}
	if got != "done in 3s\n" {
		t.Errorf("logs = %q, want only the final state of the progress line", got)
	}
}

// A line with no carriage return is untouched, and the CRLF normalisation that
// was already there still applies.
func TestBuildLogLeavesOrdinaryLinesAlone(t *testing.T) {
	buffer := &bytes.Buffer{}
	printer := NewBuildLogPrinter(lines(buffer), "site:web", false)

	printer.Ingest("building\r\ncompiled\nready\n")

	if got, want := body(buffer), "building\ncompiled\nready\n"; got != want {
		t.Errorf("logs = %q, want %q", got, want)
	}
}

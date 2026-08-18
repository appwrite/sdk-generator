package output

import (
	"fmt"
	"io"
	"sync"

	"github.com/charmbracelet/lipgloss"
)

// Status lines, not data. Like the rest of the human-readable output these are
// explicitly outside the parity contract -- but the prefixes
// are reproduced verbatim, because they are what a user scanning a terminal
// recognises and what a script grepping stderr may match on.
var (
	infoStyle    = lipgloss.NewStyle().Foreground(lipgloss.Color("6"))
	warningStyle = lipgloss.NewStyle().Foreground(lipgloss.Color("3"))
	successStyle = lipgloss.NewStyle().Foreground(lipgloss.Color("2"))
	failureStyle = lipgloss.NewStyle().Foreground(lipgloss.Color("1"))
	traceStyle   = lipgloss.NewStyle().Foreground(lipgloss.Color("8"))
)

// synchronizedWriter serializes whole writes to an underlying writer.
type synchronizedWriter struct {
	writer io.Writer
	mutex  sync.Mutex
}

func (w *synchronizedWriter) Write(contents []byte) (int, error) {
	w.mutex.Lock()
	defer w.mutex.Unlock()

	return w.writer.Write(contents)
}

// Synchronized returns a writer safe for concurrent status and build-log
// lines. It intentionally hides whether the underlying writer is a terminal:
// independent one-row spinners would otherwise redraw over each other, so a
// parallel push falls back to plain progress lines.
func Synchronized(writer io.Writer) io.Writer {
	if _, ok := writer.(*synchronizedWriter); ok {
		return writer
	}

	return &synchronizedWriter{writer: writer}
}

// Log writes an informational line.
func Log(writer io.Writer, format string, arguments ...any) {
	writeMessage(writer, infoStyle, "ℹ Info:", format, arguments...)
}

// Warn writes a warning line.
func Warn(writer io.Writer, format string, arguments ...any) {
	writeMessage(writer, warningStyle, "ℹ Warning:", format, arguments...)
}

// Hint writes a suggested next command.
//
// Distinct from Log because it is advice rather than status: its own prefix and
// colour let a user who just saw "No buckets found." tell the follow-up apart
// from the report.
func Hint(writer io.Writer, format string, arguments ...any) {
	writeMessage(writer, infoStyle, "♥ Hint:", format, arguments...)
}

// Trace writes a diagnostic line under --verbose.
//
// Faint and on stderr: it is for the person watching, not for whatever is
// reading the command's output.
func Trace(writer io.Writer, format string, arguments ...any) {
	fmt.Fprintf(writer, "%s\n", traceStyle.Render("· "+fmt.Sprintf(format, arguments...)))
}

// Success writes a success line.
func Success(writer io.Writer, format string, arguments ...any) {
	writeMessage(writer, successStyle, "✓ Success:", format, arguments...)
}

// Note writes a plain line, dimmed and with no prefix.
//
// For something the CLI is telling the user about the user's own action rather
// than about an operation. A cancelled prompt is the case: it is not
// information, not a warning and not an error, and giving it one of those
// prefixes miscategorises a deliberate decision as a problem. The user pressed
// Ctrl-C a moment ago, so the line only has to confirm what happened.
func Note(writer io.Writer, format string, arguments ...any) {
	fmt.Fprintf(writer, "%s\n", traceStyle.Render(fmt.Sprintf(format, arguments...)))
}

// Heading styles a label that introduces a block rather than a line.
//
// Cyan and bold. Returned rather than written because the caller decides the
// blank lines around it -- a heading with nothing under it is worse than no
// heading.
func Heading(text string) string {
	return infoStyle.Bold(true).Render(text)
}

// Failure writes an error line.
//
// Named Failure rather than Error so it does not read as constructing an error
// value, which is what an `Error` function conventionally does in Go.
func Failure(writer io.Writer, format string, arguments ...any) {
	writeMessage(writer, failureStyle, "✗ Error:", format, arguments...)
}

func writeMessage(writer io.Writer, style lipgloss.Style, prefix, format string, arguments ...any) {
	message := fmt.Sprintf(format, arguments...)
	fmt.Fprintf(writer, "%s %s\n", style.Bold(true).Render(prefix), style.Render(message))
}

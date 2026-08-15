package output

import (
	"strings"

	"github.com/charmbracelet/lipgloss"
)

// DeploymentLogPrinter streams deployment log chunks.
//
// A deployment's build log arrives as ONE STRING that grows: every read returns
// the whole log so far, not the part that is new. The printer's job is to turn
// that into a stream -- work out what was added since the last read and print
// only that, so a build that logs a hundred lines does not print a hundred
// growing copies of itself.
//
// PARTIAL LINES ARE HELD BACK. A read can land mid-line, and printing half a
// line then the rest on the next read would break the line in two on screen.
// Everything up to the last newline is printed and the remainder waits, except
// on Complete, where there is no next read to wait for.
var buildLogHeadingStyle = lipgloss.NewStyle().Bold(true).Foreground(lipgloss.Color("6"))

// buildLogLabelStyle tints the `[function:name]` prefix so the log text stays
// the terminal's default colour -- build output has its own colours and
// recolouring it would fight them.
var buildLogLabelStyle = lipgloss.NewStyle().Foreground(lipgloss.Color("6"))

// BuildLogPrinter streams one deployment's build log.
//
// Not safe for concurrent use. Each deployment gets its own printer, and the
// push loop drives them one at a time.
type BuildLogPrinter struct {
	// emit takes one finished line. A push with a spinner running passes the
	// spinner's Log so the line lands ABOVE the status row rather than fighting
	// it for the same one.
	emit func(string)

	// label names the deployment, shown per line when more than one is being
	// pushed and two logs would otherwise interleave unattributably.
	label      string
	showPrefix bool

	// last is the whole log as of the most recent read; printed is the part of
	// it already on screen. printed is always a prefix of last in the ordinary
	// case, and the difference is what has yet to be shown.
	last    string
	printed string

	headerPrinted bool
	footerPrinted bool
}

// NewBuildLogPrinter returns a printer for one deployment's build log.
//
// showPrefix is set when more than one deployment is being followed: with a
// single deployment the label is noise, with several it is the only way to tell
// whose line is whose.
func NewBuildLogPrinter(emit func(string), label string, showPrefix bool) *BuildLogPrinter {
	return &BuildLogPrinter{emit: emit, label: label, showPrefix: showPrefix}
}

// Ingest takes the build log as of the latest read and prints what is new.
//
// An empty or unchanged log is not progress and prints nothing -- a deployment
// polled every two seconds for ten minutes would otherwise redraw its header
// three hundred times.
func (p *BuildLogPrinter) Ingest(logs string) {
	if logs == "" || logs == p.last {
		return
	}

	p.last = logs
	p.flush(false)
}

// Complete prints whatever is left, including a trailing partial line.
//
// Called when the deployment has finished one way or another: the log will not
// grow again, so the line that was being held back is all there is.
func (p *BuildLogPrinter) Complete() {
	p.flush(true)

	// Close the block the way it opened, with a blank line. Without it the
	// deployment's verdict butts straight up against the last line of build
	// output and reads as part of it.
	if p.headerPrinted && !p.footerPrinted {
		p.emit("")
		p.footerPrinted = true
	}
}

func (p *BuildLogPrinter) flush(includePartial bool) {
	if p.last == "" || p.last == p.printed {
		return
	}

	// The log SHRANK, or was replaced by something that is not an extension of
	// what was printed. A rebuild restarts it from empty, and reprinting the
	// whole thing over output the reader has already seen is worse than
	// silently resyncing.
	if p.printed != "" && strings.HasPrefix(p.printed, p.last) {
		p.printed = p.last

		return
	}

	if p.printed != "" && !strings.HasPrefix(p.last, p.printed) {
		// Diverged rather than grew: start over from the current log.
		p.printed = ""
	}

	chunk := p.last[len(p.printed):]
	written := p.writeChunk(chunk, includePartial)
	if written == "" {
		return
	}

	p.printed += written
}

// writeChunk prints the complete lines of chunk and returns the part of it that
// was printed, so the caller can advance its bookmark by exactly that much.
func (p *BuildLogPrinter) writeChunk(chunk string, includePartial bool) string {
	printable := chunk
	if !includePartial && !strings.HasSuffix(chunk, "\n") {
		// Hold back the partial trailing line for the next read.
		printable = chunk[:strings.LastIndex(chunk, "\n")+1]
	}
	if printable == "" {
		return ""
	}

	// The header waits until there is something to put under it: a deployment
	// that never logs should not leave an empty "Build logs" heading behind.
	if !p.headerPrinted {
		p.emit("")
		p.emit(buildLogHeadingStyle.Render("Build logs"))
		p.emit("")
		p.headerPrinted = true
	}

	prefix := ""
	if p.showPrefix && p.label != "" {
		prefix = buildLogLabelStyle.Render("["+p.label+"]") + " "
	}

	body := strings.ReplaceAll(printable, "\r\n", "\n")
	lines := strings.Split(body, "\n")
	if strings.HasSuffix(body, "\n") {
		lines = lines[:len(lines)-1]
	}

	for _, line := range lines {
		p.emit(prefix + lastCarriageReturnSegment(line))
	}

	return printable
}

// lastCarriageReturnSegment keeps only what a terminal would still be showing
// after the line finished printing.
//
// Build tools redraw progress in place with a bare carriage return -- `vite
// build` does, so does npm and docker. Passing one through moves the cursor to
// column zero and the rest of the line overwrites whatever was already there,
// which is how a half-erased status line ended up superimposed on the spinner.
//
// The last segment is the progress line's final state, which is the only part
// worth keeping in a log that scrolls rather than redraws.
func lastCarriageReturnSegment(line string) string {
	if index := strings.LastIndexByte(line, '\r'); index >= 0 {
		return line[index+1:]
	}

	return line
}

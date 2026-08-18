package output

import (
	"fmt"
	"io"
	"os"
	"strings"
	"sync"
	"time"

	"github.com/charmbracelet/lipgloss"
	"github.com/charmbracelet/x/ansi"
	"golang.org/x/term"
)

// The live status line a push shows while it waits for a deployment to build.
// One row, redrawn in place, because pushdeploy.go pushes one resource at a
// time.
//
// Only on a terminal: a progress line written to a pipe is a smear of escape
// codes, so everything below degrades to Log when there is no terminal.

const (
	// spinnerInterval is the dots spinner's frame rate.
	spinnerInterval = 80 * time.Millisecond

	// statusWidth pads the status to a fixed width, which keeps the middle
	// column from shifting as the status changes.
	statusWidth = 12

	// middleWidth is DEFAULT_MIDDLE_WIDTH, the room the `name (id)` column gets
	// before it is truncated.
	middleWidth = 40

	// minMiddleWidth is MIN_MIDDLE_WIDTH.
	minMiddleWidth = 1

	separator = "•"
)

// spinnerFrames is the `dots` set.
var spinnerFrames = []string{"⠋", "⠙", "⠹", "⠸", "⠼", "⠴", "⠦", "⠧", "⠇", "⠏"}

var (
	spinnerPendingStyle   = lipgloss.NewStyle().Foreground(lipgloss.Color("6"))
	spinnerActiveStyle    = lipgloss.NewStyle().Foreground(lipgloss.Color("6")).Bold(true)
	spinnerDoneStyle      = lipgloss.NewStyle().Foreground(lipgloss.Color("2")).Bold(true)
	spinnerFailStyle      = lipgloss.NewStyle().Foreground(lipgloss.Color("1")).Bold(true)
	spinnerTrailingStyle  = lipgloss.NewStyle().Foreground(lipgloss.Color("3"))
	spinnerFailTrailStyle = lipgloss.NewStyle().Foreground(lipgloss.Color("1"))
)

// SpinnerState is one row's payload.
type SpinnerState struct {
	Status   string
	Resource string
	ID       string
	// End is the trailing note: the deployment's current status, or why it
	// failed.
	End string
}

// Spinner owns a single line of the terminal and redraws it in place.
//
// A disabled Spinner is not nil and every method still works -- it just writes
// finished lines instead of animating. That keeps the caller free of `if
// spinner != nil` at every step.
type Spinner struct {
	writer  io.Writer
	enabled bool

	mutex   sync.Mutex
	state   SpinnerState
	frame   int
	drawn   bool
	stopped bool
	// hidCursor tracks whether the terminal cursor is currently hidden.
	hidCursor bool

	done chan struct{}
	wait sync.WaitGroup
}

// NewSpinner returns a Spinner that animates only when writer is a terminal.
func NewSpinner(writer io.Writer, state SpinnerState) *Spinner {
	spinner := &Spinner{
		writer:  writer,
		enabled: isTerminal(writer),
		state:   state,
		done:    make(chan struct{}),
	}

	if !spinner.enabled {
		// Same line the port printed before there was a spinner, so a piped
		// run's output is unchanged.
		Log(writer, "%s ( %s ) %s", state.Resource, state.ID, state.Status)

		return spinner
	}

	spinner.draw()
	spinner.wait.Add(1)
	go spinner.animate()

	return spinner
}

// Update changes the trailing note and status.
//
// Off a terminal only a CHANGED line is printed. The deployment is polled every
// two seconds and mostly reports the same status, which on a terminal is a
// still frame and in a log file would be hundreds of identical lines.
func (s *Spinner) Update(status, end string) {
	s.mutex.Lock()
	defer s.mutex.Unlock()

	if s.stopped || (status == s.state.Status && end == s.state.End) {
		return
	}

	s.state.Status = status
	s.state.End = end

	if !s.enabled {
		Log(s.writer, "%s ( %s ) %s", s.state.Resource, s.state.ID, end)

		return
	}

	s.draw()
}

// Log prints a line ABOVE the spinner, which then redraws below it.
//
// Without this a build log line and the
// status line would fight over the same row.
func (s *Spinner) Log(line string) {
	s.mutex.Lock()
	defer s.mutex.Unlock()

	s.clear()
	fmt.Fprintln(s.writer, line)
	s.draw()
}

// Succeed settles the line as done and stops animating.
func (s *Spinner) Succeed(status, end string) {
	s.finish(status, end, spinnerDoneStyle, "✓")
}

// Fail settles the line as failed.
func (s *Spinner) Fail(status, reason string) {
	s.finish(status, reason, spinnerFailStyle, "✗")
}

// Stop clears the line without settling it, for a push that ends without a
// verdict of its own -- the caller is about to print the real outcome.
func (s *Spinner) Stop() {
	s.mutex.Lock()
	if s.stopped {
		s.mutex.Unlock()

		return
	}
	s.stopped = true
	s.clear()
	s.mutex.Unlock()

	s.halt()
}

func (s *Spinner) finish(status, end string, style lipgloss.Style, mark string) {
	s.mutex.Lock()
	if s.stopped {
		s.mutex.Unlock()

		return
	}
	s.stopped = true
	s.state.Status = status
	s.state.End = end

	if s.enabled {
		s.clear()
		trailing := spinnerTrailingStyle
		if mark == "✗" {
			trailing = spinnerFailTrailStyle
		}
		fmt.Fprintln(s.writer, spinnerLine(
			style.Render(mark), style.Render(Pad(status, statusWidth)),
			middleColumn(s.state), end, trailing, s.width()))
	} else {
		Log(s.writer, "%s ( %s ) %s", s.state.Resource, s.state.ID, status)
	}
	s.mutex.Unlock()

	s.halt()
}

// halt stops the animation goroutine. Called with the mutex RELEASED: the
// goroutine takes the same mutex to draw, so holding it here would deadlock.
func (s *Spinner) halt() {
	if !s.enabled {
		return
	}

	close(s.done)
	s.wait.Wait()

	// Restored unconditionally: leaving a terminal without a cursor is a worse
	// failure than the one hiding it avoided.
	s.mutex.Lock()
	if s.hidCursor {
		fmt.Fprint(s.writer, "\033[?25h")
		s.hidCursor = false
	}
	s.mutex.Unlock()
}

func (s *Spinner) animate() {
	defer s.wait.Done()

	ticker := time.NewTicker(spinnerInterval)
	defer ticker.Stop()

	for {
		select {
		case <-s.done:
			return
		case <-ticker.C:
			s.mutex.Lock()
			if !s.stopped {
				s.frame++
				s.draw()
			}
			s.mutex.Unlock()
		}
	}
}

// draw repaints the line. Callers hold the mutex.
func (s *Spinner) draw() {
	if !s.enabled {
		return
	}

	// The line is redrawn in place with no newline, so the terminal cursor
	// parks at the end of it and blinks there. Hidden for the duration, and
	// restored by halt().
	if !s.drawn && !s.hidCursor {
		fmt.Fprint(s.writer, "\033[?25l")
		s.hidCursor = true
	}

	s.clear()
	mark := spinnerPendingStyle.Render(spinnerFrames[s.frame%len(spinnerFrames)])
	fmt.Fprint(s.writer, spinnerLine(
		mark, spinnerActiveStyle.Render(Pad(s.state.Status, statusWidth)),
		middleColumn(s.state), s.state.End, spinnerTrailingStyle, s.width()))
	s.drawn = true
}

// clear erases the drawn line so the cursor is back at its start. Callers hold
// the mutex.
func (s *Spinner) clear() {
	if !s.enabled || !s.drawn {
		return
	}

	// Carriage return and erase-to-end-of-line: the line is rewritten in place
	// rather than scrolled away.
	fmt.Fprint(s.writer, "\r\033[K")
	s.drawn = false
}

func (s *Spinner) width() int {
	return terminalWidth()
}

// middleColumn is the `resource (id)` column.
func middleColumn(state SpinnerState) string {
	return fmt.Sprintf("%s (%s)", state.Resource, state.ID)
}

// spinnerLine assembles one row. Kept pure -- width in, string out -- because
// it is the part worth pinning in a test.
//
// The trailing note is styled here rather than by the caller: styling it outside
// turns an absent note into a non-empty string of escape codes, and the "is
// there a note?" test then prints a separator with nothing after it.
func spinnerLine(mark, status, middle, end string, endStyle lipgloss.Style, width int) string {
	leading := lipgloss.Width(mark + " " + status + " " + separator + " ")

	trailing := 0
	if end != "" {
		trailing = lipgloss.Width(" " + separator + " " + end)
		end = endStyle.Render(end)
	}

	available := max(width-leading-trailing, minMiddleWidth)
	line := mark + " " + status + " " + separator + " " +
		fitMiddle(middle, min(middleWidth, available))

	if end != "" {
		line += " " + separator + " " + end
	}

	// A line that reaches the terminal's width wraps, and clear() erases only
	// the row the cursor is on -- so the wrapped remainder stayed on screen as
	// debris under the spinner. minMiddleWidth above is a floor that can push
	// past the width on a narrow terminal, and the trailing note is appended
	// after it, so the total is bounded here rather than assumed.
	if width > 1 && lipgloss.Width(line) >= width {
		line = ansi.Truncate(line, width-1, "")
	}

	return line
}

// fitMiddle pads or truncates the middle column to exactly target columns.
func fitMiddle(middle string, target int) string {
	if lipgloss.Width(middle) <= target {
		return Pad(middle, target)
	}
	if target <= 1 {
		return "…"
	}

	// Truncated a RUNE at a time and measured by display width, so a name with
	// wide characters in it does not overflow the column it was fitted to.
	truncated := ""
	for _, character := range middle {
		if lipgloss.Width(truncated+string(character)+"…") > target {
			break
		}
		truncated += string(character)
	}

	return truncated + "…"
}

// Pad right-pads text to width.
//
// Measured with lipgloss.Width rather than len: a styled or non-ASCII cell
// occupies a different number of columns than it does bytes, and padding by
// bytes misaligns every column after it.
func Pad(text string, width int) string {
	if gap := width - lipgloss.Width(text); gap > 0 {
		return text + strings.Repeat(" ", gap)
	}

	return text
}

// isTerminal reports whether writer is a terminal this can safely animate on.
func isTerminal(writer io.Writer) bool {
	file, ok := writer.(*os.File)
	if !ok {
		return false
	}

	return term.IsTerminal(int(file.Fd()))
}

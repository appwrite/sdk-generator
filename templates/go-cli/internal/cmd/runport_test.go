package cmd

import (
	"errors"
	"strings"
	"testing"
	"time"
)

// `run` printed one URL and nothing about how it got there, so a function that
// came up on 3001 looked like it had always been on 3001 -- leaving the reader to
// wonder whether the port had moved or something else was holding the one they
// expected.
func TestPortNoticeNamesWhatWasSkipped(t *testing.T) {
	cases := []struct {
		requested int
		chosen    int
		want      string
	}{
		// The common case: one port taken, the next one used.
		{0, portSearchStart + 1, "Port 3000 is in use, so this function is on 3001."},
		// A range reads as a range, rather than naming only the first.
		{0, portSearchStart + 3, "Ports 3000-3002 are in use, so this function is on 3003."},
		// Nothing to explain: the search settled on the port it started from.
		{0, portSearchStart, ""},
		// An explicit --port is the user's own choice, and a taken one is an
		// error rather than a substitution, so there is never a notice.
		{3000, 3000, ""},
		{4500, 4500, ""},
	}

	for _, test := range cases {
		got := portNotice(test.requested, test.chosen)
		if got != test.want {
			t.Errorf("portNotice(%d, %d) = %q, want %q",
				test.requested, test.chosen, got, test.want)
		}
	}
}

// The notice has to agree with the URL that follows it.
func TestPortNoticeAgreesWithTheChosenPort(t *testing.T) {
	notice := portNotice(0, portSearchStart+5)

	if !strings.HasSuffix(notice, "on 3005.") {
		t.Errorf("notice does not end with the port in use: %q", notice)
	}
	if strings.Contains(notice, "3005 is in use") {
		t.Errorf("notice claims the chosen port is in use: %q", notice)
	}
}

// A container that dies after startup -- a runtime panic on the first import, an
// OOM kill -- is only observable through the wait function that both Start call
// sites used to discard. Without it `run` left "Visit http://localhost:<port>/"
// on screen and waited on a dead port until the user gave up.
func TestWatchExitReportsTheContainerStopping(t *testing.T) {
	failure := errors.New("exit status 1")

	exited := watchExit(func() error { return failure })

	select {
	case err := <-exited:
		if !errors.Is(err, failure) {
			t.Errorf("reported %v, want %v", err, failure)
		}
	case <-time.After(5 * time.Second):
		t.Fatal("the container's exit was never reported")
	}
}

// No container running is not an exit to report. serve selects on this channel,
// and a nil channel blocks, which is what keeps it waiting for the next file
// change rather than reporting a crash that did not happen.
func TestWatchExitOfNothingNeverFires(t *testing.T) {
	if exited := watchExit(nil); exited != nil {
		t.Error("watchExit(nil) returned a channel, which would report a phantom exit")
	}
}

// A deliberate restart also makes wait return, and serve stops listening across
// it. The channel has to be buffered for that goroutine to finish anyway, or
// every reload would strand one for the lifetime of the command.
func TestWatchExitIsBufferedSoADiscardedWatcherFinishes(t *testing.T) {
	exited := watchExit(func() error { return nil })

	if cap(exited) < 1 {
		t.Errorf("capacity %d: a watcher nobody reads would block forever", cap(exited))
	}
}

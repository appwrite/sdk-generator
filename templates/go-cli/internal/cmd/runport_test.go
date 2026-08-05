package cmd

import (
	"strings"
	"testing"
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

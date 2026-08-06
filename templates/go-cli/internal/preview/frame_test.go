package preview

import (
	"strings"
	"testing"
	"unicode/utf8"
)

func TestFrameBoxesEveryLineToOneWidth(t *testing.T) {
	framed := Frame("ab\nabcd\nabc")

	want := strings.Join([]string{
		"+------+",
		"| ab   |",
		"| abcd |",
		"| abc  |",
		"+------+",
	}, "\n")

	// The reset is emitted after every line, so compare without it rather than
	// writing it into the expectation five times.
	if got := strings.ReplaceAll(framed, ansiReset, ""); got != want {
		t.Errorf("Frame boxed the lines wrongly:\n%s", got)
	}
}

// Colour escapes cost no columns. Measuring the raw string instead pads every
// line by the twenty-odd bytes a coloured cell costs, which puts the right-hand
// border a screen and a half away.
func TestFrameMeasuresWhatIsVisible(t *testing.T) {
	framed := Frame("\x1b[38;2;1;2;3m\x1b[48;2;4;5;6m▀\x1b[0m")

	if got := len(strings.Split(framed, "\n")); got != 3 {
		t.Fatalf("framed %d lines, want 3", got)
	}

	border := strings.Split(framed, "\n")[0]
	if border != "+---+" {
		t.Errorf("the border measured the escapes: %q", border)
	}
}

// The reset goes before the padding. Without it the last cell's background
// colour runs through the gap and swallows the right-hand border, which is the
// one part of the frame that has to be there for the frame to read as a box.
func TestFrameResetsBeforeThePadding(t *testing.T) {
	line := strings.Split(Frame("\x1b[48;2;9;9;9m▀\nxx"), "\n")[1]

	if !strings.HasSuffix(line, ansiReset+" "+" |") {
		t.Errorf("the padding was not reset first: %q", line)
	}
}

// An image is fitted into a box it rarely fills, and the letterboxing it
// arrives with is not part of the page.
func TestFrameDropsSurroundingBlankLines(t *testing.T) {
	framed := Frame("\n  \nabc\n\n")

	if got := len(strings.Split(framed, "\n")); got != 3 {
		t.Errorf("blank lines were framed:\n%s", framed)
	}
}

func TestFrameOfNothingIsNothing(t *testing.T) {
	if got := Frame("\n \n"); got != "" {
		t.Errorf("Frame(blank) = %q, want empty", got)
	}
}

// The border has to be as wide as the widest cell row, which is what makes a
// rendered screenshot square in its box.
func TestFrameOfRenderedArt(t *testing.T) {
	art := "\x1b[38;2;0;0;0m\x1b[48;2;0;0;0m▀\x1b[38;2;0;0;0m\x1b[48;2;0;0;0m▀" + ansiReset
	lines := strings.Split(Frame(art+"\n"+art), "\n")

	for _, line := range lines {
		if got := utf8.RuneCountInString(visible(line)); got != 6 {
			t.Errorf("line %q is %d visible columns, want 6", visible(line), got)
		}
	}
}

func TestVisible(t *testing.T) {
	cases := map[string]string{
		"plain":                    "plain",
		"\x1b[0m":                  "",
		"\x1b[38;2;1;2;3mx\x1b[0m": "x",
		// An escape that never terminates: dropped whole, not half-printed.
		"a\x1b[38;2;1":  "a",
		"a\x1b(Bb":      "ab",
		"\x1b":          "",
		"\x1b[1;31mred": "red",
	}

	for line, want := range cases {
		if got := visible(line); got != want {
			t.Errorf("visible(%q) = %q, want %q", line, got, want)
		}
	}
}

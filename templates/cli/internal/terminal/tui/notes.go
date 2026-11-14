package tui

import (
	"strings"
	"unicode/utf8"

	"charm.land/lipgloss/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// noMatch is what a list says when its filter hides every row: no table matches orders: esc
// clears the filter.
func noMatch(row, filter string) string {
	return "no " + row + " matches " + filter + ": esc clears the filter"
}

// cellLines wraps a cell to width and draws each of its lines.
func cellLines(c cell, width int) []string {
	return strings.Split(lipgloss.Wrap(styles.Render(c...), max(1, width), " /"), "\n")
}

// cutMiddle cuts plain text to width columns in the middle, keeping its start and more of its
// end, as the end of a url or an id names what it is. A wide rune the cut falls inside is left
// out, so the text stays within width. Only the ends it keeps are measured, so cutting a value of
// megabytes costs what cutting a line does: a rune takes no more columns than it has bytes, and a
// value of more than cutMiddleBytes bytes a column is taken not to fit.
func cutMiddle(s string, width int) string {
	if width < 8 || len(s) <= width || len(s) <= cutMiddleBytes*width && ansi.StringWidth(s) <= width {
		return s
	}
	head := (width - 1) / 4
	tail := width - 1 - head
	start := s[:runeBefore(s, min(len(s), cutMiddleBytes*head))]
	end := s[runeAfter(s, max(0, len(s)-cutMiddleBytes*tail)):]
	cut := max(0, ansi.StringWidth(end)-tail)
	kept := ansi.TruncateLeft(end, cut, "")
	if ansi.StringWidth(kept) > tail {
		kept = ansi.TruncateLeft(end, cut+1, "")
	}
	return ansi.Truncate(start, head, "") + "…" + kept
}

// cutMiddleBytes is the most bytes cutMiddle takes a column to hold.
const cutMiddleBytes = 8

// runeBefore is i, or the start of the rune of s it falls inside.
func runeBefore(s string, i int) int {
	for i > 0 && i < len(s) && !utf8.RuneStart(s[i]) {
		i--
	}
	return i
}

// runeAfter is i, or the start of the rune of s after the one it falls inside.
func runeAfter(s string, i int) int {
	for i < len(s) && !utf8.RuneStart(s[i]) {
		i++
	}
	return i
}

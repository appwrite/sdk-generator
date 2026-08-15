package preview

import (
	"strings"
	"unicode/utf8"
)

// Frame draws a box around rendered art.
//
// The box
// is what separates a picture of a web page from the terminal it is printed in:
// without it a screenshot with a white background bleeds into a light terminal
// and there is no telling where the page ends.
//
// Blank leading and trailing lines are dropped first. The image is fitted into
// a box it rarely fills exactly, and the letterboxing it arrives with would
// otherwise be framed as though it were part of the page.
func Frame(art string) string {
	lines := strings.Split(art, "\n")
	for index, line := range lines {
		lines[index] = strings.TrimRight(line, " \t")
	}

	for len(lines) > 0 && strings.TrimSpace(visible(lines[0])) == "" {
		lines = lines[1:]
	}
	for len(lines) > 0 && strings.TrimSpace(visible(lines[len(lines)-1])) == "" {
		lines = lines[:len(lines)-1]
	}

	if len(lines) == 0 {
		return ""
	}

	width := 0
	for _, line := range lines {
		width = max(width, utf8.RuneCountInString(visible(line)))
	}

	border := "+-" + strings.Repeat("-", width) + "-+"
	framed := make([]string, 0, len(lines)+2)
	framed = append(framed, border)

	for _, line := range lines {
		// Reset before the padding, or the last cell's background colour runs
		// through the gap and swallows the right-hand border.
		padding := strings.Repeat(" ", width-utf8.RuneCountInString(visible(line)))
		framed = append(framed, "| "+line+ansiReset+padding+" |")
	}

	return strings.Join(append(framed, border), "\n")
}

// visible is a line without its ANSI escape sequences.
//
// Only the CSI sequences this package emits and the ones a terminal library is
// likely to have added are recognised -- a colour escape counts for
// no columns, and measuring the raw string instead would pad every line by the
// twenty-odd bytes each cell costs.
func visible(line string) string {
	var builder strings.Builder

	for index := 0; index < len(line); {
		if line[index] != 0x1b {
			builder.WriteByte(line[index])
			index++

			continue
		}

		index++
		if index >= len(line) {
			// An escape that never terminates is dropped whole rather than
			// half-printed.
			break
		}

		switch {
		case line[index] == '[':
			// CSI: ESC [ ... <final byte in @ to ~>. Every escape this package
			// emits is one of these.
			index++
			for index < len(line) && (line[index] < '@' || line[index] > '~') {
				index++
			}
			if index < len(line) {
				index++
			}
		case strings.IndexByte("()*+#%", line[index]) >= 0:
			// A two-byte designator -- ESC ( B selects ASCII, which lipgloss
			// and bubbletea both emit. Its final byte is ordinary text, so
			// skipping only the intermediate would print a stray "B".
			index += 2
		default:
			index++
		}
	}

	return builder.String()
}

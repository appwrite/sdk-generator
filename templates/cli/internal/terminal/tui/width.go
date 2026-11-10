package tui

import (
	"strings"

	"charm.land/lipgloss/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// truncate cuts plain text to width columns, ending it in an ellipsis when it was cut.
func truncate(s string, width int) string {
	if width <= 0 {
		return ""
	}
	return ansi.Truncate(s, width, "…")
}

// indent moves every line of s one column to the right, off the frame's edge.
func indent(s string) string {
	return " " + strings.ReplaceAll(s, "\n", "\n ")
}

// tableWidth is the width of a table that fills the content area, a column in from each side.
func tableWidth() int { return contentWidth - 2 }

// spread places left and right on one line of the given width. When they do not fit, left is cut
// to an ellipsis so right stays whole.
func spread(left, right string, width int) string {
	rw := ansi.StringWidth(right)
	if ansi.StringWidth(left)+1+rw > width {
		left = truncate(left, width-rw-1)
	}
	gap := max(1, width-ansi.StringWidth(left)-rw)
	return left + strings.Repeat(" ", gap) + right
}

// clip cuts s to height lines, ending lines wider than width in an ellipsis.
func clip(s string, width, height int) string {
	lines := strings.Split(s, "\n")
	if len(lines) > height {
		lines = lines[:max(height, 0)]
	}
	for i, line := range lines {
		if ansi.StringWidth(line) > width {
			lines[i] = ansi.Truncate(line, width, "…")
		}
	}
	return strings.Join(lines, "\n")
}

// fill pads every line of s to width and s to height lines, after cutting what does not fit, so
// a block lays out the same whatever it holds.
func fill(s string, width, height int) string {
	lines := strings.Split(clip(s, width, height), "\n")
	for len(lines) < height {
		lines = append(lines, "")
	}
	for i, line := range lines {
		lines[i] = line + strings.Repeat(" ", max(0, width-ansi.StringWidth(line)))
	}
	return strings.Join(lines, "\n")
}

// place centres s in an area of width and height, and cuts what does not fit.
func place(width, height int, s string) string {
	return lipgloss.Place(width, height, lipgloss.Center, lipgloss.Center, clip(s, width, height))
}

// frame draws the rounded border around the content area, with a title set into the top edge,
// meta to its right, and foot into the bottom edge. The body fills the content area, with a
// blank line above and below it.
func frame(title, meta, foot, body string) string {
	border := lipgloss.NewStyle().Foreground(styles.P.Rule)
	edge := func(n int) string { return border.Render(strings.Repeat("─", max(1, n))) }

	metaWidth := 0
	if meta != "" {
		meta = " " + ansi.Truncate(meta, max(frameWidth/3, frameWidth-9-ansi.StringWidth(title)), "…") + " "
		metaWidth = ansi.StringWidth(meta)
	}
	title = ansi.Truncate(title, frameWidth-7-metaWidth, "…")
	top := border.Render("╭─ ") + title + " " + edge(frameWidth-6-ansi.StringWidth(title)-metaWidth) + meta + border.Render("─╮")

	footWidth := 0
	if foot != "" {
		foot = " " + ansi.Truncate(foot, frameWidth-6, "…") + " "
		footWidth = ansi.StringWidth(foot)
	}
	bottom := border.Render("╰") + edge(frameWidth-3-footWidth) + foot + border.Render("─╯")

	side := border.Render("│")
	blank := side + strings.Repeat(" ", contentWidth) + side
	lines := strings.Split(fill(body, contentWidth, contentHeight), "\n")
	var b strings.Builder
	b.WriteString(top + "\n" + blank)
	for _, line := range lines {
		b.WriteString("\n" + side + line + side)
	}
	b.WriteString("\n" + blank + "\n" + bottom)
	return b.String()
}

// scrollCue says which lines of a page that scrolls are in sight, as lines 12–40 of 230: shown
// lines from the one at first, of total. It is empty when every line is in sight.
func scrollCue(first, shown, total int) string {
	first = max(0, min(first, total-1))
	shown = min(shown, total-first)
	if shown <= 0 || shown >= total {
		return ""
	}
	return "lines " + formatInt(first+1) + "–" + formatInt(first+shown) + " of " + formatInt(total)
}

// detailWidth is the width of the lines of an item's page: they start one column in and end one
// before the frame, as a table's rows do, so their section rules end where a table's does.
func detailWidth() int { return contentWidth - 2 }

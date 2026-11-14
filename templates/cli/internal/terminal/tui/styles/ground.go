package styles

import (
	"image/color"
	"strings"

	"github.com/charmbracelet/x/ansi"
)

// Ground paints the palette's base under every cell of s, a screen of width by height. The view's
// background colour sets the terminal's own ground, which not every terminal takes; this paints
// it into the cells as well: every line is padded to width and s to height, and the base is set
// again after each style inside a line, as a span's reset would otherwise drop it.
func Ground(s string, width, height int) string {
	bg := ansi.Style{}.BackgroundColor(P.Base).String()
	lines := strings.Split(s, "\n")
	for len(lines) < height {
		lines = append(lines, "")
	}
	p := ansi.GetParser()
	defer ansi.PutParser(p)
	var b strings.Builder
	for i, line := range lines {
		if i > 0 {
			b.WriteByte('\n')
		}
		b.WriteString(bg)
		var state byte
		for rest := line; rest != ""; {
			p.Reset()
			seq, _, n, next := ansi.DecodeSequence(rest, state, p)
			b.WriteString(seq)
			if ansi.HasCsiPrefix(seq) && p.Command() == 'm' && !setsBackground(p.Params()) {
				b.WriteString(bg)
			}
			rest, state = rest[n:], next
		}
		b.WriteString(strings.Repeat(" ", max(0, width-ansi.StringWidth(line))))
		b.WriteString(ansi.ResetStyle)
	}
	return b.String()
}

// setsBackground reports whether an sgr sequence sets a background of its own, skipping the
// components of a foreground or underline colour, which can hold the same numbers.
func setsBackground(params ansi.Params) bool {
	for i := 0; i < len(params); i++ {
		switch n := params[i].Param(0); {
		case n == 38 || n == 58:
			var c color.Color
			if read := ansi.ReadStyleColor(params[i:], &c); read > 0 {
				i += read - 1
			}
		case n == 48, n >= 40 && n <= 47, n >= 100 && n <= 107:
			return true
		}
	}
	return false
}

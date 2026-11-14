package styles

import (
	"fmt"
	"strings"
	"unicode"
	"unicode/utf8"
)

// Clean makes text from appwrite or an error safe to draw: control characters (C0, DEL,
// C1) and bytes that are not utf-8 are shown escaped, so an escape sequence in a name cannot reach
// the terminal, and so are bidi controls and line separators, which would reorder or break the
// line around them.
func Clean(s string) string { return clean(s, false) }

// CleanBlock is Clean for multi-line text, keeping newlines; tabs become 4 spaces, as lipgloss
// expands tabs differently from how it measures them.
func CleanBlock(s string) string { return clean(s, true) }

func clean(s string, block bool) string {
	if utf8.ValidString(s) && strings.IndexFunc(s, escaped) < 0 {
		return s
	}
	if block {
		s = strings.ReplaceAll(s, "\r\n", "\n")
	}
	var b strings.Builder
	b.Grow(len(s))
	for i := 0; i < len(s); {
		r, size := utf8.DecodeRuneInString(s[i:])
		switch {
		case r == utf8.RuneError && size == 1:
			fmt.Fprintf(&b, `\x%02x`, s[i])
		case r == '\n' && block:
			b.WriteRune(r)
		case r == '\t' && block:
			b.WriteString("    ")
		case r == '\t':
			b.WriteByte(' ')
		case unicode.IsControl(r):
			fmt.Fprintf(&b, `\x%02x`, r)
		case escaped(r):
			fmt.Fprintf(&b, `\u%04x`, r)
		default:
			b.WriteRune(r)
		}
		i += size
	}
	return b.String()
}

// escaped reports whether r is drawn as an escape: a control character, a bidi control (the
// embeddings, overrides and isolates, and the marks), or a line or paragraph separator.
func escaped(r rune) bool {
	switch {
	case unicode.IsControl(r):
		return true
	case r == '\u061c', r == '\u200e', r == '\u200f', r == '\u2028', r == '\u2029':
		return true
	case r >= '\u202a' && r <= '\u202e', r >= '\u2066' && r <= '\u2069':
		return true
	}
	return false
}

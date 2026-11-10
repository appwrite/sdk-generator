package styles

import (
	"testing"
	"unicode/utf8"
)

// TestClean checks control characters, bidi controls and bytes that are not utf-8 are escaped,
// newlines kept only in blocks, and text that is safe left alone.
func TestClean(t *testing.T) {
	tests := []struct {
		name  string
		in    string
		block bool
		want  string
	}{
		{name: "plain", in: "orders-dev", want: "orders-dev"},
		{name: "empty", in: "", want: ""},
		{name: "wide runes", in: "日本語 🚀", want: "日本語 🚀"},
		{name: "emoji with joiner", in: "\U0001F469\u200D\U0001F4BB ok", want: "\U0001F469\u200D\U0001F4BB ok"},
		{name: "replacement rune kept", in: "a\uFFFDb", want: "a\uFFFDb"},
		{name: "osc", in: "a\x1b]0;title\x07b", want: `a\x1b]0;title\x07b`},
		{name: "osc 8 link", in: "\x1b]8;;https://evil.example/\x1b\\x", want: `\x1b]8;;https://evil.example/\x1b\x`},
		{name: "csi", in: "\x1b[31mred", want: `\x1b[31mred`},
		{name: "clear screen", in: "\x1b[2J", want: `\x1b[2J`},
		{name: "c1", in: "a\u009bb", want: `a\x9bb`},
		{name: "nul and del", in: "a\x00b\x7f", want: `a\x00b\x7f`},
		{name: "bell", in: "\a", want: `\x07`},
		{name: "backspace", in: "ab\bc", want: `ab\x08c`},
		{name: "newline", in: "a\nb", want: `a\x0ab`},
		{name: "carriage return", in: "ok\rfail", want: `ok\x0dfail`},
		{name: "crlf", in: "a\r\nb", want: `a\x0d\x0ab`},
		{name: "tab", in: "a\tb", want: "a b"},
		{name: "right to left override", in: "user\u202Egnp.exe", want: `user\u202egnp.exe`},
		{name: "embedding and pop", in: "\u202Aa\u202C", want: `\u202aa\u202c`},
		{name: "isolates", in: "\u2066a\u2067b\u2068c\u2069", want: `\u2066a\u2067b\u2068c\u2069`},
		{name: "marks", in: "a\u200Eb\u200Fc\u061Cd", want: `a\u200eb\u200fc\u061cd`},
		{name: "line separator", in: "a\u2028b\u2029c", want: `a\u2028b\u2029c`},
		{name: "lone byte", in: "a\xffb", want: `a\xffb`},
		{name: "raw c1 byte", in: "a\x9b31mb", want: `a\x9b31mb`},
		{name: "cut rune", in: "ab\xe6\x97", want: `ab\xe6\x97`},
		{name: "invalid with escape", in: "\xc0\x1b", want: `\xc0\x1b`},
		{name: "block newline", in: "a\r\nb", block: true, want: "a\nb"},
		{name: "block lone carriage return", in: "a\rb\n", block: true, want: "a\\x0db\n"},
		{name: "block tab", in: "a\tb", block: true, want: "a    b"},
		{name: "block escape", in: "a\n\x1b[2Jb", block: true, want: "a\n\\x1b[2Jb"},
		{name: "block bidi", in: "a\n\u202Eb", block: true, want: "a\n\\u202eb"},
		{name: "block invalid", in: "a\n\xffb", block: true, want: "a\n\\xffb"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			got := Clean(tt.in)
			if tt.block {
				got = CleanBlock(tt.in)
			}
			if got != tt.want {
				t.Errorf("got %q, want %q", got, tt.want)
			}
		})
	}
}

// TestCleanIsSafe checks what Clean and CleanBlock return is utf-8 and holds nothing escaped but
// the newlines of a block, whatever went in.
func TestCleanIsSafe(t *testing.T) {
	tests := []struct {
		name string
		in   string
	}{
		{name: "every byte", in: everyByte()},
		{name: "mixed", in: "日\x1b]8;;x\x07\u202E\xff\t\r\n🚀\u2066"},
		{name: "only invalid", in: "\x80\x81\xfe\xff"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			for i, got := range []string{Clean(tt.in), CleanBlock(tt.in)} {
				if !utf8.ValidString(got) {
					t.Fatalf("%d: %q is not utf-8", i, got)
				}
				for _, r := range got {
					if escaped(r) && (r != '\n' || i == 0) {
						t.Fatalf("%d: %q holds %U", i, got, r)
					}
				}
			}
		})
	}
}

// everyByte is every byte value once, in order.
func everyByte() string {
	b := make([]byte, 256)
	for i := range b {
		b[i] = byte(i)
	}
	return string(b)
}

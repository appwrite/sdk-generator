package styles

import (
	"strings"
	"testing"
	"unicode/utf8"
)

// fuzzUnsafe is the first rune of s the terminal must not get raw, and whether there is one: a
// control character, newline included unless block, a bidi control or a line separator, or a
// byte that is not utf-8.
func fuzzUnsafe(s string, block bool) (rune, bool) {
	for i, r := range s {
		switch {
		case r == utf8.RuneError && !strings.HasPrefix(s[i:], "\ufffd"):
			return r, true
		case r == '\n' && block:
		case escaped(r):
			return r, true
		}
	}
	return 0, false
}

// FuzzClean checks whatever Clean is given comes out as utf-8 with nothing the terminal would act
// on, that cleaning it again changes nothing, and that CleanBlock keeps its newlines and nothing
// else that Clean escapes.
func FuzzClean(f *testing.F) {
	for _, seed := range []string{
		"", "orders-dev", "日本語 🚀", "a\x1b]8;;https://evil.example/\x07b", "\x1b[2J", "a\u009bb", "a\x9bb",
		"\xff\xfe", "a\r\nb\tc", "user\u202egnp.exe", "a\u2028b", "\U0001F469\u200d\U0001F4BB", "\xe2\x80", "a\ufffdb",
	} {
		f.Add(seed)
	}
	f.Fuzz(func(t *testing.T, s string) {
		for _, block := range []bool{false, true} {
			got := clean(s, block)
			if !utf8.ValidString(got) {
				t.Fatalf("clean(%q, %v) = %q, not utf-8", s, block, got)
			}
			if r, bad := fuzzUnsafe(got, block); bad {
				t.Fatalf("clean(%q, %v) = %q holds %U", s, block, got, r)
			}
			if again := clean(got, block); again != got {
				t.Fatalf("clean(%q, %v) = %q, and again %q", s, block, got, again)
			}
		}
		if n, want := strings.Count(CleanBlock(s), "\n"), strings.Count(strings.ReplaceAll(s, "\r\n", "\n"), "\n"); n != want {
			t.Fatalf("CleanBlock(%q) has %d newlines, want %d", s, n, want)
		}
	})
}

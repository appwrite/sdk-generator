package tui

import (
	"strings"
	"testing"
	"unicode/utf8"

	"github.com/charmbracelet/x/ansi"
)

// FuzzJSONLines checks whatever json a resource holds, its lines carry no escape sequence of
// its own to the terminal: once the styles are stripped, no escape, bell or bidi control is left.
func FuzzJSONLines(f *testing.F) {
	for _, seed := range []string{`{"a":1}`, `{"x\u001b]8;;y":"\u202e\u0007"}`, `[1,"two",null,true,{"k":[]}]`, `"\u009b2J"`} {
		f.Add([]byte(seed))
	}
	f.Fuzz(func(t *testing.T, data []byte) {
		lines, ok := jsonLines(data)
		if !ok {
			return
		}
		for _, l := range lines {
			plain := ansi.Strip(l)
			if strings.ContainsAny(plain, "\x1b\x07\u009b\u202e\u2066") || !utf8.ValidString(plain) {
				t.Fatalf("line %q reaches the terminal raw", plain)
			}
		}
	})
}

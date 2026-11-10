package tui

import (
	"bytes"
	"encoding/json"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// jsonLines indents json and tints each line: keys and punctuation faint, strings in the body's
// tone, numbers and booleans warm, null faint. It reports false when data is not json.
func jsonLines(data []byte) ([]string, bool) {
	var b bytes.Buffer
	if err := json.Indent(&b, bytes.TrimSpace(data), "", "  "); err != nil {
		return nil, false
	}
	lines := strings.Split(b.String(), "\n")
	for i, l := range lines {
		lines[i] = tintJSONLine(l)
	}
	return lines, true
}

// tintJSONLine tints one line of indented json: its indent, a key, a value and a comma.
func tintJSONLine(line string) string {
	trimmed := strings.TrimLeft(line, " ")
	indent := line[:len(line)-len(trimmed)]
	var b strings.Builder
	b.WriteString(indent)
	rest := trimmed
	if strings.HasPrefix(rest, `"`) {
		if end := stringEnd(rest); end > 0 && strings.HasPrefix(rest[end:], ": ") {
			b.WriteString(styles.Faint(styles.Clean(rest[:end]) + ": "))
			rest = rest[end+2:]
		}
	}
	comma := ""
	if strings.HasSuffix(rest, ",") {
		rest, comma = rest[:len(rest)-1], ","
	}
	switch {
	case rest == "":
	case strings.HasPrefix(rest, `"`):
		b.WriteString(styles.Body(styles.Clean(rest)))
	case rest == "null":
		b.WriteString(styles.Faint(rest))
	case rest == "true" || rest == "false" || strings.ContainsAny(rest[:1], "-0123456789"):
		b.WriteString(styles.Fg(styles.ToneWarm).Render(rest))
	default:
		b.WriteString(styles.Faint(styles.Clean(rest)))
	}
	b.WriteString(styles.Faint(comma))
	return b.String()
}

// stringEnd is the index just past the json string s starts with, or 0 when it does not end.
func stringEnd(s string) int {
	for i := 1; i < len(s); i++ {
		switch s[i] {
		case '\\':
			i++
		case '"':
			return i + 1
		}
	}
	return 0
}

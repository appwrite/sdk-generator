package dotenv

import (
	"regexp"
	"strings"
)

// Every pair it produces is handed to the container as an environment
// variable, so a parse difference changes what the function sees at runtime.
// Behaviour is pinned to the package itself, not to its documentation; see
// internal/dotenv/testdata.
//
// dotenv is one regular expression plus a short post-processing step, and it is
// reproduced that way rather than rewritten as a hand-rolled scanner -- a
// scanner would be clearer to read and would diverge on the first odd line.

// linePattern is dotenv's LINE regex, transcribed.
//
//   - `export ` is optional and dropped
//   - a key is [\w.-]+, so `A.B` and `A-B` are keys but `A B` is not
//   - a value is a quoted run (single, double or backtick, honouring an
//     escaped closing quote) or an unquoted run that stops at #, CR or LF --
//     which is why `B=2# no space` yields "2" with no space needed before the
//     hash
//   - a trailing `#...` outside quotes is discarded
var linePattern = regexp.MustCompile(
	"(?m)^\\s*(?:export\\s+)?([\\w.-]+)(?:\\s*=\\s*?|:\\s+?)" +
		"(\\s*'(?:\\\\'|[^'])*'|\\s*\"(?:\\\\\"|[^\"])*\"|\\s*`(?:\\\\`|[^`])*`|[^#\\r\\n]+)?" +
		"\\s*(?:#.*)?$")

// Parse reads a .env document into key/value pairs.
//
// A later assignment to the same key replaces an earlier one, matching the
// package. Lines that do not parse are skipped silently: dotenv reports no
// error for them, and refusing to run because a function's .env has a stray
// line would be worse than the omission.
func Parse(document string) map[string]string {
	// dotenv normalises line endings before matching, so a CRLF file behaves
	// exactly like an LF one.
	normalized := strings.ReplaceAll(document, "\r\n", "\n")
	normalized = strings.ReplaceAll(normalized, "\r", "\n")

	values := map[string]string{}

	for _, match := range linePattern.FindAllStringSubmatch(normalized, -1) {
		values[match[1]] = unquote(match[2])
	}

	return values
}

// ParseOrdered is Parse, plus the keys in the order they first appear.
//
// Go maps have no order, and the caller passes these to `docker run -e` one
// flag at a time. Without this the command line would be shuffled on every
// invocation, which makes two runs impossible to diff.
func ParseOrdered(document string) ([]string, map[string]string) {
	values := Parse(document)

	normalized := strings.ReplaceAll(document, "\r\n", "\n")
	normalized = strings.ReplaceAll(normalized, "\r", "\n")

	keys := make([]string, 0, len(values))
	seen := make(map[string]bool, len(values))
	for _, match := range linePattern.FindAllStringSubmatch(normalized, -1) {
		if seen[match[1]] {
			continue
		}
		seen[match[1]] = true
		keys = append(keys, match[1])
	}

	return keys, values
}

// unquote applies dotenv's post-processing to a captured value.
func unquote(value string) string {
	value = strings.TrimSpace(value)
	if value == "" {
		return ""
	}

	// Note only \n and \r are unescaped, and only inside double quotes. \t is
	// NOT handled -- dotenv leaves it as a literal backslash-t.
	quote := value[0]
	if (quote == '\'' || quote == '"' || quote == '`') &&
		len(value) > 1 && value[len(value)-1] == quote {
		value = value[1 : len(value)-1]

		if quote == '"' {
			value = strings.ReplaceAll(value, `\n`, "\n")
			value = strings.ReplaceAll(value, `\r`, "\r")
		}
	}

	return value
}

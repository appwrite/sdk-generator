package ignore

import (
	"regexp"
	"strings"
)

// Implements the `ignore` npm package (7.x), which decides which files reach a
// deployment -- a pattern that fails to match ships a secret, one that
// over-matches drops a source file.
//
// Ported rather than taken from a Go gitignore library because those disagree
// with `ignore` in exactly the corners that matter: trailing spaces, `**`
// placement, negation after a directory match. The contract is "whatever the
// the established CLI does", pinned to baselines in internal/ignore/testdata.

// Matcher decides whether a path is ignored.
type Matcher struct {
	rules []rule
}

// rule is one compiled pattern.
type rule struct {
	pattern  *regexp.Regexp
	negative bool
}

// New returns an empty matcher.
func New() *Matcher {
	return &Matcher{}
}

// Add compiles newline-separated patterns, as ignore().add(string) does.
//
// Blank lines and `#` comments are skipped. Each call appends, and order
// matters: the last rule that matches decides, which is what makes a negation
// able to rescue a path an earlier rule ignored.
func (m *Matcher) Add(patterns string) *Matcher {
	for line := range strings.SplitSeq(patterns, "\n") {
		m.addLine(line)
	}

	return m
}

// AddAll compiles a list of patterns, as ignore().add(string[]) does.
func (m *Matcher) AddAll(patterns []string) *Matcher {
	for _, pattern := range patterns {
		m.addLine(pattern)
	}

	return m
}

func (m *Matcher) addLine(line string) {
	pattern, negative, ok := normalize(line)
	if !ok {
		return
	}

	compiled, err := compile(pattern)
	if err != nil {
		// An uncompilable pattern is dropped rather than fatal: `ignore`
		// silently ignores what it cannot parse, and refusing to run because a
		// user's .gitignore has an odd line would be worse than the mismatch.
		return
	}

	m.rules = append(m.rules, rule{pattern: compiled, negative: negative})
}

// trailingSpace matches unescaped trailing whitespace, which git strips.
var trailingSpace = regexp.MustCompile(`(?:\\?\s)+$`)

// normalize prepares one raw line, reporting whether it is a usable pattern.
func normalize(line string) (pattern string, negative bool, ok bool) {
	// A carriage return from a CRLF file is not part of the pattern.
	line = strings.TrimSuffix(line, "\r")

	// Comments and blanks are dropped. Note `\#` is an escaped literal hash,
	// not a comment, and survives.
	if strings.TrimSpace(line) == "" || strings.HasPrefix(line, "#") {
		return "", false, false
	}

	// Trailing whitespace is stripped unless the last space is escaped.
	if !strings.HasSuffix(line, `\ `) {
		line = trailingSpace.ReplaceAllString(line, "")
	}
	if line == "" {
		return "", false, false
	}

	if strings.HasPrefix(line, "!") {
		negative = true
		line = line[1:]
	}

	// `\!` and `\#` are literals; the backslash goes away.
	if strings.HasPrefix(line, `\!`) || strings.HasPrefix(line, `\#`) {
		line = line[1:]
	}

	if line == "" {
		return "", false, false
	}

	return line, negative, true
}

// compile turns one gitignore pattern into an anchored regular expression.
//
// The path being tested is always slash-separated and relative to the ignore
// root, so the expression matches against that form directly.
func compile(pattern string) (*regexp.Regexp, error) {
	directoryOnly := strings.HasSuffix(pattern, "/")
	pattern = strings.TrimSuffix(pattern, "/")

	// A slash anywhere but the end anchors the pattern to the root. Without
	// one, the pattern matches at any depth -- which is why `node_modules`
	// matches `a/b/node_modules` but `/node_modules` does not.
	anchored := strings.Contains(pattern, "/")
	pattern = strings.TrimPrefix(pattern, "/")

	var expression strings.Builder
	expression.WriteString("^")
	if !anchored {
		expression.WriteString("(?:.*/)?")
	}

	expression.WriteString(translate(pattern))

	// A directory pattern matches the directory and everything under it; a
	// plain pattern also matches a directory's contents, which is why both
	// forms end with an optional `/...` tail.
	if directoryOnly {
		expression.WriteString("/")
	} else {
		expression.WriteString("(?:/|$)")
	}

	return regexp.Compile(expression.String())
}

// translate converts glob syntax to regular-expression syntax.
func translate(pattern string) string {
	var out strings.Builder

	for index := 0; index < len(pattern); index++ {
		character := pattern[index]

		switch character {
		case '*':
			// `**` spans separators; a single `*` stops at one.
			if index+1 < len(pattern) && pattern[index+1] == '*' {
				index++

				// A trailing `/**` matches everything below; a leading or
				// embedded `**/` may also match nothing at all, so the
				// separator is folded into the optional group.
				if index+1 < len(pattern) && pattern[index+1] == '/' {
					index++
					out.WriteString("(?:.*/)?")

					continue
				}

				out.WriteString(".*")

				continue
			}

			out.WriteString("[^/]*")
		case '?':
			out.WriteString("[^/]")
		case '[':
			closing := strings.IndexByte(pattern[index:], ']')
			if closing < 0 {
				// An unterminated class is a literal bracket.
				out.WriteString(`\[`)

				continue
			}

			class := pattern[index : index+closing+1]
			index += closing

			// `[!abc]` is gitignore's negation; regexp spells it `[^abc]`.
			if strings.HasPrefix(class, "[!") {
				class = "[^" + class[2:]
			}
			out.WriteString(class)
		case '\\':
			// An escape carries the next character through literally.
			if index+1 < len(pattern) {
				index++
				out.WriteString(regexp.QuoteMeta(string(pattern[index])))

				continue
			}

			out.WriteString(`\\`)
		default:
			out.WriteString(regexp.QuoteMeta(string(character)))
		}
	}

	return out.String()
}

// Result is one path's verdict, as `ignore`'s test() reports it.
//
// Ignored and Unignored are both false when no rule mentioned the path at all.
// That third state is what lets several matchers combine: a matcher with no
// opinion leaves an earlier matcher's verdict standing, while one that
// explicitly re-includes the path overrules it.
type Result struct {
	Ignored   bool
	Unignored bool
}

// Ignores reports whether a path is excluded.
//
// The path must be relative to the ignore root and slash-separated.
func (m *Matcher) Ignores(path string) bool {
	return m.Test(path).Ignored
}

// Test returns the full verdict for a path.
//
// Two rules combine, and the second is easy to miss: the last matching pattern
// wins, so a negation rescues a path -- but a negation cannot re-include
// anything beneath an excluded directory, so `temp/` followed by
// `!temp/keep.txt` still ignores it. Only the directory itself can be rescued.
func (m *Matcher) Test(path string) Result {
	path = strings.TrimPrefix(path, "./")
	if path == "" {
		return Result{}
	}

	// Ancestors first, shortest to longest. An excluded one settles it, and it
	// settles it as ignored rather than as unignored -- a rescued child of an
	// excluded directory is still excluded.
	for index, character := range path {
		if character != '/' {
			continue
		}
		if m.matches(path[:index+1]).Ignored {
			return Result{Ignored: true}
		}
	}

	return m.matches(path)
}

// matches runs the last-match-wins pass over one path.
//
// A directory is passed with its trailing slash, which is what lets a
// directory-only pattern match it.
func (m *Matcher) matches(path string) Result {
	var result Result
	for _, rule := range m.rules {
		if !rule.pattern.MatchString(path) {
			continue
		}
		result = Result{Ignored: !rule.negative, Unignored: rule.negative}
	}

	return result
}

// Filter returns the paths that are not ignored, preserving order.
func (m *Matcher) Filter(paths []string) []string {
	kept := make([]string, 0, len(paths))
	for _, path := range paths {
		if !m.Ignores(path) {
			kept = append(kept, path)
		}
	}

	return kept
}

package typegen

import (
	"fmt"
	"regexp"
	"strings"
)

// A Handlebars subset, so both CLIs can render the same .hbs files.
//
// The templates under templates/cli/lib/commands/generators/typescript/templates
// use only `{{var}}`, `{{{raw}}}` and `{{#if}}/{{else}}/{{/if}}` -- no loops, no
// helpers, no nested paths. Implementing that subset is a fraction of the work
// of maintaining a second set of templates in Go syntax, and it removes the way
// those two copies would drift.
//
// Semantics were captured by running handlebars itself, not inferred:
//
//	{{a}}   with a = `x&y<z>"q'`  ->  x&amp;y&lt;z&gt;&quot;q&#x27;
//	{{{a}}} with a = `x&y<z>`     ->  x&y<z>
//	{{#if b}} is false for false, missing, and ""
//	a missing variable renders as empty
type Values map[string]any

var (
	ifBlockPattern = regexp.MustCompile(`(?s)\{\{#if\s+([a-zA-Z0-9_.]+)\s*\}\}(.*?)\{\{/if\}\}`)
	triplePattern  = regexp.MustCompile(`\{\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}\}`)
	doublePattern  = regexp.MustCompile(`\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}`)

	// standaloneTag matches a block tag that is the only thing on its line.
	standaloneTag = regexp.MustCompile(`^\s*(\{\{#if\s+[a-zA-Z0-9_.]+\s*\}\}|\{\{else\}\}|\{\{/if\}\})\s*$`)
)

// stripStandaloneTags removes the line a lone block tag sits on.
//
// Handlebars treats a block open, close or else tag that shares its line with
// nothing but whitespace as "standalone" and deletes the surrounding whitespace
// and the newline. Without this every `{{#if}}` leaves a blank line behind, and
// a false condition leaves two.
//
// Only block tags are standalone; a lone `{{var}}` keeps its line.
func stripStandaloneTags(template string) string {
	lines := strings.Split(template, "\n")
	var out strings.Builder

	for index, line := range lines {
		if match := standaloneTag.FindStringSubmatch(line); match != nil {
			out.WriteString(match[1])

			continue
		}

		out.WriteString(line)
		if index < len(lines)-1 {
			out.WriteString("\n")
		}
	}

	return out.String()
}

// htmlEscaper matches Handlebars' escaping exactly.
//
// Note this escapes `&` in a URL: an endpoint with a query string is written
// into generated TypeScript as `&amp;`. That is what the TypeScript CLI does
// today, and byte-identical output is the requirement, so it is reproduced
// rather than corrected here.
var htmlEscaper = strings.NewReplacer(
	"&", "&amp;",
	"<", "&lt;",
	">", "&gt;",
	`"`, "&quot;",
	"'", "&#x27;",
	"`", "&#x60;",
	"=", "&#x3D;",
)

// Render expands a Handlebars template against values.
func Render(template string, values Values) string {
	template = stripStandaloneTags(template)

	// Blocks first, innermost-last: the regex is non-greedy, so nested blocks
	// resolve from the inside out across repeated passes.
	for {
		expanded := ifBlockPattern.ReplaceAllStringFunc(template, func(match string) string {
			parts := ifBlockPattern.FindStringSubmatch(match)
			body := parts[2]

			consequent, alternative, _ := strings.Cut(body, "{{else}}")

			if truthy(values[parts[1]]) {
				return consequent
			}

			return alternative
		})
		if expanded == template {
			break
		}
		template = expanded
	}

	// Triple braces before double, or the double pattern would match the inner
	// two braces of a triple and leave a stray brace behind.
	template = triplePattern.ReplaceAllStringFunc(template, func(match string) string {
		return stringify(values[triplePattern.FindStringSubmatch(match)[1]])
	})

	return doublePattern.ReplaceAllStringFunc(template, func(match string) string {
		return htmlEscaper.Replace(stringify(values[doublePattern.FindStringSubmatch(match)[1]]))
	})
}

// truthy mirrors Handlebars' notion of falsy: false, missing, empty string,
// zero, and empty collections.
func truthy(value any) bool {
	switch typed := value.(type) {
	case nil:
		return false
	case bool:
		return typed
	case string:
		return typed != ""
	case int:
		return typed != 0
	case []string:
		return len(typed) > 0
	}

	return true
}

func stringify(value any) string {
	if value == nil {
		return ""
	}
	if s, ok := value.(string); ok {
		return s
	}

	return fmt.Sprint(value)
}

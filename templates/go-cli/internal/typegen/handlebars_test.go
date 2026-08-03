package typegen

import "testing"

// Baselines captured by running handlebars itself, not written from
// expectation. The generated TypeScript must be byte-identical to what the
// TypeScript CLI produces, so these are a contract.
func TestRenderMatchesHandlebars(t *testing.T) {
	cases := []struct {
		name     string
		template string
		values   Values
		want     string
	}{
		// {{var}} HTML-escapes. This is why an endpoint containing `&` appears
		// as `&amp;` in generated TypeScript -- reproduced, not corrected.
		{"escapes", "{{a}}", Values{"a": `x&y<z>"q'`}, "x&amp;y&lt;z&gt;&quot;q&#x27;"},
		{"triple is raw", "{{{a}}}", Values{"a": "x&y<z>"}, "x&y<z>"},
		{"if true", "A{{#if b}}YES{{/if}}B", Values{"b": true}, "AYESB"},
		{"if false", "A{{#if b}}YES{{/if}}B", Values{"b": false}, "AB"},
		{"if else", "A{{#if b}}Y{{else}}N{{/if}}B", Values{"b": false}, "ANB"},
		{"if missing", "A{{#if b}}Y{{/if}}B", Values{}, "AB"},
		{"if empty string", "A{{#if b}}Y{{/if}}B", Values{"b": ""}, "AB"},
		{"missing var", "[{{nope}}]", Values{}, "[]"},
	}

	for _, tc := range cases {
		t.Run(tc.name, func(t *testing.T) {
			if got := Render(tc.template, tc.values); got != tc.want {
				t.Errorf("Render(%q)\n got %q\nwant %q", tc.template, got, tc.want)
			}
		})
	}
}

// A triple brace must not be consumed by the double-brace pattern, which would
// leave a stray brace in generated source.
func TestRenderHandlesAdjacentTriples(t *testing.T) {
	got := Render("{{{A}}}{{{B}}}", Values{"A": "<a>", "B": "<b>"})
	if got != "<a><b>" {
		t.Errorf("got %q, want %q", got, "<a><b>")
	}
}

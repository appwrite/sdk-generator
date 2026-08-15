package typegen

import "testing"

// Baselines captured by running handlebars itself, not written from
// expectation. The generated TypeScript must be byte-identical to the captured
// baselines, so these are a contract.
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

		// A block tag alone on a line is "standalone": Handlebars deletes the
		// whole line, not just the tag. Every one of the four typegen templates
		// writes its {{#if}} that way, so without this a true condition leaves
		// a stray blank line and a false one leaves two. Caught by the
		// generator baselines -- the earlier tests here only checked that the
		// rendered output *contained* the right text, which this passes either
		// way. Byte comparison, always.
		{
			"standalone if is removed with its line",
			"a\n{{#if b}}\nc\n{{/if}}\nd\n",
			Values{"b": true},
			"a\nc\nd\n",
		},
		{
			"standalone if drops the whole block",
			"a\n{{#if b}}\nc\n{{/if}}\nd\n",
			Values{"b": false},
			"a\nd\n",
		},
		{
			"standalone else",
			"a\n{{#if b}}\nc\n{{else}}\ne\n{{/if}}\nd\n",
			Values{"b": false},
			"a\ne\nd\n",
		},
		{
			"indented standalone tags lose their indentation too",
			"a\n    {{#if b}}\nc\n    {{/if}}\nd\n",
			Values{"b": true},
			"a\nc\nd\n",
		},
		// An inline tag is not standalone and keeps everything around it.
		{
			"inline if keeps its line",
			"a {{#if b}}c{{/if}} d\n",
			Values{"b": true},
			"a c d\n",
		},
		// A plain variable alone on a line is not a block tag, so its line
		// stays even when the value is empty.
		{
			"standalone variable keeps its line",
			"a\n{{b}}\nc\n",
			Values{},
			"a\n\nc\n",
		},
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

package output

import (
	"bytes"
	"strings"
	"testing"
)

// `organization get` embeds a 69-field billing plan. Rendering it as one row of
// 69 columns produced a line no terminal could show, which is what a reader
// actually saw before this.
func TestWideNestedObjectRendersAsKeyValues(t *testing.T) {
	input := decode(t, `{
		"$id": "org",
		"billingPlanDetails": {
			"$id": "tier-1", "name": "Pro", "group": "pro", "price": 25,
			"desc": "long", "order": 10, "trial": 0, "bandwidth": 2000,
			"storage": 150, "fileSize": 5000, "users": 200000,
			"webhooks": 0, "wafRules": 50, "teams": 0, "buckets": 0
		}
	}`)

	buffer := &bytes.Buffer{}
	renderer := &Renderer{Mode: ModeTable, Writer: buffer}
	if err := renderer.Render(input); err != nil {
		t.Fatal(err)
	}
	got := buffer.String()

	// A table would put every field on one line; key/value puts each on its own.
	if strings.Contains(got, "│") {
		t.Errorf("rendered as a table:\n%s", got)
	}
	for _, want := range []string{"name", "Pro", "price", "25"} {
		if !strings.Contains(got, want) {
			t.Errorf("missing %q from:\n%s", want, got)
		}
	}
	// Not on the allowlist, so counted rather than printed.
	if strings.Contains(got, "wafRules") {
		t.Errorf("printed a field outside the allowlist:\n%s", got)
	}
	if !strings.Contains(got, "more fields") || !strings.Contains(got, "--raw") {
		t.Errorf("no footer telling the reader what was withheld:\n%s", got)
	}
}

// Sizes and counts carry the unit the console uses, so 2000 GB reads as 2 TB
// and 200000 users reads as 200K.
func TestSectionValuesCarryTheirUnits(t *testing.T) {
	for _, probe := range []struct {
		name string
		got  string
		want string
	}{
		{"size climbing a unit", FormatSize(2000, "GB"), "2000 GB"},
		{"size staying put", FormatSize(150, "GB"), "150 GB"},
		{"size in MB", FormatSize(5000, "MB"), "5000 MB"},
		{"count below the threshold", FormatCount(500), "500"},
		{"count above it", FormatCount(200000), "200000"},
		{"labelled", FormatLabelled(0, "days"), "0 days"},
	} {
		if !strings.HasPrefix(probe.got, probe.want) {
			t.Errorf("%s: %q does not start with %q", probe.name, probe.got, probe.want)
		}
	}

	if got := FormatSize(2000, "GB"); !strings.Contains(got, "2 TB") {
		t.Errorf("FormatSize(2000, GB) = %q, want a TB aside", got)
	}
	if got := FormatSize(150, "GB"); strings.Contains(got, "(") {
		t.Errorf("FormatSize(150, GB) = %q, want no aside", got)
	}
	if got := FormatCount(200000); !strings.Contains(got, "200K") {
		t.Errorf("FormatCount(200000) = %q, want a 200K aside", got)
	}
	if got := FormatCount(3500000); !strings.Contains(got, "3.5M") {
		t.Errorf("FormatCount(3500000) = %q, want a 3.5M aside", got)
	}
	if got := FormatCount(500); strings.Contains(got, "(") {
		t.Errorf("FormatCount(500) = %q, want no aside", got)
	}
}

// A narrow section is still a table -- that is the readable shape when it fits,
// and the fallback must not swallow it.
func TestNarrowSectionsStayTables(t *testing.T) {
	input := decode(t, `{"$id": "x", "rows": [{"a": 1, "b": 2}, {"a": 3, "b": 4}]}`)

	buffer := &bytes.Buffer{}
	renderer := &Renderer{Mode: ModeTable, Writer: buffer}
	if err := renderer.Render(input); err != nil {
		t.Fatal(err)
	}

	if !strings.Contains(buffer.String(), "│") {
		t.Errorf("a two-column section lost its table:\n%s", buffer.String())
	}
}

// A section with no allowlist keeps every field; only the shape changes.
func TestUnknownWideSectionKeepsItsFields(t *testing.T) {
	input := decode(t, `{
		"$id": "x",
		"mystery": {"a":1,"b":2,"c":3,"d":4,"e":5,"f":6,"g":7,"h":8}
	}`)

	buffer := &bytes.Buffer{}
	renderer := &Renderer{Mode: ModeTable, Writer: buffer}
	if err := renderer.Render(input); err != nil {
		t.Fatal(err)
	}
	got := buffer.String()

	for _, key := range []string{"a", "b", "c", "d", "e", "f", "g", "h"} {
		if !strings.Contains(got, key) {
			t.Errorf("dropped %q from an unknown section:\n%s", key, got)
		}
	}
	if strings.Contains(got, "more fields") {
		t.Errorf("claimed to withhold fields it kept:\n%s", got)
	}
}

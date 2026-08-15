package output

import (
	"encoding/json"
	"fmt"
	"math"
	"strconv"

	"github.com/charmbracelet/lipgloss"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// dimStyle is the parenthesised aside -- the compact form of a number, the
// larger unit for a size. Dim because it repeats information already on the
// line in a form that is easier to read, and must not compete with it.
var dimStyle = lipgloss.NewStyle().Faint(true)

// Some embedded models arrive with far more fields than a reader asked for.
// `organization get` carries a 69-field billing plan, and printing all of it --
// as this used to, in a 69-column table -- produced a line no terminal could
// show.
//
// Allowlists rather than denylists, deliberately: the API keeps adding
// capability flags, and an allowlist stays correct without maintenance.
// Whatever is left out is counted in a footer rather than dropped silently, and
// `--raw` still shows everything.

// sectionField is one allowlisted field and how its number should read.
type sectionField struct {
	key    string
	format valueFormat
}

// valueFormat is the unit treatment a field's number gets.
type valueFormat struct {
	kind  string // "", "size", "count", "label"
	unit  string // for size: the unit the API reports in
	label string // for label: the suffix
}

// sectionAllowlists is the render order for each known section. Order here is
// the order the fields print in, which is not the API's order -- it is the
// order a reader wants them in.
var sectionAllowlists = map[string][]sectionField{
	"billingPlanDetails": {
		{key: "$id"},
		{key: "name"},
		{key: "group"},
		{key: "price"},
		{key: "currency"},
		{key: "trial", format: valueFormat{kind: "label", label: "days"}},
		{key: "bandwidth", format: valueFormat{kind: "size", unit: "GB"}},
		{key: "storage", format: valueFormat{kind: "size", unit: "GB"}},
		{key: "fileSize", format: valueFormat{kind: "size", unit: "MB"}},
		{key: "users", format: valueFormat{kind: "count"}},
		{key: "executions", format: valueFormat{kind: "count"}},
		{key: "GBHours", format: valueFormat{kind: "label", label: "GB-hours"}},
		{key: "databasesReads", format: valueFormat{kind: "count"}},
		{key: "databasesWrites", format: valueFormat{kind: "count"}},
		{key: "realtime", format: valueFormat{kind: "count"}},
		{key: "realtimeMessages", format: valueFormat{kind: "count"}},
		{key: "messages", format: valueFormat{kind: "count"}},
		{key: "domains", format: valueFormat{kind: "count"}},
	},
}

// sectionFields returns the rows to print for a section and how many fields
// were left out.
//
// A section with no allowlist keeps every field it has; the column-count
// fallback in renderHuman is what keeps those readable.
func sectionFields(section string, object *jsonx.Object) ([][2]string, int) {
	allowlist, known := sectionAllowlists[section]
	if !known {
		entries := make([][2]string, 0, object.Len())
		for _, key := range object.Keys() {
			value, _ := object.Get(key)
			entries = append(entries, [2]string{key, formatKeyValue(key, value)})
		}

		return entries, 0
	}

	entries := make([][2]string, 0, len(allowlist))
	for _, field := range allowlist {
		value, ok := object.Get(field.key)
		if !ok {
			continue
		}
		entries = append(entries, [2]string{field.key, formatSectionValue(field, value)})
	}

	return entries, object.Len() - len(entries)
}

// formatSectionValue applies the field's unit treatment, falling back to the
// ordinary key-based formatting when it has none.
func formatSectionValue(field sectionField, value any) string {
	number, ok := numericValue(value)
	if !ok {
		return formatKeyValue(field.key, value)
	}

	switch field.format.kind {
	case "size":
		return FormatSize(number, field.format.unit)
	case "count":
		return FormatCount(number)
	case "label":
		return FormatLabelled(number, field.format.label)
	}

	return formatKeyValue(field.key, value)
}

// sizeUnits is the ladder a size climbs, in the TypeScript's order and its
// factor of 1000 rather than 1024 -- these are the units the console shows.
var sizeUnits = []string{"MB", "GB", "TB", "PB"}

// FormatSize renders a size in the unit the API reports, adding a larger unit
// in parentheses when the number is big enough to be hard to read.
func FormatSize(amount float64, unit string) string {
	base := fmt.Sprintf("%s %s", trimTrailingZeros(amount), unit)

	index := -1
	for position, candidate := range sizeUnits {
		if candidate == unit {
			index = position

			break
		}
	}
	if index < 0 {
		return base
	}

	scaled := amount
	for scaled >= 1000 && index < len(sizeUnits)-1 {
		scaled /= 1000
		index++
	}

	if sizeUnits[index] == unit {
		return base
	}

	return base + " " + dimStyle.Render(
		fmt.Sprintf("(%s %s)", trimTrailingZeros(scaled), sizeUnits[index]))
}

// FormatCount adds a compact form to a number large enough to be miscounted at
// a glance. Ports formatCount (response-config.ts:144).
func FormatCount(amount float64) string {
	if math.Abs(amount) < 10000 {
		return trimTrailingZeros(amount)
	}

	return trimTrailingZeros(amount) + " " + dimStyle.Render("("+compactNumber(amount)+")")
}

// FormatLabelled suffixes a number with its unit.
func FormatLabelled(amount float64, label string) string {
	return trimTrailingZeros(amount) + " " + label
}

// compactNumber is Intl.NumberFormat("en", {notation: "compact"}) -- 200000
// becomes 200K, 3500000 becomes 3.5M -- to at most two fraction digits.
func compactNumber(amount float64) string {
	units := []struct {
		threshold float64
		suffix    string
	}{
		{1e12, "T"}, {1e9, "B"}, {1e6, "M"}, {1e3, "K"},
	}

	for _, unit := range units {
		if math.Abs(amount) >= unit.threshold {
			return trimTrailingZeros(
				math.Round(amount/unit.threshold*100)/100) + unit.suffix
		}
	}

	return trimTrailingZeros(amount)
}

// trimTrailingZeros prints a float without a pointless decimal tail.
func trimTrailingZeros(value float64) string {
	text := strconv.FormatFloat(value, 'f', -1, 64)

	return text
}

// numericValue reports a value as a float when it is one. Numbers arrive as
// json.Number because responses are decoded with UseNumber.
func numericValue(value any) (float64, bool) {
	switch typed := value.(type) {
	case json.Number:
		number, err := typed.Float64()

		return number, err == nil
	case float64:
		return typed, true
	case int:
		return float64(typed), true
	case int64:
		return float64(typed), true
	}

	return 0, false
}

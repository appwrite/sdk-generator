package output

import (
	"encoding/json"
	"fmt"
	"math"
	"os"
	"regexp"
	"strconv"
	"strings"

	"github.com/charmbracelet/lipgloss"
	"golang.org/x/term"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// A nested array otherwise renders as a table of whatever keys its rows carry,
// which for a deployment or a session is a dozen columns of mostly noise. These
// renderers give the sections worth special-casing a fixed set of columns.
//
// Keyed on the SECTION, not the command: the registry looks up the name of the
// nested field, so a renderer fires wherever that field appears.
//
// `invoices` and `paymentMethods` are absent because each belongs to a list
// model whose only command is excluded from the CLI.

const columnGap = "  "

// emDash is the placeholder for a value that is missing rather than empty.
const emDash = "—"

// structuredRow is one row's worth of a response section.
type structuredRow = *jsonx.Object

// column is one rendered column of a structured collection.
type column struct {
	header string
	value  func(row structuredRow, index int) string
}

// summarySchema decides whether a set of rows is the shape a renderer expects.
//
// Only two things actually gate rendering: a declared
// field must hold the type it declares when it is present, and at least one of
// requireAny must be present. Everything else passes through, matching zod's
// `.passthrough()`.
type summarySchema struct {
	// strings must be a string when present; numeric may be a string or a
	// number; booleans must be a bool.
	strings  []string
	numeric  []string
	booleans []string
	// mustExist is checked before anything else and has no null tolerance,
	// for the one schema built from a plain z.object rather than
	// createSummarySchema.
	mustExist []string
	// requireAny is the `.refine()`: at least one of these must be present.
	// Empty means no such check.
	requireAny []string
}

func (s summarySchema) matches(row structuredRow) bool {
	if row == nil {
		return false
	}

	for _, key := range s.mustExist {
		value, ok := row.Get(key)
		if !ok {
			return false
		}
		if _, isString := value.(string); !isString {
			return false
		}
	}

	for _, key := range s.strings {
		if !holdsKind(row, key, kindString) {
			return false
		}
	}

	for _, key := range s.numeric {
		if !holdsKind(row, key, kindNumeric) {
			return false
		}
	}

	for _, key := range s.booleans {
		if !holdsKind(row, key, kindBool) {
			return false
		}
	}

	if len(s.requireAny) == 0 {
		return true
	}

	for _, key := range s.requireAny {
		value, ok := row.Get(key)
		if ok && isPresent(value) {
			return true
		}
	}

	return false
}

type valueKind int

const (
	kindString valueKind = iota
	kindNumeric
	kindBool
)

// holdsKind allows absent and null, which every declared field in these
// schemas tolerates via `.nullable().optional()`.
func holdsKind(row structuredRow, key string, kind valueKind) bool {
	value, ok := row.Get(key)
	if !ok || value == nil {
		return true
	}

	switch kind {
	case kindString:
		_, isString := value.(string)

		return isString
	case kindNumeric:
		switch value.(type) {
		case string, json.Number:
			return true
		}

		return false
	case kindBool:
		_, isBool := value.(bool)

		return isBool
	}

	return false
}

// isPresent reports whether a value is worth rendering: null and whitespace do
// not count.
func isPresent(value any) bool {
	if value == nil {
		return false
	}

	if text, ok := value.(string); ok {
		return strings.TrimSpace(text) != ""
	}

	return true
}

func stringAt(row structuredRow, key string) (string, bool) {
	value, ok := row.Get(key)
	if !ok || value == nil {
		return "", false
	}

	switch typed := value.(type) {
	case string:
		return typed, true
	case json.Number:
		return typed.String(), true
	}

	return "", false
}

// compactText ports compactText: a non-string, or a blank one, becomes the
// fallback.
func compactText(row structuredRow, key string, fallback string) string {
	value, ok := row.Get(key)
	if !ok {
		return fallback
	}

	text, ok := value.(string)
	if !ok {
		return fallback
	}

	if trimmed := strings.TrimSpace(text); trimmed != "" {
		return trimmed
	}

	return fallback
}

// compactDate is the terse form used inside tables, distinct from
// FormatTimestamp: no relative suffix, and it only normalises the separator.
func compactDate(row structuredRow, key string) string {
	text, ok := stringAt(row, key)
	if !ok || strings.TrimSpace(text) == "" {
		return emDash
	}

	return strings.Replace(strings.Replace(text, "T", " ", 1), "+00:00", "Z", 1)
}

var trailingZero = regexp.MustCompile(`\.0$`)

// compactBytes renders a byte count in binary units, unlike the decimal ones
// formatSize uses for plan quotas -- a stored file is measured differently
// from a purchased allowance, and the two must stay apart.
func compactBytes(row structuredRow, key string) string {
	bytes, ok := numberAt(row, key)
	if !ok || bytes < 0 {
		return emDash
	}

	if bytes < 1024 {
		return fmt.Sprintf("%d B", int64(math.Round(bytes)))
	}

	units := []string{"KB", "MB", "GB", "TB"}
	amount := bytes / 1024
	index := 0

	for amount >= 1024 && index < len(units)-1 {
		amount /= 1024
		index++
	}

	return trailingZero.ReplaceAllString(strconv.FormatFloat(amount, 'f', 1, 64), "") +
		" " + units[index]
}

// compactDuration is NOT HumanizeSeconds: it always shows two units and pads
// with zeros, where HumanizeSeconds drops empty ones. Both are used; do not
// merge them.
func compactDuration(row structuredRow, key string) string {
	duration, ok := numberAt(row, key)
	if !ok || duration < 0 {
		return emDash
	}

	total := int64(math.Round(duration))
	hours := total / 3600
	minutes := (total % 3600) / 60
	seconds := total % 60

	switch {
	case hours > 0:
		return fmt.Sprintf("%dh %dm", hours, minutes)
	case minutes > 0:
		return fmt.Sprintf("%dm %ds", minutes, seconds)
	default:
		return fmt.Sprintf("%ds", seconds)
	}
}

// numberAt accepts the number-or-numeric-string the schemas allow.
func numberAt(row structuredRow, key string) (float64, bool) {
	value, ok := row.Get(key)
	if !ok || value == nil {
		return 0, false
	}

	switch typed := value.(type) {
	case json.Number:
		parsed, err := typed.Float64()

		return parsed, err == nil && !math.IsNaN(parsed) && !math.IsInf(parsed, 0)
	case string:
		if strings.TrimSpace(typed) == "" {
			return 0, false
		}
		parsed, err := strconv.ParseFloat(strings.TrimSpace(typed), 64)

		return parsed, err == nil && !math.IsNaN(parsed) && !math.IsInf(parsed, 0)
	}

	return 0, false
}

var camelBoundary = regexp.MustCompile(`([a-z0-9])([A-Z])`)
var wordSeparator = regexp.MustCompile(`[\s_-]+`)

// toTitleCase ports toTitleCase: split on camel humps and separators, then
// capitalise each word and lowercase its tail.
func toTitleCase(value string) string {
	spaced := camelBoundary.ReplaceAllString(value, "$1 $2")

	var words []string
	for _, part := range wordSeparator.Split(spaced, -1) {
		if part == "" {
			continue
		}
		words = append(words,
			strings.ToUpper(part[:1])+strings.ToLower(part[1:]))
	}

	return strings.Join(words, " ")
}

func indexedLabel(label string, index int) string {
	return fmt.Sprintf("[%d] %s", index+1, label)
}

var (
	statusReady    = lipgloss.NewStyle().Foreground(lipgloss.Color("2"))
	statusFailed   = lipgloss.NewStyle().Foreground(lipgloss.Color("1"))
	statusPending  = lipgloss.NewStyle().Foreground(lipgloss.Color("3"))
	statusCanceled = lipgloss.NewStyle().Faint(true)
	headerStyle    = lipgloss.NewStyle().Bold(true).Foreground(lipgloss.Color("6"))
	enabledStyle   = lipgloss.NewStyle().Foreground(lipgloss.Color("2"))
	disabledStyle  = lipgloss.NewStyle().Faint(true)
)

func deploymentStatus(row structuredRow) string {
	status := compactText(row, "status", "unknown")

	switch strings.ToLower(status) {
	case "ready":
		return statusReady.Render(status)
	case "failed":
		return statusFailed.Render(status)
	case "waiting", "processing", "building":
		return statusPending.Render(status)
	case "canceled":
		return statusCanceled.Render(status)
	default:
		return status
	}
}

func runtimeLabel(row structuredRow) string {
	name := compactText(row, "name", "")
	key := compactText(row, "key", "")

	runtime := name
	if runtime == "" {
		runtime = "Runtime"
		if key != "" {
			runtime = toTitleCase(key)
		}
	}

	version, ok := stringAt(row, "version")
	if !ok || strings.TrimSpace(version) == "" {
		return runtime
	}

	return runtime + " " + version
}

// clientDescription is the "Chrome on macOS" cell shared by sessions and logs.
func clientDescription(row structuredRow, withDevice bool) string {
	client := compactText(row, "clientName", "")
	os := compactText(row, "osName", "")
	device := ""
	if withDevice {
		device = compactText(row, "deviceName", "")
	}

	switch {
	case client != "" && os != "":
		return client + " on " + os
	case withDevice && client != "" && device != "":
		return client + " on " + device
	case client != "":
		return client
	case os != "":
		return os
	case device != "":
		return device
	default:
		return emDash
	}
}

// location prefers the country name and falls back to its code.
func location(row structuredRow) string {
	if name := compactText(row, "countryName", ""); name != "" {
		return name
	}

	return compactText(row, "countryCode", emDash)
}

type structuredRenderer struct {
	schema  summarySchema
	columns []column
}

var structuredRenderers = map[string]structuredRenderer{
	"deployments": {
		schema: summarySchema{
			mustExist: []string{"$id", "status"},
			strings:   []string{"type"},
			numeric:   []string{"totalSize", "buildDuration"},
			booleans:  []string{"activate"},
		},
		columns: []column{
			{"deployment", func(row structuredRow, index int) string {
				return indexedLabel(compactText(row, "$id", emDash), index)
			}},
			{"status", func(row structuredRow, _ int) string { return deploymentStatus(row) }},
			{"type", func(row structuredRow, _ int) string { return compactText(row, "type", emDash) }},
			{"auto-activate", func(row structuredRow, _ int) string {
				if activate, _ := row.Get("activate"); activate == true {
					return "yes"
				}

				return "no"
			}},
			{"size", func(row structuredRow, _ int) string { return compactBytes(row, "totalSize") }},
			{"build", func(row structuredRow, _ int) string { return compactDuration(row, "buildDuration") }},
		},
	},
	"runtimes": {
		schema: summarySchema{
			strings:    []string{"$id", "key", "name", "base", "image", "logo"},
			numeric:    []string{"version"},
			requireAny: []string{"$id", "name", "version", "base", "image"},
		},
		columns: []column{
			{"runtime", func(row structuredRow, index int) string {
				return indexedLabel(runtimeLabel(row), index)
			}},
			{"id", func(row structuredRow, _ int) string { return compactText(row, "$id", emDash) }},
			{"base", func(row structuredRow, _ int) string { return compactText(row, "base", emDash) }},
			{"image", func(row structuredRow, _ int) string { return compactText(row, "image", emDash) }},
		},
	},
	"identities": {
		schema: summarySchema{
			strings:    []string{"provider", "providerEmail", "providerAccessTokenExpiry"},
			numeric:    []string{"providerUid"},
			requireAny: []string{"provider", "providerUid", "providerEmail"},
		},
		columns: []column{
			{"identity", func(row structuredRow, index int) string {
				return indexedLabel(toTitleCase(compactText(row, "provider", "Identity")), index)
			}},
			{"account", func(row structuredRow, _ int) string {
				if email := compactText(row, "providerEmail", ""); email != "" {
					return email
				}
				if uid, ok := stringAt(row, "providerUid"); ok {
					return "uid " + uid
				}

				return emDash
			}},
			{"identifier", func(row structuredRow, _ int) string {
				// Only when BOTH are present: the uid is a disambiguator here,
				// and it already filled the account column when there was no
				// email to show.
				email := compactText(row, "providerEmail", "")
				uid, hasUID := stringAt(row, "providerUid")
				if email != "" && hasUID {
					return "uid " + uid
				}

				return ""
			}},
			{"expires", func(row structuredRow, _ int) string {
				if _, ok := stringAt(row, "providerAccessTokenExpiry"); !ok {
					return ""
				}

				return "expires " + compactDate(row, "providerAccessTokenExpiry")
			}},
		},
	},
	"sessions": {
		schema: summarySchema{
			strings: []string{
				"provider", "expire", "clientName", "clientType",
				"osName", "deviceName", "countryName", "countryCode",
			},
			booleans:   []string{"current"},
			requireAny: []string{"provider", "expire"},
		},
		columns: []column{
			{"session", func(row structuredRow, index int) string {
				label := toTitleCase(compactText(row, "provider", "Session"))
				if current, _ := row.Get("current"); current == true {
					label += " (current)"
				}

				return indexedLabel(label, index)
			}},
			{"client", func(row structuredRow, _ int) string { return clientDescription(row, true) }},
			{"location", func(row structuredRow, _ int) string { return location(row) }},
			{"expires", func(row structuredRow, _ int) string {
				return "expires " + compactDate(row, "expire")
			}},
		},
	},
	"logs": {
		schema: summarySchema{
			strings: []string{
				"event", "time", "clientName", "osName",
				"countryName", "countryCode", "mode",
			},
			requireAny: []string{"event", "time"},
		},
		columns: []column{
			{"time", func(row structuredRow, _ int) string { return compactDate(row, "time") }},
			{"event", func(row structuredRow, _ int) string {
				event := compactText(row, "event", "event")
				mode := compactText(row, "mode", "")
				if mode != "" && mode != "default" {
					return event + " (" + mode + ")"
				}

				return event
			}},
			{"client", func(row structuredRow, _ int) string { return clientDescription(row, false) }},
			{"location", func(row structuredRow, _ int) string { return location(row) }},
		},
	},
}

// RenderStructuredCollection writes a section as a fixed-column table.
//
// Reports false when the section has no renderer and the rows are not toggles
// either, which is the caller's signal to fall back to the generic table.
func RenderStructuredCollection(section string, rows []structuredRow, indent string) (string, bool) {
	renderer, ok := structuredRenderers[section]
	if !ok {
		return renderToggleCollection(rows, indent)
	}

	// One row of the wrong shape sends the whole section to the fallback. A
	// half-structured table would be worse than a plain one.
	for _, row := range rows {
		if !renderer.schema.matches(row) {
			return renderToggleCollection(rows, indent)
		}
	}

	columns := make([][]string, 0, len(renderer.columns))
	headers := make([]string, 0, len(renderer.columns))
	for _, definition := range renderer.columns {
		cells := make([]string, 0, len(rows))
		for index, row := range rows {
			cells = append(cells, definition.value(row, index))
		}
		columns = append(columns, cells)
		headers = append(headers, definition.header)
	}

	return renderAlignedColumns(columns, headers, indent), true
}

// renderAlignedColumns pads every column but the last to its widest cell.
//
// Widths are measured with lipgloss.Width rather than len: a cell can hold
// styling and non-ASCII -- the em-dash fallback does -- and both make the byte
// count the wrong number.
func renderAlignedColumns(columns [][]string, headers []string, indent string) string {
	if len(columns) == 0 || len(columns[0]) == 0 {
		return ""
	}

	widths := make([]int, len(columns))
	for index, cells := range columns {
		widest := lipgloss.Width(headers[index])
		for _, cell := range cells {
			if width := lipgloss.Width(cell); width > widest {
				widest = width
			}
		}
		widths[index] = widest
	}

	var builder strings.Builder

	writeRow := func(cells []string, style func(string) string) {
		parts := make([]string, 0, len(cells))
		for index, cell := range cells {
			rendered := cell
			if style != nil {
				rendered = style(cell)
			}
			if index == len(cells)-1 {
				parts = append(parts, rendered)

				continue
			}
			parts = append(parts, Pad(rendered, widths[index]))
		}
		builder.WriteString(
			strings.TrimRight(indent+strings.Join(parts, columnGap), " ") + "\n")
	}

	writeRow(headers, func(value string) string { return headerStyle.Render(value) })

	for rowIndex := range columns[0] {
		cells := make([]string, 0, len(columns))
		for _, column := range columns {
			cells = append(cells, column[rowIndex])
		}
		writeRow(cells, nil)
	}

	return strings.TrimRight(builder.String(), "\n")
}

// renderToggleCollection collapses rows that carry nothing but a name and an
// on/off switch -- authMethods, services, protocols -- into two wrapped lists.
// A two column table wastes a screen on them.
func renderToggleCollection(rows []structuredRow, indent string) (string, bool) {
	if len(rows) == 0 {
		return "", false
	}

	for _, row := range rows {
		if !isToggleRow(row) {
			return "", false
		}
	}

	var enabled, disabled []string
	for _, row := range rows {
		if value, _ := row.Get("enabled"); value == true {
			enabled = append(enabled, toggleLabel(row))
		} else {
			disabled = append(disabled, toggleLabel(row))
		}
	}

	type group struct {
		heading string
		labels  []string
		style   lipgloss.Style
	}

	var groups []group
	if len(enabled) > 0 {
		groups = append(groups, group{fmt.Sprintf("enabled (%d)", len(enabled)), enabled, enabledStyle})
	}
	if len(disabled) > 0 {
		groups = append(groups, group{fmt.Sprintf("disabled (%d)", len(disabled)), disabled, disabledStyle})
	}

	headingWidth := 0
	for _, entry := range groups {
		if width := lipgloss.Width(entry.heading); width > headingWidth {
			headingWidth = width
		}
	}

	// A floor of 40 columns, and an assumed 100 when the terminal width is
	// unknown, which is what a piped run gets.
	available := terminalWidth() - lipgloss.Width(indent) - headingWidth - len(columnGap)
	if available < 40 {
		available = 40
	}

	var builder strings.Builder
	for _, entry := range groups {
		for lineIndex, line := range wrapValues(entry.labels, available) {
			prefix := strings.Repeat(" ", headingWidth)
			if lineIndex == 0 {
				prefix = Pad(entry.style.Render(entry.heading), headingWidth)
			}
			builder.WriteString(indent + prefix + columnGap + line + "\n")
		}
	}

	return strings.TrimRight(builder.String(), "\n"), true
}

// isToggleRow ports ToggleRowSchema, which is a STRICT object: an unexpected
// key disqualifies the row. That strictness is what stops a rich resource that
// happens to carry `enabled` from collapsing into a toggle list.
func isToggleRow(row structuredRow) bool {
	if row == nil {
		return false
	}

	enabled, ok := row.Get("enabled")
	if !ok {
		return false
	}
	if _, isBool := enabled.(bool); !isBool {
		return false
	}

	named := false
	for _, key := range row.Keys() {
		switch key {
		case "enabled":
		case "$id", "name", "key":
			value, _ := row.Get(key)
			if value == nil {
				continue
			}
			if _, isString := value.(string); !isString {
				return false
			}
			if isPresent(value) {
				named = true
			}
		default:
			return false
		}
	}

	return named
}

func toggleLabel(row structuredRow) string {
	for _, key := range []string{"$id", "key", "name"} {
		if value, ok := row.Get(key); ok && value != nil {
			return compactText(row, key, emDash)
		}
	}

	return emDash
}

// terminalWidth reports the usable width, falling back to 100 when the width is
// unknown -- which is what a piped or redirected run gets, and what makes the
// output reproducible in tests.
func terminalWidth() int {
	width, _, err := term.GetSize(int(os.Stdout.Fd()))
	if err != nil || width <= 0 {
		return 100
	}

	return width
}

// wrapValues packs comma-separated labels into lines no wider than width.
func wrapValues(values []string, width int) []string {
	var lines []string
	current := ""

	for index, value := range values {
		piece := value
		if index != len(values)-1 {
			piece += ","
		}

		if current == "" {
			current = piece

			continue
		}

		if lipgloss.Width(current+" "+piece) > width {
			lines = append(lines, current)
			current = piece

			continue
		}

		current = current + " " + piece
	}

	if current != "" {
		lines = append(lines, current)
	}

	return lines
}

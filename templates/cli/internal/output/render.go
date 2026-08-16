package output

import (
	"encoding/json"
	"fmt"
	"io"
	"regexp"
	"strings"

	"github.com/charmbracelet/lipgloss"
	"github.com/charmbracelet/lipgloss/table"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// Unlike --json and --raw this output is not scripted against, so the structure
// is what matters -- scalars first, then one section per nested value -- rather
// than exact spacing.
//
// Partially ported: the scalar half is in valueformat.go and `sectionFields` in
// sections.go. Still missing are per-resource selection of the top-level fields
// and the aligned-column renderers that lay several values out side by side.

var (
	sectionStyle = lipgloss.NewStyle().Bold(true).Underline(true).Foreground(lipgloss.Color("3"))
	labelStyle   = lipgloss.NewStyle().Bold(true)
	// hintStyle is the "N more fields" footer: present, but not competing with
	// the values above it.
	hintStyle = lipgloss.NewStyle().Faint(true)
)

// Render writes a response in whichever mode the renderer is set to.
func (r *Renderer) Render(value any) error {
	writer := r.Writer
	if writer == nil {
		return nil
	}

	redactor := &Redactor{ShowSecrets: r.ShowSecrets}

	switch r.Mode {
	case ModeRaw:
		// Big integers are quoted here too. --raw is not "the bytes verbatim":
		// the response is parsed and re-rendered, and leaving a big integer a
		// bare number would read back through most JSON parsers as a rounded
		// float, which is the loss the quoting exists to prevent.
		return RenderJSON(writer, quoteBigIntegers(redactor.Mask(value, "")))
	case ModeJSON:
		masked := redactor.Mask(value, "")
		if isGraphQLResponse(masked) {
			return RenderJSON(writer, quoteBigIntegers(masked))
		}
		if object, ok := masked.(*jsonx.Object); ok {
			return RenderJSON(writer, FilterData(object))
		}

		return RenderJSON(writer, masked)
	}

	return r.renderHuman(writer, redactor.Mask(value, ""))
}

// renderHuman prints scalars as a key/value block, then each nested value as
// its own titled section.
func (r *Renderer) renderHuman(writer io.Writer, value any) error {
	object, ok := value.(*jsonx.Object)
	if !ok {
		_, err := fmt.Fprintln(writer, formatScalar(value))

		return err
	}

	type section struct {
		key   string
		value any
	}

	listKey, listCount, listTotal, isDiscoveryList := discoveryListSummary(object)

	var (
		scalars  [][2]string
		sections []section
	)

	for _, key := range object.Keys() {
		if IsNormalViewHiddenKey(key) || (isDiscoveryList && key == "total") {
			continue
		}
		item, _ := object.Get(key)
		if item == nil {
			continue
		}

		switch typed := item.(type) {
		case []any:
			if len(typed) == 0 || allEmptyObjects(typed) {
				continue
			}
			sections = append(sections, section{key, typed})
		case *jsonx.Object:
			if typed.Len() == 0 {
				continue
			}
			sections = append(sections, section{key, typed})
		case string:
			if strings.TrimSpace(typed) == "" {
				continue
			}
			// Through formatKeyValue, not appended raw: a string is where the
			// timestamps live, and this branch bypassing it is why they used
			// to print as the API sent them.
			scalars = append(scalars, [2]string{key, formatKeyValue(key, typed)})
		default:
			scalars = append(scalars, [2]string{key, formatKeyValue(key, item)})
		}
	}

	printed := false
	if len(scalars) > 0 {
		writeKeyValues(writer, scalars, "")
		printed = true
	}

	for _, item := range sections {
		if printed {
			fmt.Fprintln(writer, " ")
		}

		switch typed := item.value.(type) {
		case []any:
			rows := objectRows(typed)
			if len(rows) > 0 {
				title := fmt.Sprintf("%s (%d)", item.key, len(typed))
				if isDiscoveryList && item.key == listKey {
					title = fmt.Sprintf("%s (%d of %s)", item.key, listCount, listTotal)
				}
				fmt.Fprintln(writer, sectionStyle.Render(title))
				// A section with a renderer of its own, or one whose rows are
				// plain on/off toggles, gets a shape built for it. Everything
				// else falls back to the generic table -- unless that table
				// would be too wide to read, in which case each row is printed
				// as key/value instead.
				if rendered, ok := RenderStructuredCollection(item.key, rows, "  "); ok {
					fmt.Fprintln(writer, rendered)
				} else if columnCount(rows) > maximumColumns {
					writeRowsAsKeyValues(writer, item.key, rows, "  ")
				} else {
					fmt.Fprintln(writer, renderTable(rows))
				}
			} else {
				fmt.Fprintln(writer, sectionStyle.Render(item.key))
				for _, entry := range typed {
					fmt.Fprintf(writer, "  %s\n", formatScalar(entry))
				}
			}
		case *jsonx.Object:
			fmt.Fprintln(writer, sectionStyle.Render(item.key))
			kept, withheld := sectionFields(item.key, FilterObject(typed))
			writeKeyValues(writer, kept, "  ")
			writeWithheldNote(writer, withheld, "  ")
		}
		printed = true
	}

	if !printed {
		if isDiscoveryList {
			fmt.Fprintf(writer, "No %s found.\n", listKey)
		} else {
			fmt.Fprintln(writer, "Request completed successfully.")
		}
	}

	return nil
}

// discoveryListSummary recognizes the two normalized session discovery lists.
// Their total belongs in the collection heading, not in a detached key/value
// line whose relationship to the returned page is unclear.
func discoveryListSummary(object *jsonx.Object) (string, int, string, bool) {
	// A project or organization model can itself contain a `total` and a
	// `projects` field. Only the two-key list envelope is a discovery list.
	if object.Len() != 2 {
		return "", 0, "", false
	}

	for _, key := range []string{"organizations", "projects"} {
		value, ok := object.Get(key)
		if !ok {
			continue
		}
		items, ok := value.([]any)
		if !ok {
			continue
		}
		total, ok := object.Get("total")
		if !ok {
			continue
		}

		return key, len(items), formatScalar(total), true
	}

	return "", 0, "", false
}

// objectRows returns the elements that are objects, flattened for display.
// Returns nil when the array is not a list of objects.
func objectRows(items []any) []*jsonx.Object {
	rows := make([]*jsonx.Object, 0, len(items))
	for _, item := range items {
		object, ok := item.(*jsonx.Object)
		if !ok {
			return nil
		}
		rows = append(rows, FilterObject(object))
	}

	return rows
}

func allEmptyObjects(items []any) bool {
	for _, item := range items {
		object, ok := item.(*jsonx.Object)
		if !ok || object.Len() > 0 {
			return false
		}
	}

	return true
}

// RenderTable draws a headers-and-rows table in the CLI's one table style.
//
// Exported so the hand-built tables -- the push change list, which is not a
// list of API objects -- render the same way as everything else instead of
// each aligning columns their own way.
func RenderTable(headers []string, rows [][]string) string {
	return drawTable(headers, rows)
}

// renderTable lays rows out as a table, using the union of their keys in
// first-seen order so a field missing from the first row is not dropped.
func renderTable(rows []*jsonx.Object) string {
	headers := []string{}
	seen := map[string]bool{}
	for _, row := range rows {
		for _, key := range row.Keys() {
			if !seen[key] {
				seen[key] = true
				headers = append(headers, key)
			}
		}
	}
	if len(headers) == 0 {
		return ""
	}

	data := make([][]string, 0, len(rows))
	for _, row := range rows {
		cells := make([]string, 0, len(headers))
		for _, header := range headers {
			value, _ := row.Get(header)
			cells = append(cells, formatKeyValue(header, value))
		}
		data = append(data, cells)
	}

	return drawTable(headers, data)
}

func drawTable(headers []string, data [][]string) string {
	// No outer box: a row is a value rather than a value wrapped in pipes, and
	// double-clicking an id in a boxed table selects the borders with it. A
	// single-column table therefore has no vertical rule at all.
	return table.New().
		Border(lipgloss.NormalBorder()).
		BorderTop(false).
		BorderBottom(false).
		BorderLeft(false).
		BorderRight(false).
		BorderColumn(true).
		BorderHeader(true).
		BorderStyle(tableBorderStyle).
		StyleFunc(tableCellStyle).
		Headers(headers...).
		Rows(data...).
		Render()
}

// tableBorderStyle paints the remaining rules cyan.
var tableBorderStyle = lipgloss.NewStyle().Foreground(lipgloss.Color("6"))

// tableCellStyle pads each cell by one column, which the captured tables do by
// default and which the border configuration does not change. Without it the
// values sit flush against the separators and are hard to read.
func tableCellStyle(row, _ int) lipgloss.Style {
	style := lipgloss.NewStyle().Padding(0, 1)
	if row == table.HeaderRow {
		return style.Bold(true).Italic(true).Foreground(lipgloss.Color("6"))
	}

	return style
}

// formatKeyValue renders one value for display, using the key to decide
// whether it deserves more than its raw form.
//
// The key matters: `status` reads as
// active/inactive rather than true/false, and a `*duration` number is seconds
// that nobody parses at a glance. Anything else falls through to formatScalar.
func formatKeyValue(key string, value any) string {
	switch typed := value.(type) {
	case bool:
		if key == "status" {
			if typed {
				return "active"
			}

			return "inactive"
		}
	case json.Number:
		// Durations come over the wire as raw seconds. The raw value stays --
		// it is what the API returned and what a reader may need to pass back
		// -- with the readable form beside it.
		if durationKey.MatchString(key) {
			if seconds, err := typed.Float64(); err == nil {
				if humanized := HumanizeSeconds(seconds); humanized != "" {
					return typed.String() + " (" + humanized + ")"
				}
			}
		}
	case string:
		if stamp, ok := FormatTimestamp(typed); ok {
			return stamp
		}
	}

	return formatScalar(value)
}

// A suffix test rather than a contains test: `durationLimit` is not a
// duration.
var durationKey = regexp.MustCompile(`(?i)duration$`)

// formatScalar renders one value for display.
func formatScalar(value any) string {
	switch typed := value.(type) {
	case nil:
		return ""
	case string:
		return typed
	case bool:
		if typed {
			return "true"
		}

		return "false"
	case json.Number:
		return typed.String()
	}

	return fmt.Sprint(value)
}

// maximumColumns is where a table stops being readable.
//
// `organization get` embeds a plan with 69 fields, and rendering that as one
// row of 69 columns produced a line no
// terminal could show. Past this width each row is printed as key/value
// instead, which stays readable at any size.
const maximumColumns = 6

// columnCount is the number of distinct keys across a section's rows.
func columnCount(rows []*jsonx.Object) int {
	seen := map[string]bool{}
	for _, row := range rows {
		for _, key := range row.Keys() {
			seen[key] = true
		}
	}

	return len(seen)
}

// writeKeyValues prints aligned `label : value` lines.
func writeKeyValues(writer io.Writer, entries [][2]string, indent string) {
	width := 0
	for _, entry := range entries {
		if len(entry[0]) > width {
			width = len(entry[0])
		}
	}

	for _, entry := range entries {
		fmt.Fprintf(writer, "%s%s %s\n", indent,
			labelStyle.Render(fmt.Sprintf("%-*s :", width, entry[0])), entry[1])
	}
}

// writeRowsAsKeyValues prints each row of a too-wide section as its own block.
func writeRowsAsKeyValues(writer io.Writer, section string, rows []*jsonx.Object, indent string) {
	for index, row := range rows {
		if index > 0 {
			fmt.Fprintln(writer)
		}
		rowIndent := indent
		// Numbered only when there is more than one, so a single embedded
		// object reads as a plain block.
		if len(rows) > 1 {
			fmt.Fprintf(writer, "%s%s\n", indent, sectionStyle.Render(fmt.Sprintf("[%d]", index+1)))
			rowIndent = indent + "  "
		}

		kept, withheld := sectionFields(section, row)
		writeKeyValues(writer, kept, rowIndent)
		writeWithheldNote(writer, withheld, rowIndent)
	}
}

// writeWithheldNote says how many fields were left out, and how to see them.
func writeWithheldNote(writer io.Writer, count int, indent string) {
	if count <= 0 {
		return
	}

	label := "fields"
	if count == 1 {
		label = "field"
	}

	fmt.Fprintf(writer, "%s%s\n", indent,
		hintStyle.Render(fmt.Sprintf("… %d more %s — pass --raw to show all", count, label)))
}

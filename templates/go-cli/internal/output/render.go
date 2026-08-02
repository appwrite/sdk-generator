package output

import (
	"encoding/json"
	"fmt"
	"io"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
	"github.com/charmbracelet/lipgloss"
	"github.com/charmbracelet/lipgloss/table"
)

// Ports the human-readable half of templates/cli/lib/parser.ts:parse().
//
// Unlike --json and --raw this output is explicitly NOT part of the contract
// (docs/go-cli/PLAN.md §3), so the structure is reproduced -- scalars first,
// then one section per nested value -- without chasing the TypeScript's exact
// spacing.
//
// NOT YET PORTED: response-config.ts, which supplies per-resource field
// selection, timestamp formatting and duration humanising. Values render as
// their raw JSON scalars until that lands.

var (
	sectionStyle = lipgloss.NewStyle().Bold(true).Underline(true).Foreground(lipgloss.Color("3"))
	labelStyle   = lipgloss.NewStyle().Bold(true)
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
		return RenderJSON(writer, redactor.Mask(value, ""))
	case ModeJSON:
		masked := redactor.Mask(value, "")
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

	var (
		scalars  [][2]string
		sections []section
	)

	for _, key := range object.Keys() {
		if IsNormalViewHiddenKey(key) {
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
			scalars = append(scalars, [2]string{key, typed})
		default:
			scalars = append(scalars, [2]string{key, formatScalar(item)})
		}
	}

	printed := false
	if len(scalars) > 0 {
		width := 0
		for _, entry := range scalars {
			if len(entry[0]) > width {
				width = len(entry[0])
			}
		}
		for _, entry := range scalars {
			fmt.Fprintf(writer, "%s %s\n",
				labelStyle.Render(fmt.Sprintf("%-*s :", width, entry[0])), entry[1])
		}
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
				fmt.Fprintln(writer, sectionStyle.Render(fmt.Sprintf("%s (%d)", item.key, len(typed))))
				fmt.Fprintln(writer, renderTable(rows))
			} else {
				fmt.Fprintln(writer, sectionStyle.Render(item.key))
				for _, entry := range typed {
					fmt.Fprintf(writer, "  %s\n", formatScalar(entry))
				}
			}
		case *jsonx.Object:
			fmt.Fprintln(writer, sectionStyle.Render(item.key))
			fmt.Fprintln(writer, renderTable([]*jsonx.Object{FilterObject(typed)}))
		}
		printed = true
	}

	if !printed {
		fmt.Fprintln(writer, "Request completed successfully.")
	}

	return nil
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
			cells = append(cells, formatScalar(value))
		}
		data = append(data, cells)
	}

	return table.New().
		Border(lipgloss.NormalBorder()).
		Headers(headers...).
		Rows(data...).
		Render()
}

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

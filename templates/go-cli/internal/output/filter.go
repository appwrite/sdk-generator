package output

import (
	"encoding/json"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
)

// Ports the field-selection half of templates/cli/lib/parser.ts.
//
// FilterData decides what --json shows, so it is part of invariant 4 rather
// than cosmetic.

// normalViewHiddenKeys are dropped from the human-readable view.
//
// Internal bookkeeping that carries no meaning for a CLI reader, plus fields
// the API returns twice under two names (`billingPlanId` === `billingPlan`).
//
// Ports NORMAL_VIEW_HIDDEN_KEYS (templates/cli/lib/parser.ts:84).
var normalViewHiddenKeys = map[string]bool{
	"onboarding":    true,
	"billingPlanId": true,
}

// IsNormalViewHiddenKey reports whether a field is hidden from the table view.
//
// `$`-prefixed keys are Appwrite's internal metadata and are hidden, except
// `$id`, which is the one a user actually needs.
func IsNormalViewHiddenKey(key string) bool {
	if strings.HasPrefix(key, "$") && key != "$id" {
		return true
	}

	return normalViewHiddenKeys[key]
}

// FilterObject flattens one object for display: scalars survive, nested objects
// and blank strings are dropped.
//
// Ports filterObject().
func FilterObject(object *jsonx.Object) *jsonx.Object {
	result := jsonx.NewObject()

	for _, key := range object.Keys() {
		value, _ := object.Get(key)
		if value == nil {
			continue
		}
		if number, ok := value.(json.Number); ok {
			result.Set(key, number.String())

			continue
		}
		switch typed := value.(type) {
		case *jsonx.Object, []any:
			_ = typed

			continue
		case string:
			if strings.TrimSpace(typed) == "" {
				continue
			}
		}
		result.Set(key, value)
	}

	return result
}

// FilterData prepares a response for --json.
//
// Scalars survive. Arrays survive with their object elements flattened by
// FilterObject. Nested objects, nulls and blank strings are dropped. Numbers
// become strings, which looks odd but is what the TypeScript does: json-bigint
// yields BigNumber instances and `String(value)` is how they are rendered.
//
// Ports filterData().
func FilterData(data *jsonx.Object) *jsonx.Object {
	result := jsonx.NewObject()

	for _, key := range data.Keys() {
		value, _ := data.Get(key)
		if value == nil {
			continue
		}

		if number, ok := value.(json.Number); ok {
			result.Set(key, number.String())

			continue
		}

		switch typed := value.(type) {
		case []any:
			items := make([]any, 0, len(typed))
			for _, item := range typed {
				if number, ok := item.(json.Number); ok {
					items = append(items, number.String())

					continue
				}
				if object, ok := item.(*jsonx.Object); ok {
					items = append(items, FilterObject(object))

					continue
				}
				items = append(items, item)
			}
			result.Set(key, items)
		case *jsonx.Object:
			continue
		case string:
			if strings.TrimSpace(typed) == "" {
				continue
			}
			result.Set(key, typed)
		default:
			result.Set(key, value)
		}
	}

	return result
}

// ApplyDisplayFields narrows rows to the fields named by --display-field.
//
// A row that has none of the requested fields is returned whole rather than
// blank: showing nothing would look like the row does not exist.
//
// Ports applyDisplayFilter().
func ApplyDisplayFields(rows []*jsonx.Object, fields []string) []*jsonx.Object {
	if len(fields) == 0 {
		return rows
	}

	filtered := make([]*jsonx.Object, 0, len(rows))
	for _, row := range rows {
		narrowed := jsonx.NewObject()
		for _, field := range fields {
			if value, ok := row.Get(field); ok {
				narrowed.Set(field, value)
			}
		}
		if narrowed.Len() == 0 {
			filtered = append(filtered, row)

			continue
		}
		filtered = append(filtered, narrowed)
	}

	return filtered
}

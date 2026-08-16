package output

import (
	"encoding/json"
	"strconv"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// jsSafeInteger is the largest integer JavaScript represents exactly, 2^53 - 1.
//
// Anything inside this range is a plain number; anything outside it cannot
// survive a JavaScript consumer as a number, so it is rendered as a string.
const jsSafeInteger = 1<<53 - 1

// renderedAsString reports whether this number has to be rendered as a
// string.
//
// An earlier version read "a big integer is rendered as a string" as "every
// number becomes a string", because with UseNumber() every number arrives as a
// json.Number. That turned `"total": 0` into `"total": "0"` and
// broke any consumer doing arithmetic on --json output.
func renderedAsString(number json.Number) bool {
	text := number.String()

	// Only integer literals are ever promoted; a float stays a float however
	// large it is.
	if strings.ContainsAny(text, ".eE") {
		return false
	}

	value, err := strconv.ParseInt(text, 10, 64)
	if err != nil {
		// Too many digits for an int64, so certainly past 2^53.
		return true
	}

	return value > jsSafeInteger || value < -jsSafeInteger
}

// filteredNumber returns a number as --json should carry it.
func filteredNumber(number json.Number) any {
	if renderedAsString(number) {
		return number.String()
	}

	return number
}

// FilterData decides what --json shows, so it is part of invariant 4 rather
// than cosmetic.

// normalViewHiddenKeys are dropped from the human-readable view.
//
// Internal bookkeeping that carries no meaning for a CLI reader, plus fields
// the API returns twice under two names (`billingPlanId` === `billingPlan`).
var normalViewHiddenKeys = map[string]bool{
	"onboarding":    true,
	"billingPlanId": true,
	"prefs":         true,
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
func FilterObject(object *jsonx.Object) *jsonx.Object {
	result := jsonx.NewObject()

	for _, key := range object.Keys() {
		value, _ := object.Get(key)
		if value == nil {
			continue
		}
		if number, ok := value.(json.Number); ok {
			result.Set(key, filteredNumber(number))

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

var graphQLResponseKeys = map[string]bool{
	"data":       true,
	"errors":     true,
	"extensions": true,
}

func isGraphQLResponse(value any) bool {
	switch typed := value.(type) {
	case *jsonx.Object:
		return isGraphQLResponseObject(typed)
	case map[string]any:
		return isGraphQLResponseMap(typed)
	case []any:
		if len(typed) == 0 {
			return false
		}
		for _, item := range typed {
			if !isGraphQLResponse(item) {
				return false
			}
		}

		return true
	}

	return false
}

func isGraphQLResponseObject(object *jsonx.Object) bool {
	return isGraphQLResponseKeys(object.Keys())
}

func isGraphQLResponseMap(object map[string]any) bool {
	keys := make([]string, 0, len(object))
	for key := range object {
		keys = append(keys, key)
	}

	return isGraphQLResponseKeys(keys)
}

func isGraphQLResponseKeys(keys []string) bool {
	if len(keys) == 0 {
		return false
	}

	hasPayload := false
	for _, key := range keys {
		if !graphQLResponseKeys[key] {
			return false
		}
		if key == "data" || key == "errors" {
			hasPayload = true
		}
	}

	return hasPayload
}

// FilterData prepares a response for --json.
//
// Scalars survive. Arrays survive with their object elements flattened by
// FilterObject. Nested objects, nulls and blank strings are dropped. Integers
// past 2^53 become strings; see renderedAsString.
//
// GraphQL is the exception: its response envelope is user-selected data under
// `data` and/or `errors`, so flattening would erase successful responses.
func FilterData(data *jsonx.Object) *jsonx.Object {
	if isGraphQLResponseObject(data) {
		filtered, _ := quoteBigIntegers(data).(*jsonx.Object)

		return filtered
	}

	result := jsonx.NewObject()

	for _, key := range data.Keys() {
		value, _ := data.Get(key)
		if value == nil {
			continue
		}

		if number, ok := value.(json.Number); ok {
			result.Set(key, filteredNumber(number))

			continue
		}

		switch typed := value.(type) {
		case []any:
			items := make([]any, 0, len(typed))
			for _, item := range typed {
				if number, ok := item.(json.Number); ok {
					items = append(items, filteredNumber(number))

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

// quoteBigIntegers walks a decoded response and renders integers past 2^53 as
// strings, leaving every other value alone.
//
// --raw keeps whatever the API sent, so unlike FilterData this drops nothing
// and reshapes nothing; it only makes the same precision decision.
func quoteBigIntegers(value any) any {
	switch typed := value.(type) {
	case json.Number:
		return filteredNumber(typed)
	case *jsonx.Object:
		result := jsonx.NewObject()
		for _, key := range typed.Keys() {
			nested, _ := typed.Get(key)
			result.Set(key, quoteBigIntegers(nested))
		}

		return result
	case map[string]any:
		result := make(map[string]any, len(typed))
		for key, nested := range typed {
			result[key] = quoteBigIntegers(nested)
		}

		return result
	case []any:
		items := make([]any, 0, len(typed))
		for _, item := range typed {
			items = append(items, quoteBigIntegers(item))
		}

		return items
	}

	return value
}

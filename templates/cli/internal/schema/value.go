package schema

import (
	"bytes"
	"encoding/json"
	"fmt"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// JavaScript value semantics, reproduced because the diff depends on them.
//
// Attributes are compared both as rendered strings and as JSON. Both forms
// distinguish a property that is ABSENT from one that is present and null, and
// both render arrays and numbers in ways Go's fmt does not. Any of it wrong
// shows up as a spurious recreation of a live
// attribute -- which destroys data -- so the semantics are modelled explicitly
// rather than approximated.

// undefinedValue stands for a property JavaScript would read as `undefined`.
//
// A distinct type, not nil: `"default": null` in a config means "no default"
// and IS sent to the API, while an absent `default` is not sent at all.
// Collapsing the two would change the request body.
type undefinedValue struct{}

var undefined = undefinedValue{}

// field reads a property the way JavaScript would, yielding undefined when the
// key is absent.
func field(object *jsonx.Object, key string) any {
	if value, present := object.Get(key); present {
		return value
	}

	return undefined
}

// isEmpty reports whether a value counts as absent for comparison.
//
// Null, undefined, an all-whitespace string
// and an empty array all read as "nothing", so a field the API omits does not
// register as a change against a config that leaves it blank.
func isEmpty(value any) bool {
	switch typed := value.(type) {
	case nil, undefinedValue:
		return true
	case string:
		return strings.TrimSpace(typed) == ""
	case []any:
		return len(typed) == 0
	}

	return false
}

// isObject reports whether `typeof value === "object" && value !== null`.
//
// Arrays are objects in JavaScript, and so is a nested document. undefined is
// not, which is why it falls through to the string comparison instead.
func isObject(value any) bool {
	switch value.(type) {
	case []any, *jsonx.Object:
		return true
	}

	return false
}

// jsString renders a value as JavaScript's String() would.
func jsString(value any) string {
	switch typed := value.(type) {
	case undefinedValue:
		return "undefined"
	case nil:
		return "null"
	case string:
		return typed
	case bool:
		if typed {
			return "true"
		}

		return "false"
	case json.Number:
		return typed.String()
	case []any:
		// Array.prototype.toString joins with a comma and renders null and
		// undefined entries as the empty string.
		parts := make([]string, 0, len(typed))
		for _, item := range typed {
			if item == nil {
				parts = append(parts, "")

				continue
			}
			parts = append(parts, jsString(item))
		}

		return strings.Join(parts, ",")
	case *jsonx.Object:
		return "[object Object]"
	}

	return fmt.Sprint(value)
}

// jsStringify renders a value as JSON.stringify would.
//
// Only ever used to compare two values, never to build a request body, so an
// encoding failure degrades to the string form rather than propagating an error
// the caller has no way to act on.
func jsStringify(value any) string {
	if _, isUndefined := value.(undefinedValue); isUndefined {
		return "undefined"
	}

	var buffer bytes.Buffer
	encoder := json.NewEncoder(&buffer)
	encoder.SetEscapeHTML(false)
	if err := encoder.Encode(value); err != nil {
		return jsString(value)
	}

	return strings.TrimRight(buffer.String(), "\n")
}

// isEqual compares two values with JavaScript's equality semantics.
//
// A structural comparison for two objects, a string comparison for everything
// else -- so the number 128 and the string "128" are equal here. That looseness
// is deliberate: a hand-written config carries strings where the API returns
// numbers, and treating those as a difference would recreate the attribute.
func isEqual(first, second any) bool {
	if isObject(first) && isObject(second) {
		return jsStringify(first) == jsStringify(second)
	}

	return jsString(first) == jsString(second)
}

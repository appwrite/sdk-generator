package query

import (
	"encoding/json"
	"errors"
	"fmt"
	"regexp"
	"strings"
)

// The output is Appwrite query JSON, so it goes on the wire: the shapes here
// are a contract, not a formatting choice.

// Options are the query-building flags a list command exposes.
type Options struct {
	// Queries are raw Appwrite query strings from --queries.
	Queries []string

	Filter []string
	Where  []string

	SortAsc  []string
	SortDesc []string

	Limit        *int
	Offset       *int
	Select       []string
	CursorAfter  *string
	CursorBefore *string
}

// filterOperators are tried in order. Two-character operators come first, or
// `a >= 1` would match the `>` pattern and parse the attribute as `a >`.
var filterOperators = []struct {
	pattern *regexp.Regexp
	method  string
}{
	{regexp.MustCompile(`^(.+?)\s*!=\s*(.*)$`), "notEqual"},
	{regexp.MustCompile(`^(.+?)\s*>=\s*(.*)$`), "greaterThanEqual"},
	{regexp.MustCompile(`^(.+?)\s*<=\s*(.*)$`), "lessThanEqual"},
	{regexp.MustCompile(`^(.+?)\s*=\s*(.*)$`), "equal"},
	{regexp.MustCompile(`^(.+?)\s*>\s*(.*)$`), "greaterThan"},
	{regexp.MustCompile(`^(.+?)\s*<\s*(.*)$`), "lessThan"},
}

var numberPattern = regexp.MustCompile(`^-?(?:\d+|\d*\.\d+)(?:[eE][+-]?\d+)?$`)

// ErrInvalidFilter is returned for a filter expression the parser cannot read.
var ErrInvalidFilter = errors.New(
	"filters must use one of: field=value, field!=value, field>value, field>=value, field<value, field<=value")

// stringify renders one query in the wire format.
//
// Built through an ordered object rather than a Go map so `method`, `attribute`
// and `values` keep their order -- a map would sort them, changing the bytes
// the API receives.
func stringify(method string, attribute *string, values []any) (string, error) {
	var builder strings.Builder
	builder.WriteString(`{"method":`)

	encoded, err := marshal(method)
	if err != nil {
		return "", err
	}
	builder.Write(encoded)

	if attribute != nil {
		builder.WriteString(`,"attribute":`)
		encoded, err = marshal(*attribute)
		if err != nil {
			return "", err
		}
		builder.Write(encoded)
	}

	if values != nil {
		builder.WriteString(`,"values":`)
		encoded, err = marshal(values)
		if err != nil {
			return "", err
		}
		builder.Write(encoded)
	}

	builder.WriteString("}")

	return builder.String(), nil
}

// marshal encodes without Go's HTML escaping, so an attribute or value
// containing &, < or > reaches the API as the user typed it.
func marshal(value any) ([]byte, error) {
	var buffer strings.Builder
	encoder := json.NewEncoder(&buffer)
	encoder.SetEscapeHTML(false)
	if err := encoder.Encode(value); err != nil {
		return nil, err
	}

	return []byte(strings.TrimRight(buffer.String(), "\n")), nil
}

// ParseValue turns the right-hand side of a filter into a typed value.
//
// Implements parseQueryValue(): `true`, `false` and `null` are literals, anything
// numeric becomes a number, a bracketed string is parsed as a JSON array, and
// everything else stays a string.
func ParseValue(value string) (any, error) {
	normalized := strings.TrimSpace(value)

	switch normalized {
	case "true":
		return true, nil
	case "false":
		return false, nil
	case "null":
		return nil, nil
	}

	if numberPattern.MatchString(normalized) {
		var number json.Number = json.Number(normalized)
		if _, err := number.Float64(); err != nil {
			return nil, errors.New("numeric filter values must be finite numbers")
		}

		return number, nil
	}

	if strings.HasPrefix(normalized, "[") && strings.HasSuffix(normalized, "]") {
		decoder := json.NewDecoder(strings.NewReader(normalized))
		decoder.UseNumber()

		var items []any
		if err := decoder.Decode(&items); err != nil {
			return nil, errors.New("array filter values must be valid JSON arrays")
		}
		for _, item := range items {
			switch item.(type) {
			case nil, string, bool, json.Number:
			default:
				return nil, errors.New(
					"array filters can only contain strings, numbers, booleans, or null")
			}
		}

		return items, nil
	}

	return normalized, nil
}

// ParseFilter turns `field>=10` into an Appwrite query string.
func ParseFilter(expression string) (string, error) {
	for _, operator := range filterOperators {
		match := operator.pattern.FindStringSubmatch(expression)
		if match == nil {
			continue
		}

		attribute := strings.TrimSpace(match[1])
		if attribute == "" {
			return "", errors.New("filters must include an attribute before the operator")
		}

		value, err := ParseValue(match[2])
		if err != nil {
			return "", err
		}

		// An array value becomes the values list itself rather than its single
		// element, so `tags=["a","b"]` serialises as ["a","b"], not
		// [["a","b"]].
		values, isList := value.([]any)
		if !isList {
			values = []any{value}
		}

		return stringify(operator.method, &attribute, values)
	}

	return "", fmt.Errorf("%w: %q", ErrInvalidFilter, expression)
}

// Build assembles the final query list.
//
// Order is part of the contract and is documented in the --queries help text:
// raw queries first, then --filter, then the deprecated --where, then sorts,
// pagination, cursors and select. Returns nil when nothing was requested, so
// the caller omits the parameter rather than sending an empty list.
func Build(options Options) ([]string, error) {
	queries := append([]string(nil), options.Queries...)

	// --filter and --where arrive already serialised by ParseFilter. --where is
	// appended after --filter so the preferred flag wins when both are mixed.
	queries = append(queries, options.Filter...)
	queries = append(queries, options.Where...)

	for _, attribute := range options.SortAsc {
		encoded, err := stringify("orderAsc", &attribute, nil)
		if err != nil {
			return nil, err
		}
		queries = append(queries, encoded)
	}
	for _, attribute := range options.SortDesc {
		encoded, err := stringify("orderDesc", &attribute, nil)
		if err != nil {
			return nil, err
		}
		queries = append(queries, encoded)
	}

	if options.Limit != nil {
		encoded, err := stringify("limit", nil, []any{*options.Limit})
		if err != nil {
			return nil, err
		}
		queries = append(queries, encoded)
	}
	if options.Offset != nil {
		encoded, err := stringify("offset", nil, []any{*options.Offset})
		if err != nil {
			return nil, err
		}
		queries = append(queries, encoded)
	}
	if options.CursorAfter != nil {
		encoded, err := stringify("cursorAfter", nil, []any{*options.CursorAfter})
		if err != nil {
			return nil, err
		}
		queries = append(queries, encoded)
	}
	if options.CursorBefore != nil {
		encoded, err := stringify("cursorBefore", nil, []any{*options.CursorBefore})
		if err != nil {
			return nil, err
		}
		queries = append(queries, encoded)
	}

	if len(options.Select) > 0 {
		values := make([]any, 0, len(options.Select))
		for _, attribute := range options.Select {
			values = append(values, attribute)
		}
		encoded, err := stringify("select", nil, values)
		if err != nil {
			return nil, err
		}
		queries = append(queries, encoded)
	}

	if len(queries) == 0 {
		return nil, nil
	}

	return queries, nil
}

// ParseFilters turns raw --filter expressions into Appwrite query strings.
//
// cobra's flag layer has no per-flag parse hook, so the conversion happens here
// rather than as the flag is read -- and it must happen somewhere, or
// `--filter name=x` reaches the API as the literal string
// "name=x" rather than a query.
func ParseFilters(expressions []string) ([]string, error) {
	if len(expressions) == 0 {
		return nil, nil
	}

	parsed := make([]string, 0, len(expressions))
	for _, expression := range expressions {
		encoded, err := ParseFilter(expression)
		if err != nil {
			return nil, err
		}
		parsed = append(parsed, encoded)
	}

	return parsed, nil
}

package query

import (
	"encoding/json"
)

func toArray(val interface{}) []interface{} {
	switch v := val.(type) {
	case nil:
		return nil
	case []interface{}:
		return v
	default:
		return []interface{}{val}
	}
}

type queryOptions struct {
	Method    string
	Attribute *string
	Values    *[]interface{}
}

func parseQuery(options queryOptions) string {
	data := struct {
		Method    string        `json:"method"`
		Attribute string        `json:"attribute,omitempty"`
		Values    []interface{} `json:"values,omitempty"`
	}{
		Method:    options.Method,
	}

	if options.Attribute != nil {
		data.Attribute = *options.Attribute
	}

	if options.Values != nil {
		data.Values = *options.Values
	}

	jsonData, _ := json.Marshal(data)

	return string(jsonData)
}

func Equal(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "equal",
		Attribute: &attribute,
		Values:    &values,
	})
}

func NotEqual(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "notEqual",
		Attribute: &attribute,
		Values:    &values,
	})
}

func LessThan(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "lessThan",
		Attribute: &attribute,
		Values:    &values,
	})
}

func LessThanEqual(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "lessThanEqual",
		Attribute: &attribute,
		Values:    &values,
	})
}

func GreaterThan(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "greaterThan",
		Attribute: &attribute,
		Values:    &values,
	})
}

func GreaterThanEqual(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "greaterThanEqual",
		Attribute: &attribute,
		Values:    &values,
	})
}

func Search(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "search",
		Attribute: &attribute,
		Values:    &values,
	})
}

func IsNull(attribute string) string {
	return parseQuery(queryOptions{
		Method:    "isNull",
		Attribute: &attribute,
	})
}

func IsNotNull(attribute string) string {
	return parseQuery(queryOptions{
		Method:    "isNotNull",
		Attribute: &attribute,
	})
}

func Between(attribute string, start, end interface{}) string {
	values := []interface{}{start, end}
	return parseQuery(queryOptions{
		Method:    "between",
		Attribute: &attribute,
		Values:    &values,
	})
}

func StartsWith(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "startsWith",
		Attribute: &attribute,
		Values:    &values,
	})
}

func EndsWith(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "endsWith",
		Attribute: &attribute,
		Values:    &values,
	})
}

func Contains(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "contains",
		Attribute: &attribute,
		Values:    &values,
	})
}

func NotContains(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "notContains",
		Attribute: &attribute,
		Values:    &values,
	})
}

func NotSearch(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "notSearch",
		Attribute: &attribute,
		Values:    &values,
	})
}

func NotBetween(attribute string, start, end interface{}) string {
	values := []interface{}{start, end}
	return parseQuery(queryOptions{
		Method:    "notBetween",
		Attribute: &attribute,
		Values:    &values,
	})
}

func NotStartsWith(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "notStartsWith",
		Attribute: &attribute,
		Values:    &values,
	})
}

func NotEndsWith(attribute string, value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method:    "notEndsWith",
		Attribute: &attribute,
		Values:    &values,
	})
}

func CreatedBefore(value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method: "createdBefore",
		Values: &values,
	})
}

func CreatedAfter(value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method: "createdAfter",
		Values: &values,
	})
}

func CreatedBetween(start, end interface{}) string {
	values := []interface{}{start, end}
	return parseQuery(queryOptions{
		Method: "createdBetween",
		Values: &values,
	})
}

func UpdatedBefore(value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method: "updatedBefore",
		Values: &values,
	})
}

func UpdatedAfter(value interface{}) string {
	values := toArray(value)
	return parseQuery(queryOptions{
		Method: "updatedAfter",
		Values: &values,
	})
}

func UpdatedBetween(start, end interface{}) string {
	values := []interface{}{start, end}
	return parseQuery(queryOptions{
		Method: "updatedBetween",
		Values: &values,
	})
}

func Select(attributes interface{}) string {
	values := toArray(attributes)
	return parseQuery(queryOptions{
		Method: "select",
		Values: &values,
	})
}

func OrderAsc(attribute string) string {
	return parseQuery(queryOptions{
		Method:    "orderAsc",
		Attribute: &attribute,
	})
}

func OrderDesc(attribute string) string {
	return parseQuery(queryOptions{
		Method:    "orderDesc",
		Attribute: &attribute,
	})}

func CursorBefore(documentId interface{}) string {
	values := toArray(documentId)
	return parseQuery(queryOptions{
		Method:    "cursorBefore",
		Values:    &values,
	})
}

func CursorAfter(documentId string) string {
	values := toArray(documentId)
	return parseQuery(queryOptions{
		Method:    "cursorAfter",
		Values:    &values,
	})
}

func Limit(limit int) string {
	values := toArray(limit)
	return parseQuery(queryOptions{
		Method:    "limit",
		Values:    &values,
	})
}

func Offset(offset int) string {
	values := toArray(offset)
	return parseQuery(queryOptions{
		Method:    "offset",
		Values:    &values,
	})
}

func Or(queries []string) string {
	var parsedQueries []interface{}
	for _, query := range queries {
		var q interface{}
		if err := json.Unmarshal([]byte(query), &q); err != nil {
			// Handle error, possibly log it or return an empty result
			continue
		}
		parsedQueries = append(parsedQueries, q)
	}
	return parseQuery(queryOptions{
		Method: "or",
		Values: &parsedQueries,
	})
}

func And(queries []string) string {
	var parsedQueries []interface{}
	for _, query := range queries {
		var q interface{}
		if err := json.Unmarshal([]byte(query), &q); err != nil {
			// Handle error, possibly log it or return an empty result
			continue
		}
		parsedQueries = append(parsedQueries, q)
	}
	return parseQuery(queryOptions{
		Method: "and",
		Values: &parsedQueries,
	})
}
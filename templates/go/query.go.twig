package query

import (
	"fmt"
	"reflect"
)


func parseValue(value interface{}) string {
	if val, ok := value.(string); ok {
		return fmt.Sprintf("\"%s\"", val)
	}
	return fmt.Sprintf("%v", value)
}

func parseQuery(attribute string, method string, value interface{}) string {
	stringValue := ""
	switch reflect.TypeOf(value).Kind() {
	case reflect.Array, reflect.Slice:
		arr := reflect.Indirect(reflect.ValueOf(value))
		for i := 0; i < arr.Len(); i++ {
			stringValue += parseValue(arr.Index(i).Interface())
			if i < arr.Len()-1 {
				stringValue += ","
			}
		}
	default:
		stringValue = parseValue(value)
	}
	return fmt.Sprintf("%s(\"%s\", [%s])", method, attribute, stringValue)
}

func Equal(attribute string, value interface{}) string {
	return parseQuery(attribute, "equal", value)
}

func NotEqual(attribute string, value interface{}) string {
	return parseQuery(attribute, "notEqual", value)
}

func LessThan(attribute string, value interface{}) string {
	return parseQuery(attribute, "lessThan", value)
}

func LessThanEqual(attribute string, value interface{}) string {
	return parseQuery(attribute, "lessThanEqual", value)
}

func GreaterThan(attribute string, value interface{}) string {
	return parseQuery(attribute, "greaterThan", value)
}

func GreaterThanEqual(attribute string, value interface{}) string {
	return parseQuery(attribute, "greaterThanEqual", value)
}

func Search(attribute string, value string) string {
	return parseQuery(attribute, "search", value)
}

func IsNull(attribute string) string {
	return fmt.Sprintf("isNull(\"%s\")", attribute)
}

func IsNotNull(attribute string) string {
	return fmt.Sprintf("isNotNull(\"%s\")", attribute)
}

func Between(attribute string, start, end interface{}) string {
	return parseQuery(attribute, "between", []interface{}{start, end})
}

func StartsWith(attribute string, value interface{}) string {
	return parseQuery(attribute, "startsWith", value)
}

func EndsWith(attribute string, value interface{}) string {
	return parseQuery(attribute, "endsWith", value)
}

func Select(attributes []string) string {
	stringValue := ""
	for i, attribute := range attributes {
		stringValue += fmt.Sprintf("\"%s\"", attribute)
		if i < len(attributes)-1 {
			stringValue += ","
		}
	}
	return fmt.Sprintf("select([%s])", stringValue)
}

func OrderAsc(attribute string) string {
	return fmt.Sprintf("orderAsc(\"%s\")", attribute)
}

func OrderDesc(attribute string) string {
	return fmt.Sprintf("orderDesc(\"%s\")", attribute)
}

func CursorBefore(documentId string) string {
	return fmt.Sprintf("cursorBefore(\"%s\")", documentId)
}

func CursorAfter(documentId string) string {
	return fmt.Sprintf("cursorAfter(\"%s\")", documentId)
}

func Limit(limit int) string {
	return fmt.Sprintf("limit(%d)", limit)
}

func Offset(offset int) string {
	return fmt.Sprintf("offset(%d)", offset)
}

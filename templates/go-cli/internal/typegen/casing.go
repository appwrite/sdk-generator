package typegen

import (
	"regexp"
	"strings"
)

// Ports the case helpers on LanguageMeta
// (templates/cli/lib/type-generation/languages/language.ts).
//
// Every generated type name, field name and enum key runs through these, so
// they decide the shape of the code users import. Behaviour was captured by
// running the TypeScript regexes rather than read off them -- several results
// are not what the source reads like:
//
//	MiXeD_Case-99  ->  mi-xe-d-case-99   (not mixed-case-99)
//	a-b--c         ->  aBC               camelCase eats the doubled hyphen
//	$id            ->  id                invalid characters are dropped, not replaced
//	9lives         ->  _9LIVES           as an enum key, since it cannot start with a digit
var (
	invalidCharacters = regexp.MustCompile(`[^a-zA-Z0-9\s\-_]`)
	camelBoundary     = regexp.MustCompile(`([a-z])([A-Z])`)
	acronymBoundary   = regexp.MustCompile(`([A-Z])([A-Z][a-z])`)
	separators        = regexp.MustCompile(`[_\s]+`)
	edgeHyphens       = regexp.MustCompile(`^-+|-+$`)
	repeatedHyphens   = regexp.MustCompile(`--+`)
	hyphenLetter      = regexp.MustCompile(`-([a-z0-9])`)
	leadingDigit      = regexp.MustCompile(`^\d`)
)

// ToKebabCase is the base every other conversion is built on.
func ToKebabCase(value string) string {
	value = invalidCharacters.ReplaceAllString(value, "")
	value = camelBoundary.ReplaceAllString(value, "$1-$2")
	value = acronymBoundary.ReplaceAllString(value, "$1-$2")
	value = separators.ReplaceAllString(value, "-")
	value = edgeHyphens.ReplaceAllString(value, "")
	value = repeatedHyphens.ReplaceAllString(value, "-")

	return strings.ToLower(value)
}

// ToSnakeCase converts to snake_case.
func ToSnakeCase(value string) string {
	return strings.ReplaceAll(ToKebabCase(value), "-", "_")
}

// ToUpperSnakeCase converts to UPPER_SNAKE_CASE.
func ToUpperSnakeCase(value string) string {
	return strings.ToUpper(ToSnakeCase(value))
}

// ToCamelCase converts to camelCase.
func ToCamelCase(value string) string {
	return hyphenLetter.ReplaceAllStringFunc(ToKebabCase(value), func(match string) string {
		return strings.ToUpper(match[1:])
	})
}

// ToPascalCase converts to PascalCase.
func ToPascalCase(value string) string {
	camel := ToCamelCase(value)
	if camel == "" {
		return ""
	}

	return strings.ToUpper(camel[:1]) + camel[1:]
}

// SanitizeEnumKey makes a value usable as an enum key.
//
// An empty result, or one starting with a digit, is prefixed with an
// underscore -- so an empty input becomes "_" rather than nothing, which would
// generate a syntactically invalid member.
func SanitizeEnumKey(value string) string {
	key := ToUpperSnakeCase(value)
	if key == "" || leadingDigit.MatchString(key) {
		key = "_" + key
	}

	return key
}

// AttributeType values as the API reports them.
//
// Note FLOAT is reported
// as "double": the constant name and the wire value differ, and matching the
// wire value is what matters.
const (
	AttributeTypeString     = "string"
	AttributeTypeText       = "text"
	AttributeTypeVarchar    = "varchar"
	AttributeTypeMediumText = "mediumtext"
	AttributeTypeLongText   = "longtext"
	AttributeTypeInteger    = "integer"
	AttributeTypeFloat      = "double"
	// AttributeTypeBigInt is accepted by the config schema but has no entry in
	// attribute.ts's AttributeType map, so only the languages that switch on
	// the raw string -- TypeScript -- handle it. The rest reject it as unknown,
	// which is the existing behaviour and not corrected here.
	AttributeTypeBigInt       = "bigint"
	AttributeTypeBoolean      = "boolean"
	AttributeTypeDateTime     = "datetime"
	AttributeTypeEmail        = "email"
	AttributeTypeIP           = "ip"
	AttributeTypeURL          = "url"
	AttributeTypeEnum         = "enum"
	AttributeTypeRelationship = "relationship"
	AttributeTypePoint        = "point"
	AttributeTypeLineString   = "linestring"
	AttributeTypePolygon      = "polygon"
)

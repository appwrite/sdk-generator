package typegen

import (
	"fmt"
	"regexp"
	"slices"
	"strings"
)

type Python struct{}

const pythonLineLength = 88

const pythonIndent = "    "

var pythonIdentifier = regexp.MustCompile(`^[A-Za-z][A-Za-z0-9_]*$`)

var pythonReserved = []string{
	"False", "None", "True", "and", "as", "assert", "async", "await", "break",
	"class", "continue", "def", "del", "elif", "else", "except", "finally", "for",
	"from", "global", "if", "import", "in", "is", "lambda", "nonlocal", "not", "or",
	"pass", "raise", "return", "try", "while", "with", "yield",
	"construct", "copy", "dict", "from_orm", "json", "model_computed_fields",
	"model_config", "model_construct", "model_copy", "model_dump", "model_dump_json",
	"model_extra", "model_fields", "model_fields_set", "model_json_schema",
	"model_parametrized_name", "model_post_init", "model_rebuild", "model_validate",
	"model_validate_json", "model_validate_strings", "parse_file", "parse_obj",
	"parse_raw", "schema", "schema_json", "update_forward_refs", "validate",
	"annotations", "bool", "float", "int", "str", "BaseModel", "ConfigDict",
	"Enum", "Field", "List", "Optional", "Union",
}

type pythonClasses struct {
	models map[string]string
	enums  map[string]string
	order  []string
}

func pythonEnumKey(collectionID, attributeKey string) string {
	return collectionID + "\x00" + attributeKey
}

func pythonClassName(value string) string {
	name := ToPascalCase(value)
	if name == "" || leadingDigit.MatchString(name) {
		name = "Model" + name
	}

	return name
}

func newPythonClasses(collections []Collection) pythonClasses {
	classes := pythonClasses{models: map[string]string{}, enums: map[string]string{}}
	used := map[string]bool{}

	claim := func(name string) string {
		candidate := name
		for suffix := 1; used[candidate] || slices.Contains(pythonReserved, candidate); suffix++ {
			candidate = fmt.Sprintf("%s%d", name, suffix)
		}
		used[candidate] = true
		classes.order = append(classes.order, candidate)

		return candidate
	}

	for _, collection := range collections {
		classes.models[collection.ID] = claim(pythonClassName(collection.Name))
	}
	for _, collection := range collections {
		for _, attribute := range collection.Attributes {
			if attribute.Format != AttributeTypeEnum {
				continue
			}

			name := pythonClassName(collection.Name) + ToPascalCase(attribute.Key)
			classes.enums[pythonEnumKey(collection.ID, attribute.Key)] = claim(name)
		}
	}

	return classes
}

func pythonCollectionID(collections []Collection, collectionName string) string {
	for _, collection := range collections {
		if collection.Name == collectionName {
			return collection.ID
		}
	}

	return collectionName
}

func (p Python) Type(attribute Attribute, collections []Collection, collectionName string) (string, error) {
	classes := newPythonClasses(collections)

	return p.annotation(attribute, collections, pythonCollectionID(collections, collectionName), classes)
}

func (p Python) annotation(attribute Attribute, collections []Collection, collectionID string, classes pythonClasses) (string, error) {
	var literal string

	switch attribute.Type {
	case AttributeTypeString, AttributeTypeText, AttributeTypeVarchar,
		AttributeTypeMediumText, AttributeTypeLongText, AttributeTypeDateTime:
		literal = "str"
		if attribute.Format == AttributeTypeEnum {
			literal = classes.enums[pythonEnumKey(collectionID, attribute.Key)]
		}
	case AttributeTypeInteger, AttributeTypeBigInt:
		literal = "int"
	case AttributeTypeFloat:
		literal = "float"
	case AttributeTypeBoolean:
		literal = "bool"
	case AttributeTypeRelationship:
		related, err := RelatedCollection(attribute, collections)
		if err != nil {
			return "", err
		}

		literal = "Union[" + classes.models[related.ID] + ", str]"
		if IsRelationshipList(attribute) {
			literal = "List[" + literal + "]"
		}
	case AttributeTypePoint:
		literal = "List[float]"
	case AttributeTypeLineString:
		literal = "List[List[float]]"
	case AttributeTypePolygon:
		literal = "List[List[List[float]]]"
	default:
		return "", &UnknownAttributeTypeError{Type: attribute.Type}
	}

	if attribute.Array {
		literal = "List[" + literal + "]"
	}

	return literal, nil
}

func (p Python) SingleFile() bool { return true }

func (p Python) FileName(*Collection) string { return "appwrite_types.py" }

func pythonFieldNames(attributes []Attribute, strict bool, classes pythonClasses) []string {
	used := map[string]bool{}
	names := make([]string, len(attributes))

	for index, attribute := range attributes {
		name := attribute.Key
		if strict || !pythonIdentifier.MatchString(name) {
			name = ToSnakeCase(strings.ReplaceAll(name, ".", "_"))
		}
		if name == "" || leadingDigit.MatchString(name) {
			name = strings.TrimSuffix("field_"+name, "_")
		}
		if slices.Contains(pythonReserved, name) || slices.Contains(classes.order, name) {
			name += "_"
		}

		candidate := name
		for suffix := 1; used[candidate]; suffix++ {
			candidate = fmt.Sprintf("%s_%d", name, suffix)
		}
		used[candidate] = true
		names[index] = candidate
	}

	return names
}

func pythonString(value string) string {
	quote := `"`
	if strings.Count(value, `"`) > strings.Count(value, `'`) {
		quote = `'`
	}

	var out strings.Builder
	out.WriteString(quote)
	for _, character := range value {
		switch {
		case character == '\\':
			out.WriteString(`\\`)
		case string(character) == quote:
			out.WriteString(`\` + quote)
		case character == '\n':
			out.WriteString(`\n`)
		case character == '\r':
			out.WriteString(`\r`)
		case character == '\t':
			out.WriteString(`\t`)
		case character < 0x20 || character == 0x7f:
			fmt.Fprintf(&out, `\x%02x`, character)
		default:
			out.WriteRune(character)
		}
	}
	out.WriteString(quote)

	return out.String()
}

func pythonFits(line string) bool {
	return len([]rune(line)) <= pythonLineLength
}

func pythonBracketed(out *strings.Builder, indent, head, open string, elements []string, close, tail string) {
	single := indent + head + open + strings.Join(elements, ", ") + close + tail
	if pythonFits(single) {
		out.WriteString(single + "\n")

		return
	}

	inner := indent + pythonIndent
	body := inner + strings.Join(elements, ", ")
	if len(elements) == 1 || pythonFits(body) {
		fmt.Fprintf(out, "%s%s%s\n%s\n%s%s%s\n", indent, head, open, body, indent, close, tail)

		return
	}

	fmt.Fprintf(out, "%s%s%s\n", indent, head, open)
	for _, element := range elements {
		fmt.Fprintf(out, "%s%s,\n", inner, element)
	}
	fmt.Fprintf(out, "%s%s%s\n", indent, close, tail)
}

func pythonField(out *strings.Builder, name, annotation string, required bool, alias string) {
	if !required {
		annotation = "Optional[" + annotation + "]"
	}

	var arguments []string
	if !required && alias != "" {
		arguments = append(arguments, "default=None")
	}
	if alias != "" {
		arguments = append(arguments, "alias="+pythonString(alias))
	}

	if len(arguments) == 0 {
		tail := ""
		if !required {
			tail = " = None"
		}
		line := pythonIndent + name + ": " + annotation + " = ("
		if !required && !pythonFits(line[:len(line)-1]+"None") && pythonFits(line) {
			out.WriteString(line + "\n" + pythonIndent + pythonIndent + "None\n" + pythonIndent + ")\n")

			return
		}
		pythonAnnotation(out, name, annotation, tail, nil)

		return
	}

	head := pythonIndent + name + ": " + annotation + " = "
	if pythonFits(head+"Field(") || pythonFits(head+"Field("+strings.Join(arguments, ", ")+")") {
		pythonBracketed(out, pythonIndent, name+": "+annotation+" = Field", "(", arguments, ")", "")

		return
	}

	if pythonFits(head + "(") {
		out.WriteString(head + "(\n")
		pythonBracketed(out, pythonIndent+pythonIndent, "Field", "(", arguments, ")", "")
		out.WriteString(pythonIndent + ")\n")

		return
	}

	pythonAnnotation(out, name, annotation, "", arguments)
}

func pythonTypeElements(annotation string) []string {
	var elements []string
	depth, start := 0, 0
	for index, character := range annotation {
		switch character {
		case '[':
			depth++
		case ']':
			depth--
		case ',':
			if depth == 0 {
				elements = append(elements, annotation[start:index])
				start = index + 2
			}
		}
	}

	return append(elements, annotation[start:])
}

func pythonType(out *strings.Builder, indent, annotation string) {
	if pythonFits(indent + annotation) {
		out.WriteString(indent + annotation + "\n")

		return
	}

	if elements := pythonTypeElements(annotation); len(elements) > 1 {
		for _, element := range elements {
			pythonType(out, indent, element+",")
		}

		return
	}

	bracket := strings.Index(annotation, "[")
	if bracket < 0 {
		out.WriteString(indent + annotation + "\n")

		return
	}

	suffix := annotation[strings.LastIndex(annotation, "]")+1:]
	out.WriteString(indent + annotation[:bracket+1] + "\n")
	pythonType(out, indent+pythonIndent, annotation[bracket+1:strings.LastIndex(annotation, "]")])
	out.WriteString(indent + "]" + suffix + "\n")
}

func pythonArgumentsFit(arguments []string) bool {
	for _, argument := range arguments {
		if !pythonFits(pythonIndent + pythonIndent + argument + ",") {
			return false
		}
	}

	return true
}

func pythonAnnotation(out *strings.Builder, name, annotation, tail string, arguments []string) {
	line := pythonIndent + name + ": " + annotation + tail
	if arguments == nil && pythonFits(line) {
		out.WriteString(line + "\n")

		return
	}

	open, close, inner := "(", ")", annotation
	bracket := strings.Index(annotation, "[")
	wrappable := pythonFits(pythonIndent + pythonIndent + annotation)
	switch {
	case bracket >= 0 && (pythonFits(pythonIndent+name+": "+annotation[:bracket+1]) || !wrappable):
		open, close, inner = annotation[:bracket+1], "]", annotation[bracket+1:len(annotation)-1]
	case bracket >= 0:
		if tail != "" {
			tail = " = (None)"
		}
	case !wrappable && arguments == nil:
		out.WriteString(line + "\n")

		return
	case !wrappable && !pythonFits(pythonIndent+") = Field("+strings.Join(arguments, ", ")+")"):
		pythonBracketed(out, pythonIndent, name+": "+annotation+" = Field", "(", arguments, ")", "")

		return
	}

	out.WriteString(pythonIndent + name + ": " + open + "\n")
	pythonType(out, pythonIndent+pythonIndent, inner)
	if arguments == nil {
		out.WriteString(pythonIndent + close + tail + "\n")

		return
	}

	pythonBracketed(out, pythonIndent, close+" = Field", "(", arguments, ")", "")
}

func pythonEnumMember(out *strings.Builder, key, value string) {
	literal := pythonString(value)
	line := pythonIndent + key + " = " + literal
	if pythonFits(line) || !pythonFits(pythonIndent+pythonIndent+literal) {
		out.WriteString(line + "\n")

		return
	}

	pythonBracketed(out, pythonIndent, key+" = ", "(", []string{literal}, ")", "")
}

func (p Python) Render(collections []Collection, _ *Collection, strict bool, invocation string) (string, error) {
	classes := newPythonClasses(collections)

	type field struct {
		name, annotation, alias string
		required                bool
	}
	models := make([][]field, len(collections))

	usesList, usesOptional, usesUnion, usesField, usesEnum := false, false, false, false, false
	for index, collection := range collections {
		names := pythonFieldNames(collection.Attributes, strict, classes)
		for position, attribute := range collection.Attributes {
			annotation, err := p.annotation(attribute, collections, collection.ID, classes)
			if err != nil {
				return "", err
			}

			alias := ""
			if names[position] != attribute.Key {
				alias = attribute.Key
			}

			usesList = usesList || strings.Contains(annotation, "List[")
			usesOptional = usesOptional || !attribute.Required
			usesUnion = usesUnion || strings.Contains(annotation, "Union[")
			usesField = usesField || alias != ""
			usesEnum = usesEnum || attribute.Format == AttributeTypeEnum

			models[index] = append(models[index], field{
				name:       names[position],
				annotation: annotation,
				alias:      alias,
				required:   attribute.Required,
			})
		}
	}

	var out strings.Builder

	out.WriteString("# This file is auto-generated by the Appwrite CLI.\n")
	fmt.Fprintf(&out, "# You can regenerate it by running `appwrite %s`.\n\n", invocation)
	out.WriteString("from __future__ import annotations\n\n")

	if usesEnum {
		out.WriteString("from enum import Enum\n")
	}
	var typing []string
	if usesList {
		typing = append(typing, "List")
	}
	if usesOptional {
		typing = append(typing, "Optional")
	}
	if usesUnion {
		typing = append(typing, "Union")
	}
	if len(typing) > 0 {
		fmt.Fprintf(&out, "from typing import %s\n", strings.Join(typing, ", "))
	}
	if usesEnum || len(typing) > 0 {
		out.WriteString("\n")
	}

	pydantic := "BaseModel, ConfigDict"
	if usesField {
		pydantic += ", Field"
	}
	fmt.Fprintf(&out, "from pydantic import %s\n", pydantic)

	for _, collection := range collections {
		for _, attribute := range collection.Attributes {
			if attribute.Format != AttributeTypeEnum {
				continue
			}

			out.WriteString("\n\n")
			pythonBracketed(&out, "", "class "+classes.enums[pythonEnumKey(collection.ID, attribute.Key)],
				"(", []string{"str", "Enum"}, ")", ":")

			members := generateEnumMembers(attribute.Elements)
			if len(members) == 0 {
				out.WriteString(pythonIndent + "pass\n")
			}
			for _, member := range members {
				pythonEnumMember(&out, member.Key, member.Value)
			}
		}
	}

	for index, collection := range collections {
		out.WriteString("\n\n")
		pythonBracketed(&out, "", "class "+classes.models[collection.ID], "(", []string{"BaseModel"}, ")", ":")
		out.WriteString(pythonIndent + `model_config = ConfigDict(extra="allow", populate_by_name=True)` + "\n")

		if len(models[index]) > 0 {
			out.WriteString("\n")
		}
		for _, field := range models[index] {
			pythonField(&out, field.name, field.annotation, field.required, field.alias)
		}
	}

	return out.String(), nil
}

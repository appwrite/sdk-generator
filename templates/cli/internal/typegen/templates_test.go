package typegen

import (
	"strings"
	"testing"
)

// The templates are written in by the SDK generator, so a broken getFiles()
// entry would leave the embed empty and typegen silently producing nothing.
// Checking they load and look like themselves catches that.
func TestEmbeddedTemplatesLoad(t *testing.T) {
	cases := []struct{ name, marker string }{
		{TemplateConstants, "export const PROJECT_ID"},
		{TemplateIndex, "export { databases }"},
		{TemplateTypes, "export type RoleString"},
		{TemplateDatabases, "{{"},
	}

	for _, tc := range cases {
		contents, err := Template(tc.name)
		if err != nil {
			t.Errorf("Template(%q): %v", tc.name, err)

			continue
		}
		if !strings.Contains(contents, tc.marker) {
			t.Errorf("Template(%q) does not contain %q -- wrong file embedded?", tc.name, tc.marker)
		}
	}
}

// The constants template is small enough to check end to end, and it exercises
// both a plain variable and an {{#if}} block.
func TestRenderConstantsTemplate(t *testing.T) {
	rendered, err := RenderTemplate(TemplateConstants, Values{
		"sdkTitle":       "Appwrite",
		"projectId":      "my-project",
		"endpoint":       "https://cloud.appwrite.io/v1",
		"requiresApiKey": true,
	})
	if err != nil {
		t.Fatal(err)
	}

	for _, want := range []string{
		`export const PROJECT_ID = 'my-project';`,
		`export const ENDPOINT = 'https://cloud.appwrite.io/v1';`,
		"export const API_KEY = process.env.APPWRITE_API_KEY ?? '';",
	} {
		if !strings.Contains(rendered, want) {
			t.Errorf("rendered output missing %q:\n%s", want, rendered)
		}
	}

	withoutKey, err := RenderTemplate(TemplateConstants, Values{"requiresApiKey": false})
	if err != nil {
		t.Fatal(err)
	}
	if strings.Contains(withoutKey, "API_KEY") {
		t.Errorf("API_KEY block rendered despite requiresApiKey being false:\n%s", withoutKey)
	}
}

// The typed QueryBuilder must accept the same join forms as the SDK's Query, so
// a query written against one compiles against the other.
func TestQueryBuilderSupportsNestedJoinConditions(t *testing.T) {
	joins := []string{"join", "leftJoin", "rightJoin", "fullOuterJoin"}

	types, err := Template(TemplateTypes)
	if err != nil {
		t.Fatal(err)
	}
	declarations := []string{"on: (left: string, right: string, operator?: string) => string;"}
	for _, join := range joins {
		declarations = append(declarations, join+": (table: string, leftOrAliasOrOn: string | string[], rightOrOn?: string | string[], operator?: string, alias?: string) => string;")
	}
	for _, want := range declarations {
		if !strings.Contains(types, want) {
			t.Errorf("%s does not declare %q", TemplateTypes, want)
		}
	}

	databases, err := Template(TemplateDatabases)
	if err != nil {
		t.Fatal(err)
	}
	implementations := []string{"on: (left, right, operator = '=') => Query.on(left, right, operator),"}
	for _, join := range joins {
		implementations = append(implementations, join+": (table, leftOrAliasOrOn, rightOrOn = '', operator = '=', alias = '') => Query."+join+"(table, leftOrAliasOrOn, rightOrOn, operator, alias),")
	}
	for _, want := range implementations {
		if !strings.Contains(databases, want) {
			t.Errorf("%s does not implement %q", TemplateDatabases, want)
		}
	}
}

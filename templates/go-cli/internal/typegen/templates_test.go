package typegen

import (
	"strings"
	"testing"
)

// The templates are generated in from the TypeScript CLI's directory, so a
// broken getFiles() entry would leave the embed empty and typegen silently
// producing nothing. Checking they load and look like themselves catches that.
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

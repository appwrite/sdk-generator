package typegen

import (
	"embed"
	"fmt"
)

// The typegen templates, embedded so the binary carries them.
//
// These files are generated from the TypeScript CLI's own template directory --
// one source, two outputs. Editing the copy under internal/typegen/templates has
// no effect: change
// templates/cli/lib/commands/generators/typescript/templates/ and regenerate.

//go:embed templates/*.hbs
var templateFiles embed.FS

// Template names the generator renders.
const (
	TemplateConstants = "constants.ts.hbs"
	TemplateDatabases = "databases.ts.hbs"
	TemplateIndex     = "index.ts.hbs"
	TemplateTypes     = "types.ts.hbs"
)

// Template reads one embedded template.
func Template(name string) (string, error) {
	contents, err := templateFiles.ReadFile("templates/" + name)
	if err != nil {
		return "", fmt.Errorf("typegen template %q is missing: %w", name, err)
	}

	return string(contents), nil
}

// RenderTemplate renders one embedded template against values.
func RenderTemplate(name string, values Values) (string, error) {
	template, err := Template(name)
	if err != nil {
		return "", err
	}

	return Render(template, values), nil
}

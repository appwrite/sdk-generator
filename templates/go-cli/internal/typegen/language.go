package typegen

import (
	"encoding/json"
	"errors"
	"fmt"
	"os"
	"path/filepath"
	"strings"
)

// The TypeScript builds each language's output from an EJS template held as an
// inline string in its class. Those strings are not shared artifacts -- nothing
// else reads them -- so there is nothing to preserve by porting EJS itself.
// Each language emits directly in Go instead, and correctness is held by
// baselines captured from node rather than by reading the templates.
//
// This differs from the .hbs templates under commands/generators, which ARE
// separate files both CLIs read; those go through internal/typegen/handlebars.go.

// Attribute is one attribute of a collection, or one column of a table.
//
// The two schemas differ in a single field -- relatedCollection versus
// relatedTable -- so one struct covers both, as TypeAttribute does.
type Attribute struct {
	Key      string   `json:"key"`
	Type     string   `json:"type"`
	Required bool     `json:"required,omitempty"`
	Array    bool     `json:"array,omitempty"`
	Format   string   `json:"format,omitempty"`
	Elements []string `json:"elements,omitempty"`

	RelatedCollection string `json:"relatedCollection,omitempty"`
	RelatedTable      string `json:"relatedTable,omitempty"`
	RelationType      string `json:"relationType,omitempty"`
	Side              string `json:"side,omitempty"`

	// Default distinguishes an absent key from an explicit null, because the
	// TypeScript tests `attribute.default === null`: an omitted default is
	// undefined and does NOT widen the type with `| null`. A plain `any` would
	// collapse both cases to nil and add `| null` where the TypeScript does not.
	//
	// json.RawMessage rather than *json.RawMessage: a pointer field is set to
	// nil by encoding/json when the JSON value is null, which erases the very
	// distinction this field exists to keep. As a value it implements
	// json.Unmarshaler and is handed the literal bytes "null".
	Default json.RawMessage `json:"default,omitempty"`
}

// IsDefaultNull reports whether `default` is present and explicitly null.
func (a Attribute) IsDefaultNull() bool {
	return string(a.Default) == "null"
}

// Nullable reports whether the emitted type gains a null branch.
func (a Attribute) Nullable() bool {
	return !a.Required && a.IsDefaultNull()
}

// Collection is a collection or a table, with its attributes normalised.
type Collection struct {
	ID         string      `json:"$id"`
	Name       string      `json:"name"`
	DatabaseID string      `json:"databaseId"`
	Attributes []Attribute `json:"attributes"`
}

// EnumMember is one key/value pair of a generated enum.
type EnumMember struct {
	Key   string
	Value string
}

// EnumDefinition is a generated enum.
type EnumDefinition struct {
	Name    string
	Members []EnumMember
}

// Language emits type definitions for one target language.
type Language interface {
	// Type is the type literal for an attribute. collectionName is the name of
	// the collection the attribute belongs to; some languages use it to
	// namespace generated enums.
	Type(attribute Attribute, collections []Collection, collectionName string) (string, error)
	// SingleFile reports whether every collection lands in one file.
	SingleFile() bool
	// FileName is the output file for a collection. Single-file languages
	// ignore the argument.
	FileName(collection *Collection) string
	// Render writes the complete file contents.
	//
	// current is the collection being written, and is nil for single-file
	// languages -- matching types.ts, which only puts `collection` in the
	// template locals on the multi-file branch.
	//
	// strict switches attribute keys to camelCase, matching the `--strict`
	// flag on `appwrite types`.
	Render(collections []Collection, current *Collection, strict bool, invocation string) (string, error)
}

// ErrEnumUnsupported is returned by languages that do not generate enums,
// matching the TypeScript's base-class throw.
var ErrEnumUnsupported = errors.New("enum generation is not supported for this language")

// UnknownAttributeTypeError reports an attribute type no language handles.
type UnknownAttributeTypeError struct {
	Type string
}

func (e *UnknownAttributeTypeError) Error() string {
	return "unknown attribute type: " + e.Type
}

// RelatedCollectionID is the id a relationship attribute points at.
//
// Collections carry relatedCollection and tables carry relatedTable; the
// TypeScript checks them in that order and so does this.
func RelatedCollectionID(attribute Attribute) string {
	if attribute.RelatedCollection != "" {
		return attribute.RelatedCollection
	}

	return attribute.RelatedTable
}

// RelatedCollection resolves a relationship attribute to its target.
//
// Note the lookup matches on $id only, mirroring LanguageMeta. The shared
// TypeScript helper getTypeScriptType() additionally matches on name -- see
// resolveRelated() in typescript.go, which reproduces that wider lookup for the
// languages that go through it.
func RelatedCollection(attribute Attribute, collections []Collection) (Collection, error) {
	id := RelatedCollectionID(attribute)
	for _, collection := range collections {
		if collection.ID == id {
			return collection, nil
		}
	}

	return Collection{}, fmt.Errorf("related collection with ID '%s' not found", id)
}

// IsRelationshipList reports whether a relationship yields many rows.
func IsRelationshipList(attribute Attribute) bool {
	return (attribute.RelationType == "oneToMany" && attribute.Side == "parent") ||
		(attribute.RelationType == "manyToOne" && attribute.Side == "child") ||
		attribute.RelationType == "manyToMany"
}

// PropertyName is the emitted name for an attribute key.
func PropertyName(attribute Attribute, strict bool) string {
	if strict {
		return ToCamelCase(attribute.Key)
	}

	return attribute.Key
}

// xmlEscaper reproduces EJS's escapeXML, which `<%= %>` applies and `<%- %>`
// does not.
//
// Several templates use both forms for the same identifier, so an attribute key
// containing `&` or `<` is written one way as a field declaration and another
// as a constructor parameter -- in the same generated file. Note the numeric
// entities for quotes: this is EJS's table, not Handlebars', and the two differ.
var xmlEscaper = strings.NewReplacer(
	"&", "&amp;",
	"<", "&lt;",
	">", "&gt;",
	`"`, "&#34;",
	"'", "&#39;",
)

// EscapeXML applies the escaping an EJS `<%= %>` tag would.
func EscapeXML(value string) string {
	return xmlEscaper.Replace(value)
}

// generateEnumMembers builds enum members, disambiguating collisions.
//
// Two elements that sanitise to the same key would otherwise emit a duplicate
// member. The suffix search starts at 1 and skips keys already taken, so
// ["a-b", "a_b", "A B"] yields A_B, A_B_1, A_B_2.
func generateEnumMembers(elements []string) []EnumMember {
	used := make(map[string]bool, len(elements))
	members := make([]EnumMember, 0, len(elements))

	for _, element := range elements {
		key := SanitizeEnumKey(element)
		if used[key] {
			disambiguator := 1
			for used[fmt.Sprintf("%s_%d", key, disambiguator)] {
				disambiguator++
			}
			key = fmt.Sprintf("%s_%d", key, disambiguator)
		}
		used[key] = true
		members = append(members, EnumMember{Key: key, Value: element})
	}

	return members
}

// languageMarkers maps a language id to the files that identify a project.
//
// Order is significant and matches detectLanguage(): a project with both a
// tsconfig.json and a package.json is TypeScript, not JavaScript.
var languageMarkers = []struct {
	id    string
	files []string
}{
	{"ts", []string{"tsconfig.json", "deno.json"}},
	{"js", []string{"package.json"}},
	{"php", []string{"composer.json"}},
	{"python", []string{"requirements.txt", "Pipfile", "pyproject.toml"}},
	{"ruby", []string{"Gemfile", "Rakefile"}},
	{"kotlin", []string{"build.gradle.kts"}},
	{"java", []string{"build.gradle", "pom.xml"}},
}

// ErrLanguageUndetected matches the TypeScript's failure message verbatim,
// because it is what the user sees.
var ErrLanguageUndetected = errors.New("could not detect language, please specify with -l")

// DetectLanguage guesses the project's language from marker files.
func DetectLanguage(directory string) (string, error) {
	exists := func(names ...string) bool {
		for _, name := range names {
			if _, err := os.Stat(filepath.Join(directory, name)); err == nil {
				return true
			}
		}

		return false
	}

	for _, marker := range languageMarkers {
		if exists(marker.files...) {
			return marker.id, nil
		}
	}

	// .csproj is matched by extension rather than by name. An unreadable
	// directory is skipped rather than failing, as in the TypeScript.
	if entries, err := os.ReadDir(directory); err == nil {
		for _, entry := range entries {
			if strings.HasSuffix(entry.Name(), ".csproj") {
				return "dotnet", nil
			}
		}
	}

	if exists("Package.swift") {
		return "swift", nil
	}
	if exists("pubspec.yaml") {
		return "dart", nil
	}

	return "", ErrLanguageUndetected
}

// appwriteDependencies is the preference order getAppwriteDependency() uses
// when a package.json declares more than one Appwrite package.
var appwriteDependencies = []string{
	"@appwrite.io/console",
	"react-native-appwrite",
	"appwrite",
	"node-appwrite",
}

// AppwriteDependency is the package generated TypeScript should import from.
func AppwriteDependency(directory string) string {
	if declared, err := os.ReadFile(filepath.Join(directory, "package.json")); err == nil {
		var manifest struct {
			Dependencies map[string]string `json:"dependencies"`
		}
		// An unparseable package.json falls through to the deno.json check,
		// exactly as the TypeScript's empty catch block does.
		if json.Unmarshal(declared, &manifest) == nil {
			// Truthiness, not presence: the TypeScript tests `deps[name]`, so a
			// dependency declared with an empty version string is skipped.
			for _, name := range appwriteDependencies {
				if manifest.Dependencies[name] != "" {
					return name
				}
			}
		}
	}

	if _, err := os.Stat(filepath.Join(directory, "deno.json")); err == nil {
		return "npm:node-appwrite"
	}

	return "appwrite"
}

// JavaScriptDependency is the narrower two-way choice the JavaScript emitter
// makes. It is deliberately not AppwriteDependency: javascript.ts has its own
// _getAppwriteDependency() that only ever returns node-appwrite or appwrite, so
// a project depending on @appwrite.io/console gets "appwrite" here and
// "@appwrite.io/console" from the TypeScript emitter.
func JavaScriptDependency(directory string) string {
	declared, err := os.ReadFile(filepath.Join(directory, "package.json"))
	if err != nil {
		return "appwrite"
	}

	var manifest struct {
		Dependencies map[string]string `json:"dependencies"`
	}
	if json.Unmarshal(declared, &manifest) != nil {
		return "appwrite"
	}
	if manifest.Dependencies["node-appwrite"] != "" {
		return "node-appwrite"
	}

	return "appwrite"
}

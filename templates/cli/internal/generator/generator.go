package generator

import (
	"encoding/json"
	"errors"
	"os"
	"path/filepath"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/typegen"
)

// `appwrite generate` writes a small typed SDK into the user's project. Only
// TypeScript is implemented, in the established CLI as here -- base.ts is an
// interface plus a shared writeFiles(), and index.ts a one-entry registry.

// Language is a target `generate` can emit.
type Language string

// LanguageTypeScript is the only implemented target.
const LanguageTypeScript Language = "typescript"

// Result is the four files a generator produces.
type Result struct {
	Databases string
	Types     string
	Index     string
	Constants string
}

// Options override what would otherwise be detected from the project.
type Options struct {
	// ImportSource is the package generated files import from. Detected with
	// typegen.AppwriteDependency when empty.
	ImportSource string
	// ImportExtension is ".js" for ESM projects and "" otherwise. Detected
	// with DetectImportExtension when empty.
	//
	// A pointer so an explicit empty string can be distinguished from "not
	// set": "" is a meaningful value here, and the TypeScript's `??` only
	// falls through on undefined.
	ImportExtension *string
	// ServerSide overrides whether server-only methods are emitted:
	// "auto", "true" or "false".
	ServerSide string
}

// Generator emits a typed SDK for one language.
type Generator interface {
	Language() Language
	Generate(config Config, options Options) (Result, error)
}

// ErrProjectIDRequired matches the TypeScript's message, which the user sees.
var ErrProjectIDRequired = errors.New("project ID is required in configuration")

// New returns the generator for a language.
func New(language Language) (Generator, error) {
	if language == LanguageTypeScript {
		return &TypeScript{}, nil
	}

	return nil, &UnsupportedLanguageError{Language: language}
}

// UnsupportedLanguageError reports a language with no generator.
type UnsupportedLanguageError struct {
	Language Language
}

func (e *UnsupportedLanguageError) Error() string {
	return "no generator available for language: " + string(e.Language) +
		". Supported languages: " + string(LanguageTypeScript)
}

// outputFiles maps each Result field to its filename, in write order.
func outputFiles(result Result) [][2]string {
	return [][2]string{
		{"databases.ts", result.Databases},
		{"types.ts", result.Types},
		{"index.ts", result.Index},
		{"constants.ts", result.Constants},
	}
}

// WriteFiles writes a Result under <outputDirectory>/<sdkDirectory>.
//
// constants.ts is written only when absent: the header tells the user they may
// edit it, so regenerating must not discard what they put there. The other
// three are overwritten every time.
func WriteFiles(outputDirectory, sdkDirectory string, result Result) error {
	directory := filepath.Join(outputDirectory, sdkDirectory)
	if err := os.MkdirAll(directory, 0o755); err != nil {
		return err
	}

	for _, file := range outputFiles(result) {
		path := filepath.Join(directory, file[0])

		if file[0] == "constants.ts" {
			if _, err := os.Stat(path); err == nil {
				continue
			}
		}

		if err := os.WriteFile(path, []byte(file[1]), 0o644); err != nil {
			return err
		}
	}

	return nil
}

// GeneratedFilePaths lists what WriteFiles produces, for the success message.
func GeneratedFilePaths(sdkDirectory string) []string {
	paths := make([]string, 0, 4)
	for _, file := range outputFiles(Result{}) {
		paths = append(paths, filepath.Join(sdkDirectory, file[0]))
	}

	return paths
}

// Entity is a table or a collection.
//
// The config schema names their field lists differently -- `columns` for a
// table, `attributes` for a collection -- and Fields() picks whichever is
// present, as getFields() does.
type Entity struct {
	ID         string              `json:"$id"`
	Name       string              `json:"name"`
	DatabaseID string              `json:"databaseId"`
	Attributes []typegen.Attribute `json:"attributes,omitempty"`
	Columns    []typegen.Attribute `json:"columns,omitempty"`
}

// Fields returns the entity's attributes or columns.
func (e Entity) Fields() []typegen.Attribute {
	if e.Columns != nil {
		return e.Columns
	}

	return e.Attributes
}

// HasRelationship reports whether any field is a relationship. Bulk methods are
// withheld from such tables.
func (e Entity) HasRelationship() bool {
	for _, field := range e.Fields() {
		if field.Type == typegen.AttributeTypeRelationship {
			return true
		}
	}

	return false
}

// Config is the part of appwrite.config.json `generate` reads.
type Config struct {
	ProjectID   string   `json:"projectId"`
	Endpoint    string   `json:"endpoint"`
	Tables      []Entity `json:"tables"`
	Collections []Entity `json:"collections"`
}

// Entities returns the tables if there are any, else the collections.
//
// Not a merge: the TypeScript picks one list or the other, so a config with
// both uses tables and ignores collections entirely.
func (c Config) Entities() []Entity {
	if len(c.Tables) > 0 {
		return c.Tables
	}

	return c.Collections
}

// LoadConfig reads the generate-relevant parts of a project config.
func LoadConfig(path string) (Config, error) {
	payload, err := os.ReadFile(path)
	if err != nil {
		return Config{}, err
	}

	var config Config
	if err := json.Unmarshal(payload, &config); err != nil {
		return Config{}, err
	}

	return config, nil
}

// DetectImportExtension reports the extension generated imports should carry.
//
// Implements detectImportExtension(): ".js" for an ESM package, ".ts" for Deno, and
// "" otherwise -- including when package.json is unparseable, which the
// TypeScript swallows.
func DetectImportExtension(directory string) string {
	if payload, err := os.ReadFile(filepath.Join(directory, "package.json")); err == nil {
		var manifest struct {
			Type string `json:"type"`
		}
		if json.Unmarshal(payload, &manifest) == nil && manifest.Type == "module" {
			return ".js"
		}
	}

	for _, name := range []string{"deno.json", "deno.jsonc"} {
		if _, err := os.Stat(filepath.Join(directory, name)); err == nil {
			return ".ts"
		}
	}

	return ""
}

// SupportsServerSideMethods reports whether server-only methods are emitted.
//
// The override wins over the dependency, so
// a project can force bulk methods on or off.
func SupportsServerSideMethods(dependency, override string) bool {
	switch override {
	case "true":
		return true
	case "false":
		return false
	}

	return dependency == "node-appwrite" ||
		dependency == "npm:node-appwrite" ||
		dependency == "@appwrite.io/console"
}

//go:build !browser

package cmd

import (
	"fmt"
	"os"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/generator"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/typegen"
	"github.com/spf13/cobra"
)

// typeLanguages maps the `-l` value to its emitter. The keys are the CLI's
// public surface, so they stay as they are even where they are not the
// language's usual short name (`cs`, not `csharp`).
//
// Constructors rather than values because the two JavaScript-family emitters
// bake the project's Appwrite package into the file they write, and they
// resolve it differently: the TypeScript emitter can pick
// @appwrite.io/console or react-native-appwrite, the JavaScript one only ever
// chooses between node-appwrite and appwrite.
//
// Dart has no equivalent: there is one package name, so there is nothing to
// resolve.
var typeLanguages = map[string]func(directory string) typegen.Language{
	"ts": func(directory string) typegen.Language {
		return typegen.TypeScript{Dependency: typegen.AppwriteDependency(directory)}
	},
	"js": func(directory string) typegen.Language {
		return typegen.JavaScript{Dependency: typegen.JavaScriptDependency(directory)}
	},
	"php":    func(string) typegen.Language { return typegen.PHP{} },
	"kotlin": func(string) typegen.Language { return typegen.Kotlin{} },
	"swift":  func(string) typegen.Language { return typegen.Swift{} },
	"java":   func(string) typegen.Language { return typegen.Java{} },
	"dart":   func(string) typegen.Language { return typegen.Dart{} },
	"cs":     func(string) typegen.Language { return typegen.CSharp{} },
}

// typeLanguageChoices is the accepted `-l` values, in the order the help lists
// them.
var typeLanguageChoices = []string{"auto", "ts", "js", "php", "kotlin", "swift", "java", "dart", "cs"}

// typeLanguageAliases maps a name a user is likely to reach for to the value
// the CLI accepts.
//
// `dotnet` is not a guess: DetectLanguage returns it for a directory holding a
// .csproj, so `appwrite types .` in a C# project resolves to a value the
// emitter table does not have a key for, and the answer is always `cs`.
var typeLanguageAliases = map[string]string{
	"c#":         "cs",
	"csharp":     "cs",
	"dotnet":     "cs",
	"javascript": "js",
	"node":       "js",
	"nodejs":     "js",
	"typescript": "ts",
}

// typeLanguageNames lists what `-l` accepts, minus `auto` -- a mode rather than
// a language, and so not an answer to "pass one of these instead".
func typeLanguageNames() string {
	names := make([]string, 0, len(typeLanguageChoices))
	for _, choice := range typeLanguageChoices {
		if choice != "auto" {
			names = append(names, choice)
		}
	}

	return strings.Join(names, ", ")
}

// unsupportedLanguage explains a `-l` value nothing generates for.
//
// The list is the useful half of the message. The accepted values are short
// names, several of which are not the language's usual spelling, so a rejection
// that names none of them leaves the user guessing at eight possibilities --
// and the full name is the obvious first guess, which is why `typescript` gets
// a suggestion rather than only a list.
//
// Detection reaches this too, and there the list is the only thing that says
// what to do next: a Python or Ruby project resolves to a language the CLI has
// no emitter for, so no spelling of it would have worked.
func unsupportedLanguage(requested string) error {
	if suggestion, ok := typeLanguageAliases[strings.ToLower(requested)]; ok {
		return fmt.Errorf(
			"language '%s' is not supported -- did you mean `%s`? The supported languages are %s",
			requested, suggestion, typeLanguageNames())
	}

	return fmt.Errorf("language '%s' is not supported. The supported languages are %s",
		requested, typeLanguageNames())
}

func newTypesCommand() *cobra.Command {
	var (
		language string
		strict   bool
	)

	command := &cobra.Command{
		Use:   "types <output-directory>",
		Short: "Generate types for your Appwrite project",
		Args:  RequiredArgument("<output-directory>", "The directory to write the types to"),
		RunE: func(command *cobra.Command, args []string) error {
			return runTypes(command, args[0], language, strict)
		},
	}

	command.Flags().StringVarP(&language, "language", "l", "auto",
		"The language of the types ("+strings.Join(typeLanguageChoices, "|")+")")
	command.Flags().BoolVarP(&strict, "strict", "s", false,
		"Enable strict mode to automatically convert field names to follow language conventions")

	return command
}

// resolveTypeLanguage picks the emitter, detecting from the project when the
// user passed `auto`.
func resolveTypeLanguage(command *cobra.Command, requested string) (string, typegen.Language, error) {
	working, err := os.Getwd()
	if err != nil {
		return "", nil, err
	}

	if requested == "auto" {
		detected, err := typegen.DetectLanguage(working)
		if err != nil {
			// typegen's message is printed verbatim; the list of what to pass is
			// added here, where it is known.
			return "", nil, fmt.Errorf("%w. The supported languages are %s",
				err, typeLanguageNames())
		}
		output.Log(command.OutOrStdout(), "Detected language: %s", detected)
		requested = detected
	}

	construct, ok := typeLanguages[requested]
	if !ok {
		return "", nil, unsupportedLanguage(requested)
	}

	return requested, construct(working), nil
}

// invocation reconstructs the arguments the generated header records.
//
// Everything after the executable name, which is os.Args[1:].
func invocation() string {
	return strings.Join(os.Args[1:], " ")
}

// collectionsForTypes reads the project config and normalises it for typegen.
//
// Tables are preferred over collections, and a table's `columns` become
// `attributes`, so one set of emitters serves both. relatedTable is left where
// it is: typegen.RelatedCollectionID already checks both fields, so renaming it
// too is unnecessary.
func collectionsForTypes(path string) ([]typegen.Collection, string, error) {
	project, err := generator.LoadConfig(path)
	if err != nil {
		return nil, "", err
	}

	entities, kind := project.Tables, "tables"
	if len(entities) == 0 {
		entities, kind = project.Collections, "collections"
	}
	if len(entities) == 0 {
		return nil, "", fmt.Errorf(
			"no tables or collections found in configuration. Make sure %s exists and contains tables or collections",
			filepath.Base(path))
	}

	collections := make([]typegen.Collection, 0, len(entities))
	for _, entity := range entities {
		collections = append(collections, typegen.Collection{
			ID:         entity.ID,
			Name:       entity.Name,
			DatabaseID: entity.DatabaseID,
			Attributes: entity.Fields(),
		})
	}

	return collections, kind, nil
}

func runTypes(command *cobra.Command, rawOutput, requested string, strict bool) error {
	name, emitter, err := resolveTypeLanguage(command, requested)
	if err != nil {
		return err
	}

	if strict {
		output.Warn(command.OutOrStdout(),
			"Strict mode enabled: Field names will be converted to follow %s conventions", name)
	}

	// An output path with an extension names a file rather than a directory,
	// which only works for a language that emits one.
	directory, singleFile := rawOutput, ""
	if filepath.Ext(rawOutput) != "" {
		if !emitter.SingleFile() {
			return fmt.Errorf(
				"invalid output path: %s. Output path must be a directory for languages that generate multiple files",
				rawOutput)
		}
		directory, singleFile = filepath.Dir(rawOutput), rawOutput
	}

	collections, kind, err := collectionsForTypes(config.LocalFileName)
	if err != nil {
		return err
	}

	if _, err := os.Stat(directory); os.IsNotExist(err) {
		output.Log(command.OutOrStdout(), "Directory: %s does not exist, creating...", directory)
		if err := os.MkdirAll(directory, 0o755); err != nil {
			return err
		}
	}

	names := make([]string, 0, len(collections))
	total := 0
	for _, collection := range collections {
		names = append(names, collection.Name)
		total += len(collection.Attributes)
	}

	resource := "attributes"
	if kind == "tables" {
		resource = "columns"
	}
	output.Log(command.OutOrStdout(), "Found %d %s: %s", len(collections), kind, strings.Join(names, ", "))
	output.Log(command.OutOrStdout(), "Found %d %s across all %s", total, resource, kind)

	arguments := invocation()

	if emitter.SingleFile() {
		content, err := emitter.Render(collections, nil, strict, arguments)
		if err != nil {
			return err
		}

		destination := singleFile
		if destination == "" {
			destination = filepath.Join(directory, emitter.FileName(nil))
		}
		if err := os.WriteFile(destination, []byte(content), 0o644); err != nil {
			return err
		}
		output.Log(command.OutOrStdout(), "Added types to %s", destination)
	} else {
		for index := range collections {
			current := &collections[index]

			content, err := emitter.Render(collections, current, strict, arguments)
			if err != nil {
				return err
			}

			destination := filepath.Join(directory, emitter.FileName(current))
			if err := os.WriteFile(destination, []byte(content), 0o644); err != nil {
				return err
			}
			output.Log(command.OutOrStdout(), "Added types for %s to %s", current.Name, destination)
		}
	}

	output.Success(command.OutOrStdout(), "Generated types for all the listed %s", kind)

	return nil
}

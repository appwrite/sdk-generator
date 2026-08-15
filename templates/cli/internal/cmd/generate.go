//go:build !browser

package cmd

import (
	"fmt"
	"os"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/generator"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/spf13/cobra"
)

// serverSideModes are the accepted --server values.
var serverSideModes = []string{"auto", "true", "false"}

func newGenerateCommand() *cobra.Command {
	var (
		outputDirectory string
		language        string
		serverSide      string
		importSource    string
		importExtension string
	)

	command := &cobra.Command{
		Use:   "generate",
		Short: "Generate a type-safe SDK from your Appwrite project configuration",
		RunE: func(command *cobra.Command, args []string) error {
			// An explicitly empty --import-extension is meaningful (it selects
			// CJS), so "was the flag given" is what decides whether to detect.
			var extension *string
			if command.Flags().Changed("import-extension") {
				extension = &importExtension
			}

			return runGenerate(command, outputDirectory, language, serverSide, importSource, extension)
		},
	}

	supported := make([]string, 0, len(generator.SupportedLanguages()))
	for _, name := range generator.SupportedLanguages() {
		supported = append(supported, string(name))
	}

	command.Flags().StringVarP(&outputDirectory, "output", "o", "generated",
		"Output directory for generated files")
	command.Flags().StringVarP(&language, "language", "l", "",
		"Target language for SDK generation (supported: "+strings.Join(supported, ", ")+")")
	command.Flags().StringVar(&serverSide, "server", "auto",
		"Override server-side generation (auto|true|false)")
	command.Flags().StringVar(&importSource, "appwrite-import-source", "",
		"Override Appwrite import source in generated files (e.g. node-appwrite, appwrite, @appwrite.io/console). "+
			"Auto-detected from package.json/deno.json if not provided.")
	command.Flags().StringVar(&importExtension, "import-extension", "",
		`Override import file extension in generated files (.js for ESM, empty string for CJS). `+
			`Auto-detected from package.json "type" field if not provided.`)

	return command
}

// resolveGenerator picks the generator, either from --language or by detection.
// resolveGenerateEndpoint fills in the endpoint the generated client will use.
func resolveGenerateEndpoint(configured string) string {
	if configured != "" {
		return configured
	}

	if global, err := preferences(); err == nil {
		if endpoint := global.CurrentValue(config.PreferenceEndpoint); endpoint != "" {
			return endpoint
		}
	}

	return config.DefaultEndpoint
}

func resolveGenerator(command *cobra.Command, requested, working string) (generator.Generator, string, error) {
	if requested != "" {
		created, err := generator.New(generator.Language(requested))
		if err != nil {
			return nil, "", err
		}
		output.Log(command.OutOrStdout(), "Using specified language: %s", requested)

		return created, requested, nil
	}

	created, detection, err := generator.NewFromDetection(working)
	if err != nil {
		supported := make([]string, 0, len(generator.SupportedLanguages()))
		for _, name := range generator.SupportedLanguages() {
			supported = append(supported, string(name))
		}

		return nil, "", fmt.Errorf(
			"%w\nUse --language to specify the target language. Supported: %s",
			err, strings.Join(supported, ", "))
	}

	// Low confidence means nothing but a matching file extension was found,
	// which is weak enough to be worth saying out loud.
	if detection.Confidence == generator.ConfidenceLow {
		output.Warn(command.OutOrStdout(),
			"Detected language '%s' with low confidence (%s). Use --language to specify explicitly.",
			detection.Language, detection.Reason)
	} else {
		output.Log(command.OutOrStdout(), "Detected language: %s (%s)",
			detection.Language, detection.Reason)
	}

	return created, string(detection.Language), nil
}

func runGenerate(
	command *cobra.Command,
	outputDirectory, language, serverSide, importSource string,
	importExtension *string,
) error {
	working, err := os.Getwd()
	if err != nil {
		return err
	}

	project, err := generator.LoadConfig(config.FindLocalPath())
	if err != nil || project.ProjectID == "" {
		return fmt.Errorf("no project found. Please run '%s init project' first", app.ExecutableName)
	}

	// The generated client hard-codes the endpoint it talks to, and `pull`
	// never writes an `endpoint` key -- so reading only the project config
	// emitted `export const ENDPOINT = ''` and produced a client that cannot
	// connect. Ports the localConfig || globalConfig || DEFAULT_ENDPOINT chain
	// in generate.ts:100.
	project.Endpoint = resolveGenerateEndpoint(project.Endpoint)

	if !contains(serverSideModes, serverSide) {
		return fmt.Errorf("invalid --server value: %s", serverSide)
	}

	created, name, err := resolveGenerator(command, language, working)
	if err != nil {
		return err
	}

	absolute := outputDirectory
	if !filepath.IsAbs(absolute) {
		absolute = filepath.Join(working, absolute)
	}

	output.Log(command.OutOrStdout(), "Generating type-safe %s SDK to %s...", name, absolute)

	result, err := created.Generate(project, generator.Options{
		ImportSource:    importSource,
		ImportExtension: importExtension,
		ServerSide:      serverSide,
	})
	if err != nil {
		return fmt.Errorf("failed to generate SDK: %w", err)
	}

	if err := generator.WriteFiles(absolute, generator.SDKDirectory, result); err != nil {
		return fmt.Errorf("failed to generate SDK: %w", err)
	}

	output.Success(command.OutOrStdout(), "Generated files:")
	for _, file := range generator.GeneratedFilePaths(generator.SDKDirectory) {
		command.Printf("  - %s\n", filepath.Join(outputDirectory, file))
	}

	if name == string(generator.LanguageTypeScript) {
		printTypeScriptUsage(command, project, outputDirectory, importExtension)
	}

	return nil
}

// printTypeScriptUsage prints the import and first-call hints.
func printTypeScriptUsage(
	command *cobra.Command,
	project generator.Config,
	outputDirectory string,
	importExtension *string,
) {
	extension := ""
	if importExtension != nil {
		extension = *importExtension
	} else {
		extension = generator.DetectImportExtension(".")
	}

	// Placeholders when the config declares nothing, so the hint still reads
	// as a complete example.
	databaseID, tableName := "databaseId", "tableName"
	if entities := project.Entities(); len(entities) > 0 {
		databaseID, tableName = entities[0].DatabaseID, entities[0].Name
	}

	out := command.OutOrStdout()

	command.Println()
	output.Log(out, "Import the generated SDK in your project:")
	command.Printf("  import { databases } from \"./%s/%s/index%s\";\n",
		outputDirectory, generator.SDKDirectory, extension)
	command.Println()
	output.Log(out, "Configure your SDK constants:")
	command.Printf("  set values in ./%s/%s/constants.ts\n", outputDirectory, generator.SDKDirectory)
	command.Println()
	output.Log(out, "Usage:")
	command.Printf("  const mydb = databases.use(%s);\n", quoteForHint(databaseID))
	command.Printf("  await mydb.use(%s).create({ ... });\n", quoteForHint(tableName))
}

// quoteForHint renders a JSON string literal, as JSON.stringify does in the
// TypeScript's usage hint.
func quoteForHint(value string) string {
	encoded, err := jsonx.Marshal(value)
	if err != nil {
		return `"` + value + `"`
	}

	return string(encoded)
}

func contains(values []string, value string) bool {
	for _, candidate := range values {
		if candidate == value {
			return true
		}
	}

	return false
}

package cmd

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/appwrite"
	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/appwrite/appwrite-cli-go/internal/output"
	"github.com/appwrite/appwrite-cli-go/internal/prompt"
	"github.com/spf13/cobra"
)

// Ports initFunction (templates/cli/lib/commands/init.ts:651) and
// questionsCreateFunction (questions.ts:513).

// templatesRepo holds the function starter templates.
const templatesRepo = "https://github.com/appwrite/templates"

// runtimeChoice is one entry of the runtime list.
//
// The two names are NOT the same string and the difference is load-bearing.
// Directory is the FIRST dash-separated segment of the runtime id, used to find
// the template directory; Language is the id with its LAST segment dropped,
// used to look up the entrypoint and install command. For `node-22` both are
// "node", which is why the divergence goes unnoticed until `python-ml-3.11`
// asks for the "python" template directory and the "python-ml" entrypoint.
type runtimeChoice struct {
	ID         string
	Directory  string
	Language   string
	Entrypoint string
	Commands   string
	// CommandsKnown separates a runtime with no install step from one this CLI
	// does not recognise. Both leave Commands empty and both warn, so the
	// distinction does not change behaviour today -- it is kept so that adding
	// a runtime cannot silently start warning about a language that genuinely
	// has nothing to install.
	CommandsKnown bool
	Ignore        []string
}

func newRuntimeChoice(id string) runtimeChoice {
	directory := id
	if index := strings.Index(id, "-"); index >= 0 {
		directory = id[:index]
	}

	language := id
	if index := strings.LastIndex(id, "-"); index >= 0 {
		language = id[:index]
	}

	commands, commandsKnown := installCommandFor(language)

	return runtimeChoice{
		ID:            id,
		Directory:     directory,
		Language:      language,
		Entrypoint:    entrypointFor(language),
		Commands:      commands,
		CommandsKnown: commandsKnown,
		Ignore:        ignoresFor(language),
	}
}

// entrypointFor is getEntrypoint(). An unknown runtime returns "", and the
// caller asks the user at first push.
func entrypointFor(language string) string {
	switch language {
	case "dart":
		return "lib/main.dart"
	case "deno":
		return "src/main.ts"
	case "node":
		return "src/main.js"
	case "bun":
		return "src/main.ts"
	case "php":
		return "src/index.php"
	case "python", "python-ml":
		return "src/main.py"
	case "ruby":
		return "lib/main.rb"
	case "rust":
		return "main.rs"
	case "swift":
		return "Sources/index.swift"
	case "cpp":
		return "src/main.cc"
	case "dotnet":
		return "src/Index.cs"
	case "java":
		return "src/Main.java"
	case "kotlin":
		return "src/Main.kt"
	case "go":
		return "main.go"
	}

	return ""
}

// installCommandFor is getInstallCommand().
//
// The second return distinguishes the compiled languages, which map to an empty
// command on purpose, from an unrecognised runtime.
func installCommandFor(language string) (string, bool) {
	switch language {
	case "dart":
		return "dart pub get", true
	case "deno":
		return "deno cache src/main.ts", true
	case "node":
		return "npm install", true
	case "bun":
		return "bun install", true
	case "php":
		return "composer install", true
	case "python", "python-ml":
		return "pip install -r requirements.txt", true
	case "ruby":
		return "bundle install", true
	case "rust":
		return "cargo install", true
	case "dotnet":
		return "dotnet restore", true
	case "swift", "java", "kotlin", "cpp":
		return "", true
	}

	return "", false
}

// ignoresFor is getIgnores().
//
// Always an array, never nil. The TypeScript writes `ignore: runtime.ignore ||
// null`, but an empty array is truthy in JavaScript, so that fallback never
// fires and a deno function is written with `"ignore": []` rather than null.
func ignoresFor(language string) []string {
	switch language {
	case "cpp":
		return []string{"build", "CMakeFiles", "CMakeCaches.txt"}
	case "dart":
		return []string{".packages", ".dart_tool"}
	case "dotnet":
		return []string{"bin", "obj", ".nuget"}
	case "java", "kotlin":
		return []string{"build"}
	case "node", "bun":
		return []string{"node_modules", ".npm"}
	case "php", "ruby":
		return []string{"vendor"}
	case "python", "python-ml":
		return []string{"__pypackages__"}
	case "rust":
		return []string{"target", "debug", "*.rs.bk", "*.pdb"}
	case "swift":
		return []string{".build", ".swiftpm"}
	}

	return []string{}
}

// FunctionEntry is a function as `init function` writes it.
//
// Field order is the TypeScript's object literal, which is what lands in the
// file. `ignore` is a list here and a newline-joined string in config.Function:
// that type is the READ model used by `run`, and the two are deliberately
// separate rather than one type trying to be both.
type FunctionEntry struct {
	ID                   string   `json:"$id"`
	Name                 string   `json:"name"`
	Runtime              string   `json:"runtime"`
	BuildSpecification   string   `json:"buildSpecification"`
	RuntimeSpecification string   `json:"runtimeSpecification"`
	Execute              []string `json:"execute"`
	Events               []string `json:"events"`
	Scopes               []string `json:"scopes"`
	Schedule             string   `json:"schedule"`
	Timeout              int      `json:"timeout"`
	Enabled              bool     `json:"enabled"`
	Logging              bool     `json:"logging"`
	Entrypoint           string   `json:"entrypoint"`
	Commands             string   `json:"commands"`
	Ignore               []string `json:"ignore"`
	DeploymentRetention  int      `json:"deploymentRetention"`
	Path                 string   `json:"path"`
}

func newInitFunctionCommand() *cobra.Command {
	return &cobra.Command{
		Use:     "function",
		Aliases: []string{"functions"},
		Short:   "Init a new Appwrite function",
		RunE: func(command *cobra.Command, args []string) error {
			return runInitFunction(command)
		},
	}
}

func runInitFunction(command *cobra.Command) error {
	context, err := newInitContext()
	if err != nil {
		return err
	}

	api, err := scaffoldAPI(context.local)
	if err != nil {
		return err
	}

	out := command.OutOrStdout()

	name, err := context.prompter.Text(prompt.Text{
		Message: "What would you like to name your function?",
		Default: "My Awesome Function",
	})
	if err != nil {
		return err
	}

	id, err := context.prompter.Text(prompt.Text{
		Message: "What ID would you like to have for your function?",
		Default: appwrite.UniqueSentinel,
	})
	if err != nil {
		return err
	}

	runtimeID, err := chooseRuntime(api, context.prompter)
	if err != nil {
		return err
	}
	selected := newRuntimeChoice(runtimeID)

	buildSpecification, err := chooseSpecification(api, context.prompter,
		"/functions/specifications", "What build specification would you like to use?")
	if err != nil {
		return err
	}

	runtimeSpecification, err := chooseSpecification(api, context.prompter,
		"/functions/specifications", "What runtime specification would you like to use?")
	if err != nil {
		return err
	}

	functionID := appwrite.ResolveID(id)
	directoryName := SafeDirectoryName(name, functionID)

	base := context.local.ResourceDirname("functions")
	functionFolder := filepath.Join(base, "functions")
	functionDir := filepath.Join(functionFolder, directoryName)
	templatesDir := filepath.Join(functionFolder, functionID+"-templates")

	if _, err := os.Stat(functionDir); err == nil {
		return fmt.Errorf(
			"( %s ) already exists in the current directory. Please choose another name",
			directoryName)
	}

	if selected.Entrypoint == "" {
		output.Log(out, "Entrypoint for this runtime not found. You will be "+
			"asked to configure entrypoint when you first push the function.")
	}
	if selected.Commands == "" {
		output.Log(out, "Installation command for this runtime not found. You "+
			"will be asked to configure the install command when you first push the function.")
	}

	if err := os.MkdirAll(functionDir, 0o777); err != nil {
		return err
	}
	if err := os.MkdirAll(templatesDir, 0o777); err != nil {
		return err
	}
	defer os.RemoveAll(templatesDir)

	// The template is always "starter". The TypeScript keeps a branch for
	// choosing another one, but it initialises `selected` before the check and
	// so can never reach it; only the starter is ever fetched.
	sparse := strings.ToLower(selected.Directory + "/starter")

	if err := runGit(templatesDir, fmt.Sprintf(
		"git clone --single-branch --depth 1 --sparse %s .", templatesRepo)); err != nil {
		return err
	}
	if err := runGit(templatesDir, "git sparse-checkout add "+sparse); err != nil {
		return err
	}

	if err := os.RemoveAll(filepath.Join(templatesDir, ".git")); err != nil {
		return err
	}

	if err := copyTree(filepath.Join(templatesDir, selected.Directory, "starter"),
		functionDir); err != nil {
		return err
	}

	if err := retitleReadme(filepath.Join(functionDir, "README.md"), name); err != nil {
		return err
	}

	if err := context.local.AddFunction(FunctionEntry{
		ID:                   functionID,
		Name:                 name,
		Runtime:              selected.ID,
		BuildSpecification:   buildSpecification,
		RuntimeSpecification: runtimeSpecification,
		Execute:              []string{"any"},
		Events:               []string{},
		Scopes:               []string{"users.read"},
		Schedule:             "",
		Timeout:              15,
		Enabled:              true,
		Logging:              true,
		Entrypoint:           selected.Entrypoint,
		Commands:             selected.Commands,
		Ignore:               selected.Ignore,
		DeploymentRetention:  0,
		Path:                 "functions/" + directoryName,
	}); err != nil {
		return err
	}

	if err := context.local.Write(); err != nil {
		return err
	}

	output.Success(out, "Initialing function")
	output.Log(out, "Next you can use '%s run function' to develop a function "+
		"locally. To deploy the function, use '%s push function'",
		app.ExecutableName, app.ExecutableName)

	return nil
}

// chooseRuntime asks which runtime to use.
func chooseRuntime(api *client.Client, prompter prompt.Prompter) (string, error) {
	var response struct {
		Runtimes []struct {
			ID   string `json:"$id"`
			Name string `json:"name"`
		} `json:"runtimes"`
	}

	if err := api.Call("GET", "/functions/runtimes", nil, &response); err != nil {
		return "", err
	}

	options := make([]prompt.Option, 0, len(response.Runtimes))
	for _, entry := range response.Runtimes {
		options = append(options, prompt.Option{
			Label: fmt.Sprintf("%s (%s)", entry.Name, entry.ID),
			Value: entry.ID,
		})
	}

	return prompter.Choice(prompt.Choice{
		Message: "What runtime would you like to use?",
		Options: options,
	})
}

// chooseSpecification asks for a CPU/memory pairing.
//
// A specification the account's plan does not allow is listed and disabled
// rather than hidden, so the upgrade is discoverable.
func chooseSpecification(
	api *client.Client,
	prompter prompt.Prompter,
	path string,
	message string,
) (string, error) {
	var response struct {
		Specifications []struct {
			Slug string `json:"slug"`
			// json.Number, not float64: a 0.5 CPU specification has to render
			// as "0.5" and a 512MB one as "512", and float64 formatting turns
			// the second into "512" only by luck of the default verb.
			CPUs    json.Number `json:"cpus"`
			Memory  json.Number `json:"memory"`
			Enabled *bool       `json:"enabled"`
		} `json:"specifications"`
	}

	if err := api.Call("GET", path, nil, &response); err != nil {
		return "", err
	}

	options := make([]prompt.Option, 0, len(response.Specifications))
	for _, entry := range response.Specifications {
		option := prompt.Option{
			Label: fmt.Sprintf("%s CPU, %sMB RAM", entry.CPUs, entry.Memory),
			Value: entry.Slug,
		}
		if entry.Enabled != nil && !*entry.Enabled {
			option.Disabled = true
			option.Reason = "Upgrade to use"
		}
		options = append(options, option)
	}

	return prompter.Choice(prompt.Choice{Message: message, Options: options})
}

// scaffoldAPI builds a project-scoped client for the init scaffolds.
func scaffoldAPI(local *config.Local) (*client.Client, error) {
	global, err := preferences()
	if err != nil {
		return nil, err
	}

	return projectAPI(global, local)
}

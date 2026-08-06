package cmd

import (
	"encoding/json"
	"fmt"
	"net/url"
	"os"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Ports initFunction (templates/cli/lib/commands/init.ts:651) and
// questionsCreateFunction (questions.ts:513).

// templatesRepo holds the function starter templates.
const templatesRepo = "https://github.com/appwrite/templates"

// runtimeChoice is one entry of the runtime list. Directory and Language are not
// the same string: Directory is the first dash-separated segment of the runtime
// id, Language is the id with its last segment dropped. For `node-22` both are
// "node", so the divergence only shows up on `python-ml-3.11`, which needs the
// "python" template directory and the "python-ml" entrypoint.
type runtimeChoice struct {
	ID         string
	Directory  string
	Language   string
	Entrypoint string
	Commands   string
	Ignore     []string
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

	return runtimeChoice{
		ID:         id,
		Directory:  directory,
		Language:   language,
		Entrypoint: entrypointFor(language),
		Commands:   installCommandFor(language),
		Ignore:     ignoresFor(language),
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

// installCommandFor prefills `commands` for the runtimes that fetch dependencies
// before a build. An empty result is not a gap: swift, java, kotlin and cpp have
// nothing to fetch, and go resolves modules during the build itself.
func installCommandFor(language string) string {
	switch language {
	case "dart":
		return "dart pub get"
	case "deno":
		return "deno cache src/main.ts"
	case "node":
		return "npm install"
	case "bun":
		return "bun install"
	case "php":
		return "composer install"
	case "python", "python-ml":
		return "pip install -r requirements.txt"
	case "ruby":
		return "bundle install"
	case "rust":
		return "cargo install"
	case "dotnet":
		return "dotnet restore"
	}

	return ""
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
		"/functions/specifications", buildSpecifications,
		"What build specification would you like to use?")
	if err != nil {
		return err
	}

	runtimeSpecification, err := chooseSpecification(api, context.prompter,
		"/functions/specifications", runtimeSpecifications,
		"What runtime specification would you like to use?")
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
	// No companion line for a missing install command. The entrypoint one above
	// earns its second sentence -- push really does prompt for that -- while the
	// install-command message named an action nobody has to take: push never
	// asks for the field, and a runtime with no command listed deploys as the
	// starter template intends, whether because it has nothing to fetch
	// (swift, java, kotlin, cpp) or because its build resolves dependencies
	// itself (go).

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

// Specification types the list endpoint distinguishes. A plan allows a different
// set for each, and `type` selects which set `enabled` is computed from. Asking
// for the default `runtimes` for both questions made the build prompt offer
// specifications that `push` then rejected -- on a value `init function` had
// just written into the config.
const (
	buildSpecifications   = "builds"
	runtimeSpecifications = "runtimes"
)

// chooseSpecification asks for a CPU/memory pairing. The API omits the
// specifications that are unavailable server-side, so `enabled: false` means one
// thing -- the plan gates it -- and those rows are shown disabled rather than
// hidden to keep the upgrade discoverable.
func chooseSpecification(
	api *client.Client,
	prompter prompt.Prompter,
	path string,
	specificationType string,
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

	query := url.Values{"type": []string{specificationType}}
	if err := api.Call("GET", path+"?"+query.Encode(), nil, &response); err != nil {
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

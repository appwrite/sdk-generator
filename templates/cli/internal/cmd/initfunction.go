//go:build !browser

package cmd

import (
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/url"
	"os"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/dotenv"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Initializes a function and collects its scaffold settings.

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
// Always an array, never nil: a runtime with no ignores is written as
// `"ignore": []` rather than null.
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
// Field order is what lands in the file, so it is contractual. `ignore` is a
// list here and a newline-joined string in config.Function:
// that type is the READ model used by `run`, and the two are deliberately
// separate rather than one type trying to be both.
type FunctionEntry struct {
	ID                        string   `json:"$id"`
	Name                      string   `json:"name"`
	Runtime                   string   `json:"runtime"`
	BuildSpecification        string   `json:"buildSpecification"`
	RuntimeSpecification      string   `json:"runtimeSpecification"`
	Execute                   []string `json:"execute"`
	Events                    []string `json:"events"`
	Scopes                    []string `json:"scopes"`
	Schedule                  string   `json:"schedule"`
	Timeout                   int      `json:"timeout"`
	Enabled                   bool     `json:"enabled"`
	Logging                   bool     `json:"logging"`
	Entrypoint                string   `json:"entrypoint"`
	Commands                  string   `json:"commands"`
	Ignore                    []string `json:"ignore"`
	DeploymentRetention       int      `json:"deploymentRetention"`
	Path                      string   `json:"path"`
	PreviewDomainTarget       string   `json:"previewDomainTarget,omitempty"`
	PreviewDomainLabel        string   `json:"previewDomainLabel,omitempty"`
	InstallationID            string   `json:"installationId,omitempty"`
	ProviderRepositoryID      string   `json:"providerRepositoryId,omitempty"`
	ProviderRepositoryName    string   `json:"providerRepositoryName,omitempty"`
	ProviderRepositoryPrivate bool     `json:"providerRepositoryPrivate,omitempty"`
	ProviderRepositoryPending bool     `json:"providerRepositoryPending,omitempty"`
	ProviderBranch            string   `json:"providerBranch,omitempty"`
	ProviderSilentMode        bool     `json:"providerSilentMode,omitempty"`
	ProviderRootDirectory     string   `json:"providerRootDirectory,omitempty"`
	ProviderBranches          []string `json:"providerBranches,omitempty"`
	ProviderPaths             []string `json:"providerPaths,omitempty"`
	TemplateRepository        string   `json:"templateRepository,omitempty"`
	TemplateOwner             string   `json:"templateOwner,omitempty"`
	TemplateRootDirectory     string   `json:"templateRootDirectory,omitempty"`
	TemplateReference         string   `json:"templateReference,omitempty"`
	TemplateReferenceType     string   `json:"templateReferenceType,omitempty"`
}

type initFunctionOptions struct {
	Name                  string
	ID                    string
	Runtime               string
	BuildSpecification    string
	RuntimeSpecification  string
	Public                bool
	Source                string
	DomainTarget          string
	DomainLabel           string
	InstallationID        string
	RepositoryMode        string
	RepositoryID          string
	RepositoryName        string
	RepositoryPrivate     bool
	ProviderBranch        string
	ProviderRootDirectory string
	ProviderSilentMode    bool
	EnvFile               string
	Deploy                bool
}

func newInitFunctionCommand() *cobra.Command {
	options := initFunctionOptions{Public: true, RepositoryPrivate: true}
	command := &cobra.Command{
		Use:     "function",
		Aliases: []string{"functions"},
		Short:   "Init a new Appwrite function",
		PreRunE: func(command *cobra.Command, args []string) error {
			return applyNegatedFlags(command)
		},
		RunE: func(command *cobra.Command, args []string) error {
			return runInitFunction(command, options)
		},
	}

	flags := command.Flags()
	flags.StringVar(&options.Name, "name", "", "Function name")
	flags.StringVar(&options.ID, "function-id", "", "Function ID or unique()")
	flags.StringVar(&options.Runtime, "runtime", "", "Execution runtime")
	flags.StringVar(&options.BuildSpecification, "build-specification", "", "Build CPU and memory specification")
	flags.StringVar(&options.RuntimeSpecification, "runtime-specification", "", "Runtime CPU and memory specification")
	negatableBool(flags, &options.Public, "public", "Allow anyone to execute the function")
	flags.StringVar(&options.Source, "source", "", "Source workflow: local or github")
	flags.StringVar(&options.DomainTarget, "domain-target", "", "Function endpoint: region or edge")
	flags.StringVar(&options.DomainLabel, "domain", "", "Generated function domain label, without its suffix")
	flags.StringVar(&options.InstallationID, "installation-id", "", "Appwrite GitHub installation ID")
	flags.StringVar(&options.RepositoryMode, "repository-mode", "", "GitHub repository mode: existing or new")
	flags.StringVar(&options.RepositoryID, "provider-repository-id", "", "GitHub provider repository ID")
	flags.StringVar(&options.RepositoryName, "repository-name", "", "Name of a new GitHub repository")
	negatableBool(flags, &options.RepositoryPrivate, "repository-private", "Create a private GitHub repository")
	flags.StringVar(&options.ProviderBranch, "provider-branch", "", "Production branch for Git deployments")
	flags.StringVar(&options.ProviderRootDirectory, "provider-root-directory", "", "Path to the function in the Git repository")
	flags.BoolVar(&options.ProviderSilentMode, "provider-silent-mode", false, "Disable VCS commit and pull request comments")
	flags.StringVar(&options.EnvFile, "env-file", "", "Import function variables from a .env file")
	flags.BoolVar(&options.Deploy, "deploy", false, "Create and deploy the function after initialization")

	return command
}

func runInitFunction(command *cobra.Command, options initFunctionOptions) error {
	context, err := newInitContext()
	if err != nil {
		return err
	}

	api, err := scaffoldAPI(context.local)
	if err != nil {
		return err
	}

	out := command.OutOrStdout()

	name := strings.TrimSpace(options.Name)
	if name == "" {
		name, err = context.prompter.Text(prompt.Text{
			Message: "What would you like to name your function?",
			Default: "My Awesome Function",
			Flag:    "--name",
		})
		if err != nil {
			return err
		}
	}

	id := strings.TrimSpace(options.ID)
	if id == "" {
		id, err = context.prompter.Text(prompt.Text{
			Message: "What ID would you like to have for your function?",
			Default: appwrite.UniqueSentinel,
			Flag:    "--function-id",
		})
		if err != nil {
			return err
		}
	}

	runtimeID := strings.TrimSpace(options.Runtime)
	if runtimeID == "" {
		runtimeID, err = chooseRuntime(api, context.prompter)
		if err != nil {
			return err
		}
	} else if err := validateRuntimeChoice(api, runtimeID); err != nil {
		return err
	}
	selected := newRuntimeChoice(runtimeID)

	buildSpecification := strings.TrimSpace(options.BuildSpecification)
	if buildSpecification == "" {
		buildSpecification, err = chooseSpecification(api, context.prompter,
			"/functions/specifications", buildSpecifications,
			"What build specification would you like to use?")
		if err != nil {
			return err
		}
	} else if err := validateSpecificationChoice(
		api, buildSpecifications, buildSpecification,
	); err != nil {
		return err
	}

	runtimeSpecification := strings.TrimSpace(options.RuntimeSpecification)
	if runtimeSpecification == "" {
		runtimeSpecification, err = chooseSpecification(api, context.prompter,
			"/functions/specifications", runtimeSpecifications,
			"What runtime specification would you like to use?")
		if err != nil {
			return err
		}
	} else if err := validateSpecificationChoice(
		api, runtimeSpecifications, runtimeSpecification,
	); err != nil {
		return err
	}

	public := options.Public
	if !command.Flags().Changed("public") {
		public, err = context.prompter.Confirm(prompt.Question{
			Message: "Allow anyone to execute this function?",
			Default: true,
			Flag:    "--public or --no-public",
		})
		if err != nil {
			return err
		}
	}

	domainTarget := strings.ToLower(strings.TrimSpace(options.DomainTarget))
	if domainTarget == "" {
		domainTarget, err = context.prompter.Choice(prompt.Choice{
			Message: "Where should your function run?",
			Options: functionEndpointOptions(api),
			Default: "region",
			Flag:    "--domain-target",
		})
		if err != nil {
			return err
		}
	}
	if domainTarget != "region" && domainTarget != "edge" {
		return fmt.Errorf("domain target must be region or edge")
	}

	functionID := appwrite.ResolveID(id)
	directoryName := SafeDirectoryName(name, functionID)
	domainLabel := strings.ToLower(strings.TrimSpace(options.DomainLabel))
	if domainLabel == "" {
		suffix := appwrite.Unique()
		if len(suffix) > 4 {
			suffix = suffix[:4]
		}
		domainSuffix, _ := functionDomainSuffix(api, domainTarget)
		if domainSuffix != "" {
			domainSuffix = "." + domainSuffix
		}
		domainLabel, err = context.prompter.Text(prompt.Text{
			Message:  "Choose the function domain",
			Default:  directoryName + "-" + suffix,
			Suffix:   domainSuffix,
			Flag:     "--domain",
			Validate: validatePreviewDomainLabel,
		})
		if err != nil {
			return err
		}
	} else if err := validatePreviewDomainLabel(domainLabel); err != nil {
		return err
	}
	functionDomain := ""
	if domain, fatal, domainErr := preflightFunctionDomain(api, domainTarget, domainLabel); domainErr != nil {
		if fatal {
			return domainErr
		}
		output.Warn(out, "Could not check function domain availability: %s", domainErr)
	} else {
		functionDomain = domain
	}
	if functionDomain == "" {
		if suffix, suffixErr := functionDomainSuffix(api, domainTarget); suffixErr == nil {
			functionDomain = domainLabel + "." + suffix
		}
	}

	source := strings.ToLower(strings.TrimSpace(options.Source))
	if source == "" {
		source, err = context.prompter.Choice(prompt.Choice{
			Message: "How would you like to manage the function source?",
			Options: []prompt.Option{
				{Label: "Connect later — deploy local source with the CLI", Value: "local"},
				{Label: "Connect a GitHub repository", Value: "github"},
			},
			Default: "local",
			Flag:    "--source",
		})
		if err != nil {
			return err
		}
	}
	if source != "local" && source != "github" {
		return fmt.Errorf("source must be local or github")
	}

	template := starterFunctionTemplate{
		Repository: "templates", Owner: "appwrite", Reference: "main",
		ReferenceType: "branch", RootDirectory: strings.ToLower(selected.Directory + "/starter"),
	}
	if discovered, lookupErr := getStarterFunctionTemplate(api, selected.ID); lookupErr == nil {
		template = discovered
	} else {
		output.Warn(out, "Could not read starter template metadata; using the default template: %s", lookupErr)
	}

	vcs := functionVCS{}
	for source == "github" {
		vcs, err = configureFunctionVCS(command, api, context.prompter, name, options)
		if err == nil {
			break
		}
		if !errors.Is(err, errNoGitHubInstallation) {
			return err
		}

		setupURL := githubInstallationSetupURL(api.Endpoint,
			context.local.Data.GetString("projectId"))
		if !prompt.Interactive() {
			return fmt.Errorf(
				"no GitHub installation is connected to this project. Set one up at %s, "+
					"or rerun with --source local",
				setupURL)
		}

		action, askErr := promptGitHubInstallationFallback(
			context.prompter, setupURL)
		if askErr != nil {
			return askErr
		}
		switch action {
		case "local":
			source = "local"
		case "retry":
			continue
		default:
			return prompt.ErrAborted
		}
	}

	base := context.local.ResourceDirname("functions")
	functionFolder := filepath.Join(base, "functions")
	availableName, renamed, err := availableFunctionDirectory(functionFolder, directoryName)
	if err != nil {
		return err
	}
	if renamed {
		directoryName = availableName
	}
	functionDir := filepath.Join(functionFolder, directoryName)
	templatesDir := filepath.Join(functionFolder, functionID+"-templates")

	if selected.Entrypoint == "" {
		output.Log(out, "Entrypoint for this runtime not found. You will be "+
			"asked to configure entrypoint when you first push the function.")
	}

	envFile := strings.TrimSpace(options.EnvFile)
	if envFile == "" && prompt.Interactive() {
		environment, askErr := context.prompter.Choice(prompt.Choice{
			Message: "Configure environment variables?",
			Options: []prompt.Option{
				{Label: "No variables", Value: "none"},
				{Label: "Import from a .env file", Value: "import"},
			},
			Default: "none", Flag: "--env-file",
		})
		if askErr != nil {
			return askErr
		}
		if environment == "import" {
			envFile, askErr = context.prompter.Text(prompt.Text{
				Message: "Path to the .env file", Default: ".env",
				Flag: "--env-file", Validate: prompt.Required("environment file"),
			})
			if askErr != nil {
				return askErr
			}
		}
	}
	var envContents []byte
	if envFile != "" {
		envContents, err = os.ReadFile(envFile)
		if err != nil {
			return fmt.Errorf("read environment file %s: %w", envFile, err)
		}
		if names, _ := dotenv.ParseOrdered(string(envContents)); len(names) > 0 {
			if err := validateVariableKeys(names); err != nil {
				return err
			}
		}
	}

	deployNow := options.Deploy
	if prompt.Interactive() {
		printFunctionPreview(out, functionPreview{
			Name:                 name,
			ID:                   functionID,
			Runtime:              selected.ID,
			BuildSpecification:   buildSpecification,
			RuntimeSpecification: runtimeSpecification,
			Public:               public,
			DomainTarget:         domainTarget,
			Domain:               functionDomain,
			Source:               source,
			InstallationID:       vcs.InstallationID,
			Repository:           vcs.RepositoryName,
			Branch:               vcs.Branch,
			RootDirectory:        vcs.RootDirectory,
			SilentMode:           vcs.SilentMode,
			CreateRepository:     vcs.CreateRepository,
			RepositoryPrivate:    vcs.RepositoryPrivate,
			Directory:            "functions/" + directoryName,
			EnvironmentFile:      envFile,
		})
	}
	if !command.Flags().Changed("deploy") && prompt.Interactive() {
		action, askErr := context.prompter.Choice(prompt.Choice{
			Message: "What would you like to do?",
			Options: []prompt.Option{
				{Label: "Save configuration and deploy later", Value: "save"},
				{Label: "Create and deploy now", Value: "deploy"},
				{Label: "Start over and change selections", Value: "redo"},
				{Label: "Cancel", Value: "cancel"},
			},
			Default: "save", Flag: "--deploy",
		})
		if askErr != nil {
			return askErr
		}
		switch action {
		case "redo":
			return runInitFunction(command, options)
		case "cancel":
			return prompt.ErrAborted
		case "deploy":
			deployNow = true
		default:
			deployNow = false
		}
	}

	seedRepository := vcs.CreateRepository
	if err := os.MkdirAll(templatesDir, 0o777); err != nil {
		return err
	}
	defer os.RemoveAll(templatesDir)

	repositoryURL := fmt.Sprintf("https://github.com/%s/%s", template.Owner, template.Repository)
	cloneArguments := []string{"clone", "--single-branch", "--depth", "1"}
	if template.Reference != "" {
		reference := template.Reference
		if template.ReferenceType == "tag" {
			reference, err = resolveGitTag(templatesDir, repositoryURL, reference)
			if err != nil {
				return err
			}
		}
		cloneArguments = append(cloneArguments, "--branch", reference)
	}
	cloneArguments = append(cloneArguments, "--sparse", repositoryURL, ".")
	if err := runGitCommand(templatesDir, cloneArguments...); err != nil {
		return err
	}
	if err := runGitCommand(templatesDir, "sparse-checkout", "add",
		template.RootDirectory); err != nil {
		return err
	}
	if err := os.RemoveAll(filepath.Join(templatesDir, ".git")); err != nil {
		return err
	}
	if err := os.MkdirAll(functionDir, 0o777); err != nil {
		return err
	}
	functionCommitted := false
	defer func() {
		if !functionCommitted {
			_ = os.RemoveAll(functionDir)
		}
	}()
	if err := copyTree(filepath.Join(templatesDir, filepath.FromSlash(template.RootDirectory)),
		functionDir); err != nil {
		return err
	}
	if err := retitleReadme(filepath.Join(functionDir, "README.md"), name); err != nil {
		return err
	}
	if envFile != "" {
		if err := os.WriteFile(filepath.Join(functionDir, ".env"), envContents, 0o600); err != nil {
			return fmt.Errorf("write function environment file: %w", err)
		}
		output.Log(out, "Imported environment variables into functions/%s/.env", directoryName)
	}
	execute := []string{}
	if public {
		execute = []string{"any"}
	}
	entry := FunctionEntry{
		ID: functionID, Name: name, Runtime: selected.ID,
		BuildSpecification: buildSpecification, RuntimeSpecification: runtimeSpecification,
		Execute: execute, Events: []string{}, Scopes: []string{"users.read"},
		Schedule: "", Timeout: 15, Enabled: true, Logging: true,
		Entrypoint: selected.Entrypoint, Commands: selected.Commands,
		Ignore: selected.Ignore, DeploymentRetention: 0,
		Path:                "functions/" + directoryName,
		PreviewDomainTarget: domainTarget, PreviewDomainLabel: domainLabel,
		InstallationID:            vcs.InstallationID,
		ProviderRepositoryID:      vcs.RepositoryID,
		ProviderRepositoryName:    vcs.RepositoryName,
		ProviderRepositoryPrivate: vcs.RepositoryPrivate,
		ProviderRepositoryPending: vcs.CreateRepository,
		ProviderBranch:            vcs.Branch,
		ProviderSilentMode:        vcs.SilentMode,
		ProviderRootDirectory:     vcs.RootDirectory,
	}
	if source == "github" && seedRepository {
		entry.TemplateRepository = template.Repository
		entry.TemplateOwner = template.Owner
		entry.TemplateRootDirectory = template.RootDirectory
		entry.TemplateReference = template.Reference
		entry.TemplateReferenceType = template.ReferenceType
	}
	if err := context.local.AddFunction(entry); err != nil {
		return err
	}
	if err := context.local.Write(); err != nil {
		return err
	}
	functionCommitted = true

	output.Success(out, "Initialized function")
	output.Log(out, "Endpoint target: %s", domainTarget)
	if source == "github" {
		output.Log(out, "Source: GitHub repository %s on branch %s", vcs.RepositoryName, vcs.Branch)
	}

	if !deployNow {
		output.Log(out, "Use '%s run function' to develop locally, or '%s push function' to deploy.",
			app.ExecutableName, app.ExecutableName)
		return nil
	}

	previousForce := app.Flags().Force
	app.Flags().Force = true
	defer func() { app.Flags().Force = previousForce }()

	return runPushDeployable(command, deployables[0], deployOptions{
		ResourceID:    functionID,
		Code:          true,
		Activate:      true,
		ActivateSet:   true,
		Logs:          true,
		WithVariables: envFile != "",
		FailOnError:   true,
	})
}

type functionPreview struct {
	Name                 string
	ID                   string
	Runtime              string
	BuildSpecification   string
	RuntimeSpecification string
	Public               bool
	DomainTarget         string
	Domain               string
	Source               string
	InstallationID       string
	Repository           string
	Branch               string
	RootDirectory        string
	SilentMode           bool
	CreateRepository     bool
	RepositoryPrivate    bool
	Directory            string
	EnvironmentFile      string
}

func printFunctionPreview(writer io.Writer, preview functionPreview) {
	access := "Restricted"
	if preview.Public {
		access = "Public (any)"
	}
	target := "Region compute"
	if preview.DomainTarget == "edge" {
		target = "Edge network"
	}
	source := "Local CLI deployment"
	if preview.Source == "github" {
		source = "GitHub · " + preview.Repository
		if preview.CreateRepository {
			visibility := "public"
			if preview.RepositoryPrivate {
				visibility = "private"
			}
			source += " (new, " + visibility + ")"
		}
	}
	environment := "None"
	if preview.EnvironmentFile != "" {
		environment = preview.EnvironmentFile
	}

	fields := []output.FailureDetail{
		{Label: "Name", Value: preview.Name},
		{Label: "Function ID", Value: preview.ID},
		{Label: "Runtime", Value: preview.Runtime},
		{Label: "Build compute", Value: preview.BuildSpecification},
		{Label: "Runtime compute", Value: preview.RuntimeSpecification},
		{Label: "Access", Value: access},
		{Label: "Execution", Value: target},
		{Label: "Endpoint", Value: "https://" + preview.Domain},
		{Label: "Source", Value: source},
		{Label: "Directory", Value: preview.Directory},
		{Label: "Environment", Value: environment},
	}
	if preview.Source == "github" {
		comments := "Enabled"
		if preview.SilentMode {
			comments = "Silent"
		}
		fields = append(fields,
			output.FailureDetail{Label: "Installation", Value: preview.InstallationID},
			output.FailureDetail{Label: "Branch", Value: preview.Branch},
			output.FailureDetail{Label: "Repository path", Value: preview.RootDirectory},
			output.FailureDetail{Label: "GitHub comments", Value: comments},
		)
	}

	width := 0
	for _, field := range fields {
		width = max(width, len(field.Label))
	}
	fmt.Fprintf(writer, "\n%s\n\n", output.Heading("Function preview"))
	for _, field := range fields {
		fmt.Fprintf(writer, "  %-*s   %s\n", width, field.Label, field.Value)
	}
	fmt.Fprintln(writer)
}

func availableFunctionDirectory(parent, preferred string) (string, bool, error) {
	for suffix := 1; ; suffix++ {
		candidate := preferred
		if suffix > 1 {
			candidate = fmt.Sprintf("%s-%d", preferred, suffix)
		}
		_, err := os.Stat(filepath.Join(parent, candidate))
		switch {
		case errors.Is(err, os.ErrNotExist):
			return candidate, suffix > 1, nil
		case err != nil:
			return "", false, err
		}
	}
}

func functionEndpointOptions(api *client.Client) []prompt.Option {
	domains, _, _ := availableFunctionDomains(api)

	return functionEndpointOptionsFromDomains(api.Endpoint, domains)
}

func functionEndpointOptionsFromDomains(endpoint string, domains []string) []prompt.Option {
	regionLabel := "Region compute"
	if regionSuffix, err := functionDomainForTarget(domains, "region"); err == nil {
		regionLabel += " (*." + regionSuffix + ")"
	} else if region := endpointRegion(endpoint); region != "" {
		regionLabel += " (" + region + ")"
	}

	options := []prompt.Option{
		{
			Label: regionLabel,
			Value: "region",
		},
	}

	edgeSuffix, _ := functionDomainForTarget(domains, "edge")
	if edgeSuffix != "" {
		options = append(options, prompt.Option{
			Label: "Edge network (*." + edgeSuffix + ")",
			Value: "edge",
		})
	}

	return options
}

func functionDomainSuffix(api *client.Client, target string) (string, error) {
	domains, _, _ := availableFunctionDomains(api)

	return functionDomainForTarget(domains, target)
}

func availableFunctionDomains(
	api *client.Client,
) ([]string, *client.Client, error) {
	console, _, consoleErr := consoleClientAt(api.Endpoint)
	domains := make([]string, 0)
	if consoleErr == nil {
		variables := jsonx.NewObject()
		if err := console.Call("GET", "/console/variables", nil, variables); err == nil {
			configured := strings.Join([]string{
				// Prefer the Sites suffix for edge Functions. Regional Cloud responses
				// can also include a region-prefixed appwrite.network Functions suffix.
				variables.GetString("_APP_DOMAIN_SITES"),
				variables.GetString("_APP_DOMAIN_FUNCTIONS"),
			}, ",")
			for _, domain := range strings.Split(configured, ",") {
				domain = strings.TrimSpace(domain)
				if domain == "" {
					continue
				}
				if region := endpointRegion(api.Endpoint); region != "" &&
					!strings.HasSuffix(strings.ToLower(domain), ".appwrite.network") {
					parts := strings.Split(domain, ".")
					if len(parts) >= 3 {
						parts[0] = region
						domain = strings.Join(parts, ".")
					}
				}
				domains = append(domains, domain)
			}
		} else {
			consoleErr = err
		}
	}
	if len(domains) == 0 {
		domains = cloudFunctionDomains(api.Endpoint)
	}

	return domains, console, consoleErr
}

func preflightFunctionDomain(
	api *client.Client,
	target, label string,
) (string, bool, error) {
	domains, console, consoleErr := availableFunctionDomains(api)
	if len(domains) == 0 && consoleErr != nil {
		return "", false, consoleErr
	}

	suffix, err := functionDomainForTarget(domains, target)
	if err != nil {
		return "", true, err
	}
	domain := label + "." + suffix

	// An API-key workflow can derive Cloud's endpoint without a Console
	// session, but only a session can query global rule availability.
	if consoleErr != nil {
		return domain, false, nil
	}

	query := url.Values{"value": []string{domain}, "type": []string{"rules"}}
	if err := console.Call("GET", "/console/resources?"+query.Encode(), nil, nil); err != nil {
		var apiErr *client.APIError
		if errors.As(err, &apiErr) && apiErr.Status == 409 {
			return "", true, fmt.Errorf("function domain %s is not available", domain)
		}
		return "", false, err
	}

	return domain, false, nil
}

func validatePreviewDomainLabel(value string) error {
	value = strings.TrimSpace(value)
	if value == "" {
		return fmt.Errorf("domain is required")
	}
	if len(value) > 63 {
		return fmt.Errorf("domain must be at most 63 characters")
	}
	for index, character := range value {
		valid := character >= 'a' && character <= 'z' ||
			character >= '0' && character <= '9' || character == '-'
		if !valid {
			return fmt.Errorf("domain can contain only lowercase letters, numbers, and hyphens")
		}
		if character == '-' && (index == 0 || index == len(value)-1) {
			return fmt.Errorf("domain cannot start or end with a hyphen")
		}
	}

	return nil
}

type starterFunctionTemplate struct {
	Repository    string
	Owner         string
	Reference     string
	ReferenceType string
	RootDirectory string
}

func getStarterFunctionTemplate(api *client.Client, runtime string) (starterFunctionTemplate, error) {
	var response struct {
		ID                   string `json:"id"`
		ProviderRepositoryID string `json:"providerRepositoryId"`
		ProviderOwner        string `json:"providerOwner"`
		ProviderVersion      string `json:"providerVersion"`
		Runtimes             []struct {
			Name                  string `json:"name"`
			ProviderRootDirectory string `json:"providerRootDirectory"`
		} `json:"runtimes"`
	}
	if err := api.Call("GET", "/functions/templates/starter", nil, &response); err != nil {
		return starterFunctionTemplate{}, err
	}
	for _, candidate := range response.Runtimes {
		if candidate.Name != runtime {
			continue
		}
		typeName := "tag"
		if response.ProviderVersion == "main" || response.ProviderVersion == "master" {
			typeName = "branch"
		}
		return starterFunctionTemplate{
			Repository:    response.ProviderRepositoryID,
			Owner:         response.ProviderOwner,
			Reference:     response.ProviderVersion,
			ReferenceType: typeName,
			RootDirectory: strings.TrimPrefix(candidate.ProviderRootDirectory, "./"),
		}, nil
	}

	return starterFunctionTemplate{}, fmt.Errorf("starter template does not support runtime %s", runtime)
}

var errNoGitHubInstallation = errors.New("no GitHub installation is connected to this project")

func githubInstallationSetupURL(endpoint, projectID string) string {
	base := consoleBaseURL(config.NormalizeCloudConsoleEndpoint(endpoint))
	region := endpointRegion(endpoint)
	if region != "" {
		return fmt.Sprintf("%s/console/project-%s-%s/settings",
			base, region, url.PathEscape(projectID))
	}
	if _, cloud := config.CloudBaseHost(endpoint); cloud {
		return fmt.Sprintf("%s/console/project-%s/settings",
			base, url.PathEscape(projectID))
	}

	return fmt.Sprintf("%s/console/project-default-%s/settings",
		base, url.PathEscape(projectID))
}

func githubInstallationDescription(setupURL string) string {
	return "No GitHub installation is connected to this project.\n\n" +
		"Set one up in the Appwrite Console:\n" + setupURL
}

func promptGitHubInstallationFallback(
	prompter prompt.Prompter,
	setupURL string,
) (string, error) {
	return prompter.Choice(prompt.Choice{
		Message:     "GitHub connection required",
		Description: githubInstallationDescription(setupURL),
		Options: []prompt.Option{
			{Label: "Continue with local source", Value: "local"},
			{Label: "I connected GitHub — retry", Value: "retry"},
			{Label: "Cancel", Value: "cancel"},
		},
		Default: "local", Flag: "--source local",
	})
}

type functionVCS struct {
	InstallationID    string
	RepositoryID      string
	RepositoryName    string
	Branch            string
	RootDirectory     string
	SilentMode        bool
	CreateRepository  bool
	RepositoryPrivate bool
}

type vcsInstallation struct {
	ID           string `json:"$id"`
	Organization string `json:"organization"`
}

type vcsRepository struct {
	ID            string `json:"id"`
	Name          string `json:"name"`
	Organization  string `json:"organization"`
	DefaultBranch string `json:"defaultBranch"`
}

func configureFunctionVCS(
	command *cobra.Command,
	api *client.Client,
	prompter prompt.Prompter,
	functionName string,
	options initFunctionOptions,
) (functionVCS, error) {
	var installationList struct {
		Installations []vcsInstallation `json:"installations"`
	}
	if err := api.Call("GET", "/vcs/installations", nil, &installationList); err != nil {
		return functionVCS{}, fmt.Errorf("list GitHub installations: %w", err)
	}
	if len(installationList.Installations) == 0 {
		return functionVCS{}, errNoGitHubInstallation
	}

	installationID := strings.TrimSpace(options.InstallationID)
	if installationID == "" {
		choices := make([]prompt.Option, 0, len(installationList.Installations))
		for _, installation := range installationList.Installations {
			choices = append(choices, prompt.Option{
				Label: installation.Organization + " (" + installation.ID + ")",
				Value: installation.ID,
			})
		}
		var err error
		installationID, err = prompter.Choice(prompt.Choice{
			Message: "Select a GitHub installation", Options: choices,
			Default: choices[0].Value, Flag: "--installation-id",
		})
		if err != nil {
			return functionVCS{}, err
		}
	}

	repositoryMode := strings.ToLower(strings.TrimSpace(options.RepositoryMode))
	if repositoryMode == "" {
		if options.RepositoryID != "" {
			repositoryMode = "existing"
		} else {
			var err error
			repositoryMode, err = prompter.Choice(prompt.Choice{
				Message: "Which repository would you like to use?",
				Options: []prompt.Option{
					{Label: "Create a new repository", Value: "new"},
					{Label: "Connect an existing repository", Value: "existing"},
				},
				Default: "new", Flag: "--repository-mode",
			})
			if err != nil {
				return functionVCS{}, err
			}
		}
	}
	if repositoryMode != "new" && repositoryMode != "existing" {
		return functionVCS{}, fmt.Errorf("repository mode must be new or existing")
	}

	installationOrganization := ""
	for _, installation := range installationList.Installations {
		if installation.ID == installationID {
			installationOrganization = installation.Organization
			break
		}
	}

	var repository vcsRepository
	createRepository := false
	repositoryPrivate := false
	if repositoryMode == "new" {
		repositoryName := strings.TrimSpace(options.RepositoryName)
		if repositoryName == "" {
			var err error
			repositoryName, err = prompter.Text(prompt.Text{
				Message: "Name the new GitHub repository",
				Default: SafeDirectoryName(functionName, "appwrite-function"),
				Flag:    "--repository-name", Validate: prompt.Required("repository name"),
			})
			if err != nil {
				return functionVCS{}, err
			}
		}
		private := options.RepositoryPrivate
		if !command.Flags().Changed("repository-private") {
			var err error
			private, err = prompter.Confirm(prompt.Question{
				Message: "Keep the repository private?", Default: true,
				Flag: "--repository-private",
			})
			if err != nil {
				return functionVCS{}, err
			}
		}
		// Repository creation is deferred until the review screen is accepted.
		// Choosing “Start over” must not leave an unused GitHub repository behind.
		repository = vcsRepository{
			Name: repositoryName, Organization: installationOrganization,
			DefaultBranch: "main",
		}
		createRepository = true
		repositoryPrivate = private
	} else {
		repositoryID := strings.TrimSpace(options.RepositoryID)
		basePath := "/vcs/github/installations/" + url.PathEscape(installationID) +
			"/providerRepositories"
		if repositoryID != "" {
			path := basePath + "/" + url.PathEscape(repositoryID)
			if err := api.Call("GET", path, nil, &repository); err != nil {
				return functionVCS{}, fmt.Errorf("get GitHub repository %s: %w", repositoryID, err)
			}
		} else {
			var listing struct {
				Repositories []vcsRepository `json:"runtimeProviderRepositories"`
			}
			path := basePath + "?type=runtime&" + client.EncodeQueries(
				[]string{`{"method":"limit","values":[100]}`})
			if err := api.Call("GET", path, nil, &listing); err != nil {
				return functionVCS{}, fmt.Errorf("list GitHub repositories: %w", err)
			}
			if len(listing.Repositories) == 0 {
				return functionVCS{}, fmt.Errorf("the selected GitHub installation has no repositories")
			}
			choices := make([]prompt.Option, 0, len(listing.Repositories))
			for _, candidate := range listing.Repositories {
				choices = append(choices, prompt.Option{
					Label: candidate.Organization + "/" + candidate.Name,
					Value: candidate.ID,
				})
			}
			var err error
			repositoryID, err = prompter.Choice(prompt.Choice{
				Message: "Select a GitHub repository", Options: choices,
				Filter: true, Flag: "--provider-repository-id",
			})
			if err != nil {
				return functionVCS{}, err
			}
			for _, candidate := range listing.Repositories {
				if candidate.ID == repositoryID {
					repository = candidate
					break
				}
			}
		}
		if repository.ID == "" {
			return functionVCS{}, fmt.Errorf("repository %s is not available to installation %s",
				repositoryID, installationID)
		}
	}

	branch := strings.TrimSpace(options.ProviderBranch)
	if branch == "" {
		branch = repository.DefaultBranch
		if branch == "" {
			branch = "main"
		}
		if repository.ID != "" {
			var listing struct {
				Branches []struct {
					Name string `json:"name"`
				} `json:"branches"`
			}
			path := "/vcs/github/installations/" + url.PathEscape(installationID) +
				"/providerRepositories/" + url.PathEscape(repository.ID) + "/branches?" +
				client.EncodeQueries([]string{`{"method":"limit","values":[100]}`})
			if err := api.Call("GET", path, nil, &listing); err == nil && len(listing.Branches) > 0 {
				choices := make([]prompt.Option, 0, len(listing.Branches))
				for _, candidate := range listing.Branches {
					choices = append(choices, prompt.Option{Label: candidate.Name, Value: candidate.Name})
				}
				var err error
				branch, err = prompter.Choice(prompt.Choice{
					Message: "Select the production branch", Options: choices,
					Default: branch, Filter: true, Flag: "--provider-branch",
				})
				if err != nil {
					return functionVCS{}, err
				}
			}
		}
	}

	rootDirectory := strings.TrimSpace(options.ProviderRootDirectory)
	if rootDirectory == "" {
		var err error
		rootDirectory, err = prompter.Text(prompt.Text{
			Message: "Function root directory", Default: "./",
			Flag: "--provider-root-directory", Validate: prompt.Required("root directory"),
		})
		if err != nil {
			return functionVCS{}, err
		}
	}

	silentMode := options.ProviderSilentMode
	if !command.Flags().Changed("provider-silent-mode") {
		var err error
		silentMode, err = prompter.Confirm(prompt.Question{
			Message: "Enable silent mode for Git comments?", Default: false,
			Flag: "--provider-silent-mode",
		})
		if err != nil {
			return functionVCS{}, err
		}
	}

	return functionVCS{
		InstallationID: installationID, RepositoryID: repository.ID,
		RepositoryName: strings.TrimPrefix(repository.Organization+"/"+repository.Name, "/"),
		Branch:         branch, RootDirectory: rootDirectory, SilentMode: silentMode,
		CreateRepository: createRepository, RepositoryPrivate: repositoryPrivate,
	}, nil
}

func createFunctionVCSRepository(
	api *client.Client,
	vcs functionVCS,
) (functionVCS, error) {
	parts := strings.SplitN(vcs.RepositoryName, "/", 2)
	repositoryName := vcs.RepositoryName
	if len(parts) == 2 {
		repositoryName = parts[1]
	}
	body := jsonx.NewObject()
	body.Set("name", repositoryName)
	body.Set("private", vcs.RepositoryPrivate)
	path := "/vcs/github/installations/" + url.PathEscape(vcs.InstallationID) +
		"/providerRepositories"
	var repository vcsRepository
	if err := api.Call("POST", path, body, &repository); err != nil {
		return functionVCS{}, fmt.Errorf("create GitHub repository: %w", err)
	}

	vcs.RepositoryID = repository.ID
	vcs.RepositoryName = repository.Organization + "/" + repository.Name
	if repository.DefaultBranch != "" {
		vcs.Branch = repository.DefaultBranch
	}
	vcs.CreateRepository = false

	return vcs, nil
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
		// Start in filtering mode so typing "go" immediately narrows the list;
		// the user should not have to discover and press `/` first.
		Filter: true,
		Flag:   "--runtime",
	})
}

func validateRuntimeChoice(api *client.Client, runtime string) error {
	var response struct {
		Runtimes []struct {
			ID string `json:"$id"`
		} `json:"runtimes"`
	}
	if err := api.Call("GET", "/functions/runtimes", nil, &response); err != nil {
		return err
	}
	for _, entry := range response.Runtimes {
		if entry.ID == runtime {
			return nil
		}
	}

	return fmt.Errorf("runtime %s is not available for this project", runtime)
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

func validateSpecificationChoice(
	api *client.Client,
	specificationType, specification string,
) error {
	var response struct {
		Specifications []struct {
			Slug    string `json:"slug"`
			Enabled *bool  `json:"enabled"`
		} `json:"specifications"`
	}
	query := url.Values{"type": []string{specificationType}}
	if err := api.Call("GET", "/functions/specifications?"+query.Encode(), nil, &response); err != nil {
		return err
	}
	for _, entry := range response.Specifications {
		if entry.Slug == specification && (entry.Enabled == nil || *entry.Enabled) {
			return nil
		}
	}

	return fmt.Errorf("%s specification %s is not available for this project",
		strings.TrimSuffix(specificationType, "s"), specification)
}

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

	flag := "--runtime-specification"
	if specificationType == buildSpecifications {
		flag = "--build-specification"
	}

	return prompter.Choice(prompt.Choice{
		Message: message, Options: options, Flag: flag,
	})
}

// scaffoldAPI builds a project-scoped client for the init scaffolds.
func scaffoldAPI(local *config.Local) (*client.Client, error) {
	global, err := preferences()
	if err != nil {
		return nil, err
	}

	return projectAPI(global, local)
}

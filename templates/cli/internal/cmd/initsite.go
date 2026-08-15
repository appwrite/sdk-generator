//go:build !browser

package cmd

import (
	"encoding/json"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"strconv"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Unlike `init function`, the template repository is not a constant: the API is
// asked for the framework's starter, and the answer names the repo, tag and
// subdirectory. That is why this command can partly succeed -- the config entry
// is still written when the template lookup fails, unless the user asked to
// download it.

// SiteEntry is a site as `init site` writes it.
type SiteEntry struct {
	ID                   string `json:"$id"`
	Name                 string `json:"name"`
	Framework            string `json:"framework"`
	Adapter              string `json:"adapter"`
	BuildRuntime         string `json:"buildRuntime"`
	InstallCommand       string `json:"installCommand"`
	BuildCommand         string `json:"buildCommand"`
	OutputDirectory      string `json:"outputDirectory"`
	FallbackFile         string `json:"fallbackFile"`
	BuildSpecification   string `json:"buildSpecification"`
	RuntimeSpecification string `json:"runtimeSpecification"`
	DeploymentRetention  int    `json:"deploymentRetention"`
	Timeout              int    `json:"timeout"`
	Logging              bool   `json:"logging"`
	Path                 string `json:"path"`
}

// siteTemplate is the part of a template listing this command uses.
type siteTemplate struct {
	ProviderOwner        string `json:"providerOwner"`
	ProviderRepositoryID string `json:"providerRepositoryId"`
	ProviderVersion      string `json:"providerVersion"`
	Frameworks           []struct {
		Key                   string `json:"key"`
		Adapter               string `json:"adapter"`
		BuildRuntime          string `json:"buildRuntime"`
		InstallCommand        string `json:"installCommand"`
		BuildCommand          string `json:"buildCommand"`
		OutputDirectory       string `json:"outputDirectory"`
		FallbackFile          string `json:"fallbackFile"`
		ProviderRootDirectory string `json:"providerRootDirectory"`
	} `json:"frameworks"`
	// Variables are the environment variables the template's code reads. The
	// API declares them with placeholders the CLI is expected to fill -- see
	// writeTemplateEnv. Declaring the field and never reading it is the bug
	// being fixed here.
	Variables []siteTemplateVariable `json:"variables"`
}

// siteTemplateVariable is one environment variable a starter template needs.
//
// Value carries a placeholder such as `{projectId}` rather than a literal: the
// template is generic and the CLI is the only party that knows which project it
// is being scaffolded into.
type siteTemplateVariable struct {
	Name     string `json:"name"`
	Value    string `json:"value"`
	Required bool   `json:"required"`
}

func newInitSiteCommand() *cobra.Command {
	return &cobra.Command{
		Use:     "site",
		Aliases: []string{"sites"},
		Short:   "Init a new Appwrite site",
		RunE: func(command *cobra.Command, args []string) error {
			return runInitSite(command)
		},
	}
}

func runInitSite(command *cobra.Command) error {
	context, err := newInitContext()
	if err != nil {
		return err
	}

	api, err := scaffoldAPI(context.local)
	if err != nil {
		return err
	}

	out := command.OutOrStdout()
	base := context.local.ResourceDirname("sites")

	name, err := context.prompter.Text(prompt.Text{
		Message: "What would you like to name your site?",
		Default: "My Awesome Site",
	})
	if err != nil {
		return err
	}

	id, err := context.prompter.Text(prompt.Text{
		Message: "What ID would you like to have for your site?",
		Default: appwrite.UniqueSentinel,
	})
	if err != nil {
		return err
	}

	// The default path is derived from the NAME already given, which is why
	// this question cannot be asked before the name.
	rawPath, err := context.prompter.Text(prompt.Text{
		Message:  "What local path would you like to use for your site?",
		Default:  "sites/" + SafeDirectoryName(name, "my-awesome-site"),
		Validate: validateSitePath(base),
	})
	if err != nil {
		return err
	}

	framework, err := chooseFramework(api, context.prompter)
	if err != nil {
		return err
	}

	buildSpecification, err := chooseSpecification(api, context.prompter,
		"/sites/specifications", buildSpecifications,
		"What build specification would you like to use?")
	if err != nil {
		return err
	}

	runtimeSpecification, err := chooseSpecification(api, context.prompter,
		"/sites/specifications", runtimeSpecifications,
		"What runtime specification would you like to use?")
	if err != nil {
		return err
	}

	retention, err := context.prompter.Text(prompt.Text{
		Message: "How many days would you like to keep non-active deployments? " +
			"(0 = keep all deployments)",
		Default:  "0",
		Validate: prompt.NonNegativeInteger,
	})
	if err != nil {
		return err
	}

	downloadTemplate, err := context.prompter.Confirm(prompt.Question{
		Message: "Do you want to download the starter template code?",
		Default: false,
	})
	if err != nil {
		return err
	}

	siteID := appwrite.ResolveID(id)
	sitePath := strings.TrimSpace(rawPath)
	siteDir := sitePath
	if !filepath.IsAbs(siteDir) {
		siteDir = filepath.Join(base, sitePath)
	}
	siteFolder := filepath.Join(base, "sites")
	templatesDir := filepath.Join(siteFolder, siteID+"-templates")

	info, statErr := os.Stat(siteDir)
	siteDirExists := statErr == nil
	if siteDirExists && !info.IsDir() {
		return fmt.Errorf(
			"( %s ) already exists and is not a directory. Please choose another path",
			sitePath)
	}
	_, siteFolderErr := os.Stat(siteFolder)
	siteFolderExists := siteFolderErr == nil

	siteDirIsEmpty := !siteDirExists || isEmptyDirectory(siteDir)

	if downloadTemplate && siteDirExists && !siteDirIsEmpty {
		return fmt.Errorf(
			"( %s ) already exists and is not empty. Please choose another path", sitePath)
	}
	if !downloadTemplate && siteDirExists && !siteDirIsEmpty {
		output.Hint(out, "Using existing non-empty site directory '%s'. "+
			"No starter template code will be downloaded.", sitePath)
	}

	// The lookup failure is fatal only when the template was actually wanted.
	// Otherwise the site is still configured, just without the framework's
	// build defaults, and the missing ones are reported at the end.
	template, lookupErr := starterTemplate(api, framework)
	if lookupErr != nil {
		if downloadTemplate {
			return fmt.Errorf("Failed to fetch template for framework %s: %w",
				framework, lookupErr)
		}
		output.Log(out, "Failed to fetch template details for framework %s: %s",
			framework, lookupErr)
	}

	createdSiteDir := !siteDirExists
	if !siteDirExists {
		if err := os.MkdirAll(siteDir, 0o777); err != nil {
			return err
		}
	}

	if downloadTemplate {
		if err := downloadSiteTemplate(out, template, framework, siteDir,
			siteFolder, templatesDir, name); err != nil {
			// Everything this command created is removed, so a failed
			// download does not leave a half-populated directory that the
			// next run then refuses as "already exists and is not empty".
			_ = os.RemoveAll(templatesDir)
			if createdSiteDir {
				_ = os.RemoveAll(siteDir)
			}
			if !siteFolderExists && isEmptyDirectory(siteFolder) {
				_ = os.RemoveAll(siteFolder)
			}

			return err
		}

		// Only for a downloaded template: the variables belong to ITS code, so
		// writing them beside a directory the user populated themselves would be
		// guessing at what that code reads.
		writeTemplateEnv(out, context.local, api, template, siteDir, sitePath)
	}

	entry := SiteEntry{
		ID:                   siteID,
		Name:                 name,
		Framework:            framework,
		BuildSpecification:   buildSpecification,
		RuntimeSpecification: runtimeSpecification,
		DeploymentRetention:  atoiOrZero(retention),
		Timeout:              30,
		Logging:              true,
		Path:                 sitePath,
	}

	if template != nil && len(template.Frameworks) > 0 {
		details := template.Frameworks[0]
		entry.Adapter = details.Adapter
		entry.BuildRuntime = details.BuildRuntime
		entry.InstallCommand = details.InstallCommand
		entry.BuildCommand = details.BuildCommand
		entry.OutputDirectory = details.OutputDirectory
		entry.FallbackFile = details.FallbackFile
	}

	if entry.BuildRuntime == "" {
		output.Log(out, "Build runtime for this framework not found. You will "+
			"be asked to configure build runtime when you first push the site.")
	}
	if entry.InstallCommand == "" {
		output.Log(out, "Installation command for this framework not found. You "+
			"will be asked to configure the install command when you first push the site.")
	}
	if entry.BuildCommand == "" && siteEntryRequiresBuildCommand(entry) {
		output.Log(out, "Build command for this framework not found. You will "+
			"be asked to configure the build command when you first push the site.")
	}
	if entry.OutputDirectory == "" {
		output.Log(out, "Output directory for this framework not found. You will "+
			"be asked to configure the output directory when you first push the site.")
	}

	if err := context.local.AddSite(entry); err != nil {
		return err
	}
	if err := context.local.Write(); err != nil {
		return err
	}

	output.Success(out, "Initializing site")
	output.Log(out, "Next you can use '%s push site' to deploy the changes.",
		app.ExecutableName)

	return nil
}

// siteEntryRequiresBuildCommand reuses the push-side rule.
//
// siteRequiresBuildCommand takes the config document; this command has a
// struct, so it is rendered once rather than the rule being written twice.
func siteEntryRequiresBuildCommand(entry SiteEntry) bool {
	encoded, err := json.Marshal(entry)
	if err != nil {
		return false
	}

	object := jsonx.NewObject()
	if err := object.UnmarshalJSON(encoded); err != nil {
		return false
	}

	return siteRequiresBuildCommand(object)
}

// downloadSiteTemplate fetches the framework's starter into the site directory.
//
// The clone is built by hand rather than with `git clone` because the template
// lives at a tag in a subdirectory of a shared repository: a sparse checkout of
// one directory at one tag is cheaper than cloning the whole thing.
func downloadSiteTemplate(
	out interface{ Write([]byte) (int, error) },
	template *siteTemplate,
	framework string,
	siteDir string,
	siteFolder string,
	templatesDir string,
	name string,
) error {
	if template == nil || len(template.Frameworks) == 0 {
		return fmt.Errorf("No starter template found for framework %s", framework)
	}

	root := template.Frameworks[0].ProviderRootDirectory

	if err := os.MkdirAll(siteFolder, 0o777); err != nil {
		return err
	}
	if err := os.MkdirAll(templatesDir, 0o777); err != nil {
		return err
	}
	defer os.RemoveAll(templatesDir)

	repo := fmt.Sprintf("https://github.com/%s/%s",
		template.ProviderOwner, template.ProviderRepositoryID)

	setup := []string{
		"git init",
		"git remote add origin " + repo,
		"git config --global init.defaultBranch main",
	}

	if root != "./" {
		sparse := strings.TrimPrefix(root, "./")
		setup = append(setup,
			"git config core.sparseCheckout true",
			fmt.Sprintf("echo %q >> .git/info/sparse-checkout", sparse),
			"git config --add remote.origin.fetch '+refs/heads/*:refs/remotes/origin/*'",
			"git config remote.origin.tagopt --no-tags",
		)
	}

	output.Log(out, "Fetching site code ...")

	// The tag is resolved remotely and the LAST match taken, which is how a
	// version like "0.1.*" picks its newest release.
	setup = append(setup,
		fmt.Sprintf("git fetch --depth=1 origin refs/tags/$(git ls-remote --tags "+
			"origin %q | tail -n 1 | awk -F '/' '{print $3}')", template.ProviderVersion),
		"git checkout FETCH_HEAD",
	)

	if err := runGit(templatesDir, strings.Join(setup, "\n")); err != nil {
		return err
	}

	if err := os.RemoveAll(filepath.Join(templatesDir, ".git")); err != nil {
		return err
	}

	source := templatesDir
	if root != "./" {
		source = filepath.Join(templatesDir, root)
	}
	if err := copyTree(source, siteDir); err != nil {
		return err
	}

	// Unlike a function template, a site template is not guaranteed to ship a
	// README, so a missing one is not an error.
	readme := filepath.Join(siteDir, "README.md")
	if _, err := os.Stat(readme); err == nil {
		return retitleReadme(readme, name)
	}

	return nil
}

// starterTemplate asks the API for the framework's starter template.
func starterTemplate(api *client.Client, framework string) (*siteTemplate, error) {
	var response struct {
		Total     int            `json:"total"`
		Templates []siteTemplate `json:"templates"`
	}

	path := fmt.Sprintf("/sites/templates?frameworks[0]=%s&useCases[0]=starter&limit=1",
		framework)
	if err := api.Call("GET", path, nil, &response); err != nil {
		return nil, err
	}

	if response.Total == 0 || len(response.Templates) == 0 {
		return nil, fmt.Errorf("No starter template found for framework %s", framework)
	}

	return &response.Templates[0], nil
}

// writeTemplateEnv writes the .env a scaffolded template needs to build.
//
// Every starter reads its endpoint and project from the environment, and the API
// declares them with `{apiEndpoint}`, `{projectId}` and `{projectName}`
// placeholders for the CLI to fill. Nothing filled them, so the first deploy of
// every template site failed in the build.
//
// An existing .env is never touched -- it is the one file in a scaffolded site
// that may already hold secrets.
func writeTemplateEnv(
	out io.Writer,
	local *config.Local,
	api *client.Client,
	template *siteTemplate,
	siteDir, sitePath string,
) {
	if template == nil || len(template.Variables) == 0 {
		return
	}

	path := filepath.Join(siteDir, ".env")
	if _, err := os.Stat(path); err == nil {
		output.Hint(out, "'%s/.env' already exists, so the template's variables were left alone.",
			sitePath)

		return
	}

	endpoint := local.Data.GetString("endpoint")
	if endpoint == "" {
		endpoint = api.Endpoint
	}
	fill := strings.NewReplacer(
		"{apiEndpoint}", endpoint,
		"{projectId}", local.Data.GetString("projectId"),
		"{projectName}", local.Data.GetString("projectName"),
	)

	var (
		lines      []string
		unresolved []string
	)
	for _, variable := range template.Variables {
		if variable.Name == "" {
			continue
		}

		value := fill.Replace(variable.Value)
		// A placeholder this CLI does not know how to fill is left EMPTY rather
		// than written through literally: `{apiKey}` in a .env is a value the
		// build would use as if it were real, and an empty one fails in a way
		// that points at the variable.
		if strings.ContainsAny(value, "{}") {
			value = ""
		}
		if value == "" && variable.Required {
			unresolved = append(unresolved, variable.Name)
		}

		lines = append(lines, variable.Name+"="+value)
	}

	if len(lines) == 0 {
		return
	}

	contents := strings.Join(lines, "\n") + "\n"
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		// Not fatal. The site is configured and its code is downloaded; the
		// user can write the file themselves, and saying so is more use than
		// unwinding a successful scaffold.
		output.Warn(out, "Could not write '%s/.env': %s", sitePath, err)

		return
	}

	output.Log(out, "Wrote '%s/.env' with %d variable(s) the template needs to build.",
		sitePath, len(lines))
	if len(unresolved) > 0 {
		output.Hint(out, "Fill in %s before deploying -- the template requires them.",
			strings.Join(unresolved, ", "))
	}
	// The variables are local until they are pushed, and the build runs on the
	// server. Naming the flag here is the difference between a working first
	// deploy and a build that fails on an undefined endpoint.
	output.Hint(out, "Run '%s push site --with-variables' to send them to the site.",
		app.ExecutableName)
}

// chooseFramework asks which framework the site uses.
func chooseFramework(api *client.Client, prompter prompt.Prompter) (string, error) {
	var response struct {
		Frameworks []struct {
			Key  string `json:"key"`
			Name string `json:"name"`
		} `json:"frameworks"`
	}

	if err := api.Call("GET", "/sites/frameworks", nil, &response); err != nil {
		return "", err
	}

	options := make([]prompt.Option, 0, len(response.Frameworks))
	for _, entry := range response.Frameworks {
		options = append(options, prompt.Option{
			Label: fmt.Sprintf("%s (%s)", entry.Name, entry.Key),
			Value: entry.Key,
		})
	}

	return prompter.Choice(prompt.Choice{
		Message: "What framework would you like to use?",
		Options: options,
	})
}

// validateSitePath rejects a path that exists and is not a directory.
func validateSitePath(base string) func(string) error {
	required := prompt.Required("site path")

	return func(value string) error {
		if err := required(value); err != nil {
			return err
		}

		path := strings.TrimSpace(value)
		if !filepath.IsAbs(path) {
			path = filepath.Join(base, path)
		}

		if info, err := os.Stat(path); err == nil && !info.IsDir() {
			return fmt.Errorf("Site path already exists and is not a directory.")
		}

		return nil
	}
}

// atoiOrZero converts a validated non-negative integer.
func atoiOrZero(value string) int {
	parsed, err := strconv.Atoi(strings.TrimSpace(value))
	if err != nil {
		return 0
	}

	return parsed
}

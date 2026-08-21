package cmd

import (
	"bytes"
	"encoding/json"
	"errors"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

func TestMissingGitHubInstallationOffersConsoleLinkAndLocalFallback(t *testing.T) {
	setupURL := githubInstallationSetupURL(
		"https://sgp.cloud.appwrite.io/v1", "project-id")
	wantURL := "https://cloud.appwrite.io/console/project-sgp-project-id/settings"
	if setupURL != wantURL {
		t.Fatalf("setup URL = %q, want %q", setupURL, wantURL)
	}
	baseURL := githubInstallationSetupURL(
		"https://cloud.appwrite.io/v1", "project-id")
	if baseURL != "https://cloud.appwrite.io/console/project-project-id/settings" {
		t.Fatalf("base Cloud setup URL = %q", baseURL)
	}

	description := githubInstallationDescription(setupURL)
	for _, wanted := range []string{"No GitHub installation", wantURL} {
		if !strings.Contains(description, wanted) {
			t.Errorf("guide does not contain %q:\n%s", wanted, description)
		}
	}

	prompter := &prompt.Scripted{Choices: map[string]string{
		"GitHub connection required": "local",
	}}
	action, err := promptGitHubInstallationFallback(prompter, setupURL)
	if err != nil {
		t.Fatal(err)
	}
	if action != "local" {
		t.Fatalf("action = %q", action)
	}

	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, _ *http.Request) {
		response.Header().Set("content-type", "application/json")
		_, _ = response.Write([]byte(`{"installations":[]}`))
	}))
	defer server.Close()
	_, err = configureFunctionVCS(&cobra.Command{}, client.New(server.URL, "test"),
		prompter, "Checkout", initFunctionOptions{})
	if !errors.Is(err, errNoGitHubInstallation) {
		t.Fatalf("error = %v", err)
	}
}

func TestExplicitGitHubRepositoryIsFetchedDirectly(t *testing.T) {
	asked := []string{}
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		asked = append(asked, request.URL.Path)
		response.Header().Set("content-type", "application/json")
		switch request.URL.Path {
		case "/vcs/installations":
			_, _ = response.Write([]byte(`{"installations":[{"$id":"installation","organization":"team"}]}`))
		case "/vcs/github/installations/installation/providerRepositories/repository":
			_, _ = response.Write([]byte(`{"id":"repository","name":"checkout","organization":"team","defaultBranch":"main"}`))
		default:
			http.NotFound(response, request)
		}
	}))
	defer server.Close()

	prompter := &prompt.Scripted{Confirms: map[string]bool{
		"Enable silent mode for Git comments?": false,
	}}
	vcs, err := configureFunctionVCS(&cobra.Command{}, client.New(server.URL, "test"),
		prompter, "Checkout", initFunctionOptions{
			InstallationID: "installation", RepositoryMode: "existing",
			RepositoryID: "repository", ProviderBranch: "main",
			ProviderRootDirectory: "./",
		})
	if err != nil {
		t.Fatal(err)
	}
	if vcs.RepositoryID != "repository" || len(asked) != 2 {
		t.Fatalf("vcs=%#v requests=%#v", vcs, asked)
	}
}

func TestNewGitHubRepositoryIsCreatedOnlyAfterReview(t *testing.T) {
	posts := 0
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("content-type", "application/json")
		switch request.Method {
		case http.MethodGet:
			_, _ = response.Write([]byte(`{"installations":[{"$id":"installation","organization":"team"}]}`))
		case http.MethodPost:
			posts++
			_, _ = response.Write([]byte(`{"id":"repository","name":"checkout","organization":"team","defaultBranch":"main"}`))
		}
	}))
	defer server.Close()

	prompter := &prompt.Scripted{
		Choices: map[string]string{
			"Select a GitHub installation":            "installation",
			"Which repository would you like to use?": "new",
		},
		Texts: map[string]string{
			"Name the new GitHub repository": "checkout",
			"Function root directory":        "./",
		},
		Confirms: map[string]bool{
			"Keep the repository private?":         true,
			"Enable silent mode for Git comments?": false,
		},
	}
	vcs, err := configureFunctionVCS(&cobra.Command{}, client.New(server.URL, "test"),
		prompter, "Checkout", initFunctionOptions{})
	if err != nil {
		t.Fatal(err)
	}
	if posts != 0 || !vcs.CreateRepository || vcs.RepositoryID != "" {
		t.Fatalf("repository created before review: posts=%d vcs=%#v", posts, vcs)
	}

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	if err := os.WriteFile(path, []byte(`{"functions":[{"$id":"checkout","installationId":"installation","providerRepositoryName":"team/checkout","providerRepositoryPrivate":true,"providerRepositoryPending":true,"providerBranch":"main","providerRootDirectory":"./"}]}`), 0o600); err != nil {
		t.Fatal(err)
	}
	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	context := pushContext{api: client.New(server.URL, "test"), local: local}
	if err := context.materializePendingFunctionRepositories(
		local.ResourceEntries("functions"),
	); err != nil {
		t.Fatal(err)
	}
	reloaded, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	entry := reloaded.ResourceEntries("functions")[0]
	if posts != 1 || entry.GetString("providerRepositoryId") != "repository" {
		t.Fatalf("repository was not materialized: posts=%d entry=%#v", posts, entry)
	}
	// Pending survives materialization: it is cleared only once the deployment
	// that needed the repository has been submitted, so a failure in between
	// leaves a state the next push fully retries.
	if !entry.GetBool("providerRepositoryPending") {
		t.Fatal("pending flag was cleared before any deployment was submitted")
	}

	// A retry with the repository already recorded must not create another.
	if err := context.materializePendingFunctionRepositories(
		local.ResourceEntries("functions"),
	); err != nil {
		t.Fatal(err)
	}
	if posts != 1 {
		t.Fatalf("retry created a second repository: posts=%d", posts)
	}

	if err := context.confirmFunctionRepository(entry); err != nil {
		t.Fatal(err)
	}
	confirmed, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if confirmed.ResourceEntries("functions")[0].GetBool("providerRepositoryPending") {
		t.Fatal("pending flag survived repository confirmation")
	}
}

func TestFunctionPreviewShowsSelectedConfiguration(t *testing.T) {
	buffer := &bytes.Buffer{}
	printFunctionPreview(buffer, functionPreview{
		Name: "Checkout", ID: "checkout", Runtime: "node-22",
		BuildSpecification: "s-2vcpu-2gb", RuntimeSpecification: "s-1vcpu-1gb",
		Public: true, DomainTarget: "edge", Domain: "checkout.appwrite.network",
		Source: "github", InstallationID: "installation", Repository: "team/checkout",
		Branch: "main", RootDirectory: "functions/checkout", SilentMode: true,
		CreateRepository: true, RepositoryPrivate: true,
		Directory: "functions/checkout", EnvironmentFile: ".env",
	})

	for _, wanted := range []string{
		"Function preview", "Checkout", "node-22", "s-2vcpu-2gb", "s-1vcpu-1gb",
		"Public (any)", "Edge network", "https://checkout.appwrite.network",
		"team/checkout (new, private)", "installation", "main", "functions/checkout",
		"Silent", ".env",
	} {
		if !strings.Contains(buffer.String(), wanted) {
			t.Errorf("preview does not contain %q:\n%s", wanted, buffer.String())
		}
	}
}

func TestAvailableFunctionDirectoryAddsNumericSuffix(t *testing.T) {
	parent := t.TempDir()
	for _, name := range []string{"my-function", "my-function-2"} {
		if err := os.Mkdir(filepath.Join(parent, name), 0o755); err != nil {
			t.Fatal(err)
		}
	}

	name, renamed, err := availableFunctionDirectory(parent, "my-function")
	if err != nil {
		t.Fatal(err)
	}
	if name != "my-function-3" || !renamed {
		t.Fatalf("name = %q, renamed = %v", name, renamed)
	}
}

func TestValidateRuntimeAndSpecificationChoices(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("content-type", "application/json")
		switch request.URL.Path {
		case "/functions/runtimes":
			_, _ = response.Write([]byte(`{"runtimes":[{"$id":"node-22"}]}`))
		case "/functions/specifications":
			if request.URL.Query().Get("type") != "builds" {
				t.Fatalf("type = %q", request.URL.Query().Get("type"))
			}
			_, _ = response.Write([]byte(`{"specifications":[{"slug":"s-2vcpu-2gb","enabled":true}]}`))
		}
	}))
	defer server.Close()

	api := client.New(server.URL, "test")
	if err := validateRuntimeChoice(api, "node-22"); err != nil {
		t.Fatal(err)
	}
	if err := validateRuntimeChoice(api, "node-26"); err == nil {
		t.Fatal("unavailable runtime accepted")
	}
	if err := validateSpecificationChoice(api, buildSpecifications, "s-2vcpu-2gb"); err != nil {
		t.Fatal(err)
	}
	if err := validateSpecificationChoice(api, buildSpecifications, "s-1vcpu-512mb"); err == nil {
		t.Fatal("unavailable build specification accepted")
	}
}

func TestValidatePreviewDomainLabel(t *testing.T) {
	valid := []string{"checkout-api-hl3k", "a", "function-123"}
	for _, value := range valid {
		if err := validatePreviewDomainLabel(value); err != nil {
			t.Errorf("validatePreviewDomainLabel(%q) = %v", value, err)
		}
	}

	invalid := []string{"", "UPPER", "has.dot", "-starts", "ends-", strings.Repeat("a", 64)}
	for _, value := range invalid {
		if err := validatePreviewDomainLabel(value); err == nil {
			t.Errorf("validatePreviewDomainLabel(%q) unexpectedly passed", value)
		}
	}
}

func TestFunctionEndpointOptionsShowFullAssignedSuffixes(t *testing.T) {
	options := functionEndpointOptionsFromDomains(
		"https://fra.cloud.appwrite.io/v1",
		[]string{"fra.appwrite.run", "appwrite.network"},
	)
	if len(options) != 2 || options[0].Label != "Region compute (*.fra.appwrite.run)" ||
		options[1].Label != "Edge network (*.appwrite.network)" {
		t.Fatalf("options = %#v", options)
	}
}

func TestRuleDomainsKeepsEdgeGlobalAndRegionalisesCompute(t *testing.T) {
	variables := jsonx.NewObject()
	variables.Set("_APP_DOMAIN_SITES", "stage.appwrite.network")
	variables.Set("_APP_DOMAIN_FUNCTIONS", "ams.stage.appwrite.run")
	context := pushContext{api: client.New("https://fra.cloud.staging.appwrite.io/v1", "test")}

	domains := context.ruleDomains(deployables[0], variables)
	if len(domains) != 2 || domains[0] != "stage.appwrite.network" ||
		domains[1] != "fra.stage.appwrite.run" {
		t.Fatalf("domains = %#v", domains)
	}
}

func TestFunctionDomainForTarget(t *testing.T) {
	domains := []string{"fra.appwrite.run", "appwrite.network"}

	region, err := functionDomainForTarget(domains, "region")
	if err != nil || region != "fra.appwrite.run" {
		t.Fatalf("region = %q, %v", region, err)
	}
	edge, err := functionDomainForTarget(domains, "edge")
	if err != nil || edge != "appwrite.network" {
		t.Fatalf("edge = %q, %v", edge, err)
	}
	if _, err := functionDomainForTarget(domains[:1], "edge"); err == nil {
		t.Fatal("missing edge endpoint unexpectedly passed")
	}
}

func TestPullPreservesPendingTemplateDeploymentState(t *testing.T) {
	path := filepath.Join(t.TempDir(), config.LocalFileName)
	if err := os.WriteFile(path, []byte(`{"functions":[{"$id":"checkout","providerRepositoryName":"team/checkout","providerRepositoryPending":true,"templateRepository":"templates","templateOwner":"appwrite","templateRootDirectory":"node/starter","templateReference":"main","templateReferenceType":"branch"}]}`), 0o600); err != nil {
		t.Fatal(err)
	}
	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	pulled := jsonx.NewObject()
	pulled.Set("$id", "checkout")
	pulled.Set("name", "Checkout")
	preservePendingFunctionTemplate(local, pulled)

	for _, key := range []string{
		"providerRepositoryName", "providerRepositoryPending", "templateRepository",
		"templateOwner", "templateRootDirectory", "templateReference",
		"templateReferenceType",
	} {
		if !pulled.Has(key) {
			t.Errorf("pending key %s was discarded", key)
		}
	}
}

func TestFunctionWriteBodyIncludesVCSButNotDomainIntent(t *testing.T) {
	entry := jsonx.NewObject()
	entry.Set("installationId", "installation")
	entry.Set("providerRepositoryId", "repository")
	entry.Set("providerBranch", "main")
	entry.Set("providerRootDirectory", "functions/api")
	entry.Set("previewDomainTarget", "edge")
	entry.Set("previewDomainLabel", "api")
	entry.Set("providerRepositoryName", "team/api")
	entry.Set("providerRepositoryPending", true)

	body := writeBody(entry, deployables[0].WriteKeys, nil, "functionId", "api")
	if body.GetString("installationId") != "installation" ||
		body.GetString("providerRepositoryId") != "repository" ||
		body.GetString("providerBranch") != "main" {
		t.Fatalf("VCS fields missing from body: %#v", body)
	}
	for _, key := range []string{
		"previewDomainTarget", "providerRepositoryName", "providerRepositoryPending",
	} {
		if _, exists := body.Get(key); exists {
			t.Fatalf("local intent %s leaked into Function API body", key)
		}
	}
}

func TestPullInfersEdgePreviewDomainAndIgnoresCustomDomain(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		response.Header().Set("content-type", "application/json")
		_, _ = response.Write([]byte(`{"total":2,"rules":[{"$id":"custom","domain":"api.example.com"},{"$id":"edge","domain":"checkout.appwrite.network"}]}`))
	}))
	defer server.Close()

	function := jsonx.NewObject()
	function.Set("$id", "checkout")
	pull := projectPull{api: client.New(server.URL, "test")}
	pull.addPreviewDomain(function)

	if function.GetString("previewDomainTarget") != "edge" ||
		function.GetString("previewDomainLabel") != "checkout" {
		t.Fatalf("preview domain = %#v", function)
	}
}

func TestPullDoesNotInferIntentFromMultipleManagedDomains(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, _ *http.Request) {
		response.Header().Set("content-type", "application/json")
		_, _ = response.Write([]byte(`{"total":2,"rules":[{"$id":"edge","domain":"checkout.appwrite.network"},{"$id":"region","domain":"checkout.fra.appwrite.run"}]}`))
	}))
	defer server.Close()

	function := jsonx.NewObject()
	function.Set("$id", "checkout")
	pull := projectPull{api: client.New(server.URL, "test")}
	pull.addPreviewDomain(function)
	if function.GetString("previewDomainTarget") != "" ||
		function.GetString("previewDomainLabel") != "" {
		t.Fatalf("ambiguous domain intent was inferred: %#v", function)
	}
}

func TestRemoveOtherPreviewRulesPreservesCustomDomain(t *testing.T) {
	deleted := []string{}
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		if request.Method == http.MethodDelete {
			deleted = append(deleted, request.URL.Path)
		}
		response.WriteHeader(http.StatusNoContent)
	}))
	defer server.Close()

	custom := jsonx.NewObject()
	custom.Set("$id", "custom")
	custom.Set("domain", "api.example.com")
	old := jsonx.NewObject()
	old.Set("$id", "old-edge")
	old.Set("domain", "old.appwrite.network")
	keep := jsonx.NewObject()
	keep.Set("$id", "keep-region")
	keep.Set("domain", "new.fra.appwrite.run")

	context := pushContext{api: client.New(server.URL, "test")}
	if err := context.removeOtherPreviewRules(
		[]any{custom, old, keep}, "new.fra.appwrite.run",
	); err != nil {
		t.Fatal(err)
	}
	if len(deleted) != 1 || deleted[0] != "/proxy/rules/old-edge" {
		t.Fatalf("deleted = %#v", deleted)
	}
}

func TestCreateGitFunctionDeploymentSeedsTemplateOnce(t *testing.T) {
	var body map[string]any
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		if request.URL.Path != "/functions/checkout/deployments/template" {
			t.Errorf("path = %s", request.URL.Path)
		}
		if err := json.NewDecoder(request.Body).Decode(&body); err != nil {
			t.Errorf("decode body: %v", err)
		}
		response.Header().Set("content-type", "application/json")
		response.WriteHeader(http.StatusAccepted)
		_, _ = response.Write([]byte(`{"$id":"deployment-1","status":"waiting"}`))
	}))
	defer server.Close()

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	contents := `{"projectId":"project","functions":[{"$id":"checkout","templateRepository":"templates","templateOwner":"appwrite","templateRootDirectory":"node/starter","templateReference":"1.0.1","templateReferenceType":"tag"}]}`
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}
	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	entry := local.ResourceEntries("functions")[0]
	context := pushContext{api: client.New(server.URL, "test"), local: local}

	deployment, err := context.createGitFunctionDeployment(entry, true)
	if err != nil {
		t.Fatal(err)
	}
	if deployment.GetString("$id") != "deployment-1" {
		t.Fatalf("deployment = %#v", deployment)
	}
	if body["repository"] != "templates" || body["type"] != "tag" || body["activate"] != true {
		t.Fatalf("body = %#v", body)
	}

	reloaded, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	persisted := reloaded.ResourceEntries("functions")[0]
	if _, exists := persisted.Get("templateRepository"); exists {
		t.Fatal("one-shot template metadata was not removed")
	}
}

func TestCreateGitFunctionDeploymentUsesBranchAfterSeed(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, request *http.Request) {
		if request.URL.Path != "/functions/checkout/deployments/vcs" {
			t.Errorf("path = %s", request.URL.Path)
		}
		body := jsonx.NewObject()
		if err := json.NewDecoder(request.Body).Decode(body); err != nil {
			t.Errorf("decode body: %v", err)
		}
		if body.GetString("type") != "branch" || body.GetString("reference") != "main" {
			t.Errorf("body = %#v", body)
		}
		response.Header().Set("content-type", "application/json")
		response.WriteHeader(http.StatusAccepted)
		_, _ = response.Write([]byte(`{"$id":"deployment-2","status":"waiting"}`))
	}))
	defer server.Close()

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	if err := os.WriteFile(path, []byte(`{"projectId":"project","functions":[]}`), 0o600); err != nil {
		t.Fatal(err)
	}
	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	entry := jsonx.NewObject()
	entry.Set("$id", "checkout")
	entry.Set("providerBranch", "main")
	context := pushContext{api: client.New(server.URL, "test"), local: local}

	if _, err := context.createGitFunctionDeployment(entry, true); err != nil {
		t.Fatal(err)
	}
}

// The one-shot template coordinates are cleared and persisted BEFORE the
// deployment request, so a deployment that succeeds remotely can never be
// submitted twice by a retry -- even when saving the cleared state after the
// fact would have failed. A request that fails restores the coordinates so
// the seed is not lost.
func TestCreateGitFunctionDeploymentRestoresTemplateOnFailure(t *testing.T) {
	path := filepath.Join(t.TempDir(), config.LocalFileName)
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, _ *http.Request) {
		// At request time the persisted config must already have consumed the
		// template coordinates; anything else re-seeds on retry.
		persisted, err := config.LoadLocal(path)
		if err != nil {
			t.Errorf("load config during request: %v", err)
		} else if persisted.ResourceEntries("functions")[0].GetString("templateRepository") != "" {
			t.Error("template coordinates were not cleared before the request")
		}
		response.Header().Set("content-type", "application/json")
		response.WriteHeader(http.StatusBadRequest)
		_, _ = response.Write([]byte(`{"message":"template deployment rejected","code":400}`))
	}))
	defer server.Close()

	contents := `{"projectId":"project","functions":[{"$id":"checkout","templateRepository":"templates","templateOwner":"appwrite","templateRootDirectory":"node/starter","templateReference":"1.0.1","templateReferenceType":"tag"}]}`
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}
	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	entry := local.ResourceEntries("functions")[0]
	context := pushContext{api: client.New(server.URL, "test"), local: local}

	if _, err := context.createGitFunctionDeployment(entry, true); err == nil {
		t.Fatal("a failed template deployment was not reported as an error")
	}

	reloaded, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	persisted := reloaded.ResourceEntries("functions")[0]
	if persisted.GetString("templateRepository") != "templates" ||
		persisted.GetString("templateReference") != "1.0.1" {
		t.Fatalf("template coordinates were not restored: %#v", persisted)
	}
}

// A settings-only push of a function whose GitHub repository is still pending
// must not send the VCS connection keys: the repository does not exist yet, so
// an installation without a repository would half-connect the function.
func TestPendingSafeBodyStripsVCSKeysWhilePending(t *testing.T) {
	entry := jsonx.NewObject()
	entry.Set("providerRepositoryPending", true)

	body := jsonx.NewObject()
	body.Set("name", "Checkout")
	body.Set("installationId", "installation")
	body.Set("providerBranch", "main")
	body.Set("providerSilentMode", false)
	body.Set("providerRootDirectory", "./")

	pruned := pendingSafeBody(entry, body)
	if _, exists := pruned.Get("installationId"); exists {
		t.Error("installationId survived a pending entry")
	}
	if _, exists := pruned.Get("providerBranch"); exists {
		t.Error("providerBranch survived a pending entry")
	}
	if pruned.GetString("name") != "Checkout" {
		t.Error("non-VCS keys must survive")
	}

	connected := jsonx.NewObject()
	kept := jsonx.NewObject()
	kept.Set("installationId", "installation")
	if _, exists := pendingSafeBody(connected, kept).Get("installationId"); !exists {
		t.Error("installationId was stripped from a connected entry")
	}
}

// A transport failure does not say whether the template deployment was
// created, so the consumed coordinates stay consumed: restoring them could
// merge the starter into the repository a second time.
func TestCreateGitFunctionDeploymentKeepsSeedConsumedOnAmbiguousFailure(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, _ *http.Request) {
		hijacker, ok := response.(http.Hijacker)
		if !ok {
			t.Fatal("response writer cannot hijack")
		}
		connection, _, err := hijacker.Hijack()
		if err != nil {
			t.Fatal(err)
		}
		_ = connection.Close()
	}))
	defer server.Close()

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	contents := `{"projectId":"project","functions":[{"$id":"checkout","templateRepository":"templates","templateOwner":"appwrite","templateRootDirectory":"node/starter","templateReference":"1.0.1","templateReferenceType":"tag"}]}`
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}
	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	entry := local.ResourceEntries("functions")[0]
	context := pushContext{api: client.New(server.URL, "test"), local: local}

	_, err = context.createGitFunctionDeployment(entry, true)
	if err == nil {
		t.Fatal("a transport failure was not reported as an error")
	}
	if !strings.Contains(err.Error(), "may still have been created") {
		t.Errorf("error does not warn about the ambiguous outcome: %v", err)
	}

	reloaded, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	persisted := reloaded.ResourceEntries("functions")[0]
	if _, exists := persisted.Get("templateRepository"); exists {
		t.Fatal("template coordinates were restored after an ambiguous failure")
	}
}

// A 5xx does not say whether the deployment was accepted -- a proxy or server
// error can arrive after acceptance -- so it keeps the seed consumed exactly
// like a transport failure.
func TestCreateGitFunctionDeploymentKeepsSeedConsumedOnServerError(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(response http.ResponseWriter, _ *http.Request) {
		response.Header().Set("content-type", "application/json")
		response.WriteHeader(http.StatusBadGateway)
		_, _ = response.Write([]byte(`{"message":"upstream timed out","code":502}`))
	}))
	defer server.Close()

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	contents := `{"projectId":"project","functions":[{"$id":"checkout","templateRepository":"templates","templateOwner":"appwrite","templateRootDirectory":"node/starter","templateReference":"1.0.1","templateReferenceType":"tag"}]}`
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}
	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	entry := local.ResourceEntries("functions")[0]
	context := pushContext{api: client.New(server.URL, "test"), local: local}

	if _, err = context.createGitFunctionDeployment(entry, true); err == nil {
		t.Fatal("a server error was not reported as an error")
	}

	reloaded, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if _, exists := reloaded.ResourceEntries("functions")[0].Get("templateRepository"); exists {
		t.Fatal("template coordinates were restored after an ambiguous 5xx")
	}
}

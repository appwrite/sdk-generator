package cmd

import (
	"bytes"
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// starterVariables is what the API actually answers for starter-for-nextjs:
// three required variables, each a placeholder for the CLI to fill.
func starterVariables() []siteTemplateVariable {
	return []siteTemplateVariable{
		{Name: "NEXT_PUBLIC_APPWRITE_ENDPOINT", Value: "{apiEndpoint}", Required: true},
		{Name: "NEXT_PUBLIC_APPWRITE_PROJECT_ID", Value: "{projectId}", Required: true},
		{Name: "NEXT_PUBLIC_APPWRITE_PROJECT_NAME", Value: "{projectName}", Required: true},
	}
}

func testLocal(t *testing.T) *config.Local {
	t.Helper()

	data := jsonx.NewObject()
	data.Set("endpoint", "https://cloud.staging.appwrite.io/v1")
	data.Set("projectId", "6a3cd0ab001c7936ecbe")
	data.Set("projectName", "Testing Project")

	return &config.Local{Data: data}
}

func writeEnv(t *testing.T, template *siteTemplate) (string, string) {
	t.Helper()

	siteDir := t.TempDir()
	var out bytes.Buffer
	writeTemplateEnv(&out, testLocal(t), client.New("https://fallback/v1", "0.0.1"),
		template, siteDir, "sites/template-site")

	contents, err := os.ReadFile(filepath.Join(siteDir, ".env"))
	if err != nil {
		return "", out.String()
	}

	return string(contents), out.String()
}

// The whole bug: the API declares these with placeholders for the CLI to fill,
// nothing filled them, no .env was written, and the first build of every
// template site died on `setEndpoint(undefined)`.
func TestTemplateEnvIsWrittenWithThePlaceholdersFilled(t *testing.T) {
	contents, _ := writeEnv(t, &siteTemplate{Variables: starterVariables()})

	for _, want := range []string{
		"NEXT_PUBLIC_APPWRITE_ENDPOINT=https://cloud.staging.appwrite.io/v1",
		"NEXT_PUBLIC_APPWRITE_PROJECT_ID=6a3cd0ab001c7936ecbe",
		"NEXT_PUBLIC_APPWRITE_PROJECT_NAME=Testing Project",
	} {
		if !strings.Contains(contents, want) {
			t.Errorf("missing %q in:\n%s", want, contents)
		}
	}

	if strings.Contains(contents, "{") {
		t.Errorf("a placeholder survived into the .env:\n%s", contents)
	}
}

// The variables are local until they are pushed, and the build runs on the
// server -- so naming the flag is the difference between a working first deploy
// and a build that fails on an undefined endpoint.
func TestTemplateEnvSaysHowToPushIt(t *testing.T) {
	_, printed := writeEnv(t, &siteTemplate{Variables: starterVariables()})

	if !strings.Contains(printed, "--with-variables") {
		t.Errorf("nothing told the user how to push the variables:\n%s", printed)
	}
	if !strings.Contains(printed, "sites/template-site/.env") {
		t.Errorf("the file that was written is not named:\n%s", printed)
	}
}

// A placeholder the CLI cannot fill is left EMPTY, not written through: an
// `{apiKey}` in a .env is a value the build would use as if it were real, and an
// empty one fails in a way that points at the variable.
func TestUnknownPlaceholdersAreLeftEmptyAndCalledOut(t *testing.T) {
	contents, printed := writeEnv(t, &siteTemplate{Variables: []siteTemplateVariable{
		{Name: "APPWRITE_API_KEY", Value: "{apiKey}", Required: true},
	}})

	if !strings.Contains(contents, "APPWRITE_API_KEY=\n") {
		t.Errorf("the unresolvable value was not left empty:\n%q", contents)
	}
	if !strings.Contains(printed, "APPWRITE_API_KEY") {
		t.Errorf("a required variable the CLI could not fill was not called out:\n%s", printed)
	}
}

// An existing .env is the one file in a scaffolded site that may already hold
// secrets, and a template's generic defaults are not worth overwriting them for.
func TestAnExistingEnvIsNeverOverwritten(t *testing.T) {
	siteDir := t.TempDir()
	path := filepath.Join(siteDir, ".env")
	if err := os.WriteFile(path, []byte("MINE=keep\n"), 0o600); err != nil {
		t.Fatal(err)
	}

	var out bytes.Buffer
	writeTemplateEnv(&out, testLocal(t), client.New("https://fallback/v1", "0.0.1"),
		&siteTemplate{Variables: starterVariables()}, siteDir, "sites/x")

	contents, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if string(contents) != "MINE=keep\n" {
		t.Errorf("an existing .env was overwritten: %q", contents)
	}
	if !strings.Contains(out.String(), "already exists") {
		t.Errorf("the skip was silent:\n%s", out.String())
	}
}

// A template with no variables writes no file, rather than an empty one.
func TestNoVariablesWritesNoFile(t *testing.T) {
	contents, printed := writeEnv(t, &siteTemplate{})

	if contents != "" {
		t.Errorf("wrote a .env for a template with no variables: %q", contents)
	}
	if printed != "" {
		t.Errorf("said something about nothing:\n%s", printed)
	}
}

// The endpoint falls back to the client's when the config carries none, so a
// site inited before `endpoint` was written still gets a usable value.
func TestEndpointFallsBackToTheClient(t *testing.T) {
	data := jsonx.NewObject()
	data.Set("projectId", "abc")

	siteDir := t.TempDir()
	var out bytes.Buffer
	writeTemplateEnv(&out, &config.Local{Data: data},
		client.New("https://fallback.example/v1", "0.0.1"),
		&siteTemplate{Variables: starterVariables()}, siteDir, "sites/x")

	contents, err := os.ReadFile(filepath.Join(siteDir, ".env"))
	if err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(string(contents), "https://fallback.example/v1") {
		t.Errorf("the client endpoint was not used:\n%s", contents)
	}
}

// A nil template is what a failed lookup leaves behind, and it must not panic.
func TestANilTemplateWritesNothing(t *testing.T) {
	siteDir := t.TempDir()
	var out bytes.Buffer
	writeTemplateEnv(&out, testLocal(t), client.New("https://x/v1", "0.0.1"),
		nil, siteDir, "sites/x")

	if _, err := os.Stat(filepath.Join(siteDir, ".env")); err == nil {
		t.Error("a nil template still wrote a .env")
	}
}

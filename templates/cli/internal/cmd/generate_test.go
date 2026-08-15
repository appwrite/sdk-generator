package cmd

import (
	"os"
	"path/filepath"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
)

// The generated client hard-codes the endpoint it connects to, and `pull` never
// writes an `endpoint` key. Reading only the project config emitted
// `export const ENDPOINT` set to an empty string -- a client that cannot
// reach anything.
func TestGenerateEndpointFallsBackToTheSession(t *testing.T) {
	home := t.TempDir()
	t.Setenv("HOME", home)
	t.Setenv("USERPROFILE", home)

	if err := os.MkdirAll(filepath.Join(home, ".appwrite"), 0o755); err != nil {
		t.Fatal(err)
	}
	preferences := `{"current":"s","s":{"endpoint":"https://self.hosted/v1"}}`
	if err := os.WriteFile(filepath.Join(home, ".appwrite", "prefs.json"),
		[]byte(preferences), 0o600); err != nil {
		t.Fatal(err)
	}

	if got := resolveGenerateEndpoint(""); got != "https://self.hosted/v1" {
		t.Errorf("endpoint = %q, want the session's", got)
	}
}

// An endpoint in the project config is the project's own and wins over
// whichever instance the user happens to be signed in to.
func TestGenerateEndpointPrefersTheProjectConfig(t *testing.T) {
	if got := resolveGenerateEndpoint("https://configured/v1"); got != "https://configured/v1" {
		t.Errorf("endpoint = %q, want the configured one", got)
	}
}

// With no config and no session there is still a sensible default endpoint.
func TestGenerateEndpointFallsBackToTheDefault(t *testing.T) {
	home := t.TempDir()
	t.Setenv("HOME", home)
	t.Setenv("USERPROFILE", home)

	if got := resolveGenerateEndpoint(""); got != config.DefaultEndpoint {
		t.Errorf("endpoint = %q, want %q", got, config.DefaultEndpoint)
	}
}

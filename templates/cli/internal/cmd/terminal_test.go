//go:build !browser

package cmd

import (
	"os"
	"path/filepath"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/sdk"
)

// The terminal finds its project and endpoint the way a project command does:
// the flag, then the environment, then the nearest project config. A key comes
// from APPWRITE_API_KEY alone, never a flag, so it stays out of the process
// list.
func TestTerminalOptions(t *testing.T) {
	dir := t.TempDir()
	path := filepath.Join(dir, config.LocalFileName)
	if err := os.WriteFile(path, []byte(`{"projectId":"from-config","endpoint":"https://config.example.com/v1"}`), 0o600); err != nil {
		t.Fatal(err)
	}

	tests := []struct {
		name         string
		flag         string
		env          map[string]string
		configFile   string
		wantProject  string
		wantEndpoint string
		wantKey      string
	}{
		{name: "nothing", configFile: filepath.Join(dir, "missing.json")},
		{name: "config", configFile: path, wantProject: "from-config", wantEndpoint: "https://config.example.com/v1"},
		{name: "environment over config", configFile: path,
			env:         map[string]string{sdk.EnvProjectID: "from-env", sdk.EnvEndpoint: "https://env.example.com/v1", envAPIKey: "k"},
			wantProject: "from-env", wantEndpoint: "https://env.example.com/v1", wantKey: "k"},
		{name: "flag over environment", flag: "from-flag", configFile: path,
			env:         map[string]string{sdk.EnvProjectID: "from-env"},
			wantProject: "from-flag", wantEndpoint: "https://config.example.com/v1"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			for _, name := range []string{sdk.EnvProjectID, sdk.EnvEndpoint, envAPIKey} {
				t.Setenv(name, tt.env[name])
			}
			previous := config.LocalFile
			config.LocalFile = tt.configFile
			t.Cleanup(func() { config.LocalFile = previous })

			got := terminalOptions(nil, tt.flag)
			if got.Project != tt.wantProject || got.Endpoint != tt.wantEndpoint || got.Key != tt.wantKey {
				t.Errorf("project %q endpoint %q key %q, want %q %q %q",
					got.Project, got.Endpoint, got.Key, tt.wantProject, tt.wantEndpoint, tt.wantKey)
			}
		})
	}
}

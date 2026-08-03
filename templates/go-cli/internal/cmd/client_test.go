package cmd

import (
	"os"
	"path/filepath"
	"testing"

	"github.com/appwrite/appwrite-cli-go/internal/config"
)

// `client --project-id X` is the non-interactive setup path CI uses. Writing
// the project into the global preferences left it inert: nothing reads it
// there, so the very next command answered "project is not set".
func TestClientProjectIDIsWrittenWhereCommandsReadIt(t *testing.T) {
	directory := t.TempDir()
	inDirectory(t, directory)

	if err := setLocalProject("chosen-project"); err != nil {
		t.Fatal(err)
	}

	local, err := config.LoadLocal(config.FindLocalPath())
	if err != nil {
		t.Fatalf("no config a command could read: %v", err)
	}
	if got := local.Data.GetString("projectId"); got != "chosen-project" {
		t.Errorf("projectId = %q, want chosen-project", got)
	}
}

// An existing config is updated in place. Creating a second one in the working
// directory would shadow the project's own and silently split the config in
// two.
func TestClientProjectIDUpdatesTheExistingConfig(t *testing.T) {
	root := t.TempDir()
	existing := filepath.Join(root, config.LocalFileName)
	if err := os.WriteFile(existing,
		[]byte(`{"projectId":"old","projectName":"Kept"}`), 0o600); err != nil {
		t.Fatal(err)
	}

	nested := filepath.Join(root, "functions", "one")
	if err := os.MkdirAll(nested, 0o755); err != nil {
		t.Fatal(err)
	}
	inDirectory(t, nested)

	if err := setLocalProject("new"); err != nil {
		t.Fatal(err)
	}

	if _, err := os.Stat(filepath.Join(nested, config.LocalFileName)); err == nil {
		t.Error("wrote a second config into the working directory")
	}

	local, err := config.LoadLocal(existing)
	if err != nil {
		t.Fatal(err)
	}
	if got := local.Data.GetString("projectId"); got != "new" {
		t.Errorf("projectId = %q, want new", got)
	}
	// Setting the id must not discard the rest of the file.
	if got := local.Data.GetString("projectName"); got != "Kept" {
		t.Errorf("projectName = %q, want it preserved", got)
	}
}

func inDirectory(t *testing.T, directory string) {
	t.Helper()

	previous, err := os.Getwd()
	if err != nil {
		t.Fatal(err)
	}
	if err := os.Chdir(directory); err != nil {
		t.Fatal(err)
	}
	t.Cleanup(func() { _ = os.Chdir(previous) })
}

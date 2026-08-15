package cmd

import (
	"os"
	"path/filepath"
	"strings"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
)

const configWithOneSite = `{
    "projectId": "probe",
    "projectName": "probe",
    "sites": [
        { "$id": "web", "name": "Marketing site", "path": "sites/web" }
    ]
}`

// Regression for `push site` answering "No sites found." on a config that has
// one.
//
// A multi-select starts with nothing ticked and Enter accepts that, so the
// prompt handed back an empty list; the caller read that as "the config has
// none" and pointed the reader at `init site` for a site already in front of
// them. prompt.RequiredSelection exists for exactly this and was never wired
// up.
func TestPushSelectionRejectsAnEmptyChoice(t *testing.T) {
	local := loadProbeConfig(t)

	context := &pushContext{
		local: local,
		// What pressing Enter on an untouched list produces.
		prompter: &prompt.Scripted{MultiChoices: map[string][]string{
			"Which sites would you like to push?": {},
		}},
	}

	_, err := context.selectResources("sites", "site", local.ResourceEntries("sites"))
	if err == nil {
		t.Fatal("an empty selection was accepted, so the caller will report the site as missing")
	}
	if !strings.Contains(err.Error(), "at least one site") {
		t.Errorf("error = %q, want it to name the resource", err)
	}
}

// The prompt still has to return what was actually chosen.
func TestPushSelectionKeepsTheChosenEntries(t *testing.T) {
	local := loadProbeConfig(t)

	context := &pushContext{
		local: local,
		prompter: &prompt.Scripted{MultiChoices: map[string][]string{
			"Which sites would you like to push?": {"web"},
		}},
	}

	chosen, err := context.selectResources("sites", "site", local.ResourceEntries("sites"))
	if err != nil {
		t.Fatal(err)
	}
	if len(chosen) != 1 || chosen[0].GetString("$id") != "web" {
		t.Errorf("chosen = %v, want the one site", chosen)
	}
}

// Every selected deployment must start before any one of them finishes. The
// sequential loop waited for a function's entire build before it even packaged
// the next one, making a multi-function push take the sum of all build times.
func TestSelectedDeploymentsStartInParallel(t *testing.T) {
	entries := make([]*jsonx.Object, 2)
	for index, id := range []string{"first", "second"} {
		entry := jsonx.NewObject()
		entry.Set("$id", id)
		entries[index] = entry
	}

	started := make(chan string, len(entries))
	release := make(chan struct{})
	finished := make(chan pushSummary, 1)

	go func() {
		finished <- pushDeployablesInParallel(entries,
			func(entry *jsonx.Object, summary *pushSummary) {
				started <- entry.GetString("$id")
				<-release
				summary.Pushed++
			})
	}()

	timer := time.NewTimer(5 * time.Second)
	defer timer.Stop()

	seen := map[string]bool{}
	for range entries {
		select {
		case id := <-started:
			seen[id] = true
		case <-timer.C:
			close(release)
			t.Fatal("a selected deployment waited for the previous one to finish")
		}
	}
	close(release)

	summary := <-finished
	if len(seen) != len(entries) {
		t.Errorf("started %v, want both selected deployments", seen)
	}
	if summary.Pushed != len(entries) {
		t.Errorf("summary counted %d pushes, want %d", summary.Pushed, len(entries))
	}
}

// `appwrite push site all` prompted for a selection, because cobra accepts
// surplus positionals by default and the word was thrown away. `push function
// all` looked like it worked only because that project had no functions.
func TestPushResourcesAcceptAllAsAnArgument(t *testing.T) {
	root := NewRootCommand()

	for _, path := range []string{
		"push site", "push function", "push bucket", "push team",
		"push webhook", "push topic", "push table", "push collection",
	} {
		command := resolveCommand(root, path)
		if command == nil {
			t.Fatalf("`%s` is not in the tree", path)
		}
		if command.Args == nil {
			t.Errorf("`%s` validates no arguments, so `all` is still discarded", path)

			continue
		}

		restore := app.Flags().All
		app.Flags().All = false

		if err := command.Args(command, []string{"all"}); err != nil {
			t.Errorf("`%s all` was rejected: %v", path, err)
		}
		if !app.Flags().All {
			t.Errorf("`%s all` did not select every resource", path)
		}

		app.Flags().All = restore
	}
}

// And anything that is not `all` is refused rather than ignored, naming both
// forms that work.
func TestPushResourcesRejectAnUnknownArgument(t *testing.T) {
	command := resolveCommand(NewRootCommand(), "push site")

	err := command.Args(command, []string{"marketing"})
	if err == nil {
		t.Fatal("`push site marketing` was accepted, and pushes whatever the prompt is left on")
	}
	for _, want := range []string{"`all`", "marketing", "choose from a list"} {
		if !strings.Contains(err.Error(), want) {
			t.Errorf("error does not mention %s:\n  %s", want, err)
		}
	}
}

func loadProbeConfig(t *testing.T) *config.Local {
	t.Helper()

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	if err := os.WriteFile(path, []byte(configWithOneSite), 0o600); err != nil {
		t.Fatal(err)
	}

	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}

	return local
}

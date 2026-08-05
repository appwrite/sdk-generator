package cmd

import (
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
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
// them. The TypeScript's checkbox carries
// `validate: validateRequired("site", value)` (questions.ts:999), which the
// port had as prompt.RequiredSelection and never wired up.
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

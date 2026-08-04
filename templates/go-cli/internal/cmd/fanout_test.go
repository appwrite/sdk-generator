package cmd

import (
	"bytes"
	"path/filepath"
	"slices"
	"strings"
	"testing"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/appwrite/appwrite-cli-go/internal/config"
	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
	"github.com/appwrite/appwrite-cli-go/internal/prompt"
	"github.com/spf13/cobra"
)

// `pull all` and `push all` mean ALL, with or without --all.
//
// Both were gated on the global --all flag, so the bare subcommand fell through
// to the picker and asked which single resource to act on -- reported for
// `push all` first, then for `pull all` the same way a day later. The
// TypeScript sets cliConfig.all itself before delegating (pull.ts:1136,
// push.ts:4288), so neither ever reaches the question.

// isolateGlobalAll restores the --all flag when the test ends.
//
// The flag is PROCESS state -- it stands in for the TypeScript's cliConfig --
// so a test that runs a fan-out sets it for every test that runs afterwards,
// and a later prompt test then silently takes the "all" path and never
// prompts. Anything here that touches the fan-out has to put it back.
func isolateGlobalAll(t *testing.T) {
	t.Helper()

	restore := app.Flags().All
	t.Cleanup(func() { app.Flags().All = restore })
}

func TestPullEverythingRunsEveryNonDeprecatedAction(t *testing.T) {
	isolateGlobalAll(t)

	var ran []string

	actions := []pullAction{
		{Value: "settings", Run: record(&ran, "settings")},
		{Value: "functions", Run: record(&ran, "functions")},
		{Value: "collections", Run: record(&ran, "collections"), Deprecated: true},
	}

	if err := runPull(&cobra.Command{}, actions, true); err != nil {
		t.Fatal(err)
	}

	assertRanEverything(t, ran)
}

func TestPushEverythingRunsEveryNonDeprecatedAction(t *testing.T) {
	isolateGlobalAll(t)

	var ran []string

	actions := []pushAction{
		{Value: "settings", Run: record(&ran, "settings")},
		{Value: "functions", Run: record(&ran, "functions")},
		{Value: "collections", Run: record(&ran, "collections"), Deprecated: true},
	}

	if err := runPushActions(&cobra.Command{}, actions, true); err != nil {
		t.Fatal(err)
	}

	assertRanEverything(t, ran)
}

// `all` has to reach INSIDE each resource.
//
// The fan-out running every resource type is only half of it: each one then
// reads the same global flag to decide whether to ask which functions, sites
// or buckets to act on. Run without setting it, `pull all` pulled the settings
// and then stopped to ask which functions to pull -- every resource type, one
// question at a time. The TypeScript sets cliConfig.all before delegating
// (pull.ts:850, push.ts:4288), which is what suppresses those.
func TestEverythingSetsTheGlobalAllFlag(t *testing.T) {
	for _, run := range []struct {
		name string
		call func() error
	}{
		{"pull", func() error { return runPull(&cobra.Command{}, nil, true) }},
		{"push", func() error { return runPushActions(&cobra.Command{}, nil, true) }},
	} {
		t.Run(run.name, func(t *testing.T) {
			restore := app.Flags().All
			app.Flags().All = false
			defer func() { app.Flags().All = restore }()

			if err := run.call(); err != nil {
				t.Fatal(err)
			}
			if !app.Flags().All {
				t.Error("`all` did not set the flag, so each resource will ask " +
					"which of its entries to use")
			}
		})
	}
}

// The regression as the user met it: `pull all` on a machine with no terminal
// stopped to ask a question instead of pulling. Whatever this fails with, it
// must not be the prompt -- the fan-out has no question to ask.
func TestAllSubcommandsNeverPrompt(t *testing.T) {
	for _, name := range []string{"pull", "push"} {
		t.Run(name, func(t *testing.T) {
			// The fan-out sets the global --all, which is process state that
			// outlives this test and would otherwise decide whether a LATER
			// test's prompt is asked at all.
			restore := app.Flags().All
			defer func() { app.Flags().All = restore }()

			// Hermetic, or this tests the developer's machine. With a real
			// session and a discoverable appwrite.config.json -- which the
			// parent-directory search makes far more likely -- the fan-out gets
			// past the credential check and reaches prompts that are perfectly
			// legitimate, and the assertion below fires on the wrong thing.
			home := t.TempDir()
			t.Setenv("HOME", home)
			t.Setenv("USERPROFILE", home)
			inDirectory(t, t.TempDir())

			root := NewRootCommand()
			root.SetArgs([]string{name, "all"})
			root.SetOut(&strings.Builder{})
			root.SetErr(&strings.Builder{})

			// Fails on config or credentials, which is fine and expected here.
			err := root.Execute()
			if err == nil {
				return
			}
			if strings.Contains(err.Error(), "no interactive terminal") {
				t.Errorf("`%s all` asked which resource to use: %v", name, err)
			}
		})
	}
}

// Collections are the legacy databases API. Running them alongside tables
// writes two representations of the same data, so `all` skips them while the
// picker still offers them.
func TestRealFanOutsMarkCollectionsDeprecated(t *testing.T) {
	for _, action := range pullActions() {
		if action.Value == "collections" && !action.Deprecated {
			t.Error("pull: collections should be deprecated")
		}
	}
	for _, action := range pushActions(true) {
		if action.Value == "collections" && !action.Deprecated {
			t.Error("push: collections should be deprecated")
		}
	}
}

// `all` has to reach SITES. The picker does not offer them -- upstream's list
// omits sites for both pull and push (questions.ts:383, :953) -- but upstream's
// fan-out still runs them, because it walks the actions map rather than the
// question's choices (pull.ts:851). The Go pull fan-out had no sites entry at
// all, so `pull all` silently skipped every site in the project.
func TestFanOutsRunSites(t *testing.T) {
	for _, action := range pullActions() {
		if action.Value == "sites" {
			return
		}
	}

	t.Error("`pull all` has no sites action, so it skips them entirely")
}

func TestPushFanOutRunsSites(t *testing.T) {
	for _, action := range pushActions(true) {
		if action.Value == "sites" {
			return
		}
	}

	t.Error("`push all` has no sites action")
}

// The picker's order is user-visible and is NOT the execution order: push runs
// settings first and databases last, because a table cannot be created before
// project settings allow the databases service.
//
// Every value offered must resolve to an action -- an entry naming nothing is
// a choice that does nothing. The converse does NOT hold: sites are run by
// `all` and deliberately absent from the picker, matching upstream.
func TestPickerOffersOnlyRealActions(t *testing.T) {
	assertResolves := func(name string, order []string, values []string) {
		for _, value := range order {
			if !slices.Contains(values, value) {
				t.Errorf("%s picker offers %q, which is not an action", name, value)
			}
		}
	}

	pushValues := []string{}
	for _, action := range pushActions(true) {
		pushValues = append(pushValues, action.Value)
	}
	assertResolves("push", promptOrder, pushValues)

	pullValues := []string{}
	for _, action := range pullActions() {
		pullValues = append(pullValues, action.Value)
	}
	assertResolves("pull", pullPromptOrder, pullValues)
}

func record(ran *[]string, value string) func(*cobra.Command) error {
	return func(*cobra.Command) error {
		*ran = append(*ran, value)

		return nil
	}
}

func assertRanEverything(t *testing.T, ran []string) {
	t.Helper()

	for _, want := range []string{"settings", "functions"} {
		if !slices.Contains(ran, want) {
			t.Errorf("%q never ran, so `all` did not mean all", want)
		}
	}
	if slices.Contains(ran, "collections") {
		t.Error("collections ran, but `all` skips the legacy databases API")
	}
}

// `push function --all` asked "Enter the entrypoint" for any function whose
// config had none. --all says "every resource, do not ask me", and it is how a
// pipeline pushes, so a question there is the opposite of what was asked -- and
// the answer is data the CLI cannot invent.
//
// The function is reported and skipped now, and the rest of the push proceeds.
// The prompter here fails the test if it is reached at all.
func TestPushAllNeverAsksForAMissingEntrypoint(t *testing.T) {
	restore := app.Flags().All
	app.Flags().All = true
	t.Cleanup(func() { app.Flags().All = restore })

	complete := jsonx.NewObject()
	complete.Set("$id", "ready")
	complete.Set("name", "Ready")
	complete.Set("entrypoint", "src/main.js")

	incomplete := jsonx.NewObject()
	incomplete.Set("$id", "bare")
	incomplete.Set("name", "Bare")

	local, err := config.LoadOrCreateLocal(filepath.Join(t.TempDir(), config.LocalFileName))
	if err != nil {
		t.Fatal(err)
	}

	context := &pushContext{local: local, prompter: refusingPrompter{t: t}}

	out := &bytes.Buffer{}
	usable, err := context.completeDeployables(out, deployable{
		Name:      "function",
		Singular:  "Function",
		Label:     "functions",
		ConfigKey: "functions",
	}, []*jsonx.Object{complete, incomplete})
	if err != nil {
		t.Fatal(err)
	}

	if len(usable) != 1 || usable[0].GetString("$id") != "ready" {
		t.Errorf("push kept %d of 2 functions, want only the complete one", len(usable))
	}

	printed := out.String()
	if !strings.Contains(printed, "Bare") || !strings.Contains(printed, "entrypoint") {
		t.Errorf("the skipped function was not explained:\n%s", printed)
	}
	if !strings.Contains(printed, config.LocalFileName) {
		t.Errorf("the message does not say where to set it:\n%s", printed)
	}
}

// refusingPrompter fails the test if anything asks it a question.
type refusingPrompter struct {
	prompt.Prompter
	t *testing.T
}

func (r refusingPrompter) Text(question prompt.Text) (string, error) {
	r.t.Errorf("prompted for %q under --all", question.Message)

	return "", nil
}

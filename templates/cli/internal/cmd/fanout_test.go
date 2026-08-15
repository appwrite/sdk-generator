package cmd

import (
	"bytes"
	"net/http"
	"net/http/httptest"
	"path/filepath"
	"slices"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// `pull all` and `push all` mean all, with or without --all. Both were gated on
// the flag, so the bare subcommand fell through to the picker and asked which
// single resource to act on.

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

// `all` has to reach inside each resource: the fan-out is only half of it, since
// each resource then reads the same flag to decide whether to ask which
// functions or buckets to act on. Without it, `pull all` stopped to ask, one
// resource type at a time.
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

// The picker's order is user-visible and is not the execution order: push runs
// settings first and databases last, because a table cannot be created before
// project settings allow the databases service.
//
// Every value offered must resolve to an action. The converse does not hold --
// sites are run by `all` and deliberately absent from the picker.
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

// `push function --all` asked for an entrypoint when the config had none, which
// is the opposite of what --all requested and unanswerable in a pipeline. The
// function is reported and skipped now; the prompter fails the test if reached.
func TestPushAllNeverAsksForAMissingEntrypoint(t *testing.T) {
	restore := app.Flags().All
	app.Flags().All = true
	t.Cleanup(func() { app.Flags().All = restore })

	// 404: neither function exists yet, so the entrypoint is required.
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, _ *http.Request) {
		w.WriteHeader(http.StatusNotFound)
		_, _ = w.Write([]byte(`{"message":"not found","code":404}`))
	}))
	t.Cleanup(server.Close)

	usable, out := completeFunctions(t, server.URL, refusingPrompter{t: t})

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

// The API requires an entrypoint for neither create nor update -- `functions
// create` asks for function-id, name and runtime only. So a blank entrypoint on
// a function that already exists is not an error: it has nothing to say about
// the field, and the value on the server stands.
func TestAnExistingFunctionNeedsNoEntrypoint(t *testing.T) {
	restore := app.Flags().All
	app.Flags().All = false
	t.Cleanup(func() { app.Flags().All = restore })

	// 200: both functions are already there, so neither is a create.
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, _ *http.Request) {
		_, _ = w.Write([]byte(`{"$id":"bare","entrypoint":"src/main.js"}`))
	}))
	t.Cleanup(server.Close)

	usable, out := completeFunctions(t, server.URL, refusingPrompter{t: t})

	if len(usable) != 2 {
		t.Errorf("push kept %d of 2 functions, want both -- an update needs no entrypoint", len(usable))
	}
	if !strings.Contains(out.String(), "Keeping the one on the server") {
		t.Errorf("the reason for not asking was not explained:\n%s", out.String())
	}
}

// completeFunctions runs the validation step over one complete and one
// entrypoint-less function.
func completeFunctions(
	t *testing.T,
	endpoint string,
	prompter prompt.Prompter,
) ([]*jsonx.Object, *bytes.Buffer) {
	t.Helper()

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

	context := &pushContext{
		api:      client.New(endpoint, "test"),
		local:    local,
		prompter: prompter,
	}

	out := &bytes.Buffer{}
	usable, err := context.completeDeployables(out,
		deployable{resourceIdentity: functionIdentity},
		[]*jsonx.Object{complete, incomplete})
	if err != nil {
		t.Fatal(err)
	}

	return usable, out
}

// An empty entrypoint means "unchanged", so it must not be sent -- the server
// would take "" as the new value and clear what is already there. Not every
// blank field: an empty `schedule` really does mean "unschedule it".
func TestAnEmptyEntrypointIsNotSent(t *testing.T) {
	entry := jsonx.NewObject()
	entry.Set("name", "Bare")
	entry.Set("entrypoint", "")
	entry.Set("schedule", "")

	body := writeBody(entry, []string{"name", "entrypoint", "schedule"},
		[]string{"entrypoint"}, "", "")

	if _, ok := body.Get("entrypoint"); ok {
		t.Error("an empty entrypoint was sent, which clears the one on the server")
	}
	if _, ok := body.Get("schedule"); !ok {
		t.Error("an empty schedule was dropped, but clearing a schedule is meaningful")
	}
	if body.GetString("name") != "Bare" {
		t.Error("a non-empty field was dropped")
	}

	// A real entrypoint still goes.
	entry.Set("entrypoint", "src/main.js")
	if got := writeBody(entry, []string{"entrypoint"}, []string{"entrypoint"}, "", "").
		GetString("entrypoint"); got != "src/main.js" {
		t.Errorf("entrypoint = %q, want it sent", got)
	}
}

// refusingPrompter fails the test if anything asks it a question.
type refusingPrompter struct {
	prompt.Prompter
	t *testing.T
}

func (r refusingPrompter) Text(question prompt.Text) (string, error) {
	r.t.Errorf("prompted for %q", question.Message)

	return "", nil
}

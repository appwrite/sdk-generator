package cmd

import (
	"slices"
	"strings"
	"testing"

	"github.com/spf13/cobra"
)

// `pull all` and `push all` mean ALL, with or without --all.
//
// Both were gated on the global --all flag, so the bare subcommand fell through
// to the picker and asked which single resource to act on -- reported for
// `push all` first, then for `pull all` the same way a day later. The
// TypeScript sets cliConfig.all itself before delegating (pull.ts:1136,
// push.ts:4288), so neither ever reaches the question.

func TestPullEverythingRunsEveryNonDeprecatedAction(t *testing.T) {
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

// The regression as the user met it: `pull all` on a machine with no terminal
// stopped to ask a question instead of pulling. Whatever this fails with, it
// must not be the prompt -- the fan-out has no question to ask.
func TestAllSubcommandsNeverPrompt(t *testing.T) {
	for _, name := range []string{"pull", "push"} {
		t.Run(name, func(t *testing.T) {
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

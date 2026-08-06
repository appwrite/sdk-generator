package prompt

import (
	"errors"
	"strings"
	"testing"
)

// TestNonInteractiveNamesTheFlag pins the behaviour that keeps a CI job from
// hanging: every refusal says which flag answers the question.
func TestNonInteractiveNamesTheFlag(t *testing.T) {
	prompter := NonInteractive{}

	_, err := prompter.Choice(Choice{Message: "Which function?", Flag: "--function-id"})
	if err == nil {
		t.Fatal("a prompt with no terminal should fail")
	}
	if !strings.Contains(err.Error(), "--function-id") {
		t.Errorf("error %q should name the flag that answers it", err)
	}
	if !strings.Contains(err.Error(), "Which function?") {
		t.Errorf("error %q should quote the question", err)
	}

	// Every method refuses, not just the one that happens to be called first.
	if _, err := prompter.Text(Text{Message: "Name?"}); err == nil {
		t.Error("Text should refuse")
	}
	if _, err := prompter.MultiChoice(MultiChoice{Message: "Which?"}); err == nil {
		t.Error("MultiChoice should refuse")
	}
	if _, err := prompter.Confirm(Question{Message: "Sure?"}); err == nil {
		t.Error("Confirm should refuse")
	}
}

// TestNonInteractiveErrorWithoutAFlag covers the question that no flag answers;
// the message must still be useful.
func TestNonInteractiveErrorWithoutAFlag(t *testing.T) {
	_, err := NonInteractive{}.Confirm(Question{Message: "Overwrite?"})

	var refusal *NonInteractiveError
	if !errors.As(err, &refusal) {
		t.Fatalf("error was %T, want *NonInteractiveError", err)
	}
	if strings.Contains(err.Error(), "Pass ") {
		t.Errorf("error %q should not dangle an empty flag suggestion", err)
	}
}

// TestForcedAnswersConfirmationsWithoutAsking pins --force. The TypeScript
// repeats `cliConfig.force === true ? true : <prompt>` at each call site;
// wrapping it once means a new confirmation cannot forget to honour it.
func TestForcedAnswersConfirmationsWithoutAsking(t *testing.T) {
	scripted := &Scripted{Confirms: map[string]bool{"Overwrite?": false}}
	forced := Forced{Prompter: scripted}

	answer, err := forced.Confirm(Question{Message: "Overwrite?", Default: false})
	if err != nil {
		t.Fatal(err)
	}
	if !answer {
		t.Error("--force should answer true even against a scripted false")
	}
	if len(scripted.Asked) != 0 {
		t.Errorf("--force should not ask; it asked %v", scripted.Asked)
	}
}

// TestForcedStillAsksEverythingElse pins the limit of --force: it skips
// confirmations, not questions that need a value.
func TestForcedStillAsksEverythingElse(t *testing.T) {
	scripted := &Scripted{Texts: map[string]string{"Project name?": "demo"}}
	forced := Forced{Prompter: scripted}

	value, err := forced.Text(Text{Message: "Project name?"})
	if err != nil {
		t.Fatal(err)
	}
	if value != "demo" {
		t.Errorf("value = %q, want %q", value, "demo")
	}
	if len(scripted.Asked) != 1 {
		t.Errorf("asked %v, want the text question to be asked", scripted.Asked)
	}
}

// TestNewSelectsNonInteractiveWithoutATerminal covers the wiring. The test
// process has no terminal on stdin, so this is the CI case exactly.
func TestNewSelectsNonInteractiveWithoutATerminal(t *testing.T) {
	if Interactive() {
		t.Skip("test is running attached to a terminal")
	}

	if _, ok := New(false).(NonInteractive); !ok {
		t.Error("New should pick NonInteractive when there is no terminal")
	}

	// --force still refuses a question that needs a value, but answers a
	// confirmation -- which is what lets `push --force` run headless.
	forced := New(true)
	if answer, err := forced.Confirm(Question{Message: "Sure?"}); err != nil || !answer {
		t.Errorf("forced Confirm = %v, %v; want true, nil", answer, err)
	}
	if _, err := forced.Text(Text{Message: "Name?", Flag: "--name"}); err == nil {
		t.Error("--force should not invent a text answer")
	}
}

func TestScriptedFallsBackToDefaults(t *testing.T) {
	scripted := &Scripted{}

	if value, _ := scripted.Text(Text{Message: "Name?", Default: "fallback"}); value != "fallback" {
		t.Errorf("Text = %q, want the default", value)
	}
	if answer, _ := scripted.Confirm(Question{Message: "Sure?", Default: true}); !answer {
		t.Error("Confirm should fall back to the default")
	}
	if value, _ := scripted.Choice(Choice{Message: "Which?", Default: "a"}); value != "a" {
		t.Errorf("Choice = %q, want the default", value)
	}
}

// TestScriptedRejectsAnAnswerThatIsNotAnOption keeps a test from passing
// against a choice the real prompt could never return.
func TestScriptedRejectsAnAnswerThatIsNotAnOption(t *testing.T) {
	scripted := &Scripted{Choices: map[string]string{"Which?": "ghost"}}

	_, err := scripted.Choice(Choice{Message: "Which?", Options: Options("a", "b")})
	if err == nil {
		t.Fatal("an answer outside the options should be rejected")
	}
}

func TestScriptedRunsValidators(t *testing.T) {
	scripted := &Scripted{Texts: map[string]string{"Name?": "   "}}

	if _, err := scripted.Text(Text{Message: "Name?", Validate: Required("name")}); err == nil {
		t.Error("a blank answer should fail the required validator")
	}
}

func TestRequiredMatchesTheTypeScriptWording(t *testing.T) {
	// The messages are what the user reads, and the two forms differ.
	err := Required("project name")("")
	if err == nil || err.Error() != "project name is required" {
		t.Errorf("scalar message = %v", err)
	}

	err = RequiredSelection("function")(nil)
	if err == nil || err.Error() != "Please select at least one function" {
		t.Errorf("list message = %v", err)
	}

	if err := Required("name")("  x  "); err != nil {
		t.Errorf("a non-blank value should pass: %v", err)
	}
	if err := RequiredSelection("function")([]string{"a"}); err != nil {
		t.Errorf("a non-empty selection should pass: %v", err)
	}
}

func TestNonNegativeInteger(t *testing.T) {
	for _, value := range []string{"0", "1", "42", "007"} {
		if err := NonNegativeInteger(value); err != nil {
			t.Errorf("%q should be accepted: %v", value, err)
		}
	}
	// A leading sign is rejected, which is why this is not strconv.Atoi --
	// Atoi accepts "+1" and "-1", and the message promises non-negative.
	for _, value := range []string{"", " ", "-1", "+1", "1.5", "1e3", "abc"} {
		if err := NonNegativeInteger(value); err == nil {
			t.Errorf("%q should be rejected", value)
		}
	}
}

// TestScriptedTreatsDefaultAsAPlaceholder mirrors the Terminal contract that a
// live comparison against the TypeScript exposed: inquirer replaces its default
// when the user types, it does not prepend it. huh's Value() seeds the editable
// buffer, so the first port produced "My Awesome TeamQA Team".
func TestScriptedTreatsDefaultAsAPlaceholder(t *testing.T) {
	scripted := &Scripted{Texts: map[string]string{"Name?": "QA Team"}}

	value, err := scripted.Text(Text{Message: "Name?", Default: "My Awesome Team"})
	if err != nil {
		t.Fatal(err)
	}
	if value != "QA Team" {
		t.Errorf("value = %q, want the typed answer alone", value)
	}
}

func TestOptionsBuildsLabelValuePairs(t *testing.T) {
	options := Options("a", "b")

	if len(options) != 2 || options[0].Label != "a" || options[0].Value != "a" {
		t.Fatalf("Options = %v", options)
	}
}

// huh runs the validator from Focus(), before the user has touched anything.
// A "select at least one" rule therefore rendered in red the instant the
// prompt appeared -- an error about a mistake nobody had made yet.
func TestMultiSelectStaysQuietUntilTheUserActs(t *testing.T) {
	validate := skipFirstCall(RequiredSelection("site"))

	// What Focus() asks: the initial, empty state.
	if err := validate(nil); err != nil {
		t.Errorf("complained before the user acted: %v", err)
	}

	// A toggle that selects something is fine either way.
	if err := validate([]string{"one"}); err != nil {
		t.Errorf("rejected a valid selection: %v", err)
	}

	// Submitting with nothing selected is a real mistake and must be reported.
	if err := validate(nil); err == nil {
		t.Error("an empty submission was accepted")
	}
}

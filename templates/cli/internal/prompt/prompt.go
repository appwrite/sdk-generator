package prompt

import (
	"errors"
	"fmt"
	"strings"
)

// The prompt layer for the interactive commands: the field types, the
// validators, and what happens when there is nobody to answer. Individual
// question definitions land with the commands that use them.
//
// An interface rather than direct huh calls because of that last part -- a
// prompt in CI must fail naming the flag that would have answered it, not block
// the pipeline until it times out.

// ErrAborted is returned when the user cancels a prompt.
var ErrAborted = errors.New("prompt cancelled")

// NonInteractiveError reports a prompt that could not be shown.
//
// Flag names the option that answers the question without prompting, so the
// message tells a CI user what to add rather than that something went wrong.
type NonInteractiveError struct {
	Message string
	Flag    string
}

func (e *NonInteractiveError) Error() string {
	if e.Flag != "" {
		return fmt.Sprintf(
			"%q needs an answer but there is no interactive terminal. Pass %s instead",
			e.Message, e.Flag)
	}

	return fmt.Sprintf(
		"%q needs an answer but there is no interactive terminal", e.Message)
}

// Option is one choice in a select.
//
// Label is shown; Value is returned. They differ constantly -- an organization
// is picked by name and used by id.
type Option struct {
	Label string
	Value string
	// Disabled greys the option out and refuses selection, matching
	// An option shown but not selectable -- a plan the account cannot pick.
	Disabled bool
	// Reason is shown beside a disabled option to say why.
	Reason string
}

// Options builds a list where each label is also its value.
func Options(values ...string) []Option {
	options := make([]Option, 0, len(values))
	for _, value := range values {
		options = append(options, Option{Label: value, Value: value})
	}

	return options
}

// Text asks for a single line.
type Text struct {
	Message string
	Default string
	// Flag is named when there is no terminal.
	Flag string
	// Validate rejects a value with a message the user sees. Nil accepts
	// anything.
	Validate func(string) error
	// Secret hides the typed characters.
	Secret bool
}

// Choice asks for one option.
type Choice struct {
	Message string
	Options []Option
	Default string
	Flag    string
	// Filter shows a type-to-narrow field. A property rather than a separate
	// question type, because the only difference is whether the filter is
	// visible.
	Filter bool
}

// MultiChoice asks for zero or more options.
type MultiChoice struct {
	Message  string
	Options  []Option
	Default  []string
	Flag     string
	Filter   bool
	Validate func([]string) error
}

// Question asks for a yes or no.
type Question struct {
	Message string
	Default bool
	Flag    string
}

// Prompter asks the user questions.
type Prompter interface {
	Text(Text) (string, error)
	Choice(Choice) (string, error)
	MultiChoice(MultiChoice) ([]string, error)
	Confirm(Question) (bool, error)
}

// Forced wraps a prompter so every confirmation answers true without asking.
//
// Wrapping the check once, rather than repeating it at each call site, means a
// new confirmation cannot forget to honour --force.
type Forced struct {
	Prompter
}

// Confirm answers true without prompting.
func (f Forced) Confirm(Question) (bool, error) { return true, nil }

// NonInteractive refuses every prompt.
//
// Used when stdin is not a terminal. Each refusal names the flag that would
// have answered it.
type NonInteractive struct{}

func (NonInteractive) Text(question Text) (string, error) {
	return "", &NonInteractiveError{Message: question.Message, Flag: question.Flag}
}

func (NonInteractive) Choice(question Choice) (string, error) {
	return "", &NonInteractiveError{Message: question.Message, Flag: question.Flag}
}

func (NonInteractive) MultiChoice(question MultiChoice) ([]string, error) {
	return nil, &NonInteractiveError{Message: question.Message, Flag: question.Flag}
}

func (NonInteractive) Confirm(question Question) (bool, error) {
	return false, &NonInteractiveError{Message: question.Message, Flag: question.Flag}
}

// Scripted answers from a prepared list, for tests.
//
// Answers are matched on the question's message, so a test reads as the
// conversation it stands for rather than as a queue of positional values.
type Scripted struct {
	Texts        map[string]string
	Choices      map[string]string
	MultiChoices map[string][]string
	Confirms     map[string]bool
	// Asked records every message in order, so a test can assert that a
	// question was skipped rather than merely answered the same way.
	Asked []string
}

func (s *Scripted) record(message string) { s.Asked = append(s.Asked, message) }

func (s *Scripted) Text(question Text) (string, error) {
	s.record(question.Message)

	value, ok := s.Texts[question.Message]
	if !ok {
		value = question.Default
	}
	if question.Validate != nil {
		if err := question.Validate(value); err != nil {
			return "", err
		}
	}

	return value, nil
}

func (s *Scripted) Choice(question Choice) (string, error) {
	s.record(question.Message)

	value, ok := s.Choices[question.Message]
	if !ok {
		return question.Default, nil
	}

	// A scripted answer that is not on the list is a test bug, and a silent
	// pass-through would hide it.
	for _, option := range question.Options {
		if option.Value == value {
			return value, nil
		}
	}

	return "", fmt.Errorf("scripted answer %q is not an option for %q", value, question.Message)
}

func (s *Scripted) MultiChoice(question MultiChoice) ([]string, error) {
	s.record(question.Message)

	values, ok := s.MultiChoices[question.Message]
	if !ok {
		values = question.Default
	}
	if question.Validate != nil {
		if err := question.Validate(values); err != nil {
			return nil, err
		}
	}

	return values, nil
}

func (s *Scripted) Confirm(question Question) (bool, error) {
	s.record(question.Message)

	value, ok := s.Confirms[question.Message]
	if !ok {
		return question.Default, nil
	}

	return value, nil
}

// Required rejects an empty value.
//
// The message is built from the resource name and is
// what the user reads, so the two forms are kept exactly: a list says "select
// at least one", a scalar says "is required".
func Required(resource string) func(string) error {
	return func(value string) error {
		if strings.TrimSpace(value) == "" {
			return fmt.Errorf("%s is required", resource)
		}

		return nil
	}
}

// RequiredSelection rejects an empty list.
func RequiredSelection(resource string) func([]string) error {
	return func(values []string) error {
		if len(values) == 0 {
			return fmt.Errorf("Please select at least one %s", resource)
		}

		return nil
	}
}

// skipFirstCall discards a validator's first verdict.
//
// huh calls a MultiSelect's validator from Focus(), before the user has touched
// anything, so a rule like RequiredSelection rendered in red the instant the
// prompt appeared. The first call describes the initial state rather than a
// choice; every call after it is a toggle or a submit.
func skipFirstCall(validate func([]string) error) func([]string) error {
	called := false

	return func(values []string) error {
		if !called {
			called = true

			return nil
		}

		return validate(values)
	}
}

// NonNegativeInteger rejects anything that is not a run of digits.
//
// Deliberately not strconv.Atoi: that
// accepts a leading `+` or `-`, and the message promises non-negative.
func NonNegativeInteger(value string) error {
	if value == "" {
		return errors.New("Please enter a non-negative integer.")
	}
	for _, character := range value {
		if character < '0' || character > '9' {
			return errors.New("Please enter a non-negative integer.")
		}
	}

	return nil
}

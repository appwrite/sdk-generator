//go:build !browser

package prompt

import (
	"errors"
	"io"
	"os"

	"github.com/charmbracelet/huh"
	"github.com/charmbracelet/lipgloss"
	"golang.org/x/term"
)

// The huh-backed prompter, and the decision of when to use it.

// Terminal asks the user interactively.
type Terminal struct {
	// Input and Output default to the process's stdin and stderr.
	//
	// Prompts go to STDERR, not stdout: `appwrite ... --json | jq` must not
	// have a question rendered into the JSON stream.
	Input  io.Reader
	Output io.Writer
}

// New returns the prompter appropriate to the environment.
//
// force answers confirmations true without asking, matching --force.
// A non-terminal stdin gets NonInteractive, so a prompt in CI fails with the
// flag that answers it instead of blocking until the job is killed.
func New(force bool) Prompter {
	var prompter Prompter = NonInteractive{}
	if Interactive() {
		prompter = &Terminal{}
	}

	if force {
		return Forced{Prompter: prompter}
	}

	return prompter
}

// Interactive reports whether there is a terminal to prompt on.
//
// Both ends are checked. stdin decides whether an answer can be typed and
// stderr whether the question can be seen; a piped stdin with a terminal
// stderr is still unanswerable.
func Interactive() bool {
	return term.IsTerminal(int(os.Stdin.Fd())) && term.IsTerminal(int(os.Stderr.Fd()))
}

func (t *Terminal) input() io.Reader {
	if t.Input != nil {
		return t.Input
	}

	return os.Stdin
}

func (t *Terminal) output() io.Writer {
	if t.Output != nil {
		return t.Output
	}

	return os.Stderr
}

// minimumWidth is the narrowest form this will render.
//
// Not cosmetic. bubbles panics with "makeslice: len out of range" drawing a
// placeholder into zero columns, and a terminal reports zero more often than
// one would like -- inside a pty opened without a window size, and briefly
// during a resize. A CLI must not crash because of that.
const minimumWidth = 40

// run executes a single-field form and normalises cancellation.
func (t *Terminal) run(field huh.Field) error {
	form := huh.NewForm(huh.NewGroup(field)).
		WithInput(t.input()).
		WithOutput(t.output()).
		WithTheme(theme)

	if width, _, err := term.GetSize(int(os.Stderr.Fd())); err != nil || width < minimumWidth {
		form = form.WithWidth(minimumWidth)
	}

	err := form.Run()
	if errors.Is(err, huh.ErrUserAborted) {
		return ErrAborted
	}

	return err
}

// Text implements Prompter.
//
// The default is a PLACEHOLDER, not pre-filled text. The contract is that a
// default is shown greyed out and replaced the moment the user types; huh's
// Value() seeds the editable buffer instead, so typing would append to it --
// answering "QA Team" against a default of "My Awesome Team" produced "My
// Awesome TeamQA Team".
// Submitting an empty field takes the default.
func (t *Terminal) Text(question Text) (string, error) {
	var value string

	field := huh.NewInput().
		Title(question.Message).
		Placeholder(question.Default).
		Value(&value)

	if question.Secret {
		field = field.EchoMode(huh.EchoModePassword)
	}
	if question.Validate != nil {
		// The validator has to see the resolved value, or a required field
		// with a default would reject an empty submission the default answers.
		field = field.Validate(func(typed string) error {
			if typed == "" {
				typed = question.Default
			}

			return question.Validate(typed)
		})
	}

	if err := t.run(field); err != nil {
		return "", err
	}

	if value == "" {
		return question.Default, nil
	}

	return value, nil
}

// huhOptions converts options, carrying the disabled reason into the label.
//
// huh has no disabled state, so an unselectable option is rendered with its
// reason and rejected by the validator instead. Dropping them would be wrong:
// an unavailable plan is shown so the user knows it exists.
func huhOptions(options []Option) ([]huh.Option[string], map[string]string) {
	converted := make([]huh.Option[string], 0, len(options))
	disabled := map[string]string{}

	for _, option := range options {
		label := option.Label
		if option.Disabled {
			disabled[option.Value] = option.Reason
			if option.Reason != "" {
				label += " (" + option.Reason + ")"
			}
		}
		converted = append(converted, huh.NewOption(label, option.Value))
	}

	return converted, disabled
}

// Choice implements Prompter.
func (t *Terminal) Choice(question Choice) (string, error) {
	options, disabled := huhOptions(question.Options)
	value := question.Default

	field := huh.NewSelect[string]().
		Title(question.Message).
		Options(options...).
		Filtering(question.Filter).
		Value(&value).
		Validate(func(selected string) error {
			if reason, ok := disabled[selected]; ok {
				if reason != "" {
					return errors.New(reason)
				}

				return errors.New("that option is not available")
			}

			return nil
		})

	if err := t.run(field); err != nil {
		return "", err
	}

	return value, nil
}

// MultiChoice implements Prompter.
func (t *Terminal) MultiChoice(question MultiChoice) ([]string, error) {
	options, disabled := huhOptions(question.Options)
	values := question.Default

	field := huh.NewMultiSelect[string]().
		Title(question.Message).
		Options(options...).
		Filterable(question.Filter).
		Value(&values).
		Validate(skipFirstCall(func(selected []string) error {
			for _, value := range selected {
				if reason, ok := disabled[value]; ok {
					if reason != "" {
						return errors.New(reason)
					}

					return errors.New("that option is not available")
				}
			}
			if question.Validate != nil {
				return question.Validate(selected)
			}

			return nil
		}))

	if err := t.run(field); err != nil {
		return nil, err
	}

	return values, nil
}

// Confirm implements Prompter.
func (t *Terminal) Confirm(question Question) (bool, error) {
	value := question.Default

	// Inline and left-aligned: huh stacks the buttons under the question and
	// centres them by default, which floats them in the middle of an otherwise
	// left-aligned terminal. A yes/no belongs beside the question it answers.
	field := huh.NewConfirm().
		Title(question.Message).
		Inline(true).
		WithButtonAlignment(lipgloss.Left).
		Value(&value)

	if err := t.run(field); err != nil {
		return false, err
	}

	return value, nil
}

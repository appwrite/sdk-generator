//go:build !browser

package prompt

import (
	"github.com/charmbracelet/huh"
	"github.com/charmbracelet/lipgloss"
)

// Prompts are themed to match the rest of the CLI's output.
//
// huh ships the Charm house theme -- magenta and pink -- which is the
// library's branding rather than ours, and it sat next to output that uses
// none of those colours. Everything else here paints with the terminal's own
// palette: cyan for headings and table rules, green for success, red for
// errors, yellow for pending, faint for hints.
//
// ANSI INDICES, NOT HEX. A numbered colour resolves against whatever palette
// the reader has configured, so the prompt follows their terminal theme
// instead of imposing one. A hex value looks the same everywhere, which is the
// problem: it is unreadable on the terminals it was not picked for.
var (
	accentColor = lipgloss.Color("6")
	errorColor  = lipgloss.Color("1")
	mutedColor  = lipgloss.Color("8")
)

// theme is built once. Constructing it per prompt would be wasteful and would
// let two prompts drift apart.
var theme = newTheme()

func newTheme() *huh.Theme {
	base := huh.ThemeBase()

	base.Focused.Base = base.Focused.Base.BorderForeground(accentColor)
	base.Focused.Title = base.Focused.Title.Foreground(accentColor).Bold(true)
	base.Focused.Description = base.Focused.Description.Foreground(mutedColor)
	base.Focused.SelectSelector = base.Focused.SelectSelector.Foreground(accentColor)
	base.Focused.SelectedOption = base.Focused.SelectedOption.Foreground(accentColor)
	base.Focused.MultiSelectSelector = base.Focused.MultiSelectSelector.Foreground(accentColor)
	base.Focused.SelectedPrefix = base.Focused.SelectedPrefix.Foreground(accentColor)
	base.Focused.TextInput.Prompt = base.Focused.TextInput.Prompt.Foreground(accentColor)
	base.Focused.TextInput.Cursor = base.Focused.TextInput.Cursor.Foreground(accentColor)

	// Reversed rather than filled with a brand colour: the focused button then
	// reads as focused on a light terminal as well as a dark one, which a
	// fixed background does not.
	//
	// MarginLeft on the first button, because Inline(true) butts it straight
	// against the question mark -- "apply these changes?Yes" reads as one word.
	base.Focused.FocusedButton = base.Focused.FocusedButton.
		Foreground(lipgloss.Color("0")).Background(accentColor).Bold(true).
		MarginLeft(1)
	base.Focused.BlurredButton = base.Focused.BlurredButton.
		Foreground(mutedColor).Background(lipgloss.NoColor{}).
		MarginLeft(1)

	base.Focused.ErrorIndicator = base.Focused.ErrorIndicator.Foreground(errorColor)
	base.Focused.ErrorMessage = base.Focused.ErrorMessage.Foreground(errorColor)

	base.Blurred = base.Focused
	base.Blurred.Base = base.Blurred.Base.BorderForeground(mutedColor)
	base.Blurred.Title = base.Blurred.Title.Foreground(mutedColor).Bold(false)

	base.Help.Ellipsis = base.Help.Ellipsis.Foreground(mutedColor)
	base.Help.ShortKey = base.Help.ShortKey.Foreground(mutedColor)
	base.Help.ShortDesc = base.Help.ShortDesc.Foreground(mutedColor)
	base.Help.ShortSeparator = base.Help.ShortSeparator.Foreground(mutedColor)

	return base
}

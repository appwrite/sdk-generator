// Package styles holds the kontrolplane palette as it applies to the terminal, as relay paints it.
//
// The values come from kontrolplane's brand tokens, its colours and their semantic roles.
// Terminals cannot blend alpha, so every rule and tint is the solid mix the token describes. A
// detected theme keeps the terminal's own background; both themes paint cornsilk
// and black forest text with olive accents on top of it. A theme chosen with --theme paints its own
// background as well (see Paint and Ground).
//
// # tones
//
// Text is painted in a tone, never a colour, and the same kind of thing takes the same tone on every
// page:
//
//   - text: what a row or a page is about: the name of a database, a table, a function, a bucket
//     or a user, the last crumb, and the keys in the footer and the help, in bold.
//   - body: values: what a column or a field says, numbers, counts, times and sizes among them,
//     and the words of a log or a payload.
//   - muted: what qualifies a value: a runtime, a type, a mime type, a reason, the tabs that are
//     not open, what a key does, and what a page or a table says while it loads or holds nothing.
//   - faint: metadata and punctuation: column headings and field labels, the keys of json, units
//     and counts beside a name or a tab, ids, separators, empty values such as "-" and "none",
//     json's null and punctuation, hints, and notes on what cannot be shown and why.
//   - accent: where the cursor is and what runs now: the open tab, the cursor's marker, the
//     spinner, a build in flight, the matches of a filter and the titles in the help.
//   - success: something done or healthy: ✓, enabled, ready, verified, a 2xx response.
//   - warning: something that needs attention and has not failed: ▲, disabled, building, waiting,
//     paused, and a dialog that asks before something that cannot be undone easily.
//   - danger: a failure or something destructive: ✗, failed, errors, a 5xx response, a timeout
//     and the dialog that deletes.
//   - info: what points elsewhere: a cloud or self-hosted instance, a relationship, a trigger.
//   - warm: numbers in a payload, and its booleans.
//
// A status carries its symbol as well as its tone, ✓, ▲ or ✗, so it reads without colour too.
// Rules are hairlines: they divide, carry nothing that is not also said in words, and are left
// out of the contrast checks.
//
// # colour profiles
//
// UseProfile picks the palette for what the terminal shows. 256 colours take palettes of their own,
// as the nearest of the 256 can lose the hue (the dark selection became green) or the contrast.
// Sixteen colours are the terminal's own, whose values cannot be known, so the palette names them
// by role, and a selected row is drawn in reverse video (see Reverse). Without colour, NO_COLOR, a
// selected row is reversed as well, danger and warning are bold, and a focused button is reversed.
package styles

import (
	"image/color"
	"strings"

	"charm.land/bubbles/v2/textinput"
	"charm.land/lipgloss/v2"
	"github.com/charmbracelet/colorprofile"
	"github.com/charmbracelet/x/ansi"
)

// Palette is one theme of the brand, resolved to solid colours.
type Palette struct {
	Dark bool

	// Base is the plate or the stock.
	Base color.Color
	// Surface is for raised surfaces and unfocused buttons.
	Surface color.Color
	// Selection is the row under the cursor.
	Selection color.Color
	// Rule is for hairlines and borders.
	Rule color.Color
	// RuleBold is a rule that has to read as a division.
	RuleBold color.Color

	// Text is for headings and anything that carries the page.
	Text color.Color
	// Body is for values and body copy.
	Body color.Color
	// Muted is for labels and meta.
	Muted color.Color
	// Faint is for hints, punctuation and empty values.
	Faint color.Color

	// Accent is olive, the brand's highlight.
	Accent color.Color
	// Warm is copper, for numbers in payloads.
	Warm color.Color

	Success color.Color
	Warning color.Color
	Info    color.Color
	Danger  color.Color
}

var (
	blackForest = lipgloss.Color("#283618")
	cornsilk    = lipgloss.Color("#FEFAE0")
)

// Dark sits on the terminal's own dark ground. Tints mix cornsilk into a neutral dark so only the
// accent reads as olive: the surface is 12% cornsilk, the selection 30% olive, the rules 16% and
// 38% cornsilk, and the body, muted and faint text 92%, 74% and 55%. Danger is a shade lighter than
// the token, to read at 4.5:1 on the selection.
var Dark = Palette{
	Dark:      true,
	Base:      lipgloss.Color("#1A1A1A"),
	Surface:   lipgloss.Color("#353532"),
	Selection: lipgloss.Color("#2F3323"),
	Rule:      lipgloss.Color("#3E3E3A"),
	RuleBold:  lipgloss.Color("#716F65"),
	Text:      cornsilk,
	Body:      lipgloss.Color("#ECE8D0"),
	Muted:     lipgloss.Color("#C3C0AD"),
	Faint:     lipgloss.Color("#979587"),
	Accent:    lipgloss.Color("#9FA57B"),
	Warm:      lipgloss.Color("#D0975D"),
	Success:   lipgloss.Color("#00C389"),
	Warning:   lipgloss.Color("#F7AB1B"),
	Info:      lipgloss.Color("#53A2FF"),
	Danger:    lipgloss.Color("#FF6B6E"),
}

// Light is the cornsilk stock. Hierarchy is a tint of black forest, never a grey: the surface is
// 5% black forest, the selection 12%, the rules 20% and 30%, and faint text 68%, which reads at
// 4.5:1 on the stock. The olive, copper and the statuses are the tokens' hues a step darker in
// oklch, so they read at 4.5:1 on the selection too.
var Light = Palette{
	Dark:      false,
	Base:      cornsilk,
	Surface:   lipgloss.Color("#F3F0D6"),
	Selection: lipgloss.Color("#E4E2C8"),
	Rule:      lipgloss.Color("#D3D3B8"),
	RuleBold:  lipgloss.Color("#BEBFA4"),
	Text:      blackForest,
	Body:      lipgloss.Color("#3D4A2C"),
	Muted:     lipgloss.Color("#60694C"),
	Faint:     lipgloss.Color("#6C7558"),
	Accent:    lipgloss.Color("#5C6736"),
	Warm:      lipgloss.Color("#8C551E"),
	Success:   lipgloss.Color("#007146"),
	Warning:   lipgloss.Color("#8C5600"),
	Info:      lipgloss.Color("#0061BC"),
	Danger:    lipgloss.Color("#C8000A"),
}

// Dark256 is Dark in the 256 colours of xterm. The cube has no olive dark enough for the
// selection, so it is a grey, as the surface is.
var Dark256 = Palette{
	Dark:      true,
	Base:      ansi.IndexedColor(234),
	Surface:   ansi.IndexedColor(236),
	Selection: ansi.IndexedColor(236),
	Rule:      ansi.IndexedColor(237),
	RuleBold:  ansi.IndexedColor(242),
	Text:      ansi.IndexedColor(230),
	Body:      ansi.IndexedColor(254),
	Muted:     ansi.IndexedColor(187),
	Faint:     ansi.IndexedColor(246),
	Accent:    ansi.IndexedColor(144),
	Warm:      ansi.IndexedColor(173),
	Success:   ansi.IndexedColor(42),
	Warning:   ansi.IndexedColor(214),
	Info:      ansi.IndexedColor(75),
	Danger:    ansi.IndexedColor(210),
}

// Light256 is Light in the 256 colours of xterm. Its darkest amber is the one copper and warning
// share, and the selection is the lightest grey, the one ground it reads on at 4.5:1.
var Light256 = Palette{
	Dark:      false,
	Base:      ansi.IndexedColor(230),
	Surface:   ansi.IndexedColor(254),
	Selection: ansi.IndexedColor(255),
	Rule:      ansi.IndexedColor(252),
	RuleBold:  ansi.IndexedColor(248),
	Text:      ansi.IndexedColor(235),
	Body:      ansi.IndexedColor(238),
	Muted:     ansi.IndexedColor(240),
	Faint:     ansi.IndexedColor(242),
	Accent:    ansi.IndexedColor(58),
	Warm:      ansi.IndexedColor(94),
	Success:   ansi.IndexedColor(22),
	Warning:   ansi.IndexedColor(94),
	Info:      ansi.IndexedColor(25),
	Danger:    ansi.IndexedColor(160),
}

// Dark16 names the terminal's own sixteen colours by role, for a dark ground. The accent is cyan,
// the one hue no status takes.
var Dark16 = Palette{
	Dark:      true,
	Base:      lipgloss.Black,
	Surface:   lipgloss.BrightBlack,
	Selection: lipgloss.BrightBlack,
	Rule:      lipgloss.BrightBlack,
	RuleBold:  lipgloss.BrightBlack,
	Text:      lipgloss.BrightWhite,
	Body:      lipgloss.White,
	Muted:     lipgloss.White,
	Faint:     lipgloss.BrightBlack,
	Accent:    lipgloss.Cyan,
	Warm:      lipgloss.Magenta,
	Success:   lipgloss.Green,
	Warning:   lipgloss.Yellow,
	Info:      lipgloss.BrightBlue,
	Danger:    lipgloss.Red,
}

// Light16 names the terminal's own sixteen colours by role, for a light ground.
var Light16 = Palette{
	Dark:      false,
	Base:      lipgloss.BrightWhite,
	Surface:   lipgloss.White,
	Selection: lipgloss.White,
	Rule:      lipgloss.White,
	RuleBold:  lipgloss.BrightBlack,
	Text:      lipgloss.Black,
	Body:      lipgloss.Black,
	Muted:     lipgloss.BrightBlack,
	Faint:     lipgloss.BrightBlack,
	Accent:    lipgloss.Cyan,
	Warm:      lipgloss.Magenta,
	Success:   lipgloss.Green,
	Warning:   lipgloss.Yellow,
	Info:      lipgloss.Blue,
	Danger:    lipgloss.Red,
}

// P is the active palette.
var P = Dark

// Paint is set when the theme was chosen rather than detected. The palette then paints its own
// ground, since the terminal's may be the opposite of what the theme was made for.
var Paint bool

// dark and profile are what P was picked for: the theme, and the colours the terminal shows.
var (
	dark    = true
	profile = colorprofile.TrueColor
)

// Use selects the dark or the light theme.
func Use(d bool) {
	dark = d
	pick()
}

// UseProfile selects the palette for the colours the terminal shows: truecolor, 256, the
// terminal's own sixteen, or none.
func UseProfile(p colorprofile.Profile) {
	profile = p
	pick()
}

// gen counts the palettes picked.
var gen int

// Gen numbers the palette in use. It changes whenever Use or UseProfile picks one, so what is kept
// drawn can tell it was drawn in another.
func Gen() int { return gen }

func pick() {
	gen++
	switch {
	case profile == colorprofile.ANSI256 && dark:
		P = Dark256
	case profile == colorprofile.ANSI256:
		P = Light256
	case profile == colorprofile.ANSI && dark:
		P = Dark16
	case profile == colorprofile.ANSI:
		P = Light16
	case dark:
		P = Dark
	default:
		P = Light
	}
}

// Mono reports whether the terminal shows no colour, as with NO_COLOR, so what a colour would
// say takes bold or reverse video.
func Mono() bool { return profile < colorprofile.ANSI }

// Reverse reports whether a selected row is drawn in reverse video rather than on the selection:
// without colour, or with only the terminal's own sixteen, no ground is sure to differ from the
// terminal's.
func Reverse() bool { return profile < colorprofile.ANSI256 }

// Tone names a foreground role, so text can be described once and painted on any ground.
type Tone int

// The tones.
const (
	ToneText Tone = iota
	ToneBody
	ToneMuted
	ToneFaint
	ToneAccent
	ToneWarm
	ToneSuccess
	ToneWarning
	ToneInfo
	ToneDanger
	ToneRule
)

// Color is the colour of a tone in the palette.
func (p Palette) Color(t Tone) color.Color {
	switch t {
	case ToneBody:
		return p.Body
	case ToneMuted:
		return p.Muted
	case ToneFaint:
		return p.Faint
	case ToneAccent:
		return p.Accent
	case ToneWarm:
		return p.Warm
	case ToneSuccess:
		return p.Success
	case ToneWarning:
		return p.Warning
	case ToneInfo:
		return p.Info
	case ToneDanger:
		return p.Danger
	case ToneRule:
		return p.RuleBold
	default:
		return p.Text
	}
}

// Fg returns a style painting the given tone. Without colour, danger and warning are bold.
func Fg(t Tone) lipgloss.Style {
	s := lipgloss.NewStyle().Foreground(P.Color(t))
	if Mono() && (t == ToneDanger || t == ToneWarning) {
		s = s.Bold(true)
	}
	return s
}

// Body, Muted, Faint and Accent paint text in a tone. The text must be clean already.
func Body(s string) string   { return Fg(ToneBody).Render(s) }
func Muted(s string) string  { return Fg(ToneMuted).Render(s) }
func Faint(s string) string  { return Fg(ToneFaint).Render(s) }
func Accent(s string) string { return Fg(ToneAccent).Render(s) }

// Span is a run of text with a single tone. Cells and inline values are built from spans so they
// can be repainted onto a selected row without the inner resets cutting through its background.
type Span struct {
	Text string
	Tone Tone
	Bold bool
}

// S and B make a span of text, cleaned, in a tone, B in bold.
func S(text string, tone Tone) Span { return Span{Text: Clean(text), Tone: tone} }
func B(text string, tone Tone) Span { return Span{Text: Clean(text), Tone: tone, Bold: true} }

// Style is the style the span is painted in.
func (s Span) Style() lipgloss.Style {
	style := Fg(s.Tone)
	if s.Bold {
		style = style.Bold(true)
	}
	return style
}

// Render joins spans on the terminal's ground.
func Render(spans ...Span) string {
	var b strings.Builder
	for _, s := range spans {
		b.WriteString(s.Style().Render(s.Text))
	}
	return b.String()
}

// Button renders a dialog button. A focused button takes the primary treatment, or the given
// tone when the action it confirms is destructive; without colour, it is reversed.
func Button(label string, focused bool, tone Tone) string {
	style := lipgloss.NewStyle().Padding(0, 3)
	if !focused {
		return style.Foreground(P.Muted).Background(P.Surface).Render(label)
	}
	bg := P.Text
	if tone != ToneText {
		bg = P.Color(tone)
	}
	return style.Foreground(P.Base).Background(bg).Bold(true).Reverse(Mono()).Render(label)
}

// Badge renders a short label on a ground of tone, as cloud or self-hosted beside the endpoint in the header,
// so it reads as a thing of its own; without colour, it is reversed.
func Badge(label string, tone Tone) string {
	return lipgloss.NewStyle().Padding(0, 1).Foreground(P.Base).Background(P.Color(tone)).Bold(true).Reverse(Mono()).Render(Clean(label))
}

// Key renders a key binding hint.
func Key(k, desc string) string {
	return Fg(ToneText).Bold(true).Render(k) + " " + Muted(desc)
}

// SectionHeader renders a lowercase label followed by a hairline, filling width. A label too long
// for it ends in an ellipsis, so the line stays within width.
func SectionHeader(title string, width int) string {
	title = Clean(title)
	if ansi.StringWidth(title) > width-2 {
		title = ansi.Truncate(title, max(0, width-2), "…")
	}
	label := Fg(ToneText).Bold(true).Render(title)
	fill := max(1, width-lipgloss.Width(label)-1)
	return label + " " + lipgloss.NewStyle().Foreground(P.Rule).Render(strings.Repeat("─", fill))
}

// TextInput returns the styles for single line inputs.
func TextInput() textinput.Styles {
	s := textinput.DefaultStyles(P.Dark)
	s.Focused.Text = Fg(ToneText)
	s.Focused.Placeholder = Fg(ToneFaint)
	s.Focused.Prompt = Fg(ToneAccent)
	s.Blurred.Text = Fg(ToneBody)
	s.Blurred.Placeholder = Fg(ToneFaint)
	s.Blurred.Prompt = Fg(ToneFaint)
	s.Cursor.Color = P.Accent
	return s
}

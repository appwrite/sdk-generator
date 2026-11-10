package tui

import (
	"strings"

	"charm.land/bubbles/v2/textinput"
	tea "charm.land/bubbletea/v2"
	"charm.land/lipgloss/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// dialogPadX is the room a dialog card leaves between its edge and its text.
const dialogPadX = 4

// dialogTextWidth is the widest a line of dialog text gets before it wraps.
func dialogTextWidth() int {
	return max(10, min(contentWidth-4-2*dialogPadX, 90))
}

// dialog renders a card with a toned edge, for confirmations and errors, to be set over the page
// with overlay. Lines wider than the card wrap evenly, and the card gives up its inner margin when
// it would not fit the height. Should it still not fit, the longest block of the body is cut, so
// the heading and the answer below, its buttons or what closes it, stay in sight.
func dialog(title string, tone styles.Tone, body ...string) string {
	width := dialogTextWidth()
	heading := append(wrapEven(styles.Render(styles.B("▲ ", tone), styles.B(title, tone)), width), "")
	blocks := make([][]string, len(body))
	height := len(heading)
	for i, block := range body {
		for line := range strings.SplitSeq(block, "\n") {
			blocks[i] = append(blocks[i], wrapEven(line, width)...)
		}
		height += len(blocks[i])
	}
	padY := 1
	if height+2+2*padY > contentHeight {
		padY = 0
	}
	room := contentHeight - 2 - 2*padY
	lines := heading
	for _, block := range fitBlocks(blocks, room-len(heading), width) {
		lines = append(lines, block...)
	}
	return lipgloss.NewStyle().
		Border(lipgloss.RoundedBorder()).
		BorderForeground(styles.P.Color(tone)).
		Padding(padY, dialogPadX).
		Render(clip(lipgloss.JoinVertical(lipgloss.Center, lines...), width, room))
}

// wrapEven wraps a line wider than width into as few lines as lipgloss would, each about as wide
// as the others, so a centred paragraph does not end on a lone word.
func wrapEven(line string, width int) []string {
	if ansi.StringWidth(line) <= width {
		return []string{line}
	}
	wrap := func(w int) []string { return strings.Split(lipgloss.Wrap(line, w, ""), "\n") }
	n := len(wrap(width))
	lo, hi := max(1, (ansi.StringWidth(line)+n-1)/n), width
	for lo < hi {
		if mid := (lo + hi) / 2; len(wrap(mid)) <= n {
			hi = mid
		} else {
			lo = mid + 1
		}
	}
	return wrap(lo)
}

// fitBlocks cuts lines from the end of the longest block until the blocks fit in room, ending
// what is left of a cut block in an ellipsis.
func fitBlocks(blocks [][]string, room, width int) [][]string {
	height := 0
	for _, b := range blocks {
		height += len(b)
	}
	cut := -1
	for height > room {
		longest := 0
		for i, b := range blocks {
			if len(b) > len(blocks[longest]) {
				longest = i
			}
		}
		if len(blocks[longest]) <= 1 {
			break
		}
		blocks[longest] = blocks[longest][:len(blocks[longest])-1]
		height--
		cut = longest
	}
	if cut >= 0 {
		b := blocks[cut]
		last := b[len(b)-1]
		keep := min(ansi.StringWidth(strings.TrimRight(ansi.Strip(last), " ")), width-1)
		b[len(b)-1] = ansi.Truncate(last, keep, "") + styles.Faint("…")
	}
	return blocks
}

// overlay sets card in the middle of the content area, over the page, which recedes behind it so
// it stays clear what the card is about.
func overlay(page, card string) string {
	lines := strings.Split(ansi.Strip(fill(page, contentWidth, contentHeight)), "\n")
	dim := lipgloss.NewStyle().Foreground(styles.P.Rule)
	for i, l := range lines {
		lines[i] = dim.Render(l)
	}
	card = lipgloss.NewStyle().Padding(0, 1).Render(card)
	w, h := lipgloss.Width(card), lipgloss.Height(card)
	c := lipgloss.NewCanvas(contentWidth, contentHeight)
	c.Compose(lipgloss.NewCompositor(
		lipgloss.NewLayer(strings.Join(lines, "\n")),
		lipgloss.NewLayer(card).X(max(0, (contentWidth-w)/2)).Y(max(0, (contentHeight-h)/2)).Z(1),
	))
	return fill(c.Render(), contentWidth, contentHeight)
}

// typedConfirm guards an action that cannot be undone behind typing a phrase, the id of what it
// deletes. action is what enter then does, as delete the row.
type typedConfirm struct {
	want   string
	action string
	input  textinput.Model
	// tried is enter pressed on what does not match, until it is typed again.
	tried bool
}

func newTypedConfirm(want, action string) (typedConfirm, tea.Cmd) {
	input := textinput.New()
	input.CharLimit = 255
	input.SetWidth(min(max(24, ansi.StringWidth(want)+2), dialogTextWidth()-6))
	input.Prompt = "› "
	input.SetStyles(styles.TextInput())
	cmd := input.Focus()
	return typedConfirm{want: want, action: action, input: input}, cmd
}

func (c typedConfirm) ok() bool {
	return strings.TrimSpace(c.input.Value()) == c.want
}

// wrong reports whether what was typed cannot become the phrase, or enter was pressed on it.
func (c typedConfirm) wrong() bool {
	typed := strings.TrimSpace(c.input.Value())
	if typed == "" || c.ok() {
		return false
	}
	return c.tried || !strings.HasPrefix(c.want, typed)
}

func (c typedConfirm) update(msg tea.Msg) (typedConfirm, tea.Cmd) {
	before := c.input.Value()
	var cmd tea.Cmd
	c.input, cmd = c.input.Update(msg)
	if c.input.Value() != before {
		c.tried = false
	}
	return c, cmd
}

// view asks for the phrase, marks whether what was typed matches it, and says below what enter
// does or that the phrase is not what was typed. The lines stay the same in number as it is
// typed, so the dialog does not move.
func (c typedConfirm) view() string {
	mark := "  "
	say := styles.Render(styles.S("esc cancels", styles.ToneFaint))
	switch {
	case c.ok():
		mark = styles.Render(styles.S("✓ ", styles.ToneSuccess))
		say = styles.Render(styles.S("enter to "+c.action, styles.ToneSuccess))
	case c.wrong():
		mark = styles.Render(styles.S("✗ ", styles.ToneDanger))
		say = styles.Render(styles.S("that is not ", styles.ToneDanger), styles.B(c.want, styles.ToneDanger))
	}
	ask := styles.Render(styles.S("type ", styles.ToneFaint), styles.B(c.want, styles.ToneText), styles.S(" to confirm", styles.ToneFaint))
	return lipgloss.JoinVertical(lipgloss.Center, ask, "", mark+c.input.View()+"  ", "", say)
}

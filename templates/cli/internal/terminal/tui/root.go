package tui

import (
	"fmt"
	"slices"
	"strings"
	"time"

	"charm.land/bubbles/v2/key"
	"charm.land/bubbles/v2/spinner"
	tea "charm.land/bubbletea/v2"
	"charm.land/lipgloss/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/keys"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
){% verbatim %}

// Init reads the counts of the overview and starts the refresh, with the generation newModel
// numbered them by, as Init cannot change the model. With --theme auto it asks the terminal for
// its background, which follows as a tea.BackgroundColorMsg, or not at all.
func (m model) Init() tea.Cmd {
	h0 := m.stack[0].home
	first := tea.Batch(m.countCmds(h0), m.usageCmd(h0.usage.gen, m.stack[0].b, projectUsage, usageOf{}), m.dashCmd(h0.dash.gen, m.stack[0].b))
	if s := m.stack[0]; s.page == listPage {
		first = m.listCmd(s.list.gen, s.list, false)
	}
	cmds := []tea.Cmd{first, refreshTick(m.refreshGen), m.spinner.Tick}
	if !styles.Paint {
		cmds = append(cmds, tea.RequestBackgroundColor)
	}
	return tea.Batch(cmds...)
}

// busy reports whether something runs that the spinner shows: an action, or the first read of
// the list on screen.
func (m model) busy() bool {
	if m.actions > 0 {
		return true
	}
	if l := m.pageList(); l != nil {
		return !l.loaded && l.err == ""
	}
	if u := m.pageUsage(); u != nil {
		return !u.loaded && u.err == ""
	}
	return false
}

// keepSpinning restarts the spinner when something began after it last stopped.
func (m model) keepSpinning() (model, tea.Cmd) {
	if !m.busy() || m.spinning {
		return m, nil
	}
	m.spinning = true
	return m, m.spinner.Tick
}

// copy puts text on the clipboard of the terminal.
func (m model) copy(text string) (model, tea.Cmd) {
	if text == "" {
		return m, nil
	}
	m, status := m.setStatus("copied "+truncate(styles.Clean(text), 48), styles.ToneSuccess)
	return m, tea.Batch(tea.SetClipboard(text), status)
}

// refresh reads the page on screen again, on the refresh's tick, and measures the round trip for
// the header every usageEvery, as each one counts in the project's usage.
func (m model) refresh() (model, tea.Cmd) {
	var cmds []tea.Cmd
	if time.Since(m.pingAt) >= usageEvery {
		m.pingAt = time.Now()
		cmds = append(cmds, m.pingCmd())
	}
	t := m.top()
	switch t.page {
	case homePage:
		if t.home.answered >= len(homeKinds) {
			var cmd tea.Cmd
			m, cmd = m.loadCounts()
			cmds = append(cmds, cmd)
		}
		if h := &m.top().home; h.usage.due(time.Now()) {
			cmds = append(cmds, m.loadUsage(&h.usage, h.b, projectUsage, usageOf{}))
		}
		if h := &m.top().home; h.dash.due(time.Now()) {
			cmds = append(cmds, m.loadDash(&h.dash, h.b))
		}
	case listPage:
		cmds = append(cmds, m.refreshList(&t.list))
	case itemPage:
		cmds = append(cmds, m.refreshItem(&t.item))
	}
	return m, tea.Batch(cmds...)
}

func (m model) togglePause() (model, tea.Cmd) {
	m.paused = !m.paused
	if m.paused {
		return m.setStatus("refresh paused", styles.ToneWarning)
	}
	m, tick := m.scheduleRefresh()
	m, status := m.setStatus("refresh resumed", styles.ToneSuccess)
	return m, tea.Batch(tick, status)
}

func (m model) Update(msg tea.Msg) (tea.Model, tea.Cmd) {
	m, cmd := m.own().update(msg)
	m, spin := m.keepSpinning()
	return m, tea.Batch(cmd, spin)
}

func (m model) update(msg tea.Msg) (model, tea.Cmd) {
	switch msg := msg.(type) {
	case tea.WindowSizeMsg:
		m.width, m.height = msg.Width, msg.Height
		setLayout(msg.Width, msg.Height)
		return m.resize(), nil
	case tea.BackgroundColorMsg:
		if !styles.Paint {
			styles.Use(msg.IsDark())
		}
		return m.restyleInputs(), nil
	case tea.ColorProfileMsg:
		styles.UseProfile(msg.Profile)
		return m.restyleInputs(), nil
	case spinner.TickMsg:
		if !m.busy() {
			m.spinning = false
			return m, nil
		}
		var cmd tea.Cmd
		m.spinner, cmd = m.spinner.Update(msg)
		return m, cmd
	case tea.KeyPressMsg:
		return m.keyPress(msg)
	case countLoadedMsg:
		return m.applyCount(msg)
	case listLoadedMsg:
		return m.applyList(msg)
	case docLoadedMsg:
		return m.applyDoc(msg)
	case usageLoadedMsg:
		return m.applyUsage(msg)
	case listUsageMsg:
		return m.applyListUsage(msg)
	case dashLoadedMsg:
		return m.applyDash(msg)
	case pingMsg:
		if msg.err == nil {
			m.info.RTT = msg.rtt
		}
	case deletedMsg:
		return m.applyDeleted(msg)
	case executedMsg:
		return m.applyExecuted(msg)
	case downloadedMsg:
		return m.applyDownloaded(msg)
	case editorDoneMsg:
		return m.applyEditorDone(msg)
	case savedMsg:
		return m.applySaved(msg)
	case statusClearMsg:
		if msg.gen == m.statusGen {
			m.status = ""
		}
	case refreshTickMsg:
		if msg.gen != m.refreshGen || m.paused {
			return m, nil
		}
		m, tick := m.scheduleRefresh()
		m, load := m.refresh()
		return m, tea.Batch(tick, load)
	default:
		return m.toInput(msg)
	}
	return m, nil
}

// toInput hands a message the model does not know, as the blink of a cursor or a paste, to the
// text input that has focus.
func (m model) toInput(msg tea.Msg) (model, tea.Cmd) {
	switch {
	case m.tooSmall():
	case m.confirm.active:
		return m.deleteUpdate(msg)
	case m.exec.active:
		return m.executeUpdate(msg)
	default:
		if l := m.pageList(); l != nil && l.filter.active {
			changed, cmd := l.filter.update(msg, m.keys)
			if changed {
				l.relay()
			}
			return m, cmd
		}
	}
	return m, nil
}

func (m model) keyPress(msg tea.KeyPressMsg) (model, tea.Cmd) {
	if key.Matches(msg, m.keys.ForceQuit) {
		return m, tea.Quit
	}
	if m.tooSmall() {
		return m, nil
	}
	if m.error != "" {
		m.error = ""
		return m, nil
	}
	if m.showHelp {
		m.showHelp = false
		return m, nil
	}
	switch {
	case m.confirm.active:
		return m.deleteUpdate(msg)
	case m.exec.active:
		return m.executeUpdate(msg)
	}
	if !m.typing() {
		switch {
		case key.Matches(msg, m.keys.Help):
			m.showHelp = true
			return m, nil
		case key.Matches(msg, m.keys.Pause):
			return m.togglePause()
		}
	}
	switch t := m.top(); t.page {
	case homePage:
		return m.homeUpdate(msg)
	case listPage:
		if m, cmd, ok := m.listUpdate(&t.list, msg); ok {
			return m, cmd
		}
		switch {
		case key.Matches(msg, m.keys.Quit) && len(m.stack) == 1:
			return m, tea.Quit
		case key.Matches(msg, m.keys.Back, m.keys.Quit):
			m = m.pop()
		}
		return m, nil
	case itemPage:
		return m.itemUpdate(msg)
	}
	return m, nil
}

func (m model) View() tea.View {
	content := m.render()
	if styles.Paint {
		content = styles.Ground(content, m.width, m.height)
	}
	v := tea.NewView(content)
	v.AltScreen = true
	in := m.headerInfo()
	v.WindowTitle = binary + " · " + cmpOr(in.Name, in.Project, "console")
	v.ForegroundColor = styles.P.Text
	if styles.Paint {
		v.BackgroundColor = styles.P.Base
	}
	return v
}

func (m model) render() string {
	if m.tooSmall() {
		notice := lipgloss.JoinVertical(lipgloss.Center,
			styles.Render(styles.B("terminal too small", styles.ToneWarning)),
			styles.Muted(fmt.Sprintf("%d×%d, needs %d×%d", m.width, m.height, minContentWidth+chromeWidth, minContentHeight+chromeHeight)),
			styles.Faint("ctrl+c to quit"),
		)
		return place(m.width, m.height, notice)
	}
	meta, foot := m.frameMeta()
	view := m.header() + "\n\n" + frame(m.breadcrumb(titleRoom(meta)), meta, foot, m.content()) + "\n" + m.footer()
	if m.width == 0 {
		return view
	}
	return place(m.width, m.height, view)
}

// content is what the frame holds: the page, with a dialog or an error over it, or the help on
// its own, as its card is nearly as wide as the frame and slivers of the page beside it read as
// stray marks.
func (m model) content() string {
	page := m.pageView()
	switch {
	case m.showHelp:
		return overlay("", m.helpView())
	case m.error != "":
		return overlay(page, dialog("error", styles.ToneDanger, styles.Body(styles.CleanBlock(m.error)), "", styles.Faint("press any key to close")))
	case m.confirm.active:
		return overlay(page, m.deleteView())
	case m.exec.active:
		return overlay(page, m.executeView())
	}
	return page
}

func (m model) pageView() string {
	switch m.top().page {
	case homePage:
		return m.homeView()
	case listPage:
		return m.listPageView()
	case itemPage:
		return m.itemView()
	}
	return ""
}

// pageList is the list on screen: the list page's, or the open tab's when it lists.
func (m model) pageList() *listState {
	t := m.top()
	switch t.page {
	case listPage:
		return &t.list
	case itemPage:
		if t.item.tabs[t.item.tab].list {
			return &t.item.lists[t.item.tab]
		}
	}
	return nil
}

// pageUsage is the usage on screen: the overview's, or the open tab's when it is the usage.
func (m model) pageUsage() *usageState {
	t := m.top()
	switch {
	case t.page == homePage:
		return &t.home.usage
	case t.page == itemPage && t.item.tabs[t.item.tab].usage:
		return &t.item.usage
	}
	return nil
}

// pageFilter is the filter of the page on screen, if it has one.
func (m model) pageFilter() *filterBox {
	if l := m.pageList(); l != nil {
		return &l.filter
	}
	return nil
}

// fact is a label and its value in the header.
type fact struct {
	label string
	value cell
	// middle cuts the value in the middle where it does not fit, as an endpoint, whose end names
	// the instance.
	middle bool
	// badge is drawn after the value as a badge, as cloud after the endpoint.
	badge     string
	badgeTone styles.Tone
}

// width is how wide the fact draws: its label, its value and its badge.
func (f fact) width() int {
	w := headerLabelWidth + cellWidth(f.value)
	if f.badge != "" {
		w += 1 + ansi.StringWidth(f.badge) + 2
	}
	return w
}

// render draws the fact in width, its value cut to what its label and badge leave.
func (f fact) render(width int) string {
	badge := ""
	room := width - headerLabelWidth
	if f.badge != "" {
		badge = " " + styles.Badge(f.badge, f.badgeTone)
		room -= ansi.StringWidth(badge)
	}
	value := strings.TrimRight(renderCell(f.value, column{width: max(1, room), middle: f.middle}, nil), " ")
	return styles.Faint(fmt.Sprintf("%-*s", headerLabelWidth, f.label)) + value + badge
}

// header shows where the interface works: the instance, cloud or self-hosted, its version and the
// round trip to it, and the project, its name and its id. Its three columns sit at the left, in
// the middle and at the right, where the frame's top border ends; where they do not fit, each
// takes a share of the width.
func (m model) header() string {
	in := m.headerInfo()
	in.RTT = m.info.RTT
	kind, tone := "self-hosted", styles.ToneAccent
	if in.Cloud {
		kind, tone = "cloud", styles.ToneInfo
	}
	name := text(cmpOr(in.Name, in.Project, "none open"), styles.ToneText)
	name[0].Bold = in.Project != ""
	if in.Project == "" {
		name[0].Tone = styles.ToneFaint
	}
	columns := [][2]fact{
		{{label: spec(appwrite.Projects).one, value: name}, {label: "id", value: text(orDash(in.Project), styles.ToneMuted)}},
		{{label: "endpoint", value: text(in.Endpoint, styles.ToneBody), middle: true, badge: kind, badgeTone: tone}, {label: "version", value: text(orDash(in.Version), styles.ToneBody)}},
		{{label: "rtt", value: text(formatRTT(in.RTT), styles.ToneBody)}, {label: "as", value: text(orDash(in.As), styles.ToneMuted)}},
	}
	want := make([]int, len(columns))
	for i, c := range columns {
		for _, f := range c {
			want[i] = max(want[i], f.width())
		}
	}
	line := frameWidth - 4
	widths := shareWidths(want, line-2*(len(columns)-1))
	for i := range widths {
		widths[i] = min(widths[i], want[i])
	}
	starts := []int{0, (line - widths[1]) / 2, line - widths[2]}
	if starts[1] < widths[0]+2 || starts[2] < starts[1]+widths[1]+2 {
		starts = []int{0, widths[0] + 2, widths[0] + widths[1] + 4}
	}
	rows := make([]string, 2)
	for r := range rows {
		var b strings.Builder
		at := 0
		for i, c := range columns {
			b.WriteString(strings.Repeat(" ", max(0, starts[i]-at)))
			drawn := c[r].render(widths[i])
			b.WriteString(drawn)
			at = max(at, starts[i]) + ansi.StringWidth(drawn)
		}
		rows[r] = b.String()
	}
	return indent(strings.Join(rows, "\n"))
}

// headerLabelWidth is the room of a label in the header, the value beside it.
const headerLabelWidth = 9

// cellWidth is how wide a cell's text is.
func cellWidth(c cell) int {
	w := 0
	for _, s := range c {
		w += ansi.StringWidth(s.Text)
	}
	return w
}

// shareWidths shares width among columns that want so much each. When every column fits an
// equal share, each gets one, so the columns line up as they always do. Otherwise a column that
// wants less than its share keeps what it wants, and what it leaves goes to the others, so a
// long endpoint is not cut while the round trip beside it leaves room. What is left once every
// column has what it wants is shared out evenly.
func shareWidths(want []int, width int) []int {
	n := len(want)
	out := make([]int, n)
	if n == 0 {
		return out
	}
	if slices.Max(want) <= width/n {
		for i := range out {
			out[i] = width / n
		}
		return out
	}
	open := make([]bool, n)
	for i := range open {
		open[i] = true
	}
	left, count := width, n
	for settled := true; settled && count > 0; {
		settled = false
		for i, w := range want {
			if open[i] && w <= left/count {
				out[i], open[i] = w, false
				left -= w
				count--
				settled = true
			}
		}
	}
	for i := range out {
		switch {
		case count == 0:
			out[i] += left / n
		case open[i]:
			out[i] = left / count
		}
	}
	return out
}

// crumbOverview is the first crumb, the project's overview.
const crumbOverview = "overview"

// crumbSep goes between the crumbs, and crumbWidth is the widest a crumb before the last gets.
const (
	crumbSep   = " › "
	crumbWidth = 32
)

// trail names where the page on screen sits: the overview, then each page of the stack, an item's
// open tab last.
func (m model) trail() []string {
	trail := make([]string, 0, len(m.stack)+1)
	for i, s := range m.stack {
		switch s.page {
		case homePage:
			if m.info.Console && m.info.Project == "" {
				trail = append(trail, crumb(spec(appwrite.Projects).one, s.home.info.Name))
			} else {
				trail = append(trail, crumbOverview)
			}
		case listPage:
			trail = append(trail, s.list.spec.many)
		case itemPage:
			trail = append(trail, crumb(s.item.spec.one, s.item.spec.title(s.item.doc)))
			if i == len(m.stack)-1 {
				trail = append(trail, s.item.tabs[s.item.tab].title)
			}
		}
	}
	return trail
}

// crumbLabel separates the kind of a step of the trail from its name, which the breadcrumb draws
// faint before it: database main, table orders.
const crumbLabel = "\x00"

// crumb is a step of the trail of a kind, by its name.
func crumb(kind, name string) string {
	return kind + crumbLabel + name
}

// breadcrumb names where the page sits in width columns: overview › databases › database main ›
// tables, the page on screen in bold.
func (m model) breadcrumb(width int) string {
	trail := m.trail()
	kinds := make(map[int]string, len(trail))
	for i := range trail {
		if kind, name, ok := strings.Cut(trail[i], crumbLabel); ok {
			kinds[i], trail[i] = styles.Clean(kind), styles.Clean(name)
		} else {
			trail[i] = styles.Clean(trail[i])
		}
		if i < len(trail)-1 {
			trail[i] = truncate(trail[i], crumbWidth)
		}
		if kinds[i] != "" {
			trail[i] = kinds[i] + " " + trail[i]
		}
	}
	crumbs := foldCrumbs(trail, width)
	if cut(crumbs, trail) {
		for i := range trail {
			if kind := kinds[i]; kind != "" {
				trail[i] = strings.TrimPrefix(trail[i], kind+" ")
			}
		}
		kinds = nil
		crumbs = foldCrumbs(trail, width)
	}
	last := len(crumbs) - 1
	var spans []styles.Span
	for i, c := range crumbs {
		if i > 0 {
			spans = append(spans, styles.S(crumbSep, styles.ToneFaint))
		}
		tone, bold := styles.ToneMuted, false
		switch {
		case i == last:
			tone, bold = styles.ToneText, true
		case c == "…" && i == 1:
			spans = append(spans, styles.S(c, styles.ToneFaint))
			continue
		}
		at := i
		if len(crumbs) != len(trail) && i > 0 {
			at = i + len(trail) - len(crumbs)
		}
		if kind := kinds[at]; kind != "" && strings.HasPrefix(c, kind+" ") {
			spans = append(spans, styles.S(kind+" ", styles.ToneFaint))
			c = strings.TrimPrefix(c, kind+" ")
		}
		spans = append(spans, styles.Span{Text: c, Tone: tone, Bold: bold})
	}
	return styles.Render(spans...)
}

// cut reports whether folding cut a crumb of the trail rather than only leaving some out.
func cut(crumbs, trail []string) bool {
	for _, c := range crumbs {
		if c != "…" && !slices.Contains(trail, c) {
			return true
		}
	}
	return false
}

// foldCrumbs fits a trail to width by giving way in the middle: the crumbs after the first fold
// into an ellipsis from the left until the two last are left, then the one before the last is
// cut, and the last, the page on screen, only once nothing else gives way.
func foldCrumbs(trail []string, width int) []string {
	fits := func(crumbs []string) bool { return ansi.StringWidth(strings.Join(crumbs, crumbSep)) <= width }
	crumbs := slices.Clone(trail)
	for n := 1; !fits(crumbs) && n < len(trail)-2; n++ {
		crumbs = append([]string{trail[0], "…"}, trail[1+n:]...)
	}
	cut := func(i int) {
		others := ansi.StringWidth(strings.Join(crumbs, crumbSep)) - ansi.StringWidth(crumbs[i])
		crumbs[i] = truncate(crumbs[i], max(1, width-others))
	}
	if !fits(crumbs) && len(crumbs) >= 3 {
		cut(len(crumbs) - 2)
	}
	if !fits(crumbs) {
		cut(len(crumbs) - 1)
	}
	return crumbs
}

// titleRoom is the room the frame's top edge leaves its title beside meta.
func titleRoom(meta string) int {
	if meta == "" {
		return frameWidth - 7
	}
	return frameWidth - 9 - min(ansi.StringWidth(meta), frameWidth/3)
}

// position is where the page on screen is, for the frame's foot: the row under the cursor of a
// table, as 12 of 240, or the lines of a tab in sight, as lines 12–40 of 230.
func (m model) position() string {
	t := m.top()
	switch t.page {
	case homePage:
		return t.home.table.position()
	case listPage:
		return t.list.position()
	case itemPage:
		it := t.item
		if it.tabs[it.tab].list {
			return it.lists[it.tab].position()
		}
		return scrollCue(it.scroll[it.tab], it.linesHeight(), len(m.tabLines(it, it.tab)))
	}
	return ""
}

// readAt is when the page on screen was last read, and whether the read after it failed.
func (m model) readAt() (time.Time, bool) {
	t := m.top()
	switch t.page {
	case homePage:
		return t.home.readAt, t.home.stale
	case listPage:
		return t.list.readAt, t.list.stale
	case itemPage:
		if l := m.pageList(); l != nil {
			return l.readAt, l.stale || t.item.stale
		}
		return t.item.readAt, t.item.stale
	}
	return time.Time{}, false
}

// frameMeta is what is set into the frame's edges: the filter on top, while it is typed or
// holds text, and at the bottom where the page is, when it was read, and whether refreshing is
// paused.
func (m model) frameMeta() (string, string) {
	var meta string
	if f := m.pageFilter(); f != nil && (f.active || f.text != "") {
		name := f.name()
		if l := m.pageList(); l != nil && l.queries != nil {
			name = "query"
		}
		meta = styles.Render(styles.S(name, styles.ToneFaint))
		if f.text != "" {
			meta = styles.Render(styles.S(name+" ", styles.ToneFaint), styles.S(f.text, styles.ToneText))
		}
	}
	var foot []footPart
	if p := m.position(); p != "" {
		foot = append(foot, footPart{full: styles.Faint(p), short: styles.Faint(strings.TrimPrefix(p, "lines "))})
	}
	switch at, stale := m.readAt(); {
	case at.IsZero():
	case stale:
		foot = append(foot, footPart{
			full:  styles.Render(styles.S("as of "+at.Local().Format(time.TimeOnly)+", could not read again", styles.ToneWarning)),
			short: styles.Render(styles.S("as of "+at.Local().Format(time.TimeOnly), styles.ToneWarning)),
		})
	default:
		foot = append(foot, footPart{full: styles.Faint("read at " + at.Local().Format(time.TimeOnly))})
	}
	if m.paused {
		foot = append(foot, footPart{full: styles.Render(styles.S("⏸ paused", styles.ToneWarning))})
	}
	return meta, fitFoot(foot, frameWidth-6)
}

// footPart is a part of the frame's foot, with a shorter form for when the foot does not fit.
type footPart struct {
	full, short string
}

// fitFoot joins the parts of the foot in width: while they do not fit, they take their shorter
// form from the left, so the position gives way before a stale read does, and the pause is never
// cut short.
func fitFoot(parts []footPart, width int) string {
	join := func() string {
		texts := make([]string, len(parts))
		for i, p := range parts {
			texts[i] = p.full
		}
		return strings.Join(texts, styles.Faint(" · "))
	}
	for i := range parts {
		if ansi.StringWidth(join()) <= width {
			break
		}
		if parts[i].short != "" {
			parts[i].full = parts[i].short
		}
	}
	return join()
}

func (m model) footer() string {
	var status string
	if m.status != "" {
		mark, tone := "✓ ", styles.ToneSuccess
		text, budget := m.status, frameWidth*2/3
		switch m.statusTone {
		case styles.ToneDanger:
			mark, tone = "✗ ", styles.ToneDanger
			text = cutError(text, budget-ansi.StringWidth(mark))
		case styles.ToneWarning:
			mark, tone = "▲ ", styles.ToneWarning
		case styles.ToneText:
			mark, tone = "● ", styles.ToneMuted
		}
		status = styles.Render(styles.S(mark, tone), styles.S(text, tone))
		status = ansi.Truncate(status, budget, "…")
	}
	width := frameWidth - 3 - ansi.StringWidth(status)
	left := fitHints(width, m.shortHelp()...)
	if f := m.pageFilter(); f != nil && f.active {
		left = f.view(width, m.keys)
	}
	return " " + spread(ansi.Truncate(left, width, "…"), status+" ", frameWidth-1)
}

// cutError fits an error in width by cutting out its middle: what failed, up to the first colon,
// stays, and so does the end, where an error from appwrite says why, as the scope a key lacks.
// When the head would leave the end too little room, the end alone stays.
func cutError(s string, width int) string {
	if ansi.StringWidth(s) <= width || width < 8 {
		return s
	}
	head, _, ok := strings.Cut(s, ": ")
	if head += ": "; !ok || width-ansi.StringWidth(head)-1 < minErrorTail {
		head = ""
	}
	room := width - ansi.StringWidth(head) - 1
	runes := []rune(s)
	start := len(runes)
	for start > 0 && ansi.StringWidth(string(runes[start-1:])) <= room {
		start--
	}
	return head + "…" + string(runes[start:])
}

// minErrorTail is the least of the end of an error cutError keeps beside its head.
const minErrorTail = 24

// fitHints renders the hints that fit width. While they do not, the one before the last three,
// pause, help and back or quit, goes, so the page's own keys give way from the right.
func fitHints(width int, pairs ...[2]string) string {
	pairs = slices.Clone(pairs)
	for len(pairs) > 3 && ansi.StringWidth(hints(pairs...)) > width {
		pairs = slices.Delete(pairs, len(pairs)-4, len(pairs)-3)
	}
	return hints(pairs...)
}

func hints(pairs ...[2]string) string {
	parts := make([]string, len(pairs))
	for i, p := range pairs {
		parts[i] = styles.Key(p[0], p[1])
	}
	return strings.Join(parts, styles.Faint("  ·  "))
}

// hint is a binding as the footer shows it, with what it does here when that is not its own help.
func hint(b key.Binding, desc ...string) [2]string {
	h := b.Help()
	if len(desc) > 0 {
		h.Desc = desc[0]
	}
	return [2]string{h.Key, h.Desc}
}

// upDown names the arrow keys that move up and down.
const upDown = "↑/↓"

// shortHelp is the hints the footer shows for the page.
func (m model) shortHelp() [][2]string {
	k := m.keys
	switch {
	case m.error != "" || m.showHelp:
		return [][2]string{{"any key", "close"}}
	case m.confirm.active:
		return [][2]string{hint(k.View, "delete"), hint(k.Back, "cancel")}
	case m.exec.active:
		return [][2]string{hint(k.Submit), {"tab/↑/↓", "field"}, hint(k.Back, "cancel")}
	}
	tail := [][2]string{m.pauseHint(), hint(k.Help), hint(k.Quit)}
	switch t := m.top(); t.page {
	case homePage:
		return append([][2]string{hint(k.View), hint(k.Refresh)}, m.pauseHint(), hint(k.Help), hint(k.Quit, "quit"))
	case listPage:
		return append(m.listHints(t.list), tail...)
	case itemPage:
		return append(m.itemHints(), tail...)
	}
	return tail
}

// pauseHint is p as it works now: pause the refresh, or resume it.
func (m model) pauseHint() [2]string {
	if m.paused {
		return hint(m.keys.Pause, "resume")
	}
	return hint(m.keys.Pause)
}

// helpSection is a column of the key reference: a title, and each key with what it does.
type helpSection struct {
	title string
	rows  [][2]string
}

// helpSections are the columns of the key reference, the keys named as the key map names them.
func helpSections(k keys.KeyMap) []helpSection {
	name := func(b key.Binding) string { return b.Help().Key }
	return []helpSection{
		{"everywhere", [][2]string{
			{name(k.Up) + " " + name(k.Down), "up / down"}, {name(k.Top) + " " + name(k.Bottom), "first / last"},
			{name(k.PageUp) + " " + name(k.PageDown), "page up / down"}, {name(k.View), "open"},
			{name(k.Quit), "back, quit on the overview"}, {name(k.Back), "back, clear, cancel"},
			{name(k.Refresh) + " " + name(k.Pause), "refresh / pause"}, {name(k.ForceQuit), "quit"},
			{name(k.Help), "this help"},
		}},
		{"lists", [][2]string{
			{name(k.Filter), "filter, enter searches"}, {"/total>10", "query rows, sort:-total"}, {name(k.More), "read the next page"},
			{name(k.Copy) + " " + name(k.CopyJSON), "copy the id / json"}, {name(k.Delete), "delete a row, file or session"},
			{name(k.Edit) + " " + name(k.Create), "edit / new row in $EDITOR"},
			{name(k.Download), "download a file"}, {name(k.Execute), "execute a function"},
		}},
		{"pages", [][2]string{
			{name(k.NextTab) + " " + name(k.PrevTab), "next / previous tab"}, {name(k.Left) + " " + name(k.Right), "switch tab"},
			{name(k.Tab), "open a tab"}, {name(k.Submit), "run an execution"},
		}},
	}
}

// helpView draws the key reference in a card: its sections side by side where they fit, the
// last two below each other beside the first where they do not, and all below each other else.
func (m model) helpView() string {
	sections := helpSections(m.keys)
	columns := make([]string, 0, len(sections))
	for _, s := range sections {
		width := 0
		for _, r := range s.rows {
			width = max(width, ansi.StringWidth(r[0]))
		}
		keyStyle := styles.Fg(styles.ToneText).Bold(true).Width(width + 2)
		lines := []string{styles.Fg(styles.ToneAccent).Bold(true).Render(s.title), ""}
		for _, r := range s.rows {
			lines = append(lines, keyStyle.Render(r[0])+styles.Muted(r[1]))
		}
		columns = append(columns, lipgloss.JoinVertical(lipgloss.Left, lines...))
	}
	beside := func(parts ...string) string {
		gap := lipgloss.NewStyle().MarginRight(4)
		for i := range parts[:len(parts)-1] {
			parts[i] = gap.Render(parts[i])
		}
		return lipgloss.JoinHorizontal(lipgloss.Top, parts...)
	}
	below := func(parts ...string) string { return strings.Join(parts, "\n\n") }
	room, height := contentWidth-10, contentHeight-6
	body := ""
	for _, layout := range []string{
		beside(slices.Clone(columns)...),
		beside(columns[0], below(columns[1:]...)),
		below(columns...),
	} {
		body = layout
		if lipgloss.Width(layout) <= room && lipgloss.Height(layout) <= height {
			break
		}
	}
	body = lipgloss.JoinVertical(lipgloss.Center, body, "", styles.Faint("press any key to close"))
	return lipgloss.NewStyle().Border(lipgloss.RoundedBorder()).BorderForeground(styles.P.RuleBold).Padding(1, 3).
		Render(clip(body, room, contentHeight-4))
}

// restyleInputs gives every text input the palette in use now, once the terminal has said what
// its background and colours are. Styling an input resets its cursor's blink, so it is done
// when the palette changes and not on every frame.
func (m model) restyleInputs() model {
	in := styles.TextInput()
	for i := range m.stack {
		s := &m.stack[i]
		s.list.filter.input.SetStyles(in)
		for j := range s.item.lists {
			s.item.lists[j].filter.input.SetStyles(in)
		}
	}
	m.confirm.confirm.input.SetStyles(in)
	for i := range m.exec.inputs {
		m.exec.inputs[i].SetStyles(in)
	}
	return m
}
{% endverbatim %}
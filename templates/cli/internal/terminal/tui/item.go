package tui

import (
	"fmt"
	"slices"
	"strings"
	"time"

	"charm.land/bubbles/v2/key"
	tea "charm.land/bubbletea/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// itemState is the page of one resource: its fields, and tabs below them that list what is
// under it or show what it holds, the last its json.
type itemState struct {
	// b is the backend of the project the resource is of.
	b backend
	// ref is the list the resource is of.
	ref  appwrite.Ref
	doc  appwrite.Doc
	spec kindSpec
	tabs []tabSpec
	tab  int
	// lists are the lists of the tabs that list, in the places of their tabs.
	lists []listState
	// scroll is how far each tab that shows lines is scrolled.
	scroll []int
	// drawn are the lines of each tab that shows the resource, drawn when it is read and when the
	// terminal is resized rather than every frame, as a response can be megabytes of json.
	drawn [][]string
	// usage is the resource's usage, for a kind that has a usage tab.
	usage usageState
	// gen numbers the read of the resource in flight, and answered the last that came back.
	gen      int
	answered int
	readAt   time.Time
	stale    bool
}

// jsonTab is the tab every page ends with, the resource as the API answers it, and usageTab the
// one before it on a page of a kind with usage.
var (
	jsonTab  = tabSpec{title: "json"}
	usageTab = tabSpec{title: "usage", usage: true}
)

// labelWidth is the room of a field's label, the value beside it, so the values of a page start
// in one column.
const labelWidth = 13

// factWidth is the narrowest a column of fields gets: a label and a value of 30 or more.
const factWidth = 46

// factColumns is how many columns of fields fit in width, up to three.
func factColumns(width int) int { return max(1, min(3, width/factWidth)) }

// factRows is how many lines the fields of a page take: as many columns of them as the page is
// wide enough for, and no more than a third of its height, so the tabs keep room.
func (it itemState) factRows() int {
	cols := factColumns(detailWidth() - 2)
	return min((len(it.spec.facts)+cols-1)/cols, max(2, contentHeight/3))
}

// tableHeight is the room of a tab's table: the content area less the fields, the gap below
// them, the tab row and its rule, and the table's header.
func (it itemState) tableHeight() int {
	return max(minTableHeight, contentHeight-it.factRows()-1-2-tableHeaderRows)
}

// linesHeight is the room of a tab that shows lines.
func (it itemState) linesHeight() int {
	return max(1, contentHeight-it.factRows()-1-2)
}

// openItem opens the page of a resource of ref, and reads each list of its tabs.
func (m model) openItem(ref appwrite.Ref, d appwrite.Doc) (model, tea.Cmd) {
	s := spec(ref.Kind)
	tabs := append([]tabSpec(nil), s.tabs...)
	if _, ok := usagePlaces[ref.Kind]; ok {
		tabs = append(tabs, usageTab)
	}
	it := itemState{b: m.top().b, ref: ref, doc: d, spec: s, tabs: append(tabs, jsonTab)}
	it.lists = make([]listState, len(it.tabs))
	it.scroll = make([]int, len(it.tabs))
	it.redraw()
	it.readAt = time.Now()
	var cmds []tea.Cmd
	for i, t := range it.tabs {
		if !t.list {
			continue
		}
		child := ref.Child(t.child, d.ID())
		it.lists[i] = newListState(child, listColumns(child, d), it.tableHeight())
		it.lists[i].b, it.lists[i].parent = it.b, d
		cmds = append(cmds, m.load(&it.lists[i]))
	}
	return m.push(screen{page: itemPage, item: it}), tea.Batch(cmds...)
}

// refreshItem reads the resource again, when its kind can be, and the list of the open tab.
func (m *model) refreshItem(it *itemState) tea.Cmd {
	var cmds []tea.Cmd
	if it.ref.Kind.Gettable() && it.gen == it.answered {
		it.gen = m.next()
		cmds = append(cmds, m.getCmd(it.b, it.gen, it.ref, it.ref.Kind.IDOf(it.doc)))
	}
	switch t := it.tabs[it.tab]; {
	case t.list:
		cmds = append(cmds, m.refreshList(&it.lists[it.tab]))
	case t.usage && it.usage.due(time.Now()):
		id := it.ref.Kind.IDOf(it.doc)
		cmds = append(cmds, m.loadUsage(&it.usage, it.b, usagePlaces[it.ref.Kind], usageOf{id: id, path: it.ref.ItemPath(id)}))
	}
	return tea.Batch(cmds...)
}

func (m model) applyDoc(msg docLoadedMsg) (model, tea.Cmd) {
	for i := range m.stack {
		it := &m.stack[i].item
		if m.stack[i].page != itemPage || it.gen != msg.gen {
			continue
		}
		it.answered = msg.gen
		if msg.err != nil {
			it.stale = true
			return m.setStatus("could not read the "+it.spec.one+" again: "+msg.err.Error(), styles.ToneDanger)
		}
		it.doc, it.readAt, it.stale = msg.doc, time.Now(), false
		it.redraw()
		for j, t := range it.tabs {
			if t.list && t.child == appwrite.Rows {
				it.lists[j].cols = rowColumns(msg.doc)
				it.lists[j].table.base = baseColumns(it.lists[j].cols)
				it.lists[j].relay()
			}
		}
	}
	return m, nil
}

// baseColumns are the columns of the specs.
func baseColumns(cols []colSpec) []column {
	out := make([]column, len(cols))
	for i, c := range cols {
		out[i] = c.column
	}
	return out
}

// tabLines are the lines a tab that does not list shows: its usage, or the resource as redraw
// drew it.
func (m model) tabLines(it itemState, i int) []string {
	if it.tabs[i].usage {
		lines := m.usageLines(it.usage, usagePlaces[it.ref.Kind], detailWidth()-2)
		id := it.ref.Kind.IDOf(it.doc)
		path := it.ref.ItemPath(id)
		room := it.linesHeight() - len(lines) - 2
		if it.usage.loaded && path != "" && room >= minTableHeight+tableHeaderRows {
			lines = append(lines, "", styles.SectionHeader("busiest routes below "+it.spec.one+" "+it.spec.title(it.doc), detailWidth()-2))
			lines = append(lines, routesTable(it.usage.routes, path, detailWidth()-2, room-1)...)
		}
		return lines
	}
	if i < len(it.drawn) {
		return it.drawn[i]
	}
	return nil
}

// redraw draws the lines of each tab that shows the resource: its json, or what its spec draws.
func (it *itemState) redraw() {
	it.drawn = make([][]string, len(it.tabs))
	for i, t := range it.tabs {
		switch {
		case t.list, t.usage:
		case t.lines != nil:
			it.drawn[i] = t.lines(it.doc)
		default:
			lines, ok := jsonLines(it.doc.Raw)
			if !ok {
				lines = []string{styles.Faint("the answer is not json")}
			}
			it.drawn[i] = lines
		}
	}
}

// switchTab opens the tab by steps from the open one, round from the last to the first.
func (it *itemState) switchTab(by int) {
	n := len(it.tabs)
	it.tab = ((it.tab+by)%n + n) % n
}

func (m model) itemUpdate(msg tea.KeyPressMsg) (model, tea.Cmd) {
	it := &m.top().item
	t := it.tabs[it.tab]
	if t.list {
		l := &it.lists[it.tab]
		if !l.filter.active {
			switch {
			case key.Matches(msg, m.keys.NextTab, m.keys.Right):
				it.switchTab(1)
				return m, m.refreshItem(it)
			case key.Matches(msg, m.keys.PrevTab, m.keys.Left):
				it.switchTab(-1)
				return m, m.refreshItem(it)
			case key.Matches(msg, m.keys.Tab):
				return m.openTab(msg)
			case key.Matches(msg, m.keys.Back) && l.filter.text != "":
			case key.Matches(msg, m.keys.Back, m.keys.Quit):
				m = m.pop()
				return m, nil
			}
		}
		if m, cmd, ok := m.listUpdate(l, msg); ok {
			return m, cmd
		}
		return m.itemKeys(msg)
	}
	lines := len(m.tabLines(*it, it.tab))
	height := it.linesHeight()
	switch {
	case key.Matches(msg, m.keys.NextTab, m.keys.Right):
		it.switchTab(1)
		return m, m.refreshItem(it)
	case key.Matches(msg, m.keys.PrevTab, m.keys.Left):
		it.switchTab(-1)
		return m, m.refreshItem(it)
	case key.Matches(msg, m.keys.Tab):
		return m.openTab(msg)
	case key.Matches(msg, m.keys.Back, m.keys.Quit):
		m = m.pop()
		return m, nil
	case key.Matches(msg, m.keys.Refresh):
		return m, m.refreshItem(it)
	}
	if s, ok := scrollBy(msg, m, it.scroll[it.tab], lines, height); ok {
		it.scroll[it.tab] = s
		return m, nil
	}
	return m.itemKeys(msg)
}

// itemKeys are the keys that act on the resource of the page itself.
func (m model) itemKeys(msg tea.KeyPressMsg) (model, tea.Cmd) {
	it := m.top().item
	switch {
	case key.Matches(msg, m.keys.Copy):
		return m.copy(it.doc.ID())
	case key.Matches(msg, m.keys.CopyJSON):
		return m.copy(string(it.doc.Raw))
	case key.Matches(msg, m.keys.Edit) && it.ref.Kind.Writable():
		return m.editRow(it.ref, it.doc)
	case key.Matches(msg, m.keys.Execute) && it.ref.Kind == appwrite.Functions:
		return m.askExecute(it.doc)
	case key.Matches(msg, m.keys.Download) && it.ref.Kind == appwrite.Files:
		return m.download(it.ref, it.doc)
	case key.Matches(msg, m.keys.Delete) && it.ref.Kind.Deletable():
		return m.askDelete(it.ref, it.doc)
	}
	return m, nil
}

// openTab opens a tab by its number.
func (m model) openTab(msg tea.KeyPressMsg) (model, tea.Cmd) {
	it := &m.top().item
	var n int
	if _, err := fmt.Sscanf(msg.String(), "%d", &n); err != nil || n < 1 || n > len(it.tabs) {
		return m, nil
	}
	it.tab = n - 1
	return m, m.refreshItem(it)
}

// scrollBy scrolls lines of a tab by the key, and reports whether the key was one that scrolls.
func scrollBy(msg tea.KeyPressMsg, m model, at, lines, height int) (int, bool) {
	k := m.keys
	switch {
	case key.Matches(msg, k.Up):
		at--
	case key.Matches(msg, k.Down):
		at++
	case key.Matches(msg, k.PageUp):
		at -= height
	case key.Matches(msg, k.PageDown):
		at += height
	case key.Matches(msg, k.Top):
		at = 0
	case key.Matches(msg, k.Bottom):
		at = lines
	default:
		return at, false
	}
	return max(0, min(at, lines-height)), true
}

func (m model) itemView() string {
	it := m.top().item
	var b strings.Builder
	b.WriteString(m.factsView(it))
	b.WriteString("\n\n")
	b.WriteString(indent(m.tabRow(it)))
	b.WriteString("\n")
	t := it.tabs[it.tab]
	if t.list {
		b.WriteString(indent(m.listView(it.lists[it.tab], it.tableHeight()+tableHeaderRows)))
		return b.String()
	}
	lines := m.tabLines(it, it.tab)
	height := it.linesHeight()
	if json := it.trailingJSON(len(lines), height); json != nil {
		lines = append(append(slices.Clip(lines), "", styles.SectionHeader("json", detailWidth()-2)), json...)
	}
	at := max(0, min(it.scroll[it.tab], len(lines)-height))
	end := min(len(lines), at+height)
	shown := make([]string, 0, end-at)
	for _, l := range lines[at:end] {
		shown = append(shown, "   "+ansi.Truncate(l, detailWidth()-2, "…"))
	}
	b.WriteString(strings.Join(shown, "\n"))
	return b.String()
}

// trailingJSON is the resource's json, as much of it as fits below a tab of n lines that leaves
// room in height, so a short tab, as a row's values, does not leave the page empty. The json tab
// and the usage, which have their own, take none.
func (it itemState) trailingJSON(n, height int) []string {
	t := it.tabs[it.tab]
	json := it.drawn[len(it.tabs)-1]
	room := height - n - 2
	if t.usage || it.tab == len(it.tabs)-1 || room < peekMin || len(json) == 0 {
		return nil
	}
	return json[:min(len(json), room)]
}

// factsView draws the fields of the page.
func (m model) factsView(it itemState) string {
	return strings.Join(factGrid(it.spec.facts, it.doc, detailWidth()-2, it.factRows()), "\n")
}

// factGrid draws fields of a resource in columns down then across, in width and rows lines, each
// label right aligned so the values of a column start in one place. Fields that do not fit are
// left out, the last first.
func factGrid(facts []factSpec, d appwrite.Doc, width, rows int) []string {
	if rows <= 0 || len(facts) == 0 {
		return nil
	}
	cols := factColumns(width)
	colWidth := width / cols
	lines := make([]string, min(rows, (len(facts)+cols-1)/cols))
	for i, f := range facts {
		r, c := i%len(lines), i/len(lines)
		if c >= cols {
			break
		}
		label := styles.Faint(fmt.Sprintf("%*s", labelWidth, truncate(styles.Clean(f.label), labelWidth))) + "  "
		value := renderCell(f.value(d), column{width: max(1, colWidth-labelWidth-4), middle: true}, nil)
		lines[r] += label + value + "  "
	}
	for i := range lines {
		lines[i] = " " + lines[i]
	}
	return lines
}

// tabRow draws the tabs, numbered, with the count of each list beside it, the open one in the
// accent above a rule that is bold under it.
func (m model) tabRow(it itemState) string {
	var names, rule strings.Builder
	for i, t := range it.tabs {
		label := fmt.Sprintf("%d %s", i+1, t.title)
		count := ""
		if t.list && it.lists[i].loaded {
			count = " " + formatInt(it.lists[i].total)
		}
		w := ansi.StringWidth(label + count)
		if i > 0 {
			names.WriteString("   ")
			rule.WriteString(styles.Fg(styles.ToneRule).Render("───"))
		}
		tone := styles.ToneMuted
		ruleTone := styles.Fg(styles.ToneRule)
		if i == it.tab {
			tone = styles.ToneAccent
			ruleTone = styles.Fg(styles.ToneAccent)
		}
		names.WriteString(styles.Render(styles.Span{Text: label, Tone: tone, Bold: i == it.tab}, styles.S(count, styles.ToneFaint)))
		rule.WriteString(ruleTone.Render(strings.Repeat("━", w)))
	}
	used := ansi.StringWidth(rule.String())
	rule.WriteString(styles.Fg(styles.ToneRule).Render(strings.Repeat("─", max(0, tableWidth()-used))))
	return ansi.Truncate(names.String(), tableWidth(), "…") + "\n" + ansi.Truncate(rule.String(), tableWidth(), "")
}

// itemHints are the keys of a page: the open tab's, then what the page itself does.
func (m model) itemHints() [][2]string {
	k := m.keys
	it := m.top().item
	var h [][2]string
	if t := it.tabs[it.tab]; t.list {
		h = m.listHints(it.lists[it.tab])
	} else {
		h = append(h, [2]string{upDown, "scroll"}, hint(k.Copy))
	}
	h = append(h, hint(k.NextTab))
	switch it.ref.Kind {
	case appwrite.Functions:
		h = append(h, hint(k.Execute))
	case appwrite.Files:
		h = append(h, hint(k.Download))
	}
	if it.ref.Kind.Writable() {
		h = append(h, hint(k.Edit, "edit this "+it.spec.one))
	}
	if it.ref.Kind.Deletable() {
		h = append(h, hint(k.Delete, "delete this "+it.spec.one))
	}
	return h
}

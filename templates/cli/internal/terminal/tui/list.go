package tui

import (
	"slices"
	"strings"
	"time"

	"charm.land/bubbles/v2/key"
	tea "charm.land/bubbletea/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// listState is a list of the resources of a ref: on a page of its own, or on a tab of the page
// of the resource it is under.
type listState struct {
	// b is the backend of the project the list is of.
	b    backend
	ref  appwrite.Ref
	spec kindSpec
	cols []colSpec
	docs []appwrite.Doc
	// total is how many the server holds, of which docs are the ones read so far.
	total int
	// shown are the indexes of the docs the filter lets through, in the rows of the table.
	shown  []int
	table  dataTable
	filter filterBox
	// search is what the server was last asked to search for, for a kind it searches.
	search string
	// queries are the filter as queries the server was asked for, for rows: the rows on screen
	// are the ones that meet them, so the filter does not hide any of them again.
	queries []string
	// usage is the usage of each resource, by its id, for a kind whose list draws it; usageGen
	// numbers its read in flight and usageAnswered the last that came back, at usageAt, failed
	// with usageErr.
	usage         map[string][]float64
	usageGen      int
	usageAnswered int
	usageAt       time.Time
	usageErr      bool
	// parent is the resource the list is under, as the table of rows, which a new row's template
	// is made from.
	parent appwrite.Doc
	// gen numbers the read in flight, and answered the last one that came back; a refresh does not
	// ask again while one is out.
	gen      int
	answered int
	// more says the read in flight follows the docs read so far.
	more   bool
	loaded bool
	// err is why the list could not be read, before it ever was.
	err    string
	readAt time.Time
	stale  bool
}

// own copies what the list changes in place, for model.own.
func (l listState) own() listState {
	l.docs = slices.Clip(l.docs)
	l.shown = slices.Clone(l.shown)
	l.table.rows = slices.Clip(l.table.rows)
	return l
}

func newListState(ref appwrite.Ref, cols []colSpec, height int) listState {
	s := spec(ref.Kind)
	base := make([]column, len(cols))
	for i, c := range cols {
		base[i] = c.column
	}
	t := newTable(base, "")
	t.setSize(tableWidth(), height)
	f := newFilterBox("filter by name or id…")
	if ref.Kind.Searchable() {
		f = newFilterBox("filter, enter searches the server…")
	}
	return listState{ref: ref, spec: s, cols: cols, table: t, filter: f}
}

// listColumns are the columns of a list of ref, the rows' from the table they are in, and the
// usage of each resource last for a kind that draws it.
func listColumns(ref appwrite.Ref, parent appwrite.Doc) []colSpec {
	if ref.Kind == appwrite.Rows {
		return rowColumns(parent)
	}
	cols := spec(ref.Kind).cols
	if r, ok := rowUsages[ref.Kind]; ok {
		cols = append(slices.Clone(cols), rowUsageColumn(r))
	}
	return cols
}

// matches reports whether the filter lets a resource through.
func (l listState) matches(d appwrite.Doc) bool {
	if l.filter.text == "" || l.queries != nil {
		return true
	}
	fields := []string{l.spec.title(d), d.ID()}
	for _, c := range l.cols {
		if c.value == nil {
			continue
		}
		for _, span := range c.value(d) {
			fields = append(fields, span.Text)
		}
	}
	return l.filter.matches(fields...)
}

// relay draws the rows again from the docs and the filter, keeping the cursor on the resource it
// was on.
func (l *listState) relay() {
	var at string
	if d, ok := l.selected(); ok {
		at = d.ID()
	}
	l.shown = nil
	rows := make([]tableRow, 0, len(l.docs))
	for i, d := range l.docs {
		if !l.matches(d) {
			continue
		}
		l.shown = append(l.shown, i)
		row := make(tableRow, len(l.cols))
		for j, c := range l.cols {
			if c.value == nil {
				series, ok := l.usage[d.ID()]
				row[j] = rowUsageCell(rowUsages[l.ref.Kind], series, l.usageErr, ok || l.usage != nil)
				continue
			}
			row[j] = c.value(d)
		}
		rows = append(rows, row)
	}
	l.table.setRows(rows)
	for i, di := range l.shown {
		if l.docs[di].ID() == at {
			l.table.setCursor(i)
		}
	}
}

// selected is the resource under the cursor.
func (l listState) selected() (appwrite.Doc, bool) {
	c := l.table.cursor
	if c < 0 || c >= len(l.shown) || l.shown[c] >= len(l.docs) {
		return appwrite.Doc{}, false
	}
	return l.docs[l.shown[c]], true
}

// hasMore reports whether the server holds more than was read.
func (l listState) hasMore() bool {
	return l.loaded && l.ref.Kind.Paged() && len(l.docs) < l.total
}

// waiting reports whether a read of the list is still out.
func (l listState) waiting() bool { return l.gen != l.answered }

// position is where the cursor is, as 12 of 240, and how many the server holds when more than
// were read.
func (l listState) position() string {
	p := l.table.position()
	if l.hasMore() && p != "" {
		p += ", " + formatInt(l.total) + " in all"
	}
	return p
}

// load reads the list from its start again.
func (m *model) load(l *listState) tea.Cmd {
	l.gen, l.more = m.next(), false
	return m.listCmd(l.gen, *l, false)
}

// loadMore reads the next page of the list.
func (m *model) loadMore(l *listState) tea.Cmd {
	if !l.hasMore() || l.waiting() {
		return nil
	}
	l.gen, l.more = m.next(), true
	return m.listCmd(l.gen, *l, true)
}

// findList is the list of the stack that waits for gen.
func (m *model) findList(gen int) *listState {
	for i := range m.stack {
		s := &m.stack[i]
		switch s.page {
		case listPage:
			if s.list.gen == gen {
				return &s.list
			}
		case itemPage:
			for j := range s.item.lists {
				if s.item.lists[j].gen == gen && s.item.tabs[j].list {
					return &s.item.lists[j]
				}
			}
		}
	}
	return nil
}

func (m model) applyList(msg listLoadedMsg) (model, tea.Cmd) {
	l := m.findList(msg.gen)
	if l == nil {
		return m, nil
	}
	l.answered = msg.gen
	if msg.err != nil {
		what := "could not list the " + l.spec.many
		if !l.loaded {
			l.err = styles.Clean(msg.err.Error())
			l.relay()
			return m, nil
		}
		l.stale = true
		return m.setStatus(what+": "+msg.err.Error(), styles.ToneDanger)
	}
	if msg.more {
		l.docs = append(l.docs, msg.page.Docs...)
	} else {
		l.docs = msg.page.Docs
	}
	l.total = max(msg.page.Total, len(l.docs))
	l.loaded, l.err, l.stale, l.readAt = true, "", false, time.Now()
	l.relay()
	return m, m.loadRowUsage(l)
}

// refreshList reads a list again, unless a read is still out or more than a page was read: a
// refresh reads the first page, which would drop the rest.
func (m *model) refreshList(l *listState) tea.Cmd {
	if l.waiting() || len(l.docs) > appwrite.PageSize {
		return nil
	}
	return m.load(l)
}

// openList opens the list of a ref on a page of its own.
func (m model) openList(ref appwrite.Ref) (model, tea.Cmd) {
	l := newListState(ref, listColumns(ref, appwrite.Doc{}), contentHeight-tableHeaderRows)
	l.b = m.top().b
	cmd := m.load(&l)
	return m.push(screen{page: listPage, list: l}), cmd
}

// listUpdate handles a key on a list, its own page's or a tab's, and reports whether it took it.
func (m model) listUpdate(l *listState, msg tea.KeyPressMsg) (model, tea.Cmd, bool) {
	if l.filter.active {
		m, cmd := m.filterUpdate(l, msg)
		return m, cmd, true
	}
	return m.listKeys(l, msg)
}

// filterUpdate takes a key while a list's filter is typed: enter applies it, as a query of rows
// when it holds a comparison, as a search on the server for a kind it searches, or as text the
// rows are filtered by; esc clears it, and lists again what a query or a search narrowed.
func (m model) filterUpdate(l *listState, msg tea.KeyPressMsg) (model, tea.Cmd) {
	apply := key.Matches(msg, m.keys.View)
	changed, cmd := l.filter.update(msg, m.keys)
	if changed {
		l.relay()
	}
	if apply && l.ref.Kind.Writable() && appwrite.IsFilter(l.filter.text) {
		queries, err := appwrite.ParseFilter(l.filter.text)
		if err != nil {
			l.filter.active = true
			m, status := m.setStatus(err.Error(), styles.ToneDanger)
			return m, tea.Batch(cmd, status, l.filter.input.Focus())
		}
		l.queries = queries
		l.relay()
		return m, tea.Batch(cmd, m.load(l))
	}
	if apply && l.queries != nil {
		l.queries = nil
		l.relay()
		return m, tea.Batch(cmd, m.load(l))
	}
	if apply && l.ref.Kind.Searchable() && l.filter.text != l.search {
		l.search = l.filter.text
		return m, tea.Batch(cmd, m.load(l))
	}
	if key.Matches(msg, m.keys.Back) && (l.search != "" || l.queries != nil) {
		l.search, l.queries = "", nil
		return m, tea.Batch(cmd, m.load(l))
	}
	return m, cmd
}

// listKeys takes a key on a list whose filter is not typed, and reports whether it took it.
func (m model) listKeys(l *listState, msg tea.KeyPressMsg) (model, tea.Cmd, bool) {
	if l.table.move(msg, m.keys) {
		if l.table.cursor >= len(l.shown)-1 && l.filter.text == "" {
			return m, m.loadMore(l), true
		}
		return m, nil, true
	}
	d, ok := l.selected()
	switch {
	case key.Matches(msg, m.keys.Back) && l.filter.text != "":
		l.filter.clear()
		l.relay()
		if l.search != "" || l.queries != nil {
			l.search, l.queries = "", nil
			return m, m.load(l), true
		}
		return m, nil, true
	case key.Matches(msg, m.keys.Filter):
		return m, l.filter.open(), true
	case key.Matches(msg, m.keys.Refresh):
		l.docs, l.loaded, l.err = nil, false, ""
		l.relay()
		return m, m.load(l), true
	case key.Matches(msg, m.keys.More):
		return m, m.loadMore(l), true
	case key.Matches(msg, m.keys.Create) && l.ref.Kind.Writable():
		m, cmd := m.newRow(l.ref, l.parent)
		return m, cmd, true
	case !ok:
		return m, nil, false
	case key.Matches(msg, m.keys.Edit) && l.ref.Kind.Writable():
		m, cmd := m.editRow(l.ref, d)
		return m, cmd, true
	case key.Matches(msg, m.keys.CopyJSON):
		m, cmd := m.copy(string(d.Raw))
		return m, cmd, true
	case key.Matches(msg, m.keys.View) && l.ref.Kind == appwrite.Projects:
		m, cmd := m.openProject(d)
		return m, cmd, true
	case key.Matches(msg, m.keys.View):
		m, cmd := m.openItem(l.ref, d)
		return m, cmd, true
	case key.Matches(msg, m.keys.Copy):
		m, cmd := m.copy(d.ID())
		return m, cmd, true
	case key.Matches(msg, m.keys.Delete) && l.ref.Kind.Deletable():
		m, cmd := m.askDelete(l.ref, d)
		return m, cmd, true
	case key.Matches(msg, m.keys.Download) && l.ref.Kind == appwrite.Files:
		m, cmd := m.download(l.ref, d)
		return m, cmd, true
	case key.Matches(msg, m.keys.Execute) && l.ref.Kind == appwrite.Functions:
		m, cmd := m.askExecute(d)
		return m, cmd, true
	}
	return m, nil, false
}

// emptyNote is what a list says without a row: that it is being read, why it could not be, that
// the filter hides every row, or that there is none.
func (m model) emptyNote(l listState) cell {
	switch {
	case l.err != "":
		return cell{styles.S("could not list the "+l.spec.many+": "+l.err, styles.ToneMuted), styles.S(" · "+m.keys.Refresh.Help().Key+" tries again", styles.ToneFaint)}
	case !l.loaded:
		return cell{styles.S(m.spinner.View()+" ", styles.ToneAccent), styles.S("reading the "+l.spec.many+"…", styles.ToneMuted)}
	case l.queries != nil:
		return text("no "+l.spec.one+" meets "+l.filter.text+": esc clears the query", styles.ToneMuted)
	case l.filter.text != "":
		return text(noMatch(l.spec.one, l.filter.text), styles.ToneMuted)
	}
	return text("no "+l.spec.many+" here yet", styles.ToneMuted)
}

// view draws the list, its table or, without rows, the note of why.
func (m model) listBody(l listState) string {
	if len(l.table.rows) > 0 {
		return l.table.View()
	}
	return withNote(l.table, m.emptyNote(l))
}

// withNote is a table without rows and, from where its second row would be, a note wrapped to
// its width, cut at the table's last row.
func withNote(t dataTable, note cell) string {
	t.empty = ""
	lines := strings.Split(t.View(), "\n")
	first := len(lines) - t.height + 1
	for i, l := range cellLines(note, t.width()-tableGutter) {
		if first+i < len(lines) {
			lines[first+i] = pad(tableGutter, nil) + l
		}
	}
	return strings.Join(lines, "\n")
}

func (m model) listPageView() string {
	return indent(m.listView(m.top().list, contentHeight))
}

// peekMin is the fewest lines a preview needs below its list to be drawn.
const peekMin = 5

// listView draws a list in height lines: its table, and, where its rows leave room below them, a
// preview of the resource under the cursor: its fields, or a row's own values, and its json.
func (m model) listView(l listState, height int) string {
	d, ok := l.selected()
	n := len(l.table.rows)
	room := height - tableHeaderRows - max(n, minTableHeight) - 1
	if !ok || n > l.table.height || room < peekMin {
		return m.listBody(l)
	}
	t := l.table
	t.height, t.offset = max(n, minTableHeight), 0
	lines := strings.Split(t.View(), "\n")
	width := tableWidth()
	lines = append(lines, "", styles.SectionHeader(l.spec.one+" "+l.spec.title(d), width))
	room -= 2
	facts := factGrid(peekFacts(l, d), d, width-2, room)
	lines = append(lines, facts...)
	room -= len(facts)
	if r, ok := rowUsages[l.ref.Kind]; ok && room >= 4 && l.usage != nil {
		spark := sparkLines([]metricSeries{rowSeries(r, l.usage[d.ID()])}, width-2)
		lines = append(lines, "")
		lines = append(lines, indentLines(spark, 1)...)
		room -= 1 + len(spark)
	}
	if json, ok := jsonLines(d.Raw); ok && room >= peekMin {
		lines = append(lines, "", styles.Faint(" json"))
		for _, j := range json[:min(len(json), room-2)] {
			lines = append(lines, "   "+ansi.Truncate(j, width-3, "…"))
		}
	}
	return strings.Join(lines, "\n")
}

// peekFacts are the fields a preview shows of a resource: a row's own values, as its table's
// columns, or the fields of its kind's page.
func peekFacts(l listState, d appwrite.Doc) []factSpec {
	if l.ref.Kind != appwrite.Rows {
		return l.spec.facts
	}
	var facts []factSpec
	for _, f := range d.Fields() {
		if !strings.HasPrefix(f, "$") {
			facts = append(facts, factSpec{label: f, value: func(d appwrite.Doc) cell { return rowCell(d.Value(f)) }})
		}
	}
	return facts
}

// listHints are the keys of a list: what opens a row and what can be done to it, by its kind.
func (m model) listHints(l listState) [][2]string {
	k := m.keys
	var h [][2]string
	if len(l.shown) > 0 {
		h = append(h, hint(k.View))
		switch l.ref.Kind {
		case appwrite.Functions:
			h = append(h, hint(k.Execute))
		case appwrite.Files:
			h = append(h, hint(k.Download))
		}
		if l.ref.Kind.Writable() {
			h = append(h, hint(k.Edit))
		}
		if l.ref.Kind.Deletable() {
			h = append(h, hint(k.Delete))
		}
	}
	if l.ref.Kind.Writable() {
		h = append(h, hint(k.Create))
	}
	search := "filter"
	switch {
	case l.ref.Kind.Writable():
		search = "filter or query"
	case l.ref.Kind.Searchable():
		search = "search"
	}
	h = append(h, hint(k.Filter, search))
	if l.hasMore() {
		h = append(h, hint(k.More, "read more"))
	}
	if len(l.shown) > 0 {
		h = append(h, hint(k.Copy))
	}
	return append(h, hint(k.Refresh))
}

package tui

import (
	"slices"
	"time"

	"charm.land/bubbles/v2/key"
	tea "charm.land/bubbletea/v2"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// homeState is the project's overview: each kind of resource it holds, and how many.
type homeState struct {
	// b is the backend of the project, and info what the header says of it.
	b      backend
	info   client.Info
	table  dataTable
	counts map[appwrite.Kind]homeCount
	usage  usageState
	dash   dashState
	// gen numbers the last read of the counts, and answered is how many came back for it; a
	// refresh does not ask again until all have.
	gen      int
	answered int
	readAt   time.Time
	stale    bool
}

// homeCount is how many resources of a kind the project holds, or why it could not be read.
type homeCount struct {
	n      int
	err    string
	loaded bool
}

// homeColumns are the columns of the overview's resources, the sparkline of their requests as
// wide as the table leaves it.
func homeColumns() []column {
	return []column{
		{title: "resource", width: 11},
		{title: "count", width: 6, right: true},
		{title: "requests, " + usageAgo, width: homeSpark() + 7, drop: 1},
	}
}

// homeSpark is how wide the sparkline of a kind's requests is: what the table leaves beside the
// resource, its count and the sum of its requests.
func homeSpark() int { return max(8, homeTableWidth()-tableGutter-11-6-2*tableGap-7) }

func (m *model) newHomeState(b backend, info client.Info) homeState {
	h := homeState{b: b, info: info, table: newTable(homeColumns(), ""), counts: map[appwrite.Kind]homeCount{}}
	h.gen = m.next()
	h.usage.gen = m.next()
	h.dash.gen = m.next()
	h.table.setSize(homeTableWidth(), len(homeKinds))
	h.table.setRows(h.rows())
	return h
}

// rows are the kinds and their counts, or what stops a count being read, as a key without the
// scope for it.
func (h homeState) rows() []tableRow {
	rows := make([]tableRow, 0, len(homeKinds))
	for _, k := range homeKinds {
		s := spec(k)
		c := h.counts[k]
		row := tableRow{text(s.many, styles.ToneText)}
		switch {
		case c.err != "":
			row = append(row, text("-", styles.ToneFaint), cell{styles.S("✗ ", styles.ToneDanger), styles.S(c.err, styles.ToneMuted)})
		case !c.loaded:
			row = append(row, text("…", styles.ToneFaint), text("", styles.ToneFaint))
		default:
			row = append(row, text(formatInt(c.n), styles.ToneBody), h.requestsCell(k))
		}
		rows = append(rows, row)
	}
	return rows
}

// requestsCell is the requests to the service of a kind over the last day, as a sparkline and
// their sum.
func (h homeState) requestsCell(k appwrite.Kind) cell {
	if !h.dash.loaded {
		return text("", styles.ToneFaint)
	}
	series := h.dash.services[kindServices[k]]
	if len(series) == 0 {
		series = make([]float64, usagePoints)
	}
	total := 0.0
	for _, v := range series {
		total += v
	}
	return append(sparkCell(series, homeSpark(), styles.ToneAccent), styles.S(" "+formatInt(int(total)), styles.ToneBody))
}

// loadCounts reads the count of every kind again, for the overview the page on screen is of.
func (m model) loadCounts() (model, tea.Cmd) {
	at := m.homeAt()
	if at < 0 {
		return m, nil
	}
	h := &m.stack[at].home
	h.gen, h.answered = m.next(), 0
	return m, m.countCmds(*h)
}

func (m model) countCmds(h homeState) tea.Cmd {
	cmds := make([]tea.Cmd, 0, len(homeKinds))
	for _, k := range homeKinds {
		cmds = append(cmds, m.countCmd(h.b, h.gen, k))
	}
	return tea.Batch(cmds...)
}

// openProject opens the overview of a project of an organisation, as the console login.
func (m model) openProject(d appwrite.Doc) (model, tea.Cmd) {
	info := m.info
	info.Project, info.Name = d.ID(), d.Str("name")
	b := m.top().b.ForProject(d.ID())
	h := m.newHomeState(b, info)
	return m.push(screen{page: homePage, b: b, home: h}), tea.Batch(m.countCmds(h), m.usageCmd(h.usage.gen, b, projectUsage, usageOf{}), m.dashCmd(h.dash.gen, b))
}

func (m model) applyCount(msg countLoadedMsg) (model, tea.Cmd) {
	at := slices.IndexFunc(m.stack, func(s screen) bool { return s.page == homePage && s.home.gen == msg.gen })
	if at < 0 {
		return m, nil
	}
	h := &m.stack[at].home
	c := homeCount{n: msg.count, loaded: true}
	if msg.err != nil {
		c = h.counts[msg.kind]
		c.err = styles.Clean(msg.err.Error())
	}
	h.counts[msg.kind] = c
	h.answered++
	if h.answered == len(homeKinds) {
		h.readAt, h.stale = time.Now(), false
	}
	h.table.setRows(h.rows())
	return m, nil
}

func (m model) homeUpdate(msg tea.KeyPressMsg) (model, tea.Cmd) {
	h := &m.top().home
	if h.table.move(msg, m.keys) {
		return m, nil
	}
	switch {
	case key.Matches(msg, m.keys.Quit) && len(m.stack) == 1:
		return m, tea.Quit
	case key.Matches(msg, m.keys.Back, m.keys.Quit):
		return m.pop(), nil
	case key.Matches(msg, m.keys.Refresh):
		m, counts := m.loadCounts()
		h := &m.top().home
		return m, tea.Batch(counts, m.loadUsage(&h.usage, h.b, projectUsage, usageOf{}), m.loadDash(&h.dash, h.b))
	case key.Matches(msg, m.keys.View):
		if c := h.table.cursor; c >= 0 && c < len(homeKinds) {
			return m.openList(appwrite.Ref{Kind: homeKinds[c]})
		}
	}
	return m, nil
}

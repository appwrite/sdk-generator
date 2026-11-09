package tui

import (
	"context"
	"maps"
	"slices"
	"time"

	"charm.land/bubbles/v2/spinner"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/keys"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
){% verbatim %}

// The layout. The content area fills the terminal inside the chrome: the header, the frame and
// the footer. Below the minimum a notice asks for a larger terminal.
const (
	tableHeaderRows  = 2
	minTableHeight   = 3
	minContentWidth  = 76
	minContentHeight = 14
	chromeWidth      = 2
	chromeHeight     = 8
)

// loadTimeout bounds every read, executeTimeout an execution, which waits for the function to
// return, and downloadTimeout a download.
const (
	loadTimeout     = 30 * time.Second
	executeTimeout  = 16 * time.Minute
	downloadTimeout = 30 * time.Minute
)

// refreshEvery is how often the page on screen is read again, unless refreshing is paused.
const refreshEvery = 5 * time.Second

// The size of the content area, which setLayout fits to the terminal.
var (
	contentWidth  = 120
	contentHeight = 30
	frameWidth    = contentWidth + chromeWidth
)

func setLayout(width, height int) {
	contentWidth = max(minContentWidth, width-chromeWidth)
	contentHeight = max(minContentHeight, height-chromeHeight)
	frameWidth = contentWidth + chromeWidth
}

type page int

const (
	homePage page = iota
	listPage
	itemPage
)

// screen is a page of the stack, with the state of its kind.
type screen struct {
	page page
	// b is the backend of the project the page is of.
	b    backend
	home homeState
	list listState
	item itemState
}

type model struct {
	ctx  context.Context
	b    backend
	keys keys.KeyMap
	info client.Info

	// stack is the pages opened, the overview first and the one on screen last.
	stack []screen
	// confirm and exec are the dialogues over the page on screen, while they are active.
	confirm deleteState
	exec    executeState

	width, height int
	showHelp      bool
	error         string
	status        string
	statusTone    styles.Tone
	statusGen     int
	spinner       spinner.Model
	spinning      bool
	paused        bool
	refreshGen    int
	// pingAt is when the round trip was last measured.
	pingAt time.Time
	// actions counts the deletes, executions and downloads in flight, which the spinner shows.
	actions int
	// gen numbers the loads; a list or a page keeps the one it waits for and drops the others.
	gen int
}

func newModel(ctx context.Context, b backend, info client.Info) model {
	m := model{
		ctx:     ctx,
		b:       b,
		keys:    keys.Keys,
		info:    info,
		spinner: spinner.New(spinner.WithSpinner(spinner.MiniDot)),
	}
	if info.Console && info.Project == "" {
		l := newListState(appwrite.Ref{Kind: appwrite.Organisations}, listColumns(appwrite.Ref{Kind: appwrite.Organisations}, appwrite.Doc{}), contentHeight-tableHeaderRows)
		l.b, l.gen = b, m.next()
		m.stack = []screen{{page: listPage, b: b, list: l}}
	} else {
		m.stack = []screen{{page: homePage, b: b, home: m.newHomeState(b, info)}}
	}
	m.refreshGen = 1
	return m
}

// own copies the pages, so what an update changes in them does not show through in the model it
// was made from: the pages are reached through pointers into the stack, which copies of a model
// would otherwise share.
func (m model) own() model {
	m.stack = slices.Clone(m.stack)
	for i := range m.stack {
		s := &m.stack[i]
		s.home.counts = maps.Clone(s.home.counts)
		s.list = s.list.own()
		s.item.lists = slices.Clone(s.item.lists)
		for j := range s.item.lists {
			s.item.lists[j] = s.item.lists[j].own()
		}
		s.item.scroll = slices.Clone(s.item.scroll)
	}
	m.exec.inputs = slices.Clone(m.exec.inputs)
	return m
}

// next numbers a new load.
func (m *model) next() int {
	m.gen++
	return m.gen
}

// withTimeout is the context of a load.
func (m model) withTimeout(d time.Duration) (context.Context, context.CancelFunc) {
	return context.WithTimeout(m.ctx, d)
}

// top is the page on screen.
func (m *model) top() *screen { return &m.stack[len(m.stack)-1] }

// push opens a page over the one on screen, of the same project unless it says otherwise.
func (m model) push(s screen) model {
	if s.b == nil {
		s.b = m.top().b
	}
	m.stack = append(slices.Clip(m.stack), s)
	return m
}

// info is what the header says of the project on screen: the overview's below the page, or the
// connection's on the console's organisations.
func (m model) headerInfo() client.Info {
	for _, s := range slices.Backward(m.stack) {
		if s.page == homePage {
			return s.home.info
		}
	}
	return m.info
}

// homeAt is the index of the overview the page on screen is of, or -1 on the console's pages.
func (m model) homeAt() int {
	for i, s := range slices.Backward(m.stack) {
		if s.page == homePage {
			return i
		}
	}
	return -1
}

// pop goes back to the page below the one on screen, when there is one.
func (m model) pop() model {
	if len(m.stack) > 1 {
		m.stack = m.stack[:len(m.stack)-1]
	}
	return m
}

// typing reports whether keys go into a text input, so letters are not shortcuts.
func (m model) typing() bool {
	if m.confirm.active || m.exec.active {
		return true
	}
	f := m.pageFilter()
	return f != nil && f.active
}

// tooSmall reports whether the terminal is smaller than the layout needs.
func (m model) tooSmall() bool {
	return m.width > 0 && (m.width < minContentWidth+chromeWidth || m.height < minContentHeight+chromeHeight)
}

// resize fits the tables of every page to the layout.
func (m model) resize() model {
	for i := range m.stack {
		s := &m.stack[i]
		switch s.page {
		case homePage:
			s.home.table.base = homeColumns()
			s.home.table.setSize(homeTableWidth(), len(homeKinds))
			s.home.table.setRows(s.home.rows())
		case listPage:
			s.list.table.setSize(tableWidth(), contentHeight-tableHeaderRows)
		case itemPage:
			for j := range s.item.lists {
				s.item.lists[j].table.setSize(tableWidth(), s.item.tableHeight())
			}
			s.item.redraw()
		}
	}
	return m
}
{% endverbatim %}
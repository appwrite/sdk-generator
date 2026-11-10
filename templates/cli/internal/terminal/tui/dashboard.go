package tui

import (
	"cmp"
	"context"
	"slices"
	"strings"
	"time"

	tea "charm.land/bubbletea/v2"
	"charm.land/lipgloss/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
)

// dashState is what the overview draws of the project's traffic besides its usage: the requests to
// each service over the last day, and the routes requested most.
type dashState struct {
	gen      int
	answered int
	loaded   bool
	err      string
	at       time.Time
	services map[string][]float64
	routes   []routeStat
}

// routeStat is a route and what it was asked for over the last day.
type routeStat struct {
	method, path       string
	requests, outbound float64
}

// dashLoadedMsg is a read of the overview's traffic.
type dashLoadedMsg struct {
	gen      int
	services map[string][]float64
	routes   []routeStat
	err      error
}

// The dimensions a route is broken down by.
const (
	dimPath   = "path"
	dimMethod = "method"
)

// quiet are the services left out of the routes: the health checks, which the round trip in the
// header reads, and the usage, which the pages read; both are the interface's own.
var quiet = []string{"health", "usage"}

// kindServices are the services a kind's requests are counted under.
var kindServices = map[appwrite.Kind]string{
	appwrite.Databases: "tablesdb", appwrite.Functions: "functions", appwrite.Sites: "sites",
	appwrite.Buckets: "storage", appwrite.Users: "users", appwrite.Teams: "teams",
	appwrite.Messages: "messaging", appwrite.Topics: "messaging", appwrite.Providers: "messaging",
}

// routeRows is how many routes the overview asks for, as many as a tall terminal shows.
const routeRows = 40

// due reports whether the traffic should be read again.
func (d dashState) due(now time.Time) bool {
	return d.gen == d.answered && (d.at.IsZero() || now.Sub(d.at) >= usageEvery)
}

// loadDash numbers a read of the overview's traffic and returns it.
func (m *model) loadDash(d *dashState, b backend) tea.Cmd {
	d.gen = m.next()
	return m.dashCmd(d.gen, b)
}

// dashCmd reads the requests of each service in the buckets of the window, and the routes
// requested most with what went out with them, the interface's own left out.
func (m model) dashCmd(gen int, b backend) tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	end := time.Now().Truncate(usageInterval).Add(usageInterval)
	start := end.Add(-usageWindow)
	return func() tea.Msg {
		defer cancel()
		fail := func(err error) tea.Msg { return dashLoadedMsg{gen: gen, err: err} }
		byService, err := b.Usage(ctx, appwrite.UsageQuery{Kind: appwrite.UsageEvents, Metrics: []string{"network.requests"},
			Interval: usageInterval, Start: start, End: end, Dimensions: []string{"service"}})
		if err != nil {
			return fail(err)
		}
		grouped := map[string][]appwrite.Point{}
		for _, p := range byService["network.requests"] {
			grouped[p.Dims["service"]] = append(grouped[p.Dims["service"]], p)
		}
		services := make(map[string][]float64, len(grouped))
		for s, ps := range grouped {
			services[s] = appwrite.Bucketed(ps, start, usageInterval, usagePoints, false)
		}
		routes, err := readRoutes(ctx, b, "", start, end)
		if err != nil {
			return fail(err)
		}
		return dashLoadedMsg{gen: gen, services: services, routes: routes}
	}
}

// readRoutes reads the routes requested most over the window, at path and below it or in the
// whole project, with what went out with them, the interface's own left out.
func readRoutes(ctx context.Context, b backend, path string, start, end time.Time) ([]routeStat, error) {
	read := func(metric string, top int) ([]appwrite.Point, error) {
		got, err := b.Usage(ctx, appwrite.UsageQuery{Kind: appwrite.UsageEvents, Metrics: []string{metric}, Start: start, End: end,
			Path: path, Dimensions: []string{dimPath, dimMethod}, Exclude: quiet, Top: top})
		return got[metric], err
	}
	top, err := read("network.requests", routeRows)
	if err != nil {
		return nil, err
	}
	out, err := read("network.outbound", routeRows*4)
	if err != nil {
		return nil, err
	}
	outbound := map[[2]string]float64{}
	for _, p := range out {
		outbound[[2]string{p.Dims[dimMethod], p.Dims[dimPath]}] += p.Value
	}
	requests := map[[2]string]float64{}
	for _, p := range top {
		requests[[2]string{p.Dims[dimMethod], p.Dims[dimPath]}] += p.Value
	}
	routes := make([]routeStat, 0, len(requests))
	for k, n := range requests {
		routes = append(routes, routeStat{method: k[0], path: k[1], requests: n, outbound: outbound[k]})
	}
	slices.SortStableFunc(routes, func(a, b routeStat) int {
		if c := cmp.Compare(b.requests, a.requests); c != 0 {
			return c
		}
		return strings.Compare(a.path, b.path)
	})
	return routes[:min(len(routes), routeRows)], nil
}

func (m model) applyDash(msg dashLoadedMsg) (model, tea.Cmd) {
	for i := range m.stack {
		h := &m.stack[i].home
		if m.stack[i].page != homePage || h.dash.gen != msg.gen {
			continue
		}
		d := &h.dash
		d.answered, d.at = msg.gen, time.Now()
		if msg.err != nil {
			d.err = styles.Clean(msg.err.Error())
			break
		}
		d.loaded, d.err, d.services, d.routes = true, "", msg.services, msg.routes
		h.table.setRows(h.rows())
	}
	return m, nil
}

// The layout of the overview: its resources beside its usage where the content area is wide
// enough, the resources' table as wide as homeLeft, and the routes below both.
const (
	homeWide = 112
	homeLeft = 54
)

// homeTableWidth is the width of the overview's table of resources.
func homeTableWidth() int {
	if contentWidth >= homeWide {
		return homeLeft
	}
	return tableWidth()
}

// homeView draws the overview: the kinds of resource and their counts and requests beside the
// project's usage, and below them the routes requested most, as far as they fit. Beside the table
// the usage takes a heading and a rule as the table's, so each of its series sits on a line of the
// table's rows; below the table it takes a section header.
func (m model) homeView() string {
	h := m.top().home
	wide := contentWidth >= homeWide
	usageWidth := tableWidth() - 2
	if wide {
		usageWidth = tableWidth() - homeLeft - 6
	}
	title := "usage, last " + usageAgo
	heading := make([]string, 0, 2)
	if wide {
		heading = append(heading, strings.Repeat(" ", tableGutter)+styles.Faint(title),
			lipgloss.NewStyle().Foreground(styles.P.Rule).Render(strings.Repeat("─", usageWidth+2)))
	} else {
		heading = append(heading, styles.SectionHeader(title, usageWidth+2))
	}
	usage := append(heading, indentLines(m.usageLines(h.usage, projectUsage, usageWidth), 2)...)
	table := strings.Split(h.table.View(), "\n")
	var top []string
	if wide {
		for i := range max(len(table), len(usage)) {
			left, right := "", ""
			if i < len(table) {
				left = table[i]
			}
			if i < len(usage) {
				right = usage[i]
			}
			top = append(top, left+strings.Repeat(" ", max(0, homeLeft+4-ansi.StringWidth(left)))+right)
		}
	} else {
		top = append(append(table, ""), usage...)
	}
	room := contentHeight - len(top) - 2
	if room >= minTableHeight+tableHeaderRows {
		top = append(top, "", styles.SectionHeader("busiest routes, last "+usageAgo, tableWidth()))
		top = append(top, m.routesView(h.dash, room-1)...)
	}
	return indent(strings.Join(top, "\n"))
}

// routeColumns are the columns of the busiest routes, the route taking what is left.
var routeColumns = []column{
	{title: "requests", width: 8, right: true},
	{title: "outbound", width: 9, right: true, drop: 1},
	{title: "share", width: 22, drop: 2},
	{title: "method", width: 6},
	{title: "route", width: 30, grow: 1, middle: true},
}

// routesView draws the routes requested most in height lines, each with the share of the busiest
// as a bar, or what stops them.
func (m model) routesView(d dashState, height int) []string {
	switch {
	case !d.loaded && d.err != "":
		return cellLines(cell{styles.S("could not read the routes: "+d.err, styles.ToneMuted), styles.S(" · it needs Appwrite 2.7, and a key the usage.read scope", styles.ToneFaint)}, tableWidth())
	case !d.loaded:
		return cellLines(cell{styles.S(m.spinner.View()+" ", styles.ToneAccent), styles.S("reading the routes…", styles.ToneMuted)}, tableWidth())
	}
	return routesTable(d.routes, "", tableWidth(), height)
}

// routesTable draws routes in width and height lines, below prefix, which is left out of each, as
// the routes below a table start with its own.
func routesTable(routes []routeStat, prefix string, width, height int) []string {
	if len(routes) == 0 {
		return []string{styles.Muted("  no requests in the last " + usageAgo + ", other than the interface's own")}
	}
	t := newTable(routeColumns, "")
	t.inert = true
	most := routes[0].requests
	rows := make([]tableRow, 0, len(routes))
	for _, r := range routes {
		path := r.path
		if rest, ok := strings.CutPrefix(path, prefix); ok && prefix != "" {
			path = cmpOr(rest, "/")
		}
		share := int(r.requests/most*20 + 0.5)
		rows = append(rows, tableRow{
			text(formatInt(int(r.requests)), styles.ToneBody),
			text(formatBytes(int64(r.outbound)), styles.ToneMuted),
			cell{styles.S(strings.Repeat("■", max(1, share)), styles.ToneAccent), styles.S(strings.Repeat("·", 20-max(1, share)), styles.ToneFaint)},
			text(r.method, methodTone(r.method)),
			text(path, styles.ToneText),
		})
	}
	t.setSize(width, height-tableHeaderRows)
	t.setRows(rows)
	return strings.Split(t.View(), "\n")
}

// methodTone tells reads from writes: a read in the body's tone, a write in the warm one and a
// delete in danger's.
func methodTone(method string) styles.Tone {
	switch method {
	case "GET", "HEAD", "OPTIONS":
		return styles.ToneMuted
	case "DELETE":
		return styles.ToneDanger
	}
	return styles.ToneWarm
}

// indentLines moves each line n columns to the right.
func indentLines(lines []string, n int) []string {
	out := make([]string, len(lines))
	for i, l := range lines {
		out[i] = strings.Repeat(" ", n) + l
	}
	return out
}

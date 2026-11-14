package tui

import (
	"context"
	"slices"
	"strings"
	"time"

	tea "charm.land/bubbletea/v2"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
){% verbatim %}

// The window of the usage: the last day, in buckets of a quarter of an hour, read again at most
// every half a minute, as the usage routes take 60 reads a minute of a project.
const (
	usageWindow   = 24 * time.Hour
	usageInterval = 15 * time.Minute
	usagePoints   = int(usageWindow / usageInterval)
	usageEvery    = 30 * time.Second
	usageAgo      = "24h"
	// gaugeLookback is how far before the window a gauge is read, so a level sampled before it,
	// which holds until it changes, starts the window.
	gaugeLookback = 30 * 24 * time.Hour
)

// usageSeries is a metric a usage section draws: its label, which route it is read from, and how
// it is drawn. A series of the path counts the requests to the route of the page's resource and
// the routes below it, so any resource with a route has its traffic, a table's or a file's too.
type usageSeries struct {
	label  string
	kind   appwrite.UsageKind
	metric string
	tone   styles.Tone
	bytes  bool
	path   bool
}

// key is where a read keeps the points of the series.
func (s usageSeries) key() string {
	if s.path {
		return "path " + s.metric
	}
	return s.metric
}

// usagePlace is where usage is drawn: the series, and the type of the resources they are of, of
// the one of the page, or of the project itself. An event is recorded once, under the resource of
// the request it was counted in, so the project's events are all of them, unfiltered; a gauge is
// sampled for the project as well as for each resource, so the project's are its own.
type usagePlace struct {
	resourceType string
	series       []usageSeries
}

var (
	requests = usageSeries{label: "requests", kind: appwrite.UsageEvents, metric: "network.requests", tone: styles.ToneAccent}
	reads    = usageSeries{label: "reads", kind: appwrite.UsageEvents, metric: "databases.operations.reads", tone: styles.ToneAccent}
	writes   = usageSeries{label: "writes", kind: appwrite.UsageEvents, metric: "databases.operations.writes", tone: styles.ToneWarm}
	dbStore  = usageSeries{label: "storage", kind: appwrite.UsageGauges, metric: "databases.storage", tone: styles.ToneInfo, bytes: true}
	execs    = usageSeries{label: "executions", kind: appwrite.UsageEvents, metric: "executions", tone: styles.ToneAccent}
	compute  = usageSeries{label: "compute", kind: appwrite.UsageEvents, metric: "executions.mbSeconds", tone: styles.ToneWarm}
	// traffic are the requests to the route of a resource, and what went in and out with them.
	traffic = []usageSeries{
		{label: "requests", kind: appwrite.UsageEvents, metric: "network.requests", tone: styles.ToneAccent, path: true},
		{label: "inbound", kind: appwrite.UsageEvents, metric: "network.inbound", tone: styles.ToneInfo, bytes: true, path: true},
		{label: "outbound", kind: appwrite.UsageEvents, metric: "network.outbound", tone: styles.ToneInfo, bytes: true, path: true},
	}
)

// withTraffic is the series of a place, then the traffic of its route.
func withTraffic(series ...usageSeries) []usageSeries {
	return append(series, traffic...)
}

// projectUsage is the usage the overview draws, of the whole project.
var projectUsage = usagePlace{resourceType: "project", series: []usageSeries{
	requests,
	{label: "inbound", kind: appwrite.UsageEvents, metric: "network.inbound", tone: styles.ToneInfo, bytes: true},
	{label: "outbound", kind: appwrite.UsageEvents, metric: "network.outbound", tone: styles.ToneInfo, bytes: true},
	reads, writes, execs,
	{label: "files", kind: appwrite.UsageGauges, metric: "files.storage", tone: styles.ToneMuted, bytes: true},
	{label: "databases", kind: appwrite.UsageGauges, metric: "databases.storage", tone: styles.ToneMuted, bytes: true},
}}

// usagePlaces are the kinds whose pages have a usage tab, and what it draws: what usage records
// of the resource itself, a database's reads or a bucket's files, and the traffic of its route.
// Usage does not record a request that failed, or one made as the console's admin.
var usagePlaces = map[appwrite.Kind]usagePlace{
	appwrite.Databases: {resourceType: "database", series: withTraffic(reads, writes, dbStore)},
	appwrite.Tables:    {series: traffic},
	appwrite.Rows:      {series: traffic},
	appwrite.Functions: {resourceType: "function", series: withTraffic(execs, compute,
		usageSeries{label: "storage", kind: appwrite.UsageGauges, metric: "deployments.storage", tone: styles.ToneInfo, bytes: true})},
	appwrite.FunctionDeployments: {series: traffic},
	appwrite.Sites: {resourceType: "site", series: []usageSeries{
		{label: "requests", kind: appwrite.UsageEvents, metric: "sites.requests", tone: styles.ToneAccent},
		{label: "inbound", kind: appwrite.UsageEvents, metric: "sites.inbound", tone: styles.ToneInfo, bytes: true},
		{label: "outbound", kind: appwrite.UsageEvents, metric: "sites.outbound", tone: styles.ToneInfo, bytes: true},
		{label: "api calls", kind: appwrite.UsageEvents, metric: "network.requests", tone: styles.ToneMuted, path: true}}},
	appwrite.SiteDeployments: {series: traffic},
	appwrite.Buckets: {resourceType: "bucket", series: withTraffic(
		usageSeries{label: "files", kind: appwrite.UsageGauges, metric: "files", tone: styles.ToneMuted})},
	appwrite.Files:       {series: traffic},
	appwrite.Users:       {series: traffic},
	appwrite.Teams:       {series: traffic},
	appwrite.Memberships: {series: traffic},
	appwrite.Providers:   {series: traffic},
	appwrite.Topics:      {series: traffic},
	appwrite.Messages:    {series: traffic},
}

// usageState is the usage of a place as last read.
type usageState struct {
	gen      int
	answered int
	loaded   bool
	// err is why the usage could not be read; with loaded, the usage on screen is the last read.
	err    string
	start  time.Time
	series map[string][]float64
	// routes are the routes below the resource requested most, for a place with a route.
	routes []routeStat
	readAt time.Time
}

// usageLoadedMsg is a read of the usage of a place.
type usageLoadedMsg struct {
	gen    int
	start  time.Time
	series map[string][]float64
	routes []routeStat
	err    error
}

// due reports whether the usage should be read again: never read, or read longer ago than
// usageEvery, with no read still out.
func (u usageState) due(now time.Time) bool {
	return u.gen == u.answered && (u.readAt.IsZero() || now.Sub(u.readAt) >= usageEvery)
}

// usageOf is what a read of usage is of: a resource, by its id and the route of its requests, or
// the project when both are empty.
type usageOf struct {
	id   string
	path string
}

// loadUsage numbers a read of the usage of a place and returns it.
func (m *model) loadUsage(u *usageState, b backend, place usagePlace, of usageOf) tea.Cmd {
	u.gen = m.next()
	return m.usageCmd(u.gen, b, place, of)
}

// usageCmd reads the usage of a place for the read numbered gen.
func (m model) usageCmd(gen int, b backend, place usagePlace, of usageOf) tea.Cmd {
	ctx, cancel := m.withTimeout(loadTimeout)
	end := time.Now().Truncate(usageInterval).Add(usageInterval)
	start := end.Add(-usageWindow)
	return func() tea.Msg {
		defer cancel()
		series, err := readUsage(ctx, b, place, of, start, end)
		if err != nil || of.path == "" {
			return usageLoadedMsg{gen: gen, start: start, series: series, err: err}
		}
		routes, err := readRoutes(ctx, b, of.path, start, end)
		return usageLoadedMsg{gen: gen, start: start, series: series, routes: routes, err: err}
	}
}

// readUsage reads the series of a place, a read for its events, one for its gauges and one for
// the traffic of its route, and lays each out in the buckets of the window.
func readUsage(ctx context.Context, b backend, place usagePlace, of usageOf, start, end time.Time) (map[string][]float64, error) {
	out := map[string][]float64{}
	type read struct {
		kind appwrite.UsageKind
		path bool
	}
	for _, r := range []read{{appwrite.UsageEvents, false}, {appwrite.UsageGauges, false}, {appwrite.UsageEvents, true}} {
		var series []usageSeries
		for _, s := range place.series {
			if s.kind == r.kind && s.path == r.path {
				series = append(series, s)
			}
		}
		if len(series) == 0 || r.path && of.path == "" {
			continue
		}
		metrics := make([]string, len(series))
		for i, s := range series {
			metrics[i] = s.metric
		}
		q := appwrite.UsageQuery{Kind: r.kind, Metrics: metrics, Interval: usageInterval, Start: start, End: end}
		switch {
		case r.path:
			q.Path = of.path
		case r.kind == appwrite.UsageGauges:
			q.Start = start.Add(-gaugeLookback)
			q.ResourceType, q.ResourceID = place.resourceType, of.id
		case of.id != "":
			q.ResourceType, q.ResourceID = place.resourceType, of.id
		}
		points, err := b.Usage(ctx, q)
		if err != nil {
			return nil, err
		}
		for _, s := range series {
			out[s.key()] = appwrite.Bucketed(points[s.metric], start, usageInterval, usagePoints, r.kind == appwrite.UsageGauges)
		}
	}
	return out, nil
}

// findUsage is the usage of the stack that waits for gen.
func (m *model) findUsage(gen int) *usageState {
	for i := range m.stack {
		s := &m.stack[i]
		switch {
		case s.page == homePage && s.home.usage.gen == gen:
			return &s.home.usage
		case s.page == itemPage && s.item.usage.gen == gen:
			return &s.item.usage
		}
	}
	return nil
}

func (m model) applyUsage(msg usageLoadedMsg) (model, tea.Cmd) {
	u := m.findUsage(msg.gen)
	if u == nil {
		return m, nil
	}
	u.answered = msg.gen
	if msg.err != nil {
		u.err = styles.Clean(msg.err.Error())
		return m, nil
	}
	u.loaded, u.err, u.start, u.series, u.routes, u.readAt = true, "", msg.start, msg.series, msg.routes, time.Now()
	return m, nil
}

// usageLines draw the usage of a place as sparklines of the last day, or what stops them.
func (m model) usageLines(u usageState, place usagePlace, width int) []string {
	note := func(c cell) []string { return cellLines(c, width) }
	switch {
	case !u.loaded && u.err != "":
		return note(cell{styles.S("could not read the usage: "+u.err, styles.ToneMuted), styles.S(" · it needs Appwrite 2.7, and a key the usage.read scope", styles.ToneFaint)})
	case !u.loaded:
		return note(cell{styles.S(m.spinner.View()+" ", styles.ToneAccent), styles.S("reading the usage…", styles.ToneMuted)})
	}
	series := make([]metricSeries, 0, len(place.series))
	for _, s := range place.series {
		points := u.series[s.key()]
		format := func(v float64) string { return formatInt(int(v + 0.5)) }
		if s.bytes {
			format = func(v float64) string { return formatBytes(int64(v + 0.5)) }
		}
		total := 0.0
		stat := ""
		if s.kind == appwrite.UsageGauges {
			stat = "now"
			if len(points) > 0 {
				total = points[len(points)-1]
			}
		} else {
			for _, v := range points {
				total += v
			}
		}
		unit := ""
		if s.kind == appwrite.UsageEvents {
			unit = "/15m"
		}
		series = append(series, metricSeries{label: s.label, points: points, tone: s.tone, stat: stat,
			total: text(format(total), styles.ToneBody), format: format, unit: unit, gauge: s.kind == appwrite.UsageGauges})
	}
	lines := sparkLines(series, width)
	if hasPath(place) {
		lines = append(lines, "", styles.Faint(truncate("requests, inbound and outbound count the requests to its route that succeeded, other than the console's", width)))
	}
	if u.err != "" {
		lines = append(lines, cellLines(text("as of "+u.readAt.Local().Format(time.TimeOnly)+", could not read again: "+u.err, styles.ToneFaint), width)...)
	}
	return lines
}

// hasPath reports whether a place draws the traffic of a route.
func hasPath(place usagePlace) bool {
	for _, s := range place.series {
		if s.path {
			return true
		}
	}
	return false
}

// rowUsage is the usage a list draws beside each of its resources: metrics of a kind, summed,
// of the resources of a type, over the last day in buckets of an hour.
type rowUsage struct {
	kind         appwrite.UsageKind
	metrics      []string
	resourceType string
	bytes        bool
	tone         styles.Tone
}

// The window of a list's usage: the last day in hours, a sparkline narrow enough for a column.
const (
	rowUsageInterval = time.Hour
	rowUsagePoints   = int(usageWindow / rowUsageInterval)
	rowUsageWidth    = 12
)

// rowUsages are the kinds whose lists draw the usage of each of their resources.
var rowUsages = map[appwrite.Kind]rowUsage{
	appwrite.Databases: {kind: appwrite.UsageEvents, metrics: []string{"databases.operations.reads", "databases.operations.writes"}, resourceType: "database", tone: styles.ToneAccent},
	appwrite.Functions: {kind: appwrite.UsageEvents, metrics: []string{"executions"}, resourceType: "function", tone: styles.ToneAccent},
	appwrite.Sites:     {kind: appwrite.UsageEvents, metrics: []string{"sites.requests"}, resourceType: "site", tone: styles.ToneAccent},
	appwrite.Buckets:   {kind: appwrite.UsageGauges, metrics: []string{"files"}, resourceType: "bucket", tone: styles.ToneInfo},
}

// rowUsageColumn is the column a list draws its resources' usage in, the last it gives up.
func rowUsageColumn(r rowUsage) colSpec {
	title := "24h ops"
	switch {
	case r.kind == appwrite.UsageGauges:
		title = "files"
	case r.metrics[0] == "executions":
		title = "24h runs"
	case r.metrics[0] == "sites.requests":
		title = "24h requests"
	}
	return colSpec{column: column{title: title, width: rowUsageWidth + 10, drop: 9}}
}

// listUsageMsg is the usage of each resource of a list, by its id.
type listUsageMsg struct {
	gen  int
	byID map[string][]float64
	err  error
}

// loadRowUsage reads the usage of the resources of a list, when its kind draws it and it is due.
func (m *model) loadRowUsage(l *listState) tea.Cmd {
	r, ok := rowUsages[l.ref.Kind]
	if !ok || l.usageGen != l.usageAnswered || time.Since(l.usageAt) < usageEvery {
		return nil
	}
	l.usageGen = m.next()
	gen, b := l.usageGen, l.b
	ctx, cancel := m.withTimeout(loadTimeout)
	end := time.Now().Truncate(rowUsageInterval).Add(rowUsageInterval)
	start := end.Add(-usageWindow)
	return func() tea.Msg {
		defer cancel()
		q := appwrite.UsageQuery{Kind: r.kind, Metrics: r.metrics, Interval: rowUsageInterval, Start: start, End: end, ResourceType: r.resourceType, ByResource: true}
		if r.kind == appwrite.UsageGauges {
			q.Start = start.Add(-gaugeLookback)
		}
		points, err := b.Usage(ctx, q)
		if err != nil {
			return listUsageMsg{gen: gen, err: err}
		}
		byID := map[string][]appwrite.Point{}
		for _, metric := range r.metrics {
			for _, p := range points[metric] {
				byID[p.ResourceID] = append(byID[p.ResourceID], p)
			}
		}
		out := make(map[string][]float64, len(byID))
		for id, ps := range byID {
			slices.SortStableFunc(ps, func(a, b appwrite.Point) int { return a.Time.Compare(b.Time) })
			out[id] = appwrite.Bucketed(ps, start, rowUsageInterval, rowUsagePoints, r.kind == appwrite.UsageGauges)
		}
		return listUsageMsg{gen: gen, byID: out}
	}
}

func (m model) applyListUsage(msg listUsageMsg) (model, tea.Cmd) {
	for i := range m.stack {
		s := &m.stack[i]
		lists := []*listState{&s.list}
		for j := range s.item.lists {
			lists = append(lists, &s.item.lists[j])
		}
		for _, l := range lists {
			if l.usageGen != msg.gen {
				continue
			}
			l.usageAnswered, l.usageAt = msg.gen, time.Now()
			if msg.err == nil {
				l.usage = msg.byID
			} else {
				l.usageErr = true
			}
			l.relay()
		}
	}
	return m, nil
}

// rowSeries is the usage of a resource of a list as a sparkline of the last day draws it.
func rowSeries(r rowUsage, points []float64) metricSeries {
	if len(points) == 0 {
		points = make([]float64, rowUsagePoints)
	}
	format := func(v float64) string { return formatInt(int(v + 0.5)) }
	total, stat, unit := 0.0, "", "/h"
	if r.kind == appwrite.UsageGauges {
		total, stat, unit = points[len(points)-1], "now", ""
	} else {
		for _, v := range points {
			total += v
		}
	}
	return metricSeries{label: strings.TrimPrefix(rowUsageColumn(r).title, "24h "), points: points, tone: r.tone, stat: stat,
		total: text(format(total), styles.ToneBody), format: format, unit: unit, gauge: r.kind == appwrite.UsageGauges}
}

// rowUsageCell is the usage of a resource in its list: a sparkline of the last day, and its sum
// or, for a gauge, its level now.
func rowUsageCell(r rowUsage, series []float64, failed, loaded bool) cell {
	switch {
	case failed:
		return text("-", styles.ToneFaint)
	case !loaded:
		return text("…", styles.ToneFaint)
	}
	if len(series) == 0 {
		series = make([]float64, rowUsagePoints)
	}
	total := 0.0
	if r.kind == appwrite.UsageGauges {
		total = series[len(series)-1]
	} else {
		for _, v := range series {
			total += v
		}
	}
	figure := formatInt(int(total + 0.5))
	if r.bytes {
		figure = formatBytes(int64(total + 0.5))
	}
	c := sparkCell(series, rowUsageWidth, r.tone)
	return append(c, styles.S(" "+figure, styles.ToneBody))
}
{% endverbatim %}
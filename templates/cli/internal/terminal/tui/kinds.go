package tui

import (
	"strconv"
	"strings"

	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/tui/styles"
){% verbatim %}

// colSpec is a column of a list and how a resource fills it.
type colSpec struct {
	column
	value func(appwrite.Doc) cell
}

// factSpec is a field of an item's page, a label and its value.
type factSpec struct {
	label string
	value func(appwrite.Doc) cell
}

// tabSpec is a tab of an item's page: a list of the resources of a kind under it, or lines drawn
// from the resource itself.
type tabSpec struct {
	title string
	child appwrite.Kind
	list  bool
	lines func(appwrite.Doc) []string
	// usage says the tab draws the resource's usage over the last day.
	usage bool
}

// kindSpec is how the interface shows a kind of resource: its name, the columns of its list, the
// fields of its page and the tabs below them. Every page ends with a json tab.
type kindSpec struct {
	one, many string
	cols      []colSpec
	facts     []factSpec
	tabs      []tabSpec
	// title is what a resource is called in a crumb, its name or else its id.
	title func(appwrite.Doc) string
}

// spec is the spec of a kind.
func spec(k appwrite.Kind) kindSpec {
	s := specs[k]
	if s.title == nil {
		s.title = func(d appwrite.Doc) string { return cmpOr(d.Str("name"), d.ID()) }
	}
	return s
}

// The cells the specs fill their columns with.
var (
	fieldCell = func(name string, tone styles.Tone) func(appwrite.Doc) cell {
		return func(d appwrite.Doc) cell { return valueCell(d.Str(name), tone) }
	}
	nameCell = func(d appwrite.Doc) cell { return text(cmpOr(d.Str("name"), "(no name)"), styles.ToneText) }
	idCell   = fieldCell("$id", styles.ToneFaint)
	agoCell  = func(f string) func(appwrite.Doc) cell {
		return func(d appwrite.Doc) cell { return text(formatAgo(d.Time(f)), styles.ToneBody) }
	}
	whenCell = func(f string) func(appwrite.Doc) cell {
		return func(d appwrite.Doc) cell {
			t := d.Time(f)
			if t.IsZero() {
				return text("-", styles.ToneFaint)
			}
			return cell{styles.S(formatTime(t), styles.ToneBody), styles.S("  "+formatAgo(t), styles.ToneFaint)}
		}
	}
	enabledCell = func(f string) func(appwrite.Doc) cell {
		return func(d appwrite.Doc) cell { return onOff(d.Bool(f), "enabled", "disabled") }
	}
	yesNoCell = func(f string) func(appwrite.Doc) cell {
		return func(d appwrite.Doc) cell {
			if d.Bool(f) {
				return text("yes", styles.ToneBody)
			}
			return text("no", styles.ToneFaint)
		}
	}
	bytesCell = func(f string) func(appwrite.Doc) cell {
		return func(d appwrite.Doc) cell {
			if !d.Has(f) {
				return text("-", styles.ToneFaint)
			}
			return text(formatBytes(d.Int(f)), styles.ToneBody)
		}
	}
	listCell = func(f string) func(appwrite.Doc) cell {
		return func(d appwrite.Doc) cell {
			values := d.Strings(f)
			if len(values) == 0 {
				return text("none", styles.ToneFaint)
			}
			return text(strings.Join(values, ", "), styles.ToneBody)
		}
	}
	created = colSpec{column{title: "created", width: 9, drop: 4}, agoCell("$createdAt")}
	updated = colSpec{column{title: "updated", width: 9, drop: 3}, agoCell("$updatedAt")}
	idCol   = colSpec{column{title: "id", width: 20, grow: 1, drop: 2, middle: true}, idCell}
	nameCol = colSpec{column{title: "name", width: 20, grow: 2}, nameCell}

	idFact          = factSpec{"id", fieldCell("$id", styles.ToneBody)}
	createdFact     = factSpec{"created", whenCell("$createdAt")}
	updatedFact     = factSpec{"updated", whenCell("$updatedAt")}
	permissionsFact = factSpec{"permissions", listCell("$permissions")}
	enabledFact     = factSpec{"status", enabledCell("enabled")}
)

// valueCell is a value as a column shows it, faint when it is empty.
func valueCell(v string, tone styles.Tone) cell {
	if v == "" {
		return text("-", styles.ToneFaint)
	}
	return text(v, tone)
}

// onOff is a state that is on or off, with its symbol, so it reads without colour.
func onOff(on bool, yes, no string) cell {
	if on {
		return cell{styles.S("✓ ", styles.ToneSuccess), styles.S(yes, styles.ToneBody)}
	}
	return cell{styles.S("▲ ", styles.ToneWarning), styles.S(no, styles.ToneWarning)}
}

// status is the state of a deployment, an execution or a column, by what it says: done, failed or
// still running.
func status(f string) func(appwrite.Doc) cell {
	return func(d appwrite.Doc) cell {
		s := d.Str(f)
		switch s {
		case "":
			return text("-", styles.ToneFaint)
		case "ready", "completed", "available", "active", "sent":
			return cell{styles.S("✓ ", styles.ToneSuccess), styles.S(s, styles.ToneBody)}
		case "failed", "canceled", "cancelled", "stuck":
			return cell{styles.S("✗ ", styles.ToneDanger), styles.S(s, styles.ToneDanger)}
		case "draft":
			return cell{styles.S("○ ", styles.ToneFaint), styles.S(s, styles.ToneMuted)}
		case "scheduled":
			return cell{styles.S("◷ ", styles.ToneInfo), styles.S(s, styles.ToneInfo)}
		}
		return cell{styles.S("● ", styles.ToneAccent), styles.S(s, styles.ToneMuted)}
	}
}

// statusCode is an http status, toned by its class.
func statusCode(f string) func(appwrite.Doc) cell {
	return func(d appwrite.Doc) cell {
		code := d.Int(f)
		tone := styles.ToneBody
		switch {
		case code == 0:
			return text("-", styles.ToneFaint)
		case code >= 500:
			tone = styles.ToneDanger
		case code >= 400:
			tone = styles.ToneWarning
		case code >= 300:
			tone = styles.ToneInfo
		case code >= 200:
			tone = styles.ToneSuccess
		}
		return text(strconv.FormatInt(code, 10), tone)
	}
}

// seconds is a duration the API gives in seconds.
func seconds(f string) func(appwrite.Doc) cell {
	return func(d appwrite.Doc) cell { return text(formatSeconds(d.Float(f)), styles.ToneBody) }
}

// request is the method and path an execution was asked with.
func request(d appwrite.Doc) cell {
	return cell{styles.S(cmpOr(d.Str("requestMethod"), "-")+" ", styles.ToneMuted), styles.S(cmpOr(d.Str("requestPath"), "/"), styles.ToneBody)}
}

// textLines is a field that holds text, as its lines, or a note that it is empty.
func textLines(f, empty string) func(appwrite.Doc) []string {
	return func(d appwrite.Doc) []string {
		v := strings.TrimRight(d.Str(f), "\n")
		if v == "" {
			return []string{styles.Faint(empty)}
		}
		lines := strings.Split(styles.CleanBlock(v), "\n")
		for i, l := range lines {
			lines[i] = styles.Body(l)
		}
		return lines
	}
}

// bodyLines is a field that holds a payload, indented when it is json.
func bodyLines(f, empty string) func(appwrite.Doc) []string {
	return func(d appwrite.Doc) []string {
		v := d.Str(f)
		if strings.TrimSpace(v) == "" {
			return []string{styles.Faint(empty)}
		}
		if lines, ok := jsonLines([]byte(v)); ok {
			return lines
		}
		return textLines(f, empty)(d)
	}
}

// objectLines is a field that holds an object, as indented json.
func objectLines(f, empty string) func(appwrite.Doc) []string {
	return func(d appwrite.Doc) []string {
		o, ok := d.Object(f)
		if !ok || len(o.Fields()) == 0 {
			return []string{styles.Faint(empty)}
		}
		lines, _ := jsonLines(o.Raw)
		return lines
	}
}

// headerLines are the headers of a request or an answer, a name and its value each.
func headerLines(f, empty string) func(appwrite.Doc) []string {
	return func(d appwrite.Doc) []string {
		list, _ := d.Value(f).([]any)
		var pairs [][2]string
		for _, h := range list {
			if m, ok := h.(map[string]any); ok {
				pairs = append(pairs, [2]string{appwrite.Format(m["name"]), appwrite.Format(m["value"])})
			}
		}
		if len(pairs) == 0 {
			return []string{styles.Faint(empty)}
		}
		width := 0
		for _, p := range pairs {
			width = max(width, len(p[0]))
		}
		lines := make([]string, 0, len(pairs))
		for _, p := range pairs {
			lines = append(lines, styles.Faint(styles.Clean(p[0])+strings.Repeat(" ", width-len(p[0])+2))+styles.Body(styles.Clean(p[1])))
		}
		return lines
	}
}

// rowValues are the columns of a row, its own fields, each a label and its value: in columns
// side by side where the page is wide enough and every value fits its column, and one below the
// other, each value whole, else.
func rowValues(d appwrite.Doc) []string {
	var names []string
	for _, f := range d.Fields() {
		if !strings.HasPrefix(f, "$") {
			names = append(names, f)
		}
	}
	if len(names) == 0 {
		return []string{styles.Faint("this row holds no values")}
	}
	width := detailWidth() - 2
	if cols := factColumns(width); cols > 1 {
		room := width/cols - labelWidth - 4
		fits := true
		facts := make([]factSpec, 0, len(names))
		for _, n := range names {
			value := rowCell(d.Value(n))
			fits = fits && cellWidth(value) <= room && len(n) <= labelWidth
			facts = append(facts, factSpec{label: n, value: func(d appwrite.Doc) cell { return rowCell(d.Value(n)) }})
		}
		if fits {
			lines := factGrid(facts, d, width, len(names))
			for i, l := range lines {
				lines[i] = strings.TrimPrefix(l, " ")
			}
			return lines
		}
	}
	labels := 0
	for _, n := range names {
		labels = max(labels, len(n))
	}
	labels = min(labels, labelWidth*2)
	lines := make([]string, 0, len(names))
	for _, n := range names {
		label := styles.Faint(truncate(styles.Clean(n), labels) + strings.Repeat(" ", max(0, labels-len(n))+2))
		lines = append(lines, label+styles.Render(rowCell(d.Value(n))...))
	}
	return lines
}

// rowCell is a value of a row's column, as a cell of the rows' table or a field of its page.
func rowCell(v any) cell {
	switch v := v.(type) {
	case nil:
		return text("null", styles.ToneFaint)
	case bool:
		return text(appwrite.Format(v), styles.ToneWarm)
	case string:
		return text(strings.ReplaceAll(v, "\n", " "), styles.ToneBody)
	case []any, map[string]any:
		return text(appwrite.Format(v), styles.ToneMuted)
	}
	return text(appwrite.Format(v), styles.ToneWarm)
}

// rowColumns are the columns of the rows of a table, from the columns the table has: its id, then
// each column of the table, the first ones kept as the terminal narrows.
func rowColumns(table appwrite.Doc) []colSpec {
	cols := []colSpec{{column{title: "$id", width: 20, grow: 1, middle: true}, fieldCell("$id", styles.ToneText)}}
	list, _ := table.Value("columns").([]any)
	for i, c := range list {
		m, ok := c.(map[string]any)
		if !ok {
			continue
		}
		key := appwrite.Format(m["key"])
		if key == "" {
			continue
		}
		cols = append(cols, colSpec{
			column{title: key, width: max(10, min(len(key), 24)), grow: 1, drop: 10 + i},
			func(d appwrite.Doc) cell { return rowCell(d.Value(key)) },
		})
	}
	return append(cols, colSpec{column{title: "$updatedAt", width: 10, drop: 5}, agoCell("$updatedAt")})
}

// deploymentCols, deploymentFacts and executionCols are shared by functions and sites.
var (
	deploymentCols = []colSpec{
		{column{title: "id", width: 20, grow: 1, middle: true}, fieldCell("$id", styles.ToneText)},
		{column{title: "status", width: 12}, status("status")},
		{column{title: "type", width: 8, drop: 3}, fieldCell("type", styles.ToneMuted)},
		{column{title: "branch", width: 12, grow: 1, drop: 4}, fieldCell("providerBranch", styles.ToneMuted)},
		{column{title: "size", width: 9, right: true, drop: 2}, bytesCell("sourceSize")},
		{column{title: "build", width: 7, right: true, drop: 1}, seconds("buildDuration")},
		created,
	}
	deploymentFacts = []factSpec{
		idFact, {"status", status("status")}, {"type", fieldCell("type", styles.ToneBody)},
		{"source", bytesCell("sourceSize")}, {"build size", bytesCell("buildSize")}, {"build", seconds("buildDuration")},
		{"branch", fieldCell("providerBranch", styles.ToneBody)}, {"commit", fieldCell("providerCommitMessage", styles.ToneBody)},
		createdFact, updatedFact,
	}
	deploymentTabs = []tabSpec{{title: "build logs", lines: textLines("buildLogs", "no build logs")}}
	executionCols  = []colSpec{
		{column{title: "created", width: 15}, func(d appwrite.Doc) cell { return text(formatTime(d.Time("$createdAt")), styles.ToneBody) }},
		{column{title: "request", width: 20, grow: 2}, request},
		{column{title: "code", width: 4, right: true}, statusCode("responseStatusCode")},
		{column{title: "duration", width: 8, right: true, drop: 2}, seconds("duration")},
		{column{title: "trigger", width: 8, drop: 3}, fieldCell("trigger", styles.ToneMuted)},
		{column{title: "status", width: 11, drop: 1}, status("status")},
		{column{title: "id", width: 20, grow: 1, drop: 4, middle: true}, idCell},
	}
	executionFacts = []factSpec{
		idFact, {"status", status("status")}, {"trigger", fieldCell("trigger", styles.ToneBody)},
		{"request", request}, {"code", statusCode("responseStatusCode")}, {"duration", seconds("duration")},
		createdFact, {"deployment", fieldCell("deploymentId", styles.ToneBody)},
	}
	executionTabs = []tabSpec{
		{title: "output", lines: executionOutput},
		{title: "response", lines: bodyLines("responseBody", "the response has no body")},
		{title: "logs", lines: textLines("logs", "no logs")},
		{title: "errors", lines: textLines("errors", "no errors")},
		{title: "headers", lines: func(d appwrite.Doc) []string {
			lines := append([]string{styles.SectionHeader("request", detailWidth()-2)}, headerLines("requestHeaders", "none")(d)...)
			lines = append(lines, "", styles.SectionHeader("response", detailWidth()-2))
			return append(lines, headerLines("responseHeaders", "none")(d)...)
		}},
	}
)

var specs = map[appwrite.Kind]kindSpec{
	appwrite.Databases: {
		one: "database", many: "databases",
		cols: []colSpec{nameCol, idCol, {column{title: "type", width: 8, drop: 1}, fieldCell("type", styles.ToneMuted)},
			{column{title: "status", width: 10}, enabledCell("enabled")}, created, updated},
		facts: []factSpec{idFact, {"type", fieldCell("type", styles.ToneBody)}, enabledFact, createdFact, updatedFact},
		tabs:  []tabSpec{{title: "tables", child: appwrite.Tables, list: true}},
	},
	appwrite.Tables: {
		one: "table", many: "tables",
		cols: []colSpec{nameCol, idCol, {column{title: "row security", width: 12, drop: 1}, yesNoCell("rowSecurity")},
			{column{title: "status", width: 10}, enabledCell("enabled")}, created, updated},
		facts: []factSpec{idFact, {"database", fieldCell("databaseId", styles.ToneBody)}, enabledFact,
			{"row security", yesNoCell("rowSecurity")}, createdFact, updatedFact, permissionsFact},
		tabs: []tabSpec{
			{title: "rows", child: appwrite.Rows, list: true},
			{title: "columns", child: appwrite.Columns, list: true},
			{title: "indexes", child: appwrite.Indexes, list: true},
		},
	},
	appwrite.Rows: {
		one: "row", many: "rows",
		title: func(d appwrite.Doc) string { return d.ID() },
		facts: []factSpec{idFact, {"sequence", fieldCell("$sequence", styles.ToneBody)}, createdFact, updatedFact, permissionsFact},
		tabs:  []tabSpec{{title: "values", lines: rowValues}},
	},
	appwrite.Columns: {
		one: "column", many: "columns",
		title: func(d appwrite.Doc) string { return d.Str("key") },
		cols: []colSpec{
			{column{title: "key", width: 18, grow: 2}, fieldCell("key", styles.ToneText)},
			{column{title: "type", width: 10}, func(d appwrite.Doc) cell {
				c := text(d.Str("type"), styles.ToneBody)
				if d.Bool("array") {
					c = append(c, styles.S("[]", styles.ToneFaint))
				}
				return c
			}},
			{column{title: "size", width: 6, right: true, drop: 2}, func(d appwrite.Doc) cell {
				if !d.Has("size") {
					return text("", styles.ToneFaint)
				}
				return text(formatInt(int(d.Int("size"))), styles.ToneBody)
			}},
			{column{title: "required", width: 8, drop: 3}, yesNoCell("required")},
			{column{title: "default", width: 12, grow: 1, drop: 4}, func(d appwrite.Doc) cell { return rowCell(d.Value("default")) }},
			{column{title: "status", width: 12}, status("status")},
		},
		facts: []factSpec{{"key", fieldCell("key", styles.ToneBody)}, {"type", fieldCell("type", styles.ToneBody)},
			{"format", fieldCell("format", styles.ToneBody)}, {"status", status("status")}, {"required", yesNoCell("required")},
			{"array", yesNoCell("array")}, {"default", func(d appwrite.Doc) cell { return rowCell(d.Value("default")) }},
			{"error", fieldCell("error", styles.ToneDanger)}, createdFact, updatedFact},
	},
	appwrite.Indexes: {
		one: "index", many: "indexes",
		title: func(d appwrite.Doc) string { return d.Str("key") },
		cols: []colSpec{
			{column{title: "key", width: 18, grow: 1}, fieldCell("key", styles.ToneText)},
			{column{title: "type", width: 8}, fieldCell("type", styles.ToneBody)},
			{column{title: "columns", width: 20, grow: 2}, listCell("columns")},
			{column{title: "orders", width: 10, drop: 2}, listCell("orders")},
			{column{title: "status", width: 12}, status("status")},
		},
		facts: []factSpec{{"key", fieldCell("key", styles.ToneBody)}, {"type", fieldCell("type", styles.ToneBody)},
			{"columns", listCell("columns")}, {"orders", listCell("orders")}, {"status", status("status")},
			{"error", fieldCell("error", styles.ToneDanger)}, createdFact, updatedFact},
	},
	appwrite.Functions: {
		one: "function", many: "functions",
		cols: []colSpec{nameCol, idCol, {column{title: "runtime", width: 10, drop: 2}, fieldCell("runtime", styles.ToneMuted)},
			{column{title: "status", width: 10}, enabledCell("enabled")},
			{column{title: "deployment", width: 12, drop: 1}, status("latestDeploymentStatus")}, updated},
		facts: []factSpec{idFact, {"runtime", fieldCell("runtime", styles.ToneBody)}, enabledFact,
			{"deployment", fieldCell("deploymentId", styles.ToneBody)}, {"entrypoint", fieldCell("entrypoint", styles.ToneBody)},
			{"latest", status("latestDeploymentStatus")}, {"schedule", fieldCell("schedule", styles.ToneBody)},
			{"timeout", func(d appwrite.Doc) cell { return text(formatSeconds(d.Float("timeout")), styles.ToneBody) }},
			{"execute", listCell("execute")}, {"events", listCell("events")}, createdFact, updatedFact},
		tabs: []tabSpec{
			{title: "executions", child: appwrite.Executions, list: true},
			{title: "deployments", child: appwrite.FunctionDeployments, list: true},
			{title: "variables", child: appwrite.FunctionVariables, list: true},
		},
	},
	appwrite.FunctionDeployments: {
		one: "deployment", many: "deployments", title: func(d appwrite.Doc) string { return d.ID() },
		cols: deploymentCols, facts: deploymentFacts, tabs: deploymentTabs,
	},
	appwrite.Executions: {
		one: "execution", many: "executions", title: func(d appwrite.Doc) string { return d.ID() },
		cols: executionCols, facts: executionFacts, tabs: executionTabs,
	},
	appwrite.FunctionVariables: {
		one: "variable", many: "variables", title: func(d appwrite.Doc) string { return d.Str("key") },
		cols: []colSpec{
			{column{title: "key", width: 20, grow: 1}, fieldCell("key", styles.ToneText)},
			{column{title: "value", width: 20, grow: 2}, secretValue},
			updated,
		},
		facts: []factSpec{{"key", fieldCell("key", styles.ToneBody)}, {"value", secretValue}, {"secret", yesNoCell("secret")}, createdFact, updatedFact},
	},
	appwrite.Sites: {
		one: "site", many: "sites",
		cols: []colSpec{nameCol, idCol, {column{title: "framework", width: 10, drop: 2}, fieldCell("framework", styles.ToneMuted)},
			{column{title: "status", width: 10}, enabledCell("enabled")},
			{column{title: "deployment", width: 12, drop: 1}, status("latestDeploymentStatus")}, updated},
		facts: []factSpec{idFact, {"framework", fieldCell("framework", styles.ToneBody)}, enabledFact,
			{"deployment", fieldCell("deploymentId", styles.ToneBody)}, {"latest", status("latestDeploymentStatus")},
			{"runtime", fieldCell("buildRuntime", styles.ToneBody)}, {"adapter", fieldCell("adapter", styles.ToneBody)},
			{"build", fieldCell("buildCommand", styles.ToneBody)}, {"output", fieldCell("outputDirectory", styles.ToneBody)},
			createdFact, updatedFact},
		tabs: []tabSpec{
			{title: "deployments", child: appwrite.SiteDeployments, list: true},
			{title: "logs", child: appwrite.SiteLogs, list: true},
		},
	},
	appwrite.SiteDeployments: {
		one: "deployment", many: "deployments", title: func(d appwrite.Doc) string { return d.ID() },
		cols: deploymentCols, facts: deploymentFacts, tabs: deploymentTabs,
	},
	appwrite.SiteLogs: {
		one: "log", many: "logs", title: func(d appwrite.Doc) string { return d.ID() },
		cols: executionCols, facts: executionFacts, tabs: executionTabs,
	},
	appwrite.Buckets: {
		one: "bucket", many: "buckets",
		cols: []colSpec{nameCol, idCol, {column{title: "max size", width: 9, right: true, drop: 2}, bytesCell("maximumFileSize")},
			{column{title: "protection", width: 14, grow: 1, drop: 3}, protection},
			{column{title: "status", width: 10}, enabledCell("enabled")}, created, updated},
		facts: []factSpec{idFact, enabledFact, {"max size", bytesCell("maximumFileSize")}, {"extensions", listCell("allowedFileExtensions")},
			{"protection", protection}, {"compression", fieldCell("compression", styles.ToneBody)},
			{"file security", yesNoCell("fileSecurity")}, createdFact, updatedFact, permissionsFact},
		tabs: []tabSpec{{title: "files", child: appwrite.Files, list: true}},
	},
	appwrite.Files: {
		one: "file", many: "files",
		cols: []colSpec{nameCol, idCol, {column{title: "type", width: 14, grow: 1, drop: 2}, fieldCell("mimeType", styles.ToneMuted)},
			{column{title: "size", width: 9, right: true}, bytesCell("sizeOriginal")}, created},
		facts: []factSpec{idFact, {"name", fieldCell("name", styles.ToneBody)}, {"type", fieldCell("mimeType", styles.ToneBody)},
			{"size", bytesCell("sizeOriginal")}, {"chunks", func(d appwrite.Doc) cell {
				return text(formatInt(int(d.Int("chunksUploaded")))+" of "+formatInt(int(d.Int("chunksTotal"))), styles.ToneBody)
			}}, {"signature", fieldCell("signature", styles.ToneMuted)}, createdFact, updatedFact, permissionsFact},
	},
	appwrite.Users: {
		one: "user", many: "users",
		title: func(d appwrite.Doc) string { return cmpOr(d.Str("name"), d.Str("email"), d.Str("phone"), d.ID()) },
		cols: []colSpec{nameCol, {column{title: "email", width: 20, grow: 2}, fieldCell("email", styles.ToneBody)},
			{column{title: "phone", width: 14, drop: 3}, fieldCell("phone", styles.ToneBody)},
			{column{title: "status", width: 9}, func(d appwrite.Doc) cell { return onOff(d.Bool("status"), "active", "blocked") }},
			{column{title: "verified", width: 8, drop: 2}, yesNoCell("emailVerification")},
			{column{title: "seen", width: 9, drop: 1}, agoCell("accessedAt")}, idCol},
		facts: []factSpec{idFact, {"name", fieldCell("name", styles.ToneBody)}, {"email", fieldCell("email", styles.ToneBody)},
			{"phone", fieldCell("phone", styles.ToneBody)},
			{"status", func(d appwrite.Doc) cell { return onOff(d.Bool("status"), "active", "blocked") }},
			{"email ok", yesNoCell("emailVerification")}, {"phone ok", yesNoCell("phoneVerification")}, {"mfa", yesNoCell("mfa")},
			{"labels", listCell("labels")}, {"registered", whenCell("registration")}, {"seen", whenCell("accessedAt")}},
		tabs: []tabSpec{
			{title: "sessions", child: appwrite.Sessions, list: true},
			{title: "memberships", child: appwrite.UserMemberships, list: true},
			{title: "targets", child: appwrite.UserTargets, list: true},
			{title: "prefs", lines: objectLines("prefs", "no preferences")},
		},
	},
	appwrite.Sessions: {
		one: "session", many: "sessions", title: func(d appwrite.Doc) string { return d.ID() },
		cols: []colSpec{
			{column{title: "client", width: 16, grow: 1}, func(d appwrite.Doc) cell {
				return cell{styles.S(cmpOr(d.Str("clientName"), "unknown"), styles.ToneText), styles.S(" "+d.Str("clientVersion"), styles.ToneFaint)}
			}},
			{column{title: "os", width: 10, grow: 1, drop: 3}, fieldCell("osName", styles.ToneBody)},
			{column{title: "device", width: 10, drop: 4}, fieldCell("deviceName", styles.ToneMuted)},
			{column{title: "country", width: 10, drop: 2}, fieldCell("countryName", styles.ToneBody)},
			{column{title: "ip", width: 15, drop: 1}, fieldCell("ip", styles.ToneMuted)},
			{column{title: "provider", width: 9}, fieldCell("provider", styles.ToneMuted)},
			created,
		},
		facts: []factSpec{idFact, {"provider", fieldCell("provider", styles.ToneBody)}, {"client", fieldCell("clientName", styles.ToneBody)},
			{"os", fieldCell("osName", styles.ToneBody)}, {"device", fieldCell("deviceName", styles.ToneBody)},
			{"country", fieldCell("countryName", styles.ToneBody)}, {"ip", fieldCell("ip", styles.ToneBody)},
			{"factors", listCell("factors")}, createdFact, {"expires", whenCell("expire")}},
	},
	appwrite.UserMemberships: {
		one: "membership", many: "memberships",
		title: func(d appwrite.Doc) string { return cmpOr(d.Str("teamName"), d.ID()) },
		cols:  membershipCols("team", "teamName"),
		facts: membershipFacts,
	},
	appwrite.Teams: {
		one: "team", many: "teams",
		cols: []colSpec{nameCol, idCol, {column{title: "members", width: 8, right: true}, func(d appwrite.Doc) cell {
			return text(formatInt(int(d.Int("total"))), styles.ToneBody)
		}}, created, updated},
		facts: []factSpec{idFact, {"members", func(d appwrite.Doc) cell { return text(formatInt(int(d.Int("total"))), styles.ToneBody) }},
			createdFact, updatedFact},
		tabs: []tabSpec{
			{title: "memberships", child: appwrite.Memberships, list: true},
			{title: "prefs", lines: objectLines("prefs", "no preferences")},
		},
	},
	appwrite.Memberships: {
		one: "membership", many: "memberships",
		title: func(d appwrite.Doc) string { return cmpOr(d.Str("userName"), d.Str("userEmail"), d.ID()) },
		cols:  membershipCols("user", "userName"),
		facts: membershipFacts,
	},
	appwrite.Providers: {
		one: "provider", many: "providers",
		cols: []colSpec{nameCol, idCol, {column{title: "provider", width: 10}, fieldCell("provider", styles.ToneMuted)},
			{column{title: "type", width: 6}, fieldCell("type", styles.ToneMuted)},
			{column{title: "status", width: 10}, enabledCell("enabled")}, updated},
		facts: []factSpec{idFact, {"provider", fieldCell("provider", styles.ToneBody)}, {"type", fieldCell("type", styles.ToneBody)},
			enabledFact, createdFact, updatedFact},
		tabs: []tabSpec{
			{title: "options", lines: objectLines("options", "no options")},
			{title: "credentials", lines: objectLines("credentials", "no credentials shown")},
		},
	},
	appwrite.Topics: {
		one: "topic", many: "topics",
		cols: []colSpec{nameCol, idCol,
			{column{title: "email", width: 6, right: true}, func(d appwrite.Doc) cell { return text(formatInt(int(d.Int("emailTotal"))), styles.ToneBody) }},
			{column{title: "sms", width: 6, right: true}, func(d appwrite.Doc) cell { return text(formatInt(int(d.Int("smsTotal"))), styles.ToneBody) }},
			{column{title: "push", width: 6, right: true}, func(d appwrite.Doc) cell { return text(formatInt(int(d.Int("pushTotal"))), styles.ToneBody) }},
			created},
		facts: []factSpec{idFact, {"description", fieldCell("description", styles.ToneBody)}, {"subscribe", listCell("subscribe")},
			createdFact, updatedFact},
		tabs: []tabSpec{{title: "subscribers", child: appwrite.Subscribers, list: true}},
	},
	appwrite.Subscribers: {
		one: "subscriber", many: "subscribers",
		title: func(d appwrite.Doc) string { return cmpOr(d.Str("userName"), d.ID()) },
		cols: []colSpec{
			{column{title: "user", width: 18, grow: 1}, func(d appwrite.Doc) cell { return text(cmpOr(d.Str("userName"), d.Str("userId")), styles.ToneText) }},
			{column{title: "target", width: 24, grow: 2}, subObject("target", "identifier", styles.ToneBody)},
			{column{title: "type", width: 6}, fieldCell("providerType", styles.ToneMuted)},
			created,
		},
		facts: []factSpec{idFact, {"user", fieldCell("userName", styles.ToneBody)}, {"target", subObject("target", "identifier", styles.ToneBody)},
			{"type", fieldCell("providerType", styles.ToneBody)}, {"topic", fieldCell("topicId", styles.ToneBody)}, createdFact},
	},
	appwrite.Messages: {
		one: "message", many: "messages",
		title: func(d appwrite.Doc) string { return plainText(subject(d)) },
		cols: []colSpec{
			{column{title: "subject", width: 24, grow: 2}, subject},
			{column{title: "type", width: 6}, fieldCell("providerType", styles.ToneMuted)},
			{column{title: "status", width: 12}, status("status")},
			{column{title: "delivered", width: 9, right: true, drop: 2}, func(d appwrite.Doc) cell { return text(formatInt(int(d.Int("deliveredTotal"))), styles.ToneBody) }},
			{column{title: "topics", width: 12, grow: 1, drop: 3}, listCell("topics")},
			created,
			{column{title: "id", width: 20, grow: 1, drop: 5, middle: true}, idCell},
		},
		facts: []factSpec{idFact, {"type", fieldCell("providerType", styles.ToneBody)}, {"status", status("status")},
			{"delivered", func(d appwrite.Doc) cell { return text(formatInt(int(d.Int("deliveredTotal"))), styles.ToneBody) }},
			{"topics", listCell("topics")}, {"users", listCell("users")}, {"scheduled", whenCell("scheduledAt")},
			{"sent", whenCell("deliveredAt")}, createdFact},
		tabs: []tabSpec{
			{title: "content", lines: messageContent},
			{title: "targets", child: appwrite.MessageTargets, list: true},
			{title: "errors", lines: func(d appwrite.Doc) []string {
				errs := d.Strings("deliveryErrors")
				if len(errs) == 0 {
					return []string{styles.Faint("no delivery errors")}
				}
				lines := make([]string, len(errs))
				for i, e := range errs {
					lines[i] = styles.Fg(styles.ToneDanger).Render(styles.Clean(e))
				}
				return lines
			}},
		},
	},
	appwrite.MessageTargets: {one: "target", many: "targets", title: func(d appwrite.Doc) string { return cmpOr(d.Str("identifier"), d.ID()) }, cols: targetCols,
		facts: []factSpec{idFact, {"identifier", fieldCell("identifier", styles.ToneBody)}, {"type", fieldCell("providerType", styles.ToneBody)},
			{"user", fieldCell("userId", styles.ToneBody)}, {"provider", fieldCell("providerId", styles.ToneBody)}, createdFact}},
	appwrite.UserTargets: {one: "target", many: "targets", title: func(d appwrite.Doc) string { return cmpOr(d.Str("identifier"), d.ID()) }, cols: targetCols,
		facts: []factSpec{idFact, {"identifier", fieldCell("identifier", styles.ToneBody)}, {"type", fieldCell("providerType", styles.ToneBody)},
			{"name", fieldCell("name", styles.ToneBody)}, {"provider", fieldCell("providerId", styles.ToneBody)}, {"expired", yesNoCell("expired")},
			createdFact, updatedFact}},
	appwrite.Organisations: {
		one: "organization", many: "organizations",
		cols: []colSpec{nameCol, idCol, {column{title: "members", width: 8, right: true}, func(d appwrite.Doc) cell {
			return text(formatInt(int(d.Int("total"))), styles.ToneBody)
		}}, created},
		facts: []factSpec{idFact, {"members", func(d appwrite.Doc) cell { return text(formatInt(int(d.Int("total"))), styles.ToneBody) }},
			createdFact, updatedFact},
		tabs: []tabSpec{{title: "projects", child: appwrite.Projects, list: true}},
	},
	appwrite.Projects: {
		one: "project", many: "projects",
		cols: []colSpec{nameCol, idCol, {column{title: "region", width: 8, drop: 1}, fieldCell("region", styles.ToneMuted)},
			{column{title: "status", width: 10, drop: 2}, fieldCell("status", styles.ToneMuted)}, created, updated},
	},
}

// subObject is a field of an object a resource holds, as the identifier of a subscriber's target.
func subObject(object, f string, tone styles.Tone) func(appwrite.Doc) cell {
	return func(d appwrite.Doc) cell {
		o, _ := d.Object(object)
		return valueCell(o.Str(f), tone)
	}
}

// subject is what a message says it is about: an email's subject, a push's title, or an sms's
// content.
func subject(d appwrite.Doc) cell {
	data, _ := d.Object("data")
	return text(cmpOr(data.Str("subject"), data.Str("title"), data.Str("content"), "(no subject)"), styles.ToneText)
}

// messageContent is what a message holds: its subject or title, and its content or body.
func messageContent(d appwrite.Doc) []string {
	data, _ := d.Object("data")
	var lines []string
	for _, f := range []string{"subject", "title"} {
		if v := data.Str(f); v != "" {
			lines = append(lines, styles.Render(styles.B(v, styles.ToneText)), "")
		}
	}
	body := cmpOr(data.Str("content"), data.Str("body"))
	if body == "" {
		return append(lines, styles.Faint("the message has no content"))
	}
	for l := range strings.SplitSeq(styles.CleanBlock(body), "\n") {
		lines = append(lines, styles.Body(l))
	}
	return lines
}

// targetCols are the columns of the targets of a user or a message: where it is sent to.
var targetCols = []colSpec{
	{column{title: "identifier", width: 24, grow: 2}, fieldCell("identifier", styles.ToneText)},
	{column{title: "type", width: 6}, fieldCell("providerType", styles.ToneMuted)},
	{column{title: "name", width: 12, grow: 1, drop: 2}, fieldCell("name", styles.ToneBody)},
	{column{title: "provider", width: 12, drop: 3}, fieldCell("providerId", styles.ToneMuted)},
	{column{title: "expired", width: 7, drop: 1}, yesNoCell("expired")},
	created,
}

// executionOutput is all an execution left on one tab: its response, then its logs and its errors
// where it has any, each under its heading, so a short one reads at a glance.
func executionOutput(d appwrite.Doc) []string {
	width := detailWidth() - 2
	lines := append([]string{styles.SectionHeader("response", width)}, bodyLines("responseBody", "the response has no body")(d)...)
	if strings.TrimSpace(d.Str("logs")) != "" {
		lines = append(append(lines, "", styles.SectionHeader("logs", width)), textLines("logs", "")(d)...)
	}
	if strings.TrimSpace(d.Str("errors")) != "" {
		errs := textLines("errors", "")(d)
		for i, l := range errs {
			errs[i] = styles.Fg(styles.ToneDanger).Render(ansi.Strip(l))
		}
		lines = append(append(lines, "", styles.SectionHeader("errors", width)), errs...)
	}
	return lines
}

// secretValue is a variable's value, or that it is secret and not shown.
func secretValue(d appwrite.Doc) cell {
	if d.Bool("secret") {
		return text("secret", styles.ToneFaint)
	}
	return valueCell(d.Str("value"), styles.ToneBody)
}

// protection is what a bucket does to its files: encrypts, scans and compresses them.
func protection(d appwrite.Doc) cell {
	var on []string
	if d.Bool("encryption") {
		on = append(on, "encrypted")
	}
	if d.Bool("antivirus") {
		on = append(on, "scanned")
	}
	if c := d.Str("compression"); c != "" && c != "none" {
		on = append(on, c)
	}
	if len(on) == 0 {
		return text("none", styles.ToneFaint)
	}
	return text(strings.Join(on, ", "), styles.ToneBody)
}

// membershipCols are the columns of memberships, named after the team or the user they are of.
func membershipCols(who, field string) []colSpec {
	return []colSpec{
		{column{title: who, width: 18, grow: 2}, func(d appwrite.Doc) cell { return text(cmpOr(d.Str(field), "-"), styles.ToneText) }},
		{column{title: "email", width: 18, grow: 1, drop: 3}, func(d appwrite.Doc) cell { return valueCell(d.Str("userEmail"), styles.ToneBody) }},
		{column{title: "roles", width: 12, grow: 1}, listCell("roles")},
		{column{title: "confirmed", width: 9, drop: 2}, yesNoCell("confirm")},
		{column{title: "joined", width: 9, drop: 1}, agoCell("joined")},
	}
}

var membershipFacts = []factSpec{
	idFact, {"user", fieldCell("userName", styles.ToneBody)}, {"email", fieldCell("userEmail", styles.ToneBody)},
	{"team", fieldCell("teamName", styles.ToneBody)}, {"roles", listCell("roles")}, {"confirmed", yesNoCell("confirm")},
	{"mfa", yesNoCell("mfa")}, {"invited", whenCell("invited")}, {"joined", whenCell("joined")},
}

// homeKinds are the kinds a project's overview lists, in its order.
var homeKinds = []appwrite.Kind{
	appwrite.Databases, appwrite.Functions, appwrite.Sites, appwrite.Buckets, appwrite.Users, appwrite.Teams,
	appwrite.Messages, appwrite.Topics, appwrite.Providers,
}
{% endverbatim %}
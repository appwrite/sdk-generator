package tui

import (
	"context"
	"maps"
	"os"
	"path/filepath"
	"slices"
	"strings"
	"testing"
	"unicode/utf8"

	tea "charm.land/bubbletea/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/client"
){% verbatim %}

// wantOnScreen fails the test for each text the screen does not show.
func wantOnScreen(t *testing.T, m model, texts ...string) {
	t.Helper()
	s := onScreen(m)
	for _, want := range texts {
		if !strings.Contains(s, want) {
			t.Errorf("screen does not show %q:\n%s", want, s)
		}
	}
}

// TestOverviewCounts checks the overview lists every kind with its count, and says why a count it
// could not read is missing.
func TestOverviewCounts(t *testing.T) {
	f := newFake()
	f.errs["teams"] = &appwrite.Error{Code: 401, Type: "general_unauthorized_scope", Message: "missing scope (teams.read)"}
	m := started(t, f)
	wantOnScreen(t, m, "databases", "functions", "buckets", "users", "Shop", "cloud", "1.8.0", "missing scope (teams.read)")
}

// TestNavigateToARow walks from the overview down to a row and its values, and back up, the
// crumbs following, and quits from the overview with q.
func TestNavigateToARow(t *testing.T) {
	m := started(t, newFake())
	m = pressKeys(t, m, "enter")
	wantOnScreen(t, m, "overview › databases", "main")
	m = pressKeys(t, m, "enter")
	wantOnScreen(t, m, "database main", "1 tables 1", "orders")
	m = pressKeys(t, m, "enter")
	wantOnScreen(t, m, "table orders", "1 rows 2", "customer", "ada", "grace", "null")
	m = pressKeys(t, m, "enter")
	wantOnScreen(t, m, "row o1", "values", "12.5")
	m = pressKeys(t, m, "3")
	wantOnScreen(t, m, `"customer": "ada"`)
	for range 3 {
		m = pressKeys(t, m, "esc")
	}
	wantOnScreen(t, m, "overview › databases")
	m = pressKeys(t, m, "q")
	if len(m.stack) != 1 {
		t.Fatalf("q left %d pages open, want the overview", len(m.stack))
	}
	if _, cmd := update(t, m, press("q")); !quits(cmd) {
		t.Error("q on the overview does not quit")
	}
}

// consoleInfo is a console login's connection, without a project.
var consoleInfo = client.Info{Endpoint: "https://cloud.appwrite.io/v1", Version: "2.7.1", Cloud: true, As: "ada@example.com", Console: true}

// TestConsole checks a console login opens on its organisations, and a project of one opens on
// its overview, read as that project, with the header and crumbs naming it.
func TestConsole(t *testing.T) {
	f := newFake()
	m := newModel(context.Background(), f, consoleInfo)
	m, _ = update(t, m, tea.WindowSizeMsg{Width: 120, Height: 40})
	m = settle(t, m, m.Init())
	wantOnScreen(t, m, "organizations", "acme", "none open", "ada@example.com")
	m = pressKeys(t, m, "enter")
	wantOnScreen(t, m, "organization acme", "1 projects 1", "Shop")
	m = pressKeys(t, m, "enter")
	if len(f.opened) != 1 || f.opened[0] != "shop" {
		t.Fatalf("opened %v, want the project shop", f.opened)
	}
	wantOnScreen(t, m, "organizations › organization acme › project Shop", "databases", "id       shop")
	m = pressKeys(t, m, "enter", "enter")
	wantOnScreen(t, m, "project Shop › databases › database main")
	m = pressKeys(t, m, "esc", "esc", "esc")
	wantOnScreen(t, m, "organization acme", "none open")
	m = pressKeys(t, m, "esc")
	if _, cmd := update(t, m, press("q")); !quits(cmd) {
		t.Error("q on the organizations does not quit")
	}
}

// TestUsage checks the overview draws the project's usage of the last day, a page of a database
// its own on its usage tab, read for it, and a usage that cannot be read says what it needs.
func TestUsage(t *testing.T) {
	f := newFake()
	m := started(t, f)
	wantOnScreen(t, m, "usage, last 24h", "requests", "now", "2.0 kB", "24h ago")
	for _, q := range f.usage {
		if len(q.Dimensions) > 0 {
			continue
		}
		want := map[appwrite.UsageKind]string{appwrite.UsageEvents: "", appwrite.UsageGauges: "project"}[q.Kind]
		if q.ResourceType != want || q.ResourceID != "" || q.Interval != usageInterval {
			t.Errorf("the overview read the %s with %+v", q.Kind, q)
		}
	}
	db := pressKeys(t, m, "enter", "enter", "2")
	wantOnScreen(t, db, "2 usage", "reads", "writes", "storage")
	var own, path bool
	for _, q := range f.usage {
		own = own || q.ResourceType == "database" && q.ResourceID == "main" && q.Path == ""
		path = path || q.Path == "/v1/tablesdb/main" && q.ResourceType == ""
	}
	if !own || !path {
		t.Errorf("the database's usage was read with %+v, want its own and its route's", f.usage)
	}
	table := pressKeys(t, db, "1", "enter", "4")
	wantOnScreen(t, table, "4 usage", "requests", "inbound", "outbound", "requests to its route")
	if last := f.usage[len(f.usage)-1]; last.Path != "/v1/tablesdb/main/tables/orders" {
		t.Errorf("the table's usage was read with %+v", last)
	}
	f.usageErr = &appwrite.Error{Code: 401, Type: "general_unauthorized_scope", Message: "missing scopes ([\"usage.read\"])"}
	m = started(t, f)
	wantOnScreen(t, m, "could not read the usage", "usage.read")
}

// TestRowQuery checks a filter of comparisons on rows lists them again with its queries, keeps
// every row the server sent, and esc lists them without.
func TestRowQuery(t *testing.T) {
	f := newFake()
	m := started(t, f)
	m = pressKeys(t, m, "enter", "enter", "enter", "/")
	m = typeText(t, m, "customer=ada sort:-total")
	m = pressKeys(t, m, "enter")
	asked := f.asked["rows/main/orders"]
	last := asked[len(asked)-1]
	if len(last.Queries) != 2 || !strings.Contains(last.Queries[0], `"equal"`) || !strings.Contains(last.Queries[1], `"orderDesc"`) {
		t.Fatalf("rows were listed with %+v, want the queries", last)
	}
	wantOnScreen(t, m, "query customer=ada sort:-total", "ada", "grace")
	m = pressKeys(t, m, "esc")
	asked = f.asked["rows/main/orders"]
	if last := asked[len(asked)-1]; last.Queries != nil {
		t.Errorf("esc listed the rows with %+v", last)
	}
	m = pressKeys(t, m, "/")
	m = typeText(t, m, "customer=ada grace")
	m = pressKeys(t, m, "enter")
	wantOnScreen(t, m, "not a comparison")
}

// TestEditRow checks a row edited in the editor is saved with what was written, an edit that
// changes nothing saves nothing, and one that is not json is kept in its file.
func TestEditRow(t *testing.T) {
	f := newFake()
	m := started(t, f)
	m = pressKeys(t, m, "enter", "enter", "enter")
	ref := m.top().item.lists[0].ref
	path := filepath.Join(t.TempDir(), "row.json")
	write := func(body string) {
		if err := os.WriteFile(path, []byte(body), 0o600); err != nil {
			t.Fatal(err)
		}
	}
	write(`{"customer": "ada", "total": 12.5}`)
	m, cmd := update(t, m, editorDoneMsg{ref: ref, id: "o1", path: path, before: []byte(`{"customer": "ada", "total": 12.5}`)})
	m = settle(t, m, cmd)
	wantOnScreen(t, m, "nothing changed")
	write(`{"customer": "ada lovelace", "total": 13}`)
	m, cmd = update(t, m, editorDoneMsg{ref: ref, id: "o1", path: path, before: []byte(`{}`)})
	m = settle(t, m, cmd)
	if len(f.saved) != 1 || f.saved[0].id != "o1" || f.saved[0].data["customer"] != "ada lovelace" {
		t.Fatalf("saved %+v", f.saved)
	}
	wantOnScreen(t, m, "saved the row o1", "ada lovelace")
	write(`{"customer": `)
	m, _ = update(t, m, editorDoneMsg{ref: ref, id: "o1", path: path, before: []byte(`{}`)})
	wantOnScreen(t, m, "nothing was saved", "it is kept in")
	if _, err := os.Stat(path); err != nil {
		t.Errorf("the edit that is not json was not kept: %v", err)
	}
}

// TestNewRow checks a new row starts from the table's columns, and is created with what was
// written.
func TestNewRow(t *testing.T) {
	f := newFake()
	m := started(t, f)
	m = pressKeys(t, m, "enter", "enter", "enter")
	l := m.top().item.lists[0]
	if got := newRowData(l.parent); len(got) != 2 || !slices.Contains(slices.Collect(maps.Keys(got)), "customer") {
		t.Errorf("template %v, want the table's columns", got)
	}
	path := filepath.Join(t.TempDir(), "row.json")
	if err := os.WriteFile(path, []byte(`{"customer": "linus", "total": 3}`), 0o600); err != nil {
		t.Fatal(err)
	}
	m, cmd := update(t, m, editorDoneMsg{ref: l.ref, path: path, before: []byte(`{}`)})
	m = settle(t, m, cmd)
	if len(f.saved) != 1 || f.saved[0].id != "" || f.saved[0].data["customer"] != "linus" {
		t.Fatalf("saved %+v", f.saved)
	}
	wantOnScreen(t, m, "created the row new1", "linus")
}

// TestMessaging checks messages open on their content, and topics on their subscribers.
func TestMessaging(t *testing.T) {
	m := started(t, newFake())
	msg := pressKeys(t, m, "down", "down", "down", "down", "down", "down", "enter")
	wantOnScreen(t, msg, "overview › messages", "welcome", "draft")
	wantOnScreen(t, pressKeys(t, msg, "enter"), "message welcome", "hello there", "2 targets 0")
	topic := pressKeys(t, m, "down", "down", "down", "down", "down", "down", "down", "enter", "enter")
	wantOnScreen(t, topic, "topic news", "1 subscribers 1", "ada@example.com")
	provider := pressKeys(t, m, "G", "enter", "enter")
	wantOnScreen(t, provider, "provider mailer", "smtp", "fromEmail")
}

// TestRowUsage checks a list of databases draws the usage of each database beside it, read at
// once for all of them, and one without usage as none.
func TestRowUsage(t *testing.T) {
	f := newFake()
	f.lists["databases"] = append(f.lists["databases"], appwrite.MustDoc(`{"$id":"analytics","name":"analytics","type":"tablesdb","enabled":true}`))
	m := started(t, f)
	m = pressKeys(t, m, "enter")
	wantOnScreen(t, m, "24h ops", "4,176")
	last := f.usage[len(f.usage)-1]
	if !last.ByResource || last.ResourceType != "database" || last.Interval != rowUsageInterval {
		t.Errorf("the databases' usage was read with %+v", last)
	}
	l := m.top().list
	if row := plainText(l.table.rows[1][len(l.cols)-1]); !strings.HasSuffix(row, " 0") {
		t.Errorf("analytics, without usage, shows %q", row)
	}
}

// TestDashboard checks the overview draws each kind's requests beside it and the routes requested
// most below, read without the interface's own health checks and usage reads.
func TestDashboard(t *testing.T) {
	f := newFake()
	m := started(t, f)
	wantOnScreen(t, m, "requests, 24h", "busiest routes, last 24h", "/v1/tablesdb/main/tables/orders/rows", "POST")
	if !strings.Contains(onScreen(m), "users") {
		t.Errorf("no users row:\n%s", onScreen(m))
	}
	var routes bool
	for _, q := range f.usage {
		if slices.Contains(q.Dimensions, "path") && q.Path == "" {
			routes = true
			if !slices.Equal(q.Exclude, quiet) || q.Top == 0 {
				t.Errorf("the routes were read with %+v", q)
			}
		}
	}
	if !routes {
		t.Error("the routes were not read")
	}
	narrow, _ := update(t, m, tea.WindowSizeMsg{Width: 90, Height: 50})
	wantOnScreen(t, narrow, "usage, last 24h", "busiest routes")
}

// TestListPreview checks a list with room below its rows previews the resource under the cursor,
// and a row's preview shows its own values.
func TestListPreview(t *testing.T) {
	m := started(t, newFake())
	databases := pressKeys(t, m, "enter")
	wantOnScreen(t, databases, "database main", `"type": "tablesdb"`)
	rows := pressKeys(t, databases, "enter", "enter")
	wantOnScreen(t, rows, "row o1", "customer  ada")
}

// TestRoutesBelow checks a page's usage tab draws the routes below the resource, each relative to
// its own route.
func TestRoutesBelow(t *testing.T) {
	m := started(t, newFake())
	m = pressKeys(t, m, "enter", "enter", "2")
	wantOnScreen(t, m, "busiest routes below database main", "/tables/orders/rows")
	if strings.Contains(onScreen(m), "/v1/users") {
		t.Errorf("a route outside the database is listed:\n%s", onScreen(m))
	}
}

// TestExecutionOutput checks an execution opens on its response and logs together.
func TestExecutionOutput(t *testing.T) {
	m := started(t, newFake())
	m = pressKeys(t, m, "down", "enter", "enter", "enter")
	wantOnScreen(t, m, "1 output", "response", `"ok": true`, "logs", "hi")
}

// TestTabs checks a page's tabs open by tab, by number and round from the last to the first.
func TestTabs(t *testing.T) {
	m := started(t, newFake())
	m = pressKeys(t, m, "enter", "enter", "enter")
	it := m.top().item
	if got := it.tabs[it.tab].title; got != "rows" {
		t.Fatalf("open tab %q, want rows", got)
	}
	m = pressKeys(t, m, "2")
	wantOnScreen(t, m, "string", "available")
	m = pressKeys(t, m, "shift+tab", "shift+tab")
	if it := m.top().item; it.tabs[it.tab].title != "json" {
		t.Errorf("shift+tab from rows opened %q, want json", it.tabs[it.tab].title)
	}
}

// TestSearchAsksTheServer checks enter on the filter of a kind the API searches lists it again
// with the search, and esc lists it again without.
func TestSearchAsksTheServer(t *testing.T) {
	f := newFake()
	m := started(t, f)
	m = pressKeys(t, m, "down", "down", "down", "down", "enter")
	wantOnScreen(t, m, "overview › users", "Ada")
	m = pressKeys(t, m, "/")
	m = typeText(t, m, "nobody")
	m = pressKeys(t, m, "enter")
	asked := f.asked["users"]
	if last := asked[len(asked)-1]; last.Search != "nobody" {
		t.Fatalf("last list of users asked %+v, want the search", last)
	}
	wantOnScreen(t, m, "no user matches nobody")
	m = pressKeys(t, m, "esc")
	asked = f.asked["users"]
	if last := asked[len(asked)-1]; last.Search != "" {
		t.Errorf("esc listed the users with %+v, want no search", last)
	}
	wantOnScreen(t, m, "Ada")
}

// TestDeleteRow checks a row is deleted only once its id is typed, and the rows are read again.
func TestDeleteRow(t *testing.T) {
	f := newFake()
	m := started(t, f)
	m = pressKeys(t, m, "enter", "enter", "enter", "ctrl+d")
	wantOnScreen(t, m, "delete row", "type o1 to confirm")
	m = pressKeys(t, m, "enter")
	if len(f.deleted) != 0 {
		t.Fatalf("enter deleted %v before the id was typed", f.deleted)
	}
	m = typeText(t, m, "o1")
	m = pressKeys(t, m, "enter")
	if len(f.deleted) != 1 || f.deleted[0] != "rows/main/orders/o1" {
		t.Fatalf("deleted %v, want rows/main/orders/o1", f.deleted)
	}
	if m.confirm.active {
		t.Error("the dialog is still open after the delete")
	}
	wantOnScreen(t, m, "deleted the row o1", "grace")
	if strings.Contains(onScreen(m), "ada") {
		t.Errorf("the deleted row is still listed:\n%s", onScreen(m))
	}
}

// TestExecuteFunction checks x opens the form, ctrl+r executes with what it holds and the page of
// the execution opens on its response.
func TestExecuteFunction(t *testing.T) {
	f := newFake()
	m := started(t, f)
	m = pressKeys(t, m, "down", "enter", "x")
	wantOnScreen(t, m, "execute hello", "method", "body")
	m = pressKeys(t, m, "tab", "tab", "tab")
	m = typeText(t, m, `{"a":1}`)
	m = pressKeys(t, m, "ctrl+r")
	if len(f.executed) != 1 {
		t.Fatalf("executed %d times, want once", len(f.executed))
	}
	if e := f.executed[0]; e.Method != "POST" || e.Path != "/" || e.Body != `{"a":1}` || e.Async {
		t.Errorf("executed %+v", e)
	}
	wantOnScreen(t, m, "execution e2", "response", "created", "executed hello")
}

// TestDownload checks ctrl+s saves a file under its name in the working directory, and under a
// numbered name when that is taken.
func TestDownload(t *testing.T) {
	t.Chdir(t.TempDir())
	m := started(t, newFake())
	m = pressKeys(t, m, "down", "down", "down", "enter", "enter", "ctrl+s")
	wantOnScreen(t, m, "saved ada.png")
	m = pressKeys(t, m, "ctrl+s")
	wantOnScreen(t, m, "saved ada-1.png")
	for _, name := range []string{"ada.png", "ada-1.png"} {
		if data, err := os.ReadFile(filepath.Clean(name)); err != nil || string(data) != "png bytes" {
			t.Errorf("%s holds %q, %v", name, data, err)
		}
	}
}

// TestStaleLoadIsDropped checks a page of a list that is no longer waited for changes nothing.
func TestStaleLoadIsDropped(t *testing.T) {
	m := started(t, newFake())
	m = pressKeys(t, m, "enter")
	l := m.top().list
	m, _ = update(t, m, listLoadedMsg{gen: l.gen - 1, page: appwrite.Page{Total: 1, Docs: []appwrite.Doc{appwrite.MustDoc(`{"$id":"old","name":"old"}`)}}})
	if strings.Contains(onScreen(m), "old") {
		t.Errorf("a stale page was shown:\n%s", onScreen(m))
	}
}

// TestReadMore checks moving past the last row read reads the next page after it.
func TestReadMore(t *testing.T) {
	f := newFake()
	docs := make([]appwrite.Doc, 0, 150)
	for i := range 150 {
		docs = append(docs, appwrite.MustDoc(`{"$id":"u`+string(rune('a'+i/26))+string(rune('a'+i%26))+`","name":"user"}`))
	}
	f.lists["users"] = docs
	m := started(t, f)
	m = pressKeys(t, m, "down", "down", "down", "down", "enter")
	if n := len(m.top().list.docs); n != appwrite.PageSize {
		t.Fatalf("read %d users, want a page of %d", n, appwrite.PageSize)
	}
	wantOnScreen(t, m, "150 in all")
	m = pressKeys(t, m, "G")
	if n := len(m.top().list.docs); n != 150 {
		t.Fatalf("read %d users after G, want all 150", n)
	}
	asked := f.asked["users"]
	if last := asked[len(asked)-1]; last.After != docs[appwrite.PageSize-1].ID() {
		t.Errorf("read more after %q, want after the last read", last.After)
	}
}

// TestListError checks a list that could not be read says why, and r tries again.
func TestListError(t *testing.T) {
	f := newFake()
	f.errs["functions"] = &appwrite.Error{Code: 401, Type: "general_unauthorized_scope", Message: "missing scope (functions.read)"}
	m := started(t, f)
	m = pressKeys(t, m, "down", "enter")
	wantOnScreen(t, m, "could not list the functions", "r tries again")
	delete(f.errs, "functions")
	m = pressKeys(t, m, "r")
	wantOnScreen(t, m, "hello")
}

// layoutPages are the interface on every kind of page and dialog, at a size.
func layoutPages(t *testing.T, w, h int) map[string]model {
	t.Helper()
	f := newFake()
	m := started(t, f)
	m, _ = update(t, m, tea.WindowSizeMsg{Width: w, Height: h})
	pages := map[string]model{"overview": m, "help": pressKeys(t, m, "?")}
	databases := pressKeys(t, m, "enter")
	pages["databases"] = databases
	table := pressKeys(t, databases, "enter", "enter")
	pages["table"] = table
	pages["row"] = pressKeys(t, table, "enter")
	pages["row json"] = pressKeys(t, table, "enter", "3")
	pages["row usage"] = pressKeys(t, table, "enter", "2")
	pages["delete"] = pressKeys(t, table, "ctrl+d")
	function := pressKeys(t, m, "down", "enter", "enter")
	pages["function"] = function
	pages["execute"] = pressKeys(t, function, "x")
	pages["execution"] = pressKeys(t, function, "enter")
	pages["user"] = pressKeys(t, m, "down", "down", "down", "down", "enter", "enter")
	pages["filter"] = pressKeys(t, databases, "/")
	pages["database usage"] = pressKeys(t, databases, "enter", "2")
	pages["query"] = pressKeys(t, table, "/")
	pages["message"] = pressKeys(t, m, "down", "down", "down", "down", "down", "down", "enter", "enter")
	pages["topic"] = pressKeys(t, m, "down", "down", "down", "down", "down", "down", "down", "enter", "enter")
	pages["provider"] = pressKeys(t, m, "G", "enter", "enter")
	c := newModel(context.Background(), f, consoleInfo)
	c, _ = update(t, c, tea.WindowSizeMsg{Width: w, Height: h})
	c = settle(t, c, c.Init())
	pages["organizations"] = c
	pages["organization"] = pressKeys(t, c, "enter")
	pages["console project"] = pressKeys(t, c, "enter", "enter")
	return pages
}

// TestLayout checks no page is taller or wider than the terminal, from a terminal too small to
// a large one.
func TestLayout(t *testing.T) {
	sizes := [][2]int{{1, 1}, {20, 5}, {60, 20}, {77, 21}, {78, 22}, {79, 23}, {80, 24}, {100, 30}, {120, 40}, {200, 60}, {300, 80}}
	for _, size := range sizes {
		w, h := size[0], size[1]
		for name, m := range layoutPages(t, w, h) {
			lines := strings.Split(m.render(), "\n")
			if len(lines) > h {
				t.Errorf("%dx%d %s: %d lines", w, h, name, len(lines))
			}
			for i, line := range lines {
				if lw := ansi.StringWidth(line); lw > w {
					t.Errorf("%dx%d %s: line %d is %d wide", w, h, name, i, lw)
					break
				}
			}
		}
	}
}

// TestEscapesNeverReachTheTerminal puts escape sequences in every string that comes from
// appwrite, and checks none reaches the view unescaped.
func TestEscapesNeverReachTheTerminal(t *testing.T) {
	evil := `x\u001b]8;;https://evil.example/\u0007y\u001b[2J\u009b\u202e`
	f := newFake()
	f.lists["databases"] = []appwrite.Doc{appwrite.MustDoc(`{"$id":"main","name":"` + evil + `","type":"` + evil + `"}`)}
	f.lists["tables/main"] = []appwrite.Doc{appwrite.MustDoc(`{"$id":"orders","name":"` + evil + `","columns":[{"key":"` + evil + `"}]}`)}
	f.lists["rows/main/orders"] = []appwrite.Doc{appwrite.MustDoc(`{"$id":"o1","` + evil + `":"` + evil + `"}`)}
	f.lists["executions/hello"] = []appwrite.Doc{appwrite.MustDoc(`{"$id":"e1","responseBody":"` + evil + `","logs":"` + evil + `","errors":"` + evil + `","requestPath":"` + evil + `"}`)}
	check := func(name string, m model) {
		t.Helper()
		v := m.render()
		if strings.Contains(v, "\x1b]") || strings.Contains(v, "\x07") || strings.Contains(v, "\x1b[2J") || strings.ContainsRune(v, '\u009b') || strings.ContainsRune(v, '\u202e') || !utf8.ValidString(v) {
			t.Errorf("%s: an escape sequence reached the view", name)
		}
	}
	m := started(t, f)
	databases := pressKeys(t, m, "enter")
	check("databases", databases)
	table := pressKeys(t, databases, "enter", "enter")
	check("rows", table)
	check("row", pressKeys(t, table, "enter"))
	check("row json", pressKeys(t, table, "enter", "3"))
	execution := pressKeys(t, m, "down", "enter", "enter", "enter")
	for _, tab := range []string{"1", "2", "3", "4", "5", "6"} {
		check("execution tab "+tab, pressKeys(t, execution, tab))
	}
}

// TestSaveName checks a download stays in the working directory whatever the file is called.
func TestSaveName(t *testing.T) {
	tests := []struct {
		name, file, id, want string
	}{
		{name: "plain", file: "ada.png", id: "f1", want: "ada.png"},
		{name: "path", file: "../../etc/passwd", id: "f1", want: "passwd"},
		{name: "windows path", file: `..\..\boot.ini`, id: "f1", want: "boot.ini"},
		{name: "empty", file: "", id: "f1", want: "f1"},
		{name: "dots", file: "..", id: "f1", want: "f1"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			if got := saveName(tt.file, tt.id); got != tt.want {
				t.Errorf("saveName(%q) = %q, want %q", tt.file, got, tt.want)
			}
		})
	}
}
{% endverbatim %}
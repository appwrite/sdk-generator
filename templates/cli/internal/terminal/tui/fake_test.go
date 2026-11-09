package tui

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"maps"
	"reflect"
	"runtime"
	"slices"
	"strings"
	"sync"
	"testing"
	"time"

	"charm.land/bubbles/v2/spinner"
	tea "charm.land/bubbletea/v2"
	"github.com/charmbracelet/x/ansi"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/appwrite"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/terminal/client"
){% verbatim %}

// fakeBackend is a project in memory: the resources of each list, by its ref, and what was
// asked of it.
type fakeBackend struct {
	mu    sync.Mutex
	lists map[string][]appwrite.Doc
	// errs fail the lists of the refs they are keyed by.
	errs map[string]error
	// asked are the options each list was read with, in order.
	asked    map[string][]appwrite.ListOptions
	deleted  []string
	executed []appwrite.Execution
	result   appwrite.Doc
	files    map[string]string
	// opened are the projects opened from the console, in order.
	opened []string
	// usage are the reads of the usage, and usageErr fails them.
	usage    []appwrite.UsageQuery
	usageErr error
	saved    []saved
}

// refKey names a ref in the fake's maps: its kind and its parents, as rows/main/orders.
func refKey(r appwrite.Ref) string {
	return strings.Join(append([]string{spec(r.Kind).many}, r.Parents...), "/")
}

func newFake() *fakeBackend {
	doc := appwrite.MustDoc
	return &fakeBackend{
		lists: map[string][]appwrite.Doc{
			"databases": {doc(`{"$id":"main","name":"main","type":"tablesdb","enabled":true,"$createdAt":"2026-01-02T10:00:00.000+00:00","$updatedAt":"2026-01-02T10:00:00.000+00:00"}`)},
			"tables/main": {doc(`{"$id":"orders","name":"orders","databaseId":"main","enabled":true,"rowSecurity":false,"$permissions":[],` +
				`"columns":[{"key":"customer","type":"string","status":"available","required":true,"array":false,"size":64},{"key":"total","type":"double","status":"available","required":false,"array":false}]}`)},
			"rows/main/orders": {
				doc(`{"$id":"o1","$sequence":"1","customer":"ada","total":12.5,"$createdAt":"2026-01-02T10:00:00.000+00:00","$updatedAt":"2026-01-02T10:00:00.000+00:00","$permissions":[]}`),
				doc(`{"$id":"o2","$sequence":"2","customer":"grace","total":null,"$createdAt":"2026-01-02T10:00:00.000+00:00","$updatedAt":"2026-01-02T10:00:00.000+00:00","$permissions":[]}`),
			},
			"columns/main/orders": {doc(`{"key":"customer","type":"string","status":"available","required":true,"array":false,"size":64}`)},
			"indexes/main/orders": {},
			"functions":           {doc(`{"$id":"hello","name":"hello","runtime":"node-22","enabled":true,"latestDeploymentStatus":"ready","deploymentId":"d1"}`)},
			"executions/hello":    {doc(`{"$id":"e1","status":"completed","trigger":"http","requestMethod":"GET","requestPath":"/","responseStatusCode":200,"duration":0.012,"responseBody":"{\"ok\":true}","logs":"hi","errors":"","$createdAt":"2026-01-02T10:00:00.000+00:00"}`)},
			"deployments/hello":   {doc(`{"$id":"d1","status":"ready","type":"manual","sourceSize":2048,"buildDuration":3,"$createdAt":"2026-01-02T10:00:00.000+00:00"}`)},
			"variables/hello":     {doc(`{"$id":"v1","key":"TOKEN","value":"","secret":true}`)},
			"sites":               {},
			"buckets":             {doc(`{"$id":"avatars","name":"avatars","enabled":true,"maximumFileSize":30000000,"encryption":true,"antivirus":true,"compression":"none"}`)},
			"files/avatars":       {doc(`{"$id":"f1","bucketId":"avatars","name":"ada.png","mimeType":"image/png","sizeOriginal":2048,"chunksTotal":1,"chunksUploaded":1}`)},
			"users":               {doc(`{"$id":"u1","name":"Ada","email":"ada@example.com","status":true,"emailVerification":true,"prefs":{"theme":"dark"}}`)},
			"sessions/u1":         {doc(`{"$id":"s1","userId":"u1","provider":"email","clientName":"Chrome","clientVersion":"130","osName":"macOS","countryName":"Netherlands","ip":"127.0.0.1"}`)},
			"memberships/u1":      {},
			"teams":               {doc(`{"$id":"t1","name":"admins","total":1}`)},
			"memberships/t1":      {},
			"messages":            {doc(`{"$id":"welcome","providerType":"email","status":"draft","topics":["news"],"deliveredTotal":0,"deliveryErrors":[],"data":{"subject":"welcome","content":"hello there"}}`)},
			"targets/welcome":     {},
			"topics":              {doc(`{"$id":"news","name":"news","emailTotal":1,"smsTotal":0,"pushTotal":0,"subscribe":["users"]}`)},
			"subscribers/news":    {doc(`{"$id":"ada","userName":"Ada","providerType":"email","topicId":"news","target":{"identifier":"ada@example.com"}}`)},
			"providers":           {doc(`{"$id":"mailer","name":"mailer","provider":"smtp","type":"email","enabled":true,"options":{"fromEmail":"shop@example.com"},"credentials":{"host":"smtp.example.com"}}`)},
			"targets/u1":          {doc(`{"$id":"t1","identifier":"ada@example.com","providerType":"email","userId":"u1"}`)},
			"organizations":       {appwrite.MustDoc(`{"$id":"org1","name":"acme","total":2}`)},
			"projects/org1":       {appwrite.MustDoc(`{"$id":"shop","name":"Shop","region":"fra","teamId":"org1"}`)},
		},
		errs:   map[string]error{},
		asked:  map[string][]appwrite.ListOptions{},
		result: appwrite.MustDoc(`{"$id":"e2","status":"completed","responseStatusCode":201,"duration":0.2,"responseBody":"created","requestMethod":"POST","requestPath":"/"}`),
		files:  map[string]string{"avatars/f1": "png bytes"},
	}
}

func (f *fakeBackend) List(_ context.Context, r appwrite.Ref, opts appwrite.ListOptions) (appwrite.Page, error) {
	f.mu.Lock()
	defer f.mu.Unlock()
	k := refKey(r)
	f.asked[k] = append(f.asked[k], opts)
	if err := f.errs[k]; err != nil {
		return appwrite.Page{}, err
	}
	docs, ok := f.lists[k]
	if !ok {
		return appwrite.Page{}, &appwrite.Error{Code: 404, Type: "general_route_not_found", Message: "no " + k}
	}
	var out []appwrite.Doc
	for _, d := range docs {
		if opts.Search == "" || strings.Contains(strings.ToLower(string(d.Raw)), strings.ToLower(opts.Search)) {
			out = append(out, d)
		}
	}
	total := len(out)
	start := 0
	if opts.After != "" {
		start = slices.IndexFunc(out, func(d appwrite.Doc) bool { return d.ID() == opts.After }) + 1
	}
	limit := opts.Limit
	if limit <= 0 {
		limit = appwrite.PageSize
	}
	end := min(len(out), start+limit)
	return appwrite.Page{Total: total, Docs: out[start:end]}, nil
}

func (f *fakeBackend) Count(ctx context.Context, r appwrite.Ref) (int, error) {
	p, err := f.List(ctx, r, appwrite.ListOptions{Limit: 1})
	return p.Total, err
}

func (f *fakeBackend) Get(_ context.Context, r appwrite.Ref, id string) (appwrite.Doc, error) {
	f.mu.Lock()
	defer f.mu.Unlock()
	for _, d := range f.lists[refKey(r)] {
		if d.ID() == id {
			return d, nil
		}
	}
	return appwrite.Doc{}, &appwrite.Error{Code: 404, Type: "document_not_found", Message: "not found"}
}

func (f *fakeBackend) Delete(_ context.Context, r appwrite.Ref, id string) error {
	f.mu.Lock()
	defer f.mu.Unlock()
	k := refKey(r)
	f.deleted = append(f.deleted, k+"/"+id)
	f.lists[k] = slices.DeleteFunc(slices.Clone(f.lists[k]), func(d appwrite.Doc) bool { return d.ID() == id })
	return nil
}

func (f *fakeBackend) Execute(_ context.Context, function string, e appwrite.Execution) (appwrite.Doc, error) {
	f.mu.Lock()
	defer f.mu.Unlock()
	f.executed = append(f.executed, e)
	k := "executions/" + function
	f.lists[k] = append([]appwrite.Doc{f.result}, f.lists[k]...)
	return f.result, nil
}

func (f *fakeBackend) Download(_ context.Context, bucket, file string, w io.Writer) (int64, error) {
	body, ok := f.files[bucket+"/"+file]
	if !ok {
		return 0, errors.New("no such file")
	}
	n, err := io.WriteString(w, body)
	return int64(n), err
}

func (f *fakeBackend) Ping(context.Context) (time.Duration, error) { return 3 * time.Millisecond, nil }

// Usage answers every metric with a point an hour ago and one now, the query noted.
func (f *fakeBackend) Usage(_ context.Context, q appwrite.UsageQuery) (map[string][]appwrite.Point, error) {
	f.mu.Lock()
	defer f.mu.Unlock()
	f.usage = append(f.usage, q)
	if f.usageErr != nil {
		return nil, f.usageErr
	}
	now := time.Now()
	out := map[string][]appwrite.Point{}
	if slices.Contains(q.Dimensions, "path") {
		for _, m := range q.Metrics {
			for _, r := range fakeRoutes {
				if q.Path != "" && r.path != q.Path && !strings.HasPrefix(r.path, q.Path+"/") {
					continue
				}
				out[m] = append(out[m], appwrite.Point{Value: r.requests, Dims: map[string]string{"path": r.path, "method": r.method}})
			}
		}
		return out, nil
	}
	if slices.Contains(q.Dimensions, "service") {
		for _, m := range q.Metrics {
			out[m] = []appwrite.Point{{Time: now, Value: 7, Dims: map[string]string{"service": "tablesdb"}}, {Time: now, Value: 3, Dims: map[string]string{"service": "users"}}}
		}
		return out, nil
	}
	for _, m := range q.Metrics {
		out[m] = []appwrite.Point{{Time: now.Add(-time.Hour), Value: 40}, {Time: now, Value: 2048}}
		if q.ByResource {
			out[m] = []appwrite.Point{{Time: now.Add(-time.Hour), Value: 40, ResourceID: "main"}, {Time: now, Value: 2048, ResourceID: "main"}}
		}
	}
	return out, nil
}

func (f *fakeBackend) Update(_ context.Context, r appwrite.Ref, id string, data map[string]any) (appwrite.Doc, error) {
	f.mu.Lock()
	defer f.mu.Unlock()
	k := refKey(r)
	f.saved = append(f.saved, saved{key: k, id: id, data: data})
	body, _ := json.Marshal(data)
	for i, d := range f.lists[k] {
		if d.ID() == id {
			merged := rowData(d)
			maps.Copy(merged, data)
			merged["$id"] = id
			raw, _ := json.Marshal(merged)
			f.lists[k][i] = appwrite.MustDoc(string(raw))
			return f.lists[k][i], nil
		}
	}
	return appwrite.Doc{}, &appwrite.Error{Code: 404, Type: "row_not_found", Message: "no row " + id + " " + string(body)}
}

func (f *fakeBackend) Create(_ context.Context, r appwrite.Ref, data map[string]any) (appwrite.Doc, error) {
	f.mu.Lock()
	defer f.mu.Unlock()
	k := refKey(r)
	f.saved = append(f.saved, saved{key: k, data: data})
	row := map[string]any{"$id": fmt.Sprintf("new%d", len(f.saved))}
	maps.Copy(row, data)
	raw, _ := json.Marshal(row)
	d := appwrite.MustDoc(string(raw))
	f.lists[k] = append(slices.Clone(f.lists[k]), d)
	return d, nil
}

// saved is a row the fake was asked to change, or to create when id is empty.
type saved struct {
	key, id string
	data    map[string]any
}

// fakeRoutes are the routes the fake's usage says were requested.
var fakeRoutes = []routeStat{
	{method: "POST", path: "/v1/tablesdb/main/tables/orders/rows", requests: 240},
	{method: "GET", path: "/v1/tablesdb/main", requests: 45},
	{method: "GET", path: "/v1/users", requests: 30},
}

// ForProject is the fake of another project: the same lists, with the project's id noted.
func (f *fakeBackend) ForProject(id string) backend {
	f.mu.Lock()
	defer f.mu.Unlock()
	f.opened = append(f.opened, id)
	return f
}

// testInfo is the connection the header shows in tests.
var testInfo = client.Info{Endpoint: "https://fra.cloud.appwrite.io/v1", Project: "shop", Name: "Shop", Version: "1.8.0", Cloud: true, RTT: 12 * time.Millisecond}

// newTestModel is the interface on a fake, at 120 by 40, before anything is loaded.
func newTestModel(t testing.TB, f *fakeBackend) model {
	t.Helper()
	m := newModel(context.Background(), f, testInfo)
	m, _ = update(t, m, tea.WindowSizeMsg{Width: 120, Height: 40})
	return m
}

// started is the interface once its first loads are in.
func started(t testing.TB, f *fakeBackend) model {
	t.Helper()
	m := newTestModel(t, f)
	return settle(t, m, m.Init())
}

func press(s string) tea.KeyPressMsg {
	switch s {
	case "enter":
		return tea.KeyPressMsg{Code: tea.KeyEnter}
	case "esc":
		return tea.KeyPressMsg{Code: tea.KeyEscape}
	case "tab":
		return tea.KeyPressMsg{Code: tea.KeyTab}
	case "shift+tab":
		return tea.KeyPressMsg{Code: tea.KeyTab, Mod: tea.ModShift}
	case "backspace":
		return tea.KeyPressMsg{Code: tea.KeyBackspace}
	case "down":
		return tea.KeyPressMsg{Code: tea.KeyDown}
	case "up":
		return tea.KeyPressMsg{Code: tea.KeyUp}
	}
	if ctrl, ok := strings.CutPrefix(s, "ctrl+"); ok {
		return tea.KeyPressMsg{Code: rune(ctrl[0]), Mod: tea.ModCtrl}
	}
	return tea.KeyPressMsg{Code: rune(s[0]), Text: s}
}

func update(t testing.TB, m model, msgs ...tea.Msg) (model, tea.Cmd) {
	t.Helper()
	var cmd tea.Cmd
	for _, msg := range msgs {
		var next tea.Model
		next, cmd = m.Update(msg)
		m = next.(model)
	}
	return m, cmd
}

// pressKeys presses keys one by one, settling what each starts.
func pressKeys(t testing.TB, m model, pressed ...string) model {
	t.Helper()
	for _, k := range pressed {
		var cmd tea.Cmd
		m, cmd = update(t, m, press(k))
		m = settle(t, m, cmd)
	}
	return m
}

func typeText(t testing.TB, m model, s string) model {
	t.Helper()
	for _, r := range s {
		m, _ = update(t, m, tea.KeyPressMsg{Code: r, Text: string(r)})
	}
	return m
}

// settle runs a command and feeds what it returns back in, until nothing more comes. Timers,
// the spinner and the refresh, are left out, as they would wait.
func settle(t testing.TB, m model, cmd tea.Cmd) model {
	t.Helper()
	for range 20 {
		msgs := run(cmd)
		if len(msgs) == 0 {
			return m
		}
		var cmds []tea.Cmd
		for _, msg := range msgs {
			var next tea.Cmd
			m, next = update(t, m, msg)
			cmds = append(cmds, next)
		}
		cmd = tea.Batch(cmds...)
	}
	return m
}

// run runs a command and the commands of a batch, returning their messages. A command that
// does not answer at once is a timer, and is dropped, one crTimer knows without waiting.
func run(cmd tea.Cmd) []tea.Msg {
	if cmd == nil || crTimer(cmd) {
		return nil
	}
	done := make(chan tea.Msg, 1)
	go func() { done <- cmd() }()
	var msg tea.Msg
	select {
	case msg = <-done:
	case <-time.After(200 * time.Millisecond):
		return nil
	}
	switch msg := msg.(type) {
	case nil, spinner.TickMsg, refreshTickMsg, statusClearMsg, tea.QuitMsg:
		return nil
	case tea.BatchMsg:
		var out []tea.Msg
		for _, c := range msg {
			out = append(out, run(c)...)
		}
		return out
	}
	if strings.HasSuffix(fmt.Sprintf("%T", msg), "setClipboardMsg") {
		return nil
	}
	return []tea.Msg{msg}
}

// onScreen is the text on screen, without its styles.
func onScreen(m model) string {
	return ansi.Strip(m.render())
}

// crTimers are the functions of the commands that wait for a timer: a tick of bubbletea, the
// refresh's, the status's and the spinner's, and the blink of a text input's cursor. Each waits
// longer than run does, so run and quits drop them at once rather than wait to drop them.
var crTimers = []string{"charm.land/bubbletea/v2.Tick.", "charm.land/bubbletea/v2.Every.", "charm.land/bubbles/v2/cursor.(*Model).Blink."}

// crTimer reports whether cmd waits for a timer, by the name of its function.
func crTimer(cmd tea.Cmd) bool {
	name := runtime.FuncForPC(reflect.ValueOf(cmd).Pointer()).Name()
	return slices.ContainsFunc(crTimers, func(prefix string) bool { return strings.HasPrefix(name, prefix) })
}

// quits reports whether a command quits the program.
func quits(cmd tea.Cmd) bool {
	if cmd == nil || crTimer(cmd) {
		return false
	}
	done := make(chan tea.Msg, 1)
	go func() { done <- cmd() }()
	select {
	case msg := <-done:
		if _, ok := msg.(tea.QuitMsg); ok {
			return true
		}
		if batch, ok := msg.(tea.BatchMsg); ok {
			return slices.ContainsFunc(batch, quits)
		}
	case <-time.After(50 * time.Millisecond):
	}
	return false
}
{% endverbatim %}
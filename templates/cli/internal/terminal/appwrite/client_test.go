package appwrite

import (
	"context"
	"encoding/json"
	"errors"
	"net/http"
	"net/http/httptest"
	"net/url"
	"slices"
	"strings"
	"testing"
	"time"
)

// server answers the requests of a test with handle, and is closed with the test.
func server(t *testing.T, handle http.HandlerFunc) *Client {
	t.Helper()
	s := httptest.NewServer(handle)
	t.Cleanup(s.Close)
	c, err := New(Options{Endpoint: s.URL + "/v1", Project: "shop", Key: "secret", UserAgent: "test"})
	if err != nil {
		t.Fatal(err)
	}
	return c
}

// queries are the queries a request sent, decoded, in order.
func queries(t *testing.T, r *http.Request) []query {
	t.Helper()
	var out []query
	for i := 0; ; i++ {
		v := r.URL.Query().Get("queries[" + string(rune('0'+i)) + "]")
		if v == "" {
			return out
		}
		var q query
		if err := json.Unmarshal([]byte(v), &q); err != nil {
			t.Fatalf("query %d %q: %v", i, v, err)
		}
		out = append(out, q)
	}
}

// TestList checks a list is asked for at its route with the project, the key, a limit, the
// cursor, the order and the search, and its answer decoded with its total.
func TestList(t *testing.T) {
	var got *http.Request
	c := server(t, func(w http.ResponseWriter, r *http.Request) {
		got = r
		_, _ = w.Write([]byte(`{"total":3,"executions":[{"$id":"e1","duration":0.5},{"$id":"e2"}]}`))
	})
	p, err := c.List(context.Background(), Ref{Kind: Executions, Parents: []string{"hello"}}, ListOptions{After: "e0"})
	if err != nil {
		t.Fatal(err)
	}
	if got.URL.Path != "/v1/functions/hello/executions" {
		t.Errorf("path %q", got.URL.Path)
	}
	if got.Header.Get("X-Appwrite-Project") != "shop" || got.Header.Get("X-Appwrite-Key") != "secret" || got.Header.Get("User-Agent") != "test" {
		t.Errorf("headers %v", got.Header)
	}
	qs := queries(t, got)
	want := []string{"limit", "cursorAfter", "orderDesc"}
	if len(qs) != len(want) {
		t.Fatalf("queries %+v, want %v", qs, want)
	}
	for i, q := range qs {
		if q.Method != want[i] {
			t.Errorf("query %d is %q, want %q", i, q.Method, want[i])
		}
	}
	if p.Total != 3 || len(p.Docs) != 2 || p.Docs[0].ID() != "e1" || p.Docs[0].Float("duration") != 0.5 {
		t.Errorf("page %+v", p)
	}
}

// TestListPaths checks each kind under parents is listed at its route, ids escaped.
func TestListPaths(t *testing.T) {
	tests := []struct {
		name string
		ref  Ref
		want string
	}{
		{name: "databases", ref: Ref{Kind: Databases}, want: "/v1/tablesdb"},
		{name: "rows", ref: Ref{Kind: Rows, Parents: []string{"main", "orders"}}, want: "/v1/tablesdb/main/tables/orders/rows"},
		{name: "escaped", ref: Ref{Kind: Files, Parents: []string{"a/b"}}, want: "/v1/storage/buckets/a%2Fb/files"},
		{name: "sessions", ref: Ref{Kind: Sessions, Parents: []string{"u1"}}, want: "/v1/users/u1/sessions"},
		{name: "site logs", ref: Ref{Kind: SiteLogs, Parents: []string{"web"}}, want: "/v1/sites/web/logs"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			var got string
			c := server(t, func(w http.ResponseWriter, r *http.Request) {
				got = r.URL.EscapedPath()
				_, _ = w.Write([]byte(`{"total":0}`))
			})
			if _, err := c.List(context.Background(), tt.ref, ListOptions{}); err != nil {
				t.Fatal(err)
			}
			if got != tt.want {
				t.Errorf("path %q, want %q", got, tt.want)
			}
		})
	}
}

// TestListWithoutQueries checks a kind the API does not page is asked for without queries, and
// one keyed rather than identified pages by an offset.
func TestListWithoutQueries(t *testing.T) {
	var got url.Values
	c := server(t, func(w http.ResponseWriter, r *http.Request) {
		got = r.URL.Query()
		_, _ = w.Write([]byte(`{"total":1,"sessions":[{"$id":"s1"}]}`))
	})
	if _, err := c.List(context.Background(), Ref{Kind: Sessions, Parents: []string{"u1"}}, ListOptions{After: "x", Search: "y"}); err != nil {
		t.Fatal(err)
	}
	if len(got) != 0 {
		t.Errorf("sessions were listed with %v, want no query", got)
	}
	if _, err := c.List(context.Background(), Ref{Kind: Columns, Parents: []string{"main", "orders"}}, ListOptions{After: "x", Offset: 100}); err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(got.Get("queries[1]"), `"offset"`) || strings.Contains(got.Encode(), "cursorAfter") {
		t.Errorf("columns were listed with %v, want an offset", got)
	}
}

// TestError checks a refusal is decoded into an Error with its code, type and message, and an
// answer that is not json still says its status.
func TestError(t *testing.T) {
	tests := []struct {
		name     string
		body     string
		status   int
		wantType string
		wantText string
	}{
		{name: "appwrite", status: 401, body: `{"message":"User (role: guests) missing scope (databases.read)","code":401,"type":"general_unauthorized_scope","version":"2.7.1"}`,
			wantType: "general_unauthorized_scope", wantText: "missing scope (databases.read) (401 general_unauthorized_scope)"},
		{name: "proxy", status: 502, body: `<html>bad gateway</html>`, wantType: "http", wantText: "Bad Gateway (502 http)"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			c := server(t, func(w http.ResponseWriter, _ *http.Request) {
				w.WriteHeader(tt.status)
				_, _ = w.Write([]byte(tt.body))
			})
			_, err := c.List(context.Background(), Ref{Kind: Databases}, ListOptions{})
			var e *Error
			if !errors.As(err, &e) {
				t.Fatalf("error %v is not an *Error", err)
			}
			if e.Code != tt.status || e.Type != tt.wantType || !IsCode(err, tt.status) || !IsType(err, tt.wantType) {
				t.Errorf("error %+v", e)
			}
			if !strings.Contains(err.Error(), tt.wantText) {
				t.Errorf("error %q does not say %q", err, tt.wantText)
			}
		})
	}
}

// TestConsoleSession checks a console session calls the console's routes as the console project
// and a project's routes in admin mode, with its cookie and no key.
func TestConsoleSession(t *testing.T) {
	var got []*http.Request
	s := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		got = append(got, r)
		_, _ = w.Write([]byte(`{"total":0}`))
	}))
	t.Cleanup(s.Close)
	c, err := New(Options{Endpoint: s.URL, Project: ConsoleProject, Cookie: "a_session_console=abc"})
	if err != nil {
		t.Fatal(err)
	}
	ctx := context.Background()
	if _, err := c.List(ctx, Ref{Kind: Projects, Parents: []string{"org1"}}, ListOptions{}); err != nil {
		t.Fatal(err)
	}
	if _, err := c.AsProject("shop").List(ctx, Ref{Kind: Databases}, ListOptions{}); err != nil {
		t.Fatal(err)
	}
	console, project := got[0], got[1]
	if console.URL.Path != "/v1/projects" || console.Header.Get("X-Appwrite-Project") != "console" || console.Header.Get("X-Appwrite-Mode") != "" {
		t.Errorf("console request %s %v", console.URL.Path, console.Header)
	}
	if !strings.Contains(console.URL.Query().Get("queries[0]"), `"teamId"`) {
		t.Errorf("projects were not filtered by their organisation: %v", console.URL.Query())
	}
	if project.Header.Get("X-Appwrite-Project") != "shop" || project.Header.Get("X-Appwrite-Mode") != "admin" || project.Header.Get("Cookie") != "a_session_console=abc" || project.Header.Get("X-Appwrite-Key") != "" {
		t.Errorf("project request %v", project.Header)
	}
}

// TestNormalizeEndpoint checks an endpoint is given /v1 when it has no path, and one that is not
// a url is refused.
func TestNormalizeEndpoint(t *testing.T) {
	tests := []struct {
		in, want string
		err      bool
	}{
		{in: "", want: DefaultEndpoint},
		{in: "https://appwrite.example.com", want: "https://appwrite.example.com/v1"},
		{in: "https://appwrite.example.com/", want: "https://appwrite.example.com/v1"},
		{in: "http://localhost:8080/v1/", want: "http://localhost:8080/v1"},
		{in: "appwrite.example.com", err: true},
		{in: "ftp://appwrite.example.com", err: true},
	}
	for _, tt := range tests {
		t.Run(tt.in, func(t *testing.T) {
			got, err := NormalizeEndpoint(tt.in)
			if (err != nil) != tt.err || got != tt.want {
				t.Errorf("NormalizeEndpoint(%q) = %q, %v", tt.in, got, err)
			}
		})
	}
}

// TestCloud checks Appwrite Cloud is told apart from a self-hosted instance, its regions too.
func TestCloud(t *testing.T) {
	tests := []struct {
		endpoint string
		want     bool
	}{
		{"https://cloud.appwrite.io/v1", true},
		{"https://fra.cloud.appwrite.io/v1", true},
		{"https://appwrite.example.com/v1", false},
		{"https://cloud.appwrite.io.example.com/v1", false},
	}
	for _, tt := range tests {
		c, err := New(Options{Endpoint: tt.endpoint})
		if err != nil {
			t.Fatal(err)
		}
		if c.Cloud() != tt.want {
			t.Errorf("Cloud() for %s = %v", tt.endpoint, !tt.want)
		}
	}
}

// TestDoc checks the fields of a resource read as their types, and a list's fields sort with the
// API's own last.
func TestDoc(t *testing.T) {
	d := MustDoc(`{"$id":"o1","name":"ada","total":12.5,"count":3,"ok":true,"tags":["a",1],"none":null,"at":"2026-01-02T10:00:00.000+00:00","$createdAt":""}`)
	if d.ID() != "o1" || d.Str("name") != "ada" || d.Float("total") != 12.5 || d.Int("count") != 3 || !d.Bool("ok") {
		t.Errorf("fields of %s", d.Raw)
	}
	if got := strings.Join(d.Strings("tags"), ","); got != "a,1" {
		t.Errorf("Strings = %q", got)
	}
	if d.Str("none") != "" || !d.Has("none") || d.Has("missing") {
		t.Error("null is not empty and present")
	}
	if d.Time("at").IsZero() || !d.Time("$createdAt").IsZero() {
		t.Error("times")
	}
	if got := strings.Join(d.Fields(), ","); got != "at,count,name,none,ok,tags,total,$createdAt,$id" {
		t.Errorf("Fields = %s", got)
	}
	if MustDoc(`{"key":"customer"}`).ID() != "customer" {
		t.Error("a column's id is its key")
	}
}

// TestUsage checks a read of the usage asks for its metrics, interval, window and resource, and
// decodes the points of each metric.
func TestUsage(t *testing.T) {
	var got url.Values
	c := server(t, func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/v1/usage/events" {
			t.Errorf("path %s", r.URL.Path)
		}
		got = r.URL.Query()
		_, _ = w.Write([]byte(`{"interval":"30m","metrics":[{"metric":"databases.operations.reads","points":[{"time":"2026-11-09T07:00:00+00:00","value":1200},{"time":"2026-11-09T08:30:00+00:00","value":3.5}]}]}`))
	})
	start := time.Date(2026, 11, 9, 6, 0, 0, 0, time.UTC)
	points, err := c.Usage(context.Background(), UsageQuery{Kind: UsageEvents, Metrics: []string{"databases.operations.reads"}, Interval: 30 * time.Minute, Start: start, ResourceType: "database", ResourceID: "main"})
	if err != nil {
		t.Fatal(err)
	}
	if got.Get("interval") != "30m" || got.Get("startAt") != "2026-11-09T06:00:00Z" || got["metrics[]"][0] != "databases.operations.reads" {
		t.Errorf("query %v", got)
	}
	if q := got["queries[]"]; len(q) != 2 || !strings.Contains(q[0], `"resourceType"`) || !strings.Contains(q[1], `["main"]`) {
		t.Errorf("filters %v", q)
	}
	p := points["databases.operations.reads"]
	if len(p) != 2 || p[0].Value != 1200 || p[1].Value != 3.5 || !p[1].Time.Equal(start.Add(150*time.Minute)) {
		t.Errorf("points %+v", p)
	}
	if _, err := c.Usage(context.Background(), UsageQuery{Kind: UsageEvents, Interval: 7 * time.Minute}); err == nil {
		t.Error("an interval the api does not take was asked for")
	}
}

// TestBuckets checks events sum into their buckets and leave the rest at zero, and a gauge keeps
// its level, from before the window too, until it is sampled again.
func TestBuckets(t *testing.T) {
	start := time.Date(2026, 11, 9, 0, 0, 0, 0, time.UTC)
	at := func(h int, v float64) Point { return Point{Time: start.Add(time.Duration(h) * time.Hour), Value: v} }
	tests := []struct {
		name   string
		points []Point
		gauge  bool
		want   []float64
	}{
		{name: "events", points: []Point{at(0, 1), at(0, 2), at(2, 5), at(9, 7)}, want: []float64{3, 0, 5, 0}},
		{name: "gauge", points: []Point{at(-3, 4), at(2, 9)}, gauge: true, want: []float64{4, 4, 9, 9}},
		{name: "gauge sampled late", points: []Point{at(1, 6)}, gauge: true, want: []float64{0, 6, 6, 6}},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			got := Bucketed(tt.points, start, time.Hour, 4, tt.gauge)
			if !slices.Equal(got, tt.want) {
				t.Errorf("Bucketed = %v, want %v", got, tt.want)
			}
		})
	}
}

// TestParseFilter checks each comparison, null, quotes and sort become the query the api takes,
// and text that is not a comparison is refused.
func TestParseFilter(t *testing.T) {
	tests := []struct {
		in   string
		want []string
		err  bool
	}{
		{in: "customer=ada", want: []string{`{"method":"equal","attribute":"customer","values":["ada"]}`}},
		{in: "total>=10 paid=true", want: []string{`{"method":"greaterThanEqual","attribute":"total","values":[10]}`, `{"method":"equal","attribute":"paid","values":[true]}`}},
		{in: "total<2.5", want: []string{`{"method":"lessThan","attribute":"total","values":[2.5]}`}},
		{in: `status!=null name~"lin us"`, want: []string{`{"method":"isNotNull","attribute":"status"}`, `{"method":"contains","attribute":"name","values":["lin us"]}`}},
		{in: `code="10"`, want: []string{`{"method":"equal","attribute":"code","values":["10"]}`}},
		{in: "status=null", want: []string{`{"method":"isNull","attribute":"status"}`}},
		{in: "sort:-total sort:customer", want: []string{`{"method":"orderDesc","attribute":"total"}`, `{"method":"orderAsc","attribute":"customer"}`}},
		{in: "customer=ada grace", err: true},
		{in: "sort:", err: true},
	}
	for _, tt := range tests {
		t.Run(tt.in, func(t *testing.T) {
			got, err := ParseFilter(tt.in)
			if (err != nil) != tt.err {
				t.Fatalf("ParseFilter(%q) error %v", tt.in, err)
			}
			if !tt.err && !slices.Equal(got, tt.want) {
				t.Errorf("ParseFilter(%q) = %v, want %v", tt.in, got, tt.want)
			}
		})
	}
	for text, want := range map[string]bool{"ada": false, "ada lovelace": false, "total>3": true, "sort:-total": true, "=x": false} {
		if IsFilter(text) != want {
			t.Errorf("IsFilter(%q) = %v", text, !want)
		}
	}
}

// TestWriteRow checks a row's values are changed with a patch of its data and a row created with
// an id the api picks, and a kind that cannot be written is refused before anything is sent.
func TestWriteRow(t *testing.T) {
	var method, path string
	var body map[string]any
	c := server(t, func(w http.ResponseWriter, r *http.Request) {
		method, path = r.Method, r.URL.Path
		_ = json.NewDecoder(r.Body).Decode(&body)
		_, _ = w.Write([]byte(`{"$id":"o9","customer":"ada"}`))
	})
	ref := Ref{Kind: Rows, Parents: []string{"main", "orders"}}
	if _, err := c.Update(context.Background(), ref, "o1", map[string]any{"customer": "ada"}); err != nil {
		t.Fatal(err)
	}
	if method != http.MethodPatch || path != "/v1/tablesdb/main/tables/orders/rows/o1" || body["data"].(map[string]any)["customer"] != "ada" {
		t.Errorf("update sent %s %s %v", method, path, body)
	}
	d, err := c.Create(context.Background(), ref, map[string]any{"customer": "ada"})
	if err != nil {
		t.Fatal(err)
	}
	if method != http.MethodPost || path != "/v1/tablesdb/main/tables/orders/rows" || body["rowId"] != "unique()" || d.ID() != "o9" {
		t.Errorf("create sent %s %s %v", method, path, body)
	}
	if _, err := c.Update(context.Background(), Ref{Kind: Users}, "u1", nil); err == nil {
		t.Error("a user was updated")
	}
}

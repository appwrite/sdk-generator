package cmd

import (
	"net/http"
	"net/http/httptest"
	"sort"
	"sync"
	"sync/atomic"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// Settings writes must go out ONE AT A TIME.
//
// They look independent -- a route per service -- and are not: every one
// read-modify-writes a nested field of the same `projects` row
// (Project/Services/Update.php:76). Sent together, each handler builds its new
// array from the snapshot it read, and updateDocument's re-read under
// `SELECT ... FOR UPDATE` does not save it, because the supplied nested array
// replaces the freshly read one. Two concurrent writes lose one of the changes.
//
// This was briefly made concurrent to match the TypeScript's Promise.all and
// to chase a slow settings push. It was wrong twice over: the row lock
// serialises them server-side anyway (~1.25s per waiter, measured), so it was
// not faster, and it opened a lost-update window that the sequential version
// does not have.
func TestSettingsWritesDoNotOverlap(t *testing.T) {
	var live atomic.Int32
	var overlapped atomic.Bool

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if live.Add(1) > 1 {
			overlapped.Store(true)
		}
		// Long enough that anything sent concurrently is still in flight.
		time.Sleep(20 * time.Millisecond)
		live.Add(-1)

		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	states := jsonx.NewObject()
	for _, service := range []string{
		"account", "avatars", "databases", "locale", "health", "storage",
		"teams", "users", "sites", "functions", "graphql", "messaging",
	} {
		states.Set(service, true)
	}

	context := &pushContext{api: client.New(server.URL, "test")}
	if err := context.applyEnabled("/project/services/", states); err != nil {
		t.Fatal(err)
	}

	if overlapped.Load() {
		t.Error("two settings writes were in flight at once, so one change can " +
			"overwrite the other on the shared project row")
	}
}

// The same holds for the auth security policies, which write the same row.
func TestSecurityPolicyWritesDoNotOverlap(t *testing.T) {
	var live atomic.Int32
	var overlapped atomic.Bool

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if live.Add(1) > 1 {
			overlapped.Store(true)
		}
		time.Sleep(20 * time.Millisecond)
		live.Add(-1)

		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	security := jsonx.NewObject()
	security.Set("duration", 3600)
	security.Set("limit", 100)
	security.Set("sessionsLimit", 10)
	security.Set("passwordDictionary", true)
	security.Set("passwordHistory", 5)
	security.Set("personalDataCheck", true)

	context := &pushContext{api: client.New(server.URL, "test")}
	if err := context.applySecurity(security); err != nil {
		t.Fatal(err)
	}

	if overlapped.Load() {
		t.Error("two policy writes were in flight at once")
	}
}

// Concurrency must not lose or duplicate a request: every entry is still sent,
// to its own route, exactly once.
func TestServiceStatusesAllArrive(t *testing.T) {
	var mutex sync.Mutex
	var paths []string

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		mutex.Lock()
		paths = append(paths, r.URL.Path)
		mutex.Unlock()
		w.Write([]byte(`{}`))
	}))
	defer server.Close()

	states := jsonx.NewObject()
	states.Set("account", true)
	states.Set("storage", false)
	states.Set("functions", true)

	context := &pushContext{api: client.New(server.URL, "test")}
	if err := context.applyEnabled("/project/services/", states); err != nil {
		t.Fatal(err)
	}

	sort.Strings(paths)
	want := []string{
		"/project/services/account",
		"/project/services/functions",
		"/project/services/storage",
	}

	if len(paths) != len(want) {
		t.Fatalf("paths = %v, want %v", paths, want)
	}
	for index, path := range want {
		if paths[index] != path {
			t.Errorf("paths = %v, want %v", paths, want)

			break
		}
	}
}

// A failing entry still fails the push. errgroup reports the first error, and
// swallowing it would report a settings push as successful when half of it did
// not land.
func TestServiceStatusesReportFailure(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusForbidden)
		w.Write([]byte(`{"message":"nope","code":403}`))
	}))
	defer server.Close()

	states := jsonx.NewObject()
	states.Set("account", true)

	context := &pushContext{api: client.New(server.URL, "test")}
	if err := context.applyEnabled("/project/services/", states); err == nil {
		t.Error("a rejected settings write was reported as success")
	}
}

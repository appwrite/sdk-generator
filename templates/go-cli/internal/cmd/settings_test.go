package cmd

import (
	"net/http"
	"net/http/httptest"
	"sort"
	"sync"
	"sync/atomic"
	"testing"
	"time"

	"github.com/appwrite/appwrite-cli-go/internal/client"
	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
)

// `push settings` was reported as "really really slow", and it was: twenty-two
// separate PATCHes -- twelve services, three protocols, seven auth methods --
// sent one after another, each waiting a full round trip. On a remote project
// that is most of a minute spent idle.
//
// The TypeScript sends each group with Promise.all (push.ts:1332). These are
// independent routes; serialising them bought a predictable request order and
// nothing else.
func TestServiceStatusesGoOutConcurrently(t *testing.T) {
	const (
		services = 12
		// How long a request waits for the rest to show up. Sent together they
		// all arrive and the gate opens at once; sent one at a time each pays
		// this in full, which is what makes the difference measurable.
		gateWait = 200 * time.Millisecond
	)

	gate := make(chan struct{})
	var arrived atomic.Int32

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if arrived.Add(1) == services {
			close(gate)
		}

		// Bounded, so a serialised run fails on the assertion below rather
		// than hanging the suite.
		select {
		case <-gate:
		case <-time.After(gateWait):
		}

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

	started := time.Now()
	if err := context.applyEnabled("/project/services/", states); err != nil {
		t.Fatal(err)
	}
	elapsed := time.Since(started)

	// Sent together this finishes as soon as the last one arrives. Sent one at
	// a time it cannot finish in under twelve gateWaits.
	if elapsed > 2*gateWait {
		t.Errorf("%d service writes took %s, so they are still going out one "+
			"at a time", services, elapsed.Round(time.Millisecond))
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

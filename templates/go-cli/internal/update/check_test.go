package update

import (
	"net/http"
	"net/http/httptest"
	"path/filepath"
	"testing"
	"time"
)

// The TypeScript CLI tells you on every command when a newer version exists.
// The Go port said nothing, so someone on an old build had no way to find out.
func TestANewerVersionIsReported(t *testing.T) {
	server := registry(t, `{"version":"2.0.0"}`, nil)

	checker := &Checker{
		RegistryURL: server,
		CachePath:   filepath.Join(t.TempDir(), "update-check.json"),
		Current:     "1.0.0",
	}

	if got := checker.UpdateAvailable(); got != "2.0.0" {
		t.Errorf("UpdateAvailable() = %q, want 2.0.0", got)
	}
}

// Nothing to say when the running version is current, and nothing to say when
// it is ahead -- a developer must not be told to "update" to something older.
func TestNoNoticeWhenCurrentOrAhead(t *testing.T) {
	for _, current := range []string{"2.0.0", "3.0.0", "(devel)"} {
		server := registry(t, `{"version":"2.0.0"}`, nil)

		checker := &Checker{
			RegistryURL: server,
			CachePath:   filepath.Join(t.TempDir(), "update-check.json"),
			Current:     current,
		}

		if got := checker.UpdateAvailable(); got != "" {
			t.Errorf("running %s was told to update to %q", current, got)
		}
	}
}

// Startup is the reason this binary exists. A cached answer must not cost a
// request, or every command pays for the check.
func TestAFreshCacheMakesNoRequest(t *testing.T) {
	requests := 0
	server := registry(t, `{"version":"2.0.0"}`, &requests)
	path := filepath.Join(t.TempDir(), "update-check.json")

	checker := &Checker{RegistryURL: server, CachePath: path, Current: "1.0.0"}

	if got := checker.UpdateAvailable(); got != "2.0.0" {
		t.Fatalf("first check = %q", got)
	}
	if got := checker.UpdateAvailable(); got != "2.0.0" {
		t.Fatalf("second check = %q", got)
	}

	if requests != 1 {
		t.Errorf("made %d requests, want the second answered from cache", requests)
	}
}

// Past the interval it asks again, so a release is noticed within a day.
func TestAStaleCacheIsRefreshed(t *testing.T) {
	requests := 0
	server := registry(t, `{"version":"2.0.0"}`, &requests)
	path := filepath.Join(t.TempDir(), "update-check.json")

	now := time.Now()
	checker := &Checker{
		RegistryURL: server, CachePath: path, Current: "1.0.0",
		Now: func() time.Time { return now },
	}
	checker.UpdateAvailable()

	now = now.Add(Interval + time.Minute)
	checker.UpdateAvailable()

	if requests != 2 {
		t.Errorf("made %d requests, want the stale cache refreshed", requests)
	}
}

// A registry that is down, slow or garbled must not change what the command
// was asked to do.
func TestAFailedCheckIsSilent(t *testing.T) {
	broken := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusInternalServerError)
	}))
	defer broken.Close()

	checker := &Checker{
		RegistryURL: broken.URL,
		CachePath:   filepath.Join(t.TempDir(), "update-check.json"),
		Current:     "1.0.0",
	}

	if got := checker.UpdateAvailable(); got != "" {
		t.Errorf("a failed check reported %q", got)
	}
}

// Opting out has to actually stop the request, not just hide the notice.
func TestTheCheckCanBeTurnedOff(t *testing.T) {
	requests := 0
	server := registry(t, `{"version":"2.0.0"}`, &requests)
	t.Setenv(DisableEnvironmentVariable, "1")

	checker := &Checker{
		RegistryURL: server,
		CachePath:   filepath.Join(t.TempDir(), "update-check.json"),
		Current:     "1.0.0",
	}

	if got := checker.UpdateAvailable(); got != "" {
		t.Errorf("reported %q with the check disabled", got)
	}
	if requests != 0 {
		t.Errorf("made %d requests with the check disabled", requests)
	}
}

// Version ordering, including the case the preview builds live in: a release
// outranks a pre-release carrying the same numbers.
func TestCompareOrdersVersions(t *testing.T) {
	for _, probe := range []struct {
		a, b string
		want int
	}{
		{"1.0.0", "2.0.0", -1},
		{"2.0.0", "1.0.0", 1},
		{"1.0.0", "1.0.0", 0},
		{"1.2.0", "1.10.0", -1},
		{"v1.0.0", "1.0.0", 0},
		{"0.6.0-preview", "0.6.0", -1},
		{"0.6.0", "0.6.0-preview", 1},
		{"0.6.0-preview", "0.6.1-preview", -1},
		{"(devel)", "9.9.9", 1},
	} {
		if got := Compare(probe.a, probe.b); got != probe.want {
			t.Errorf("Compare(%q, %q) = %d, want %d", probe.a, probe.b, got, probe.want)
		}
	}
}

// registry serves one npm-shaped response and optionally counts requests.
func registry(t *testing.T, body string, requests *int) string {
	t.Helper()

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if requests != nil {
			*requests++
		}
		w.Header().Set("content-type", "application/json")
		w.Write([]byte(body))
	}))
	t.Cleanup(server.Close)

	return server.URL
}

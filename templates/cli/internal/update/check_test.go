package update

import (
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"runtime"
	"strings"
	"testing"
	"time"
)

// Every command reports when a newer version exists. Without it someone on an
// old build has no way to find out.
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

func TestExplicitReleaseChannelSelection(t *testing.T) {
	for _, test := range []struct {
		name         string
		current      string
		latest       string
		next         string
		stableOnly   bool
		want         string
		wantNextGets int
	}{
		{name: "stable ignores next", current: "1.0.0", latest: "2.0.0", next: "3.0.0-rc.1", want: "2.0.0"},
		{name: "candidate follows next", current: "26.0.0-rc.1", latest: "25.1.0", next: "26.0.0-rc.2", want: "26.0.0-rc.2", wantNextGets: 1},
		{name: "final outranks candidate", current: "26.0.0-rc.1", latest: "26.0.0", next: "26.0.0-rc.2", want: "26.0.0", wantNextGets: 1},
		{name: "next survives latest failure", current: "26.0.0-rc.1", next: "26.0.0-rc.2", want: "26.0.0-rc.2", wantNextGets: 1},
		{name: "stable-only package manager", current: "26.0.0-rc.1", latest: "25.1.0", next: "26.0.0-rc.2", stableOnly: true, want: "25.1.0"},
	} {
		t.Run(test.name, func(t *testing.T) {
			nextGets := 0
			checker := &Checker{
				RegistryURL:           registry(t, registryPayload(test.latest), nil),
				PrereleaseRegistryURL: registry(t, registryPayload(test.next), &nextGets),
				CachePath:             filepath.Join(t.TempDir(), "update-check.json"),
				Current:               test.current,
			}

			var got string
			if test.stableOnly {
				got = checker.ExplicitStable()
			} else {
				got = checker.Explicit()
			}
			if got != test.want {
				t.Errorf("resolved %q, want %q", got, test.want)
			}
			if nextGets != test.wantNextGets {
				t.Errorf("next requests = %d, want %d", nextGets, test.wantNextGets)
			}
		})
	}
}

func TestAStableCacheDoesNotMaskThePrereleaseChannel(t *testing.T) {
	latestRequests := 0
	nextRequests := 0
	path := filepath.Join(t.TempDir(), "update-check.json")
	latest := registry(t, `{"version":"25.1.0"}`, &latestRequests)
	next := registry(t, `{"version":"26.0.0-rc.2"}`, &nextRequests)

	stable := &Checker{
		RegistryURL: latest, PrereleaseRegistryURL: next,
		CachePath: path, Current: "25.0.0",
	}
	if got := stable.Latest(time.Second); got != "25.1.0" {
		t.Fatalf("stable lookup = %q, want 25.1.0", got)
	}

	preview := &Checker{
		RegistryURL: latest, PrereleaseRegistryURL: next,
		CachePath: path, Current: "26.0.0-rc.1",
	}
	if got := preview.Latest(time.Second); got != "26.0.0-rc.2" {
		t.Errorf("prerelease lookup reused the stable cache: %q", got)
	}
	if latestRequests != 2 || nextRequests != 1 {
		t.Errorf("requests latest=%d next=%d, want latest=2 next=1", latestRequests, nextRequests)
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
	version := "1.0.0"
	requests := 0
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		requests++
		w.Header().Set("content-type", "application/json")
		w.Write([]byte(`{"version":"` + version + `"}`))
	}))
	defer server.Close()

	checker := &Checker{
		RegistryURL: server.URL,
		CachePath:   filepath.Join(t.TempDir(), "update-check.json"),
		Current:     "1.0.0",
	}

	// The second run is silent -- that is the notice throttle, covered
	// separately. What matters here is that it did not go to the network.
	checker.UpdateAvailable()
	checker.UpdateAvailable()

	if requests != 1 {
		t.Errorf("made %d requests, want the second answered from cache", requests)
	}

	// An explicit update refreshes even a fresh background cache.
	version = "2.0.0"
	if got := checker.ExplicitStable(); got != "2.0.0" {
		t.Errorf("ExplicitStable() = %q, want newly published 2.0.0", got)
	}
	if requests != 2 {
		t.Errorf("made %d requests, want explicit lookup to refresh the cache", requests)
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
		{"26.0.0-rc.1", "26.0.0-rc.2", -1},
		{"(devel)", "9.9.9", 1},
	} {
		if got := Compare(probe.a, probe.b); got != probe.want {
			t.Errorf("Compare(%q, %q) = %d, want %d", probe.a, probe.b, got, probe.want)
		}
	}
}

// registry serves one npm-shaped response and optionally counts requests.
func registryPayload(version string) string {
	if version == "" {
		return ""
	}

	return `{"version":"` + version + `"}`
}

func registry(t *testing.T, body string, requests *int) string {
	t.Helper()

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if requests != nil {
			*requests++
		}
		if body == "" {
			w.WriteHeader(http.StatusInternalServerError)

			return
		}
		w.Header().Set("content-type", "application/json")
		w.Write([]byte(body))
	}))
	t.Cleanup(server.Close)

	return server.URL
}

// The lookup was cached and the telling was not, so the notice printed on
// every single command. That is nagging, not informing.
func TestTheNoticeIsShownOncePerInterval(t *testing.T) {
	server := registry(t, `{"version":"2.0.0"}`, nil)
	path := filepath.Join(t.TempDir(), "update-check.json")

	now := time.Now()
	checker := &Checker{
		RegistryURL: server, CachePath: path, Current: "1.0.0",
		Now: func() time.Time { return now },
	}

	if got := checker.UpdateAvailable(); got != "2.0.0" {
		t.Fatalf("first run = %q, want the notice", got)
	}
	for run := range 5 {
		if got := checker.UpdateAvailable(); got != "" {
			t.Errorf("run %d repeated the notice: %q", run+2, got)
		}
	}

	// A day later it is worth saying again -- the user may have forgotten, and
	// there may be a newer version still.
	now = now.Add(Interval + time.Minute)
	if got := checker.UpdateAvailable(); got != "2.0.0" {
		t.Errorf("after the interval = %q, want the notice again", got)
	}
}

// The notice needs a blank line after it, or the command's own output starts
// on the next line and the notice reads as a heading for it.
func TestTheNoticeIsSetOffFromTheOutput(t *testing.T) {
	notice := Notice("appwrite", "1.0.0", "2.0.0")

	if !strings.HasPrefix(notice, "\n") {
		t.Errorf("no blank line before the notice: %q", notice)
	}
	if !strings.HasSuffix(notice, "\n\n") {
		t.Errorf("no blank line after the notice: %q", notice)
	}
	for _, want := range []string{"1.0.0", "2.0.0", "appwrite update"} {
		if !strings.Contains(notice, want) {
			t.Errorf("notice does not mention %q: %q", want, notice)
		}
	}
}

// The cache lives in ~/.appwrite, beside prefs.json and the tokens in it. This
// check runs from PersistentPreRun on every command, so on a fresh machine it is
// what creates that directory -- `appwrite --version` gets there long before
// `login` does. Since MkdirAll never tightens a directory that already exists,
// whatever mode it picks is the mode the credential store keeps for good.
func TestTheCacheDirectoryIsNotWorldReadable(t *testing.T) {
	if runtime.GOOS == "windows" {
		t.Skip("POSIX permission bits")
	}

	server := registry(t, `{"version":"2.0.0"}`, nil)
	directory := filepath.Join(t.TempDir(), ".appwrite")
	checker := &Checker{
		RegistryURL: server,
		CachePath:   filepath.Join(directory, "update-check.json"),
		Current:     "1.0.0",
	}

	checker.UpdateAvailable()

	info, err := os.Stat(directory)
	if err != nil {
		t.Fatalf("the cache directory was never created: %v", err)
	}
	if got := info.Mode().Perm(); got != 0o700 {
		t.Errorf("%s has mode %04o, want 0700 -- it holds credentials", directory, got)
	}
}

package update

import (
	"context"
	"encoding/json"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	"github.com/charmbracelet/lipgloss"
)

// Tells the user on every command when a newer version exists.
//
// Startup time is the binding constraint, so the check must not spend that
// budget. It reads a cache and only reaches the network when that is stale, at
// most once a day and with a short timeout.

const (
	// Interval matches UPDATE_CHECK_INTERVAL_MS.
	Interval = 24 * time.Hour

	// startupTimeout bounds the once-a-day refresh. One second: anything longer
	// is a visible fraction of the whole startup budget.
	startupTimeout = time.Second

	// explicitTimeout is for `update`, where the user is waiting for an answer
	// about versions and a slow reply beats no reply.
	explicitTimeout = 5 * time.Second

	// DisableEnvironmentVariable turns the check off entirely.
	DisableEnvironmentVariable = "APPWRITE_NO_UPDATE_CHECK"
)

// Checker looks up the newest published version.
type Checker struct {
	// RegistryURL is the npm endpoint for the package's latest release.
	RegistryURL string
	// PrereleaseRegistryURL is the npm endpoint for the package's next release.
	PrereleaseRegistryURL string
	// CachePath is where the last answer is remembered.
	CachePath string
	// Current is the running version.
	Current string
	// Now is overridable so a test does not depend on the clock.
	Now func() time.Time
}

// cache is what gets written between runs.
//
// checkedAt and notifiedAt are separate on purpose. The first throttles the
// network lookup; the second throttles the message. Without it the notice
// printed on every single command -- the lookup was cached, the telling was
// not -- which is nagging rather than informing.
type cache struct {
	CheckedAt         string `json:"checkedAt"`
	Latest            string `json:"latest"`
	Next              string `json:"next,omitempty"`
	PrereleaseChecked bool   `json:"prereleaseChecked,omitempty"`
	NotifiedAt        string `json:"notifiedAt,omitempty"`
}

type releases struct {
	Latest string
	Next   string
}

// Latest returns the newest published version, from cache when it is fresh.
//
// An error is never worth surfacing: a failed update check must not change what
// the command was asked to do. Callers get "" and carry on.
func (c *Checker) Latest(timeout time.Duration) string {
	includePrerelease := IsPrerelease(c.Current) && c.PrereleaseRegistryURL != ""
	available, _ := c.resolve(timeout, includePrerelease, true)

	return available.newest(includePrerelease)
}

// Stable returns the newest stable release, even when Current is a prerelease.
func (c *Checker) Stable(timeout time.Duration) string {
	available, _ := c.resolve(timeout, false, true)

	return available.Latest
}

// resolve returns the newest version and the cache entry it came from,
// refreshing the entry when it is stale.
func (c *Checker) resolve(
	timeout time.Duration,
	includePrerelease bool,
	useFreshCache bool,
) (releases, cache) {
	if os.Getenv(DisableEnvironmentVariable) != "" {
		return releases{}, cache{}
	}

	stored, fresh := c.read()
	if useFreshCache && fresh && (!includePrerelease || stored.PrereleaseChecked) {
		return stored.releases(), stored
	}

	fetched := c.fetch(timeout, includePrerelease)
	if fetched.Latest == "" && fetched.Next == "" {
		// Stale beats nothing: a version from yesterday still answers the
		// question, and the network may be down for a while.
		return stored.releases(), stored
	}
	if fetched.Latest != "" {
		stored.Latest = fetched.Latest
	}
	if fetched.Next != "" {
		stored.Next = fetched.Next
	}

	stored.PrereleaseChecked = includePrerelease && fetched.Next != ""
	if fetched.Latest != "" {
		stored.CheckedAt = c.now().UTC().Format(time.RFC3339)
	}
	c.write(stored)

	return stored.releases(), stored
}

// UpdateAvailable reports the newer version, or "" when there is none or when
// the user has already been told within the interval.
func (c *Checker) UpdateAvailable() string {
	includePrerelease := IsPrerelease(c.Current) && c.PrereleaseRegistryURL != ""
	available, stored := c.resolve(startupTimeout, includePrerelease, true)
	latest := available.newest(includePrerelease)
	if latest == "" || Compare(c.Current, latest) >= 0 {
		return ""
	}

	if withinInterval(stored.NotifiedAt, c.now()) {
		return ""
	}

	stored.NotifiedAt = c.now().UTC().Format(time.RFC3339)
	c.write(stored)

	return latest
}

// Explicit refreshes the registry with a longer timeout. A user who asked to
// update should see a release published since the background cache was filled.
func (c *Checker) Explicit() string {
	includePrerelease := IsPrerelease(c.Current) && c.PrereleaseRegistryURL != ""
	available, _ := c.resolve(explicitTimeout, includePrerelease, false)

	return available.newest(includePrerelease)
}

// ExplicitStable is the stable-only answer used by package managers that do
// not publish prereleases.
func (c *Checker) ExplicitStable() string {
	available, _ := c.resolve(explicitTimeout, false, false)

	return available.Latest
}

func (entry cache) releases() releases {
	return releases{Latest: entry.Latest, Next: entry.Next}
}

func (available releases) newest(includePrerelease bool) string {
	if !includePrerelease || available.Next == "" {
		return available.Latest
	}
	if available.Latest == "" || Compare(available.Latest, available.Next) < 0 {
		return available.Next
	}

	return available.Latest
}

func (c *Checker) read() (cache, bool) {
	stored := cache{}
	contents, err := os.ReadFile(c.CachePath)
	if err != nil {
		return stored, false
	}
	if err := json.Unmarshal(contents, &stored); err != nil {
		return cache{}, false
	}

	return stored, withinInterval(stored.CheckedAt, c.now())
}

// withinInterval reports whether a stored RFC3339 stamp is less than a day old.
func withinInterval(stamp string, now time.Time) bool {
	if strings.TrimSpace(stamp) == "" {
		return false
	}

	parsed, err := time.Parse(time.RFC3339, stamp)
	if err != nil {
		return false
	}

	return now.Sub(parsed) < Interval
}

func (c *Checker) write(entry cache) {
	if c.CachePath == "" {
		return
	}
	// 0700, not 0755: this is the same directory that holds prefs.json, and so
	// access and refresh tokens. MkdirAll never tightens a directory that already
	// exists, and this runs from PersistentPreRun on every command -- so a 0755
	// here wins the race on a fresh machine and config's 0700 never applies.
	if err := os.MkdirAll(filepath.Dir(c.CachePath), 0o700); err != nil {
		return
	}

	contents, err := json.Marshal(entry)
	if err != nil {
		return
	}

	// Best effort throughout: a cache that cannot be written costs one request
	// per run, which is not worth failing a command over.
	_ = os.WriteFile(c.CachePath, contents, 0o600)
}

func (c *Checker) fetch(timeout time.Duration, includePrerelease bool) releases {
	if c.RegistryURL == "" {
		return releases{}
	}

	ctx, cancel := context.WithTimeout(context.Background(), timeout)
	defer cancel()

	type result struct {
		prerelease bool
		version    string
	}

	urls := []struct {
		prerelease bool
		url        string
	}{{url: c.RegistryURL}}
	if includePrerelease && c.PrereleaseRegistryURL != "" {
		urls = append(urls, struct {
			prerelease bool
			url        string
		}{prerelease: true, url: c.PrereleaseRegistryURL})
	}

	results := make(chan result, len(urls))
	for _, endpoint := range urls {
		go func() {
			results <- result{
				prerelease: endpoint.prerelease,
				version:    fetch(ctx, endpoint.url),
			}
		}()
	}

	available := releases{}
	for range urls {
		select {
		case fetched := <-results:
			if fetched.prerelease {
				available.Next = fetched.version
			} else {
				available.Latest = fetched.version
			}
		case <-ctx.Done():
			return available
		}
	}

	return available
}

func fetch(ctx context.Context, url string) string {
	request, err := http.NewRequestWithContext(ctx, http.MethodGet, url, nil)
	if err != nil {
		return ""
	}
	request.Header.Set("accept", "application/json")

	response, err := http.DefaultClient.Do(request)
	if err != nil {
		return ""
	}
	defer response.Body.Close()

	if response.StatusCode != http.StatusOK {
		return ""
	}

	var payload struct {
		Version string `json:"version"`
	}
	if err := json.NewDecoder(response.Body).Decode(&payload); err != nil {
		return ""
	}

	return payload.Version
}

func (c *Checker) now() time.Time {
	if c.Now != nil {
		return c.Now()
	}

	return time.Now()
}

// Compare orders two semantic versions: -1 when a is older, 0 when they match,
// 1 when a is newer.
//
// A build with no version stamped -- `(devel)`, what a
// `go build` from source produces -- compares as newer than everything, so a
// developer is never nagged to "update" to a release older than their tree.
func Compare(a, b string) int {
	if isDevelopment(a) {
		return 1
	}
	if isDevelopment(b) {
		return -1
	}

	left, right := parts(a), parts(b)
	for index := range 3 {
		switch {
		case left[index] < right[index]:
			return -1
		case left[index] > right[index]:
			return 1
		}
	}

	// A release outranks any pre-release of the same numbers: 1.0.0 beats
	// 1.0.0-preview, which is what someone on a preview build wants to hear.
	leftPre, rightPre := prerelease(a), prerelease(b)
	switch {
	case leftPre == "" && rightPre != "":
		return 1
	case leftPre != "" && rightPre == "":
		return -1
	case leftPre < rightPre:
		return -1
	case leftPre > rightPre:
		return 1
	}

	return 0
}

// IsPrerelease reports whether a semantic version carries a prerelease suffix.
func IsPrerelease(version string) bool { return prerelease(version) != "" }

func isDevelopment(version string) bool {
	trimmed := strings.TrimSpace(version)

	return trimmed == "" || strings.Contains(trimmed, "devel")
}

// parts returns the major, minor and patch numbers, treating anything
// unparseable as zero.
func parts(version string) [3]int {
	numbers := [3]int{}

	core, _, _ := strings.Cut(strings.TrimPrefix(strings.TrimSpace(version), "v"), "-")
	for index, field := range strings.SplitN(core, ".", 3) {
		if index > 2 {
			break
		}
		numbers[index], _ = strconv.Atoi(field)
	}

	return numbers
}

func prerelease(version string) string {
	_, suffix, _ := strings.Cut(strings.TrimPrefix(strings.TrimSpace(version), "v"), "-")

	return suffix
}

var (
	// The same yellow and cyan internal/output uses, so the notice looks like
	// the rest of the CLI rather than like something bolted on.
	noticeStyle = lipgloss.NewStyle().Foreground(lipgloss.Color("3"))
	hintStyle   = lipgloss.NewStyle().Foreground(lipgloss.Color("6"))
	boldStyle   = lipgloss.NewStyle().Bold(true)
)

// Notice is what the user reads when a newer version exists.
//
// Blank lines top and bottom. Without the trailing one the command's own output
// starts on the line after the hint, and the notice reads as a heading for it.
func Notice(executable, current, latest string) string {
	return "\n" +
		noticeStyle.Render("⚠️  A newer version is available: "+
			boldStyle.Render(current)+" "+
			boldStyle.Render("→")+" "+
			boldStyle.Render(latest)) + "\n" +
		hintStyle.Render("💡 Run '"+boldStyle.Render(executable+" update")+
			"' to update to the latest version.") + "\n\n"
}

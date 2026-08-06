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
// The constraint the TypeScript does not have: startup time is the reason this
// CLI exists, so the check must not spend that budget. It reads a cache and only
// reaches the network when that is stale, at most once a day and with a short
// timeout.

const (
	// Interval matches UPDATE_CHECK_INTERVAL_MS.
	Interval = 24 * time.Hour

	// startupTimeout bounds the once-a-day refresh. The TypeScript allows
	// itself five seconds; that is a fifth of a second per millisecond of this
	// CLI's whole startup budget, so it gets one.
	startupTimeout = time.Second

	// explicitTimeout is for `--version`, where the user is waiting for an
	// answer about versions and a slow reply beats no reply.
	explicitTimeout = 5 * time.Second

	// DisableEnvironmentVariable turns the check off entirely.
	DisableEnvironmentVariable = "APPWRITE_NO_UPDATE_CHECK"
)

// Checker looks up the newest published version.
type Checker struct {
	// RegistryURL is the npm endpoint for the package's latest release.
	RegistryURL string
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
	CheckedAt  string `json:"checkedAt"`
	Latest     string `json:"latest"`
	NotifiedAt string `json:"notifiedAt,omitempty"`
}

// Latest returns the newest published version, from cache when it is fresh.
//
// An error is never worth surfacing: a failed update check must not change what
// the command was asked to do. Callers get "" and carry on.
func (c *Checker) Latest(timeout time.Duration) string {
	latest, _ := c.resolve(timeout)

	return latest
}

// resolve returns the newest version and the cache entry it came from,
// refreshing the entry when it is stale.
func (c *Checker) resolve(timeout time.Duration) (string, cache) {
	if os.Getenv(DisableEnvironmentVariable) != "" {
		return "", cache{}
	}

	stored, fresh := c.read()
	if fresh {
		return stored.Latest, stored
	}

	fetched := c.fetch(timeout)
	if fetched == "" {
		// Stale beats nothing: a version from yesterday still answers the
		// question, and the network may be down for a while.
		return stored.Latest, stored
	}

	stored.Latest = fetched
	stored.CheckedAt = c.now().UTC().Format(time.RFC3339)
	c.write(stored)

	return fetched, stored
}

// UpdateAvailable reports the newer version, or "" when there is none or when
// the user has already been told within the interval.
func (c *Checker) UpdateAvailable() string {
	latest, stored := c.resolve(startupTimeout)
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

// Explicit is the `--version` path: a longer timeout, and the answer either
// way rather than only when an update exists.
func (c *Checker) Explicit() string { return c.Latest(explicitTimeout) }

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

func (c *Checker) fetch(timeout time.Duration) string {
	if c.RegistryURL == "" {
		return ""
	}

	ctx, cancel := context.WithTimeout(context.Background(), timeout)
	defer cancel()

	request, err := http.NewRequestWithContext(ctx, http.MethodGet, c.RegistryURL, nil)
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

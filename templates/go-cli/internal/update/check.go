package update

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)

// Ports the update check in templates/cli/cli.ts.twig and the cache helpers in
// lib/utils.ts.
//
// The TypeScript CLI tells you on every command when a newer version exists.
// The Go port did not, so someone on an old build had no way to find out short
// of running `update` on a hunch. This restores it.
//
// The constraint the TypeScript does not have: startup is the reason this CLI
// exists -- 5ms against 206ms -- so the check must not spend that budget. It
// reads a cache, and only reaches the network when the cache is stale, at most
// once a day and with a short timeout.

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
type cache struct {
	CheckedAt string `json:"checkedAt"`
	Latest    string `json:"latest"`
}

// Latest returns the newest published version, from cache when it is fresh.
//
// An error is never worth surfacing: a failed update check must not change what
// the command was asked to do. Callers get "" and carry on.
func (c *Checker) Latest(timeout time.Duration) string {
	if os.Getenv(DisableEnvironmentVariable) != "" {
		return ""
	}

	stored, fresh := c.read()
	if fresh {
		return stored.Latest
	}

	latest := c.fetch(timeout)
	if latest == "" {
		// Stale beats nothing: a version from yesterday still answers the
		// question, and the network may be down for a while.
		return stored.Latest
	}
	c.write(latest)

	return latest
}

// UpdateAvailable reports the newer version, or "" when there is none.
func (c *Checker) UpdateAvailable() string {
	latest := c.Latest(startupTimeout)
	if latest == "" || Compare(c.Current, latest) >= 0 {
		return ""
	}

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

	checkedAt, err := time.Parse(time.RFC3339, stored.CheckedAt)
	if err != nil {
		return stored, false
	}

	return stored, c.now().Sub(checkedAt) < Interval
}

func (c *Checker) write(latest string) {
	if c.CachePath == "" {
		return
	}
	if err := os.MkdirAll(filepath.Dir(c.CachePath), 0o755); err != nil {
		return
	}

	contents, err := json.Marshal(cache{
		CheckedAt: c.now().UTC().Format(time.RFC3339),
		Latest:    latest,
	})
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
// Ports compareVersions. A build with no version stamped -- `(devel)`, what a
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

// Notice is what the user reads when a newer version exists.
func Notice(executable, current, latest string) string {
	return fmt.Sprintf(
		"\n⚠️  A newer version is available: %s → %s\n"+
			"💡 Run '%s update' to update to the latest version.\n",
		current, latest, executable)
}

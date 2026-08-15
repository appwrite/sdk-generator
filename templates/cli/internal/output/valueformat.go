package output

import (
	"fmt"
	"math"
	"regexp"
	"strings"
	"time"
)

// Implements duration humanising, timestamp formatting, and the key-aware
// dispatch around those scalar values.
//
// These are pure string functions and deliberately apply no colour. Dimming the
// parenthesised part emits nothing when stdout is not a terminal, so the plain
// text is the real contract, and the pinned baselines in
// testdata/valueformat.json are captured with FORCE_COLOR=0. Styling stays in
// the renderer, where the rest of it lives.

// now is a variable so tests can freeze it.
//
// Relative time is computed against the wall clock, so a test that used the
// real one would pass today and fail tomorrow. The baseline pins the same
// instant the capture script used.
var now = time.Now

// HumanizeSeconds renders a duration as at most two units, coarsest first.
//
// Returns "" for anything not worth rendering, which is what tells the caller
// to print the raw number alone. Note that this includes durations under half
// a second: rounding happens first, so 0.4 has no parts to show.
func HumanizeSeconds(seconds float64) string {
	if math.IsNaN(seconds) || math.IsInf(seconds, 0) || seconds <= 0 {
		return ""
	}

	units := []struct {
		suffix string
		size   int64
	}{
		{"d", 86400},
		{"h", 3600},
		{"m", 60},
		{"s", 1},
	}

	var parts []string
	remaining := int64(math.Round(seconds))

	for _, unit := range units {
		amount := remaining / unit.size
		if amount > 0 {
			parts = append(parts, fmt.Sprintf("%d%s", amount, unit.suffix))
			remaining -= amount * unit.size
		}
	}

	if len(parts) > 2 {
		parts = parts[:2]
	}

	return strings.Join(parts, " ")
}

// The offset is mandatory. A date-time without one reads as local time, so
// accepting it would mean labelling a local instant UTC.
var isoDateTime = regexp.MustCompile(
	`^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2}:\d{2})(?:\.\d+)?(Z|[+-]\d{2}:\d{2})$`)

// Coarsest-unit-wins tiers; approximate on purpose, this is a readability aid.
var relativeTiers = []struct {
	suffix string
	size   float64
}{
	{"y", 31536000},
	{"mo", 2592000},
	{"d", 86400},
	{"h", 3600},
	{"m", 60},
}

// FormatTimestamp turns `2026-07-31T02:49:41.895+00:00` into
// `2026-07-31 02:49:41 UTC (3d ago)`.
//
// Reports false when the value is not an ISO timestamp, so the caller can fall
// back to printing it unchanged.
func FormatTimestamp(value string) (string, bool) {
	match := isoDateTime.FindStringSubmatch(strings.TrimSpace(value))
	if match == nil {
		return "", false
	}

	date, clock, offset := match[1], match[2], match[3]

	zone := " " + offset
	if offset == "Z" || offset == "+00:00" {
		zone = " UTC"
	}

	stamp := date + " " + clock + zone

	// A value can match the shape and still not be a date -- 2026-13-45 does.
	// The stamp is rendered without a relative suffix rather than dropped,
	// because the text is still what the API sent.
	parsed, err := parseTimestamp(value)
	if err != nil {
		return stamp, true
	}

	return stamp + " (" + relativeTime(parsed) + ")", true
}

// parseTimestamp accepts the same spellings the regex does, including the
// space separator that JavaScript's Date tolerates.
func parseTimestamp(value string) (time.Time, error) {
	normalized := strings.Replace(strings.TrimSpace(value), " ", "T", 1)

	for _, layout := range []string{time.RFC3339Nano, time.RFC3339} {
		if parsed, err := time.Parse(layout, normalized); err == nil {
			return parsed, nil
		}
	}

	return time.Time{}, fmt.Errorf("not a date: %q", value)
}

func relativeTime(value time.Time) string {
	delta := now().Sub(value).Seconds()
	magnitude := math.Abs(delta)

	if magnitude < 45 {
		return "just now"
	}

	// Falls back to the finest tier rather than erroring.
	tier := relativeTiers[len(relativeTiers)-1]
	for _, candidate := range relativeTiers {
		if magnitude >= candidate.size {
			tier = candidate

			break
		}
	}

	label := fmt.Sprintf("%d%s", int64(magnitude/tier.size), tier.suffix)
	if delta > 0 {
		return label + " ago"
	}

	return "in " + label
}

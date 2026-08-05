package output

import (
	"encoding/json"
	"math"
	"os"
	"path/filepath"
	"strconv"
	"testing"
	"time"
)

// Pinned to output captured from the TypeScript, not to expectations written
// here. Recapture testdata/valueformat.json from the TypeScript rather than
// hand-editing it; the capture must run with FORCE_COLOR=0, and with Date.now
// frozen to the file's own frozenNow, or the relative timestamps will not
// replay.
type valueFormatBaseline struct {
	FrozenNow       string             `json:"frozenNow"`
	HumanizeSeconds map[string]string  `json:"humanizeSeconds"`
	FormatTimestamp map[string]*string `json:"formatTimestamp"`
}

func loadValueFormatBaseline(t *testing.T) valueFormatBaseline {
	t.Helper()

	raw, err := os.ReadFile(filepath.Join("testdata", "valueformat.json"))
	if err != nil {
		t.Fatal(err)
	}

	var baseline valueFormatBaseline
	if err := json.Unmarshal(raw, &baseline); err != nil {
		t.Fatal(err)
	}

	return baseline
}

// freezeClock pins relative time to the instant the baseline was captured at.
// Without it every timestamp case drifts by however long ago the capture ran.
func freezeClock(t *testing.T, at string) {
	t.Helper()

	frozen, err := time.Parse(time.RFC3339Nano, at)
	if err != nil {
		t.Fatal(err)
	}

	previous := now
	now = func() time.Time { return frozen }
	t.Cleanup(func() { now = previous })
}

func TestHumanizeSecondsMatchesTheTypeScript(t *testing.T) {
	baseline := loadValueFormatBaseline(t)

	if len(baseline.HumanizeSeconds) == 0 {
		t.Fatal("baseline has no humanizeSeconds cases")
	}

	for input, want := range baseline.HumanizeSeconds {
		seconds := parseJSNumber(t, input)

		if got := HumanizeSeconds(seconds); got != want {
			t.Errorf("HumanizeSeconds(%s) = %q, want %q", input, got, want)
		}
	}
}

func TestFormatTimestampMatchesTheTypeScript(t *testing.T) {
	baseline := loadValueFormatBaseline(t)
	freezeClock(t, baseline.FrozenNow)

	if len(baseline.FormatTimestamp) == 0 {
		t.Fatal("baseline has no formatTimestamp cases")
	}

	for input, want := range baseline.FormatTimestamp {
		got, ok := FormatTimestamp(input)

		// A null in the baseline is the TypeScript returning null, which is
		// the signal to print the value unchanged.
		if want == nil {
			if ok {
				t.Errorf("FormatTimestamp(%q) = %q, want no match", input, got)
			}

			continue
		}

		if !ok {
			t.Errorf("FormatTimestamp(%q) did not match, want %q", input, *want)

			continue
		}

		if got != *want {
			t.Errorf("FormatTimestamp(%q) = %q, want %q", input, got, *want)
		}
	}
}

// The baseline keys are JavaScript's own number spellings, including NaN and
// Infinity, which strconv does not accept.
func parseJSNumber(t *testing.T, value string) float64 {
	t.Helper()

	switch value {
	case "NaN":
		return math.NaN()
	case "Infinity":
		return math.Inf(1)
	case "-Infinity":
		return math.Inf(-1)
	}

	parsed, err := strconv.ParseFloat(value, 64)
	if err != nil {
		t.Fatal(err)
	}

	return parsed
}

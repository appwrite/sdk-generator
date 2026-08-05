package dotenv

import (
	"encoding/json"
	"os"
	"path/filepath"
	"sort"
	"testing"
)

// testdata/cases.json holds the output of dotenv 16.x over each source.
// Regenerate with scripts/go-cli/capture-dotenv-baselines.ts in the generator
// repository; do not hand-edit it.

type parseCase struct {
	Name     string            `json:"name"`
	Source   string            `json:"source"`
	Expected map[string]string `json:"expected"`
}

func TestParseMatchesDotenv(t *testing.T) {
	payload, err := os.ReadFile(filepath.Join("testdata", "cases.json"))
	if err != nil {
		t.Fatalf("reading baselines: %v", err)
	}

	var cases []parseCase
	if err := json.Unmarshal(payload, &cases); err != nil {
		t.Fatalf("parsing baselines: %v", err)
	}
	if len(cases) == 0 {
		t.Fatal("baselines are empty")
	}

	for _, testCase := range cases {
		t.Run(testCase.Name, func(t *testing.T) {
			got := Parse(testCase.Source)

			// Key sets are compared as well as values, so a pair the Go parser
			// invents shows up alongside one it drops.
			if len(got) != len(testCase.Expected) {
				t.Errorf("parsed %d keys (%v), want %d (%v)",
					len(got), keysOf(got), len(testCase.Expected), keysOf(testCase.Expected))
			}
			for key, want := range testCase.Expected {
				if value, ok := got[key]; !ok {
					t.Errorf("missing key %q (want %q)", key, want)
				} else if value != want {
					t.Errorf("%q = %q, want %q", key, value, want)
				}
			}
			for key := range got {
				if _, ok := testCase.Expected[key]; !ok {
					t.Errorf("unexpected key %q = %q", key, got[key])
				}
			}
		})
	}
}

func keysOf(values map[string]string) []string {
	keys := make([]string, 0, len(values))
	for key := range values {
		keys = append(keys, key)
	}
	sort.Strings(keys)

	return keys
}

// TestParseOrderedKeepsFirstAppearance pins the ordering the caller depends on:
// these become `docker run -e` flags, and an unstable order makes two runs
// impossible to diff.
func TestParseOrderedKeepsFirstAppearance(t *testing.T) {
	keys, values := ParseOrdered("B=1\nA=2\nC=3\nB=4\n")

	want := []string{"B", "A", "C"}
	if len(keys) != len(want) {
		t.Fatalf("keys = %v, want %v", keys, want)
	}
	for index := range want {
		if keys[index] != want[index] {
			t.Fatalf("keys = %v, want %v", keys, want)
		}
	}

	// The duplicate keeps its first position but the last value, as Parse does.
	if values["B"] != "4" {
		t.Errorf("B = %q, want %q", values["B"], "4")
	}
}

// TestTabEscapeIsNotExpanded pins a rule the captured cases do not reach:
// dotenv unescapes \n and \r inside double quotes, and nothing else.
func TestTabEscapeIsNotExpanded(t *testing.T) {
	values := Parse(`A="tab\there"` + "\n")

	if values["A"] != `tab\there` {
		t.Errorf("A = %q, want the backslash-t left literal", values["A"])
	}
}

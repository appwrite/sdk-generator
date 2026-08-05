package ignore

import (
	"encoding/json"
	"os"
	"path/filepath"
	"sort"
	"testing"
)

// testdata/cases.json holds verdicts produced by running the `ignore` npm
// package itself. Regenerate with scripts/go-cli/capture-ignore-baselines.ts
// in the generator repository; do not hand-edit it. A disagreement here is a
// real difference in which files would be deployed.

type ignoreCase struct {
	Name     string          `json:"name"`
	Patterns []string        `json:"patterns"`
	Expected map[string]bool `json:"expected"`
}

func loadCases(t *testing.T) []ignoreCase {
	t.Helper()

	payload, err := os.ReadFile(filepath.Join("testdata", "cases.json"))
	if err != nil {
		t.Fatalf("reading baselines: %v", err)
	}

	var cases []ignoreCase
	if err := json.Unmarshal(payload, &cases); err != nil {
		t.Fatalf("parsing baselines: %v", err)
	}
	if len(cases) == 0 {
		t.Fatal("baselines are empty")
	}

	return cases
}

func TestMatchesTheIgnorePackage(t *testing.T) {
	for _, testCase := range loadCases(t) {
		t.Run(testCase.Name, func(t *testing.T) {
			matcher := New().AddAll(testCase.Patterns)

			// Sorted so a failure names the same path on every run.
			paths := make([]string, 0, len(testCase.Expected))
			for path := range testCase.Expected {
				paths = append(paths, path)
			}
			sort.Strings(paths)

			for _, path := range paths {
				want := testCase.Expected[path]
				if got := matcher.Ignores(path); got != want {
					t.Errorf("Ignores(%q) = %v, want %v (patterns %v)",
						path, got, want, testCase.Patterns)
				}
			}
		})
	}
}

// TestAddAcceptsAFileBody covers the other entry point: a .gitignore is read
// whole and handed over as one newline-separated string.
func TestAddAcceptsAFileBody(t *testing.T) {
	matcher := New().Add("# comment\nnode_modules\n\n*.log\n!keep.log\r\n")

	for path, want := range map[string]bool{
		"node_modules/x": true,
		"a.log":          true,
		"keep.log":       false,
		"src/main.go":    false,
		"# comment":      false,
	} {
		if got := matcher.Ignores(path); got != want {
			t.Errorf("Ignores(%q) = %v, want %v", path, got, want)
		}
	}
}

// TestFilterPreservesOrder pins the property the callers rely on: the file
// list feeding a build must stay in the order it was walked, or the tar it
// produces differs run to run.
func TestFilterPreservesOrder(t *testing.T) {
	matcher := New().AddAll([]string{"*.log"})

	got := matcher.Filter([]string{"b.go", "a.log", "a.go", "c.log", "c.go"})
	want := []string{"b.go", "a.go", "c.go"}

	if len(got) != len(want) {
		t.Fatalf("Filter = %v, want %v", got, want)
	}
	for index := range want {
		if got[index] != want[index] {
			t.Fatalf("Filter = %v, want %v", got, want)
		}
	}
}

// TestLeadingDotSlashIsStripped covers paths built by joining a walk root,
// which can arrive as "./x".
func TestLeadingDotSlashIsStripped(t *testing.T) {
	matcher := New().AddAll([]string{"secret.txt"})

	if !matcher.Ignores("./secret.txt") {
		t.Error("./secret.txt should be ignored")
	}
	if matcher.Ignores("") {
		t.Error("an empty path should never be ignored")
	}
}

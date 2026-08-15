package generator

import (
	"os"
	"path/filepath"
	"strings"
	"testing"
)

// The baselines under testdata/ were produced by running the shipping
// TypeScriptDatabasesGenerator under node over testdata/config.json. They are
// captured output, not output anyone wrote by hand: regenerate them by
// re-running the generator rather than editing them.
//
// Neither case uses serverSide "auto": it resolves from whatever package.json
// sits in the working directory, which is exactly the ambiguity a baseline must
// not carry. Both explicit branches are pinned instead.

func loadConfig(t *testing.T) Config {
	t.Helper()

	config, err := LoadConfig(filepath.Join("testdata", "config.json"))
	if err != nil {
		t.Fatalf("reading fixture: %v", err)
	}

	return config
}

func assertMatchesBaseline(t *testing.T, name, rendered string) {
	t.Helper()

	content, err := os.ReadFile(filepath.Join("testdata", name))
	if err != nil {
		t.Fatalf("reading baseline %s: %v", name, err)
	}

	expected := string(content)
	if rendered == expected {
		return
	}

	renderedLines := strings.Split(rendered, "\n")
	expectedLines := strings.Split(expected, "\n")
	for index := range max(len(renderedLines), len(expectedLines)) {
		got, want := line(renderedLines, index), line(expectedLines, index)
		if got != want {
			t.Fatalf("%s line %d:\n  want %q\n  got  %q", name, index+1, want, got)
		}
	}

	t.Fatalf("%s: output differs but every line matches, so trailing bytes differ", name)
}

func line(lines []string, index int) string {
	if index < len(lines) {
		return lines[index]
	}

	return "<missing>"
}

// assertGenerates renders all four files for one server-side branch.
func assertGenerates(t *testing.T, prefix, serverSide, source, extension string, config Config) {
	t.Helper()

	generator := &TypeScript{}
	result, err := generator.Generate(config, Options{
		ImportSource:    source,
		ImportExtension: &extension,
		ServerSide:      serverSide,
	})
	if err != nil {
		t.Fatalf("generate: %v", err)
	}

	for name, rendered := range map[string]string{
		"databases.ts": result.Databases,
		"types.ts":     result.Types,
		"index.ts":     result.Index,
		"constants.ts": result.Constants,
	} {
		t.Run(name, func(t *testing.T) {
			assertMatchesBaseline(t, prefix+"."+name, rendered)
		})
	}
}

func TestGenerateMatchesBaseline(t *testing.T) {
	config := loadConfig(t)

	t.Run("server", func(t *testing.T) {
		assertGenerates(t, "server", "true", "node-appwrite", ".js", config)
	})
	t.Run("client", func(t *testing.T) {
		assertGenerates(t, "client", "false", "appwrite", "", config)
	})
	t.Run("empty", func(t *testing.T) {
		assertGenerates(t, "empty", "true", "node-appwrite", ".js",
			Config{ProjectID: config.ProjectID, Endpoint: config.Endpoint})
	})
}

func TestGenerateRequiresProjectID(t *testing.T) {
	if _, err := (&TypeScript{}).Generate(Config{}, Options{}); err == nil {
		t.Fatal("expected an error for a config with no projectId")
	}
}

// TestEndpointWithQueryStringSurvives pins invariant 4 through the generator:
// jsonx marshalling must not HTML-escape, and the Handlebars `{{endpoint}}`
// must. Both are true, and both are visible in constants.ts.
func TestEndpointWithQueryStringSurvives(t *testing.T) {
	config := loadConfig(t)
	if !strings.Contains(config.Endpoint, "&") {
		t.Fatal("fixture endpoint no longer contains an ampersand, so this proves nothing")
	}

	extension := ".js"
	result, err := (&TypeScript{}).Generate(config, Options{
		ImportSource:    "node-appwrite",
		ImportExtension: &extension,
		ServerSide:      "true",
	})
	if err != nil {
		t.Fatal(err)
	}

	// `{{endpoint}}` is a double-brace tag, so Handlebars escapes it -- the
	// generated constant really does contain &amp;. That is a defect in the
	// the established CLI, reproduced here; see internal/typegen/handlebars.go.
	if !strings.Contains(result.Constants, "&amp;") {
		t.Error("constants.ts should carry the Handlebars-escaped endpoint")
	}
	// The table names go through jsonx, which does not escape.
	if strings.Contains(result.Types, `\u0026`) {
		t.Error("types.ts contains a Go-style unicode escape; jsonx must not HTML-escape")
	}
}

func TestGroupByDatabaseKeepsFirstSeenOrder(t *testing.T) {
	groups := groupByDatabase([]Entity{
		{ID: "a", Name: "A", DatabaseID: "second"},
		{ID: "b", Name: "B", DatabaseID: "first"},
		{ID: "c", Name: "C", DatabaseID: "second"},
	})

	if len(groups) != 2 || groups[0].id != "second" || groups[1].id != "first" {
		t.Fatalf("groups = %v, want second then first", groups)
	}
	if len(groups[0].entities) != 2 {
		t.Errorf("second holds %d entities, want 2", len(groups[0].entities))
	}
}

// TestDedupeKeepsFirstPositionAndLastValue pins Map.set semantics. Rebuilding
// the list in last-seen order would reorder the generated output of any config
// that happens to contain a duplicate.
func TestDedupeKeepsFirstPositionAndLastValue(t *testing.T) {
	deduped := dedupeEntities([]Entity{
		{ID: "a", Name: "first", DatabaseID: "db"},
		{ID: "b", Name: "other", DatabaseID: "db"},
		{ID: "a", Name: "last", DatabaseID: "db"},
	})

	if len(deduped) != 2 {
		t.Fatalf("deduped %d entities, want 2", len(deduped))
	}
	if deduped[0].Name != "last" {
		t.Errorf("position 0 name = %q, want the last value %q", deduped[0].Name, "last")
	}
	if deduped[1].Name != "other" {
		t.Errorf("position 1 name = %q, want %q", deduped[1].Name, "other")
	}
}

func TestDetectLanguageReportsConfidence(t *testing.T) {
	for _, testCase := range []struct {
		file       string
		confidence Confidence
	}{
		{"tsconfig.json", ConfidenceHigh},
		{"yarn.lock", ConfidenceMedium},
		{"main.ts", ConfidenceLow},
	} {
		t.Run(testCase.file, func(t *testing.T) {
			directory := t.TempDir()
			if err := os.WriteFile(filepath.Join(directory, testCase.file), nil, 0o600); err != nil {
				t.Fatal(err)
			}

			detection, ok := DetectLanguage(directory)
			if !ok {
				t.Fatal("no language detected")
			}
			if detection.Confidence != testCase.confidence {
				t.Errorf("confidence = %q, want %q", detection.Confidence, testCase.confidence)
			}
		})
	}

	if _, ok := DetectLanguage(t.TempDir()); ok {
		t.Error("an empty directory should not detect a language")
	}
}

// TestWriteFilesPreservesConstants pins the one file that is not overwritten:
// its header invites the user to edit it, so regenerating must not discard
// their project id or endpoint changes.
func TestWriteFilesPreservesConstants(t *testing.T) {
	directory := t.TempDir()
	result := Result{
		Databases: "databases", Types: "types", Index: "index", Constants: "generated",
	}

	if err := WriteFiles(directory, SDKDirectory, result); err != nil {
		t.Fatal(err)
	}

	constants := filepath.Join(directory, SDKDirectory, "constants.ts")
	if err := os.WriteFile(constants, []byte("edited by hand"), 0o600); err != nil {
		t.Fatal(err)
	}

	if err := WriteFiles(directory, SDKDirectory, result); err != nil {
		t.Fatal(err)
	}

	kept, err := os.ReadFile(constants)
	if err != nil {
		t.Fatal(err)
	}
	if string(kept) != "edited by hand" {
		t.Errorf("constants.ts = %q, want the hand-edited content", kept)
	}

	// The other three are rewritten every time.
	types, err := os.ReadFile(filepath.Join(directory, SDKDirectory, "types.ts"))
	if err != nil {
		t.Fatal(err)
	}
	if string(types) != "types" {
		t.Errorf("types.ts = %q, want it regenerated", types)
	}
}

func TestSupportsServerSideMethods(t *testing.T) {
	for _, testCase := range []struct {
		dependency string
		override   string
		want       bool
	}{
		{"node-appwrite", "auto", true},
		{"npm:node-appwrite", "auto", true},
		{"@appwrite.io/console", "auto", true},
		{"appwrite", "auto", false},
		{"react-native-appwrite", "auto", false},
		// The override wins over the dependency in both directions.
		{"appwrite", "true", true},
		{"node-appwrite", "false", false},
	} {
		got := SupportsServerSideMethods(testCase.dependency, testCase.override)
		if got != testCase.want {
			t.Errorf("SupportsServerSideMethods(%q, %q) = %v, want %v",
				testCase.dependency, testCase.override, got, testCase.want)
		}
	}
}

package typegen

import (
	"encoding/json"
	"os"
	"path/filepath"
	"testing"
)

// The baselines under testdata/ were produced by running the shipping
// TypeScript emitters under node over testdata/collections.json, with
// process.argv pinned to `types ./types`. They are captured output, not output
// anyone wrote by hand -- regenerate with the generator repository's
// scripts/go-cli/capture-typegen-baselines.ts rather than editing them.
//
// Regenerating them means re-running the probe, not editing the file. A diff
// here is a real behaviour change in one implementation or the other.

// baselineInvocation is the argv the baselines were captured with.
const baselineInvocation = "types ./types"

func loadCollections(t *testing.T) []Collection {
	t.Helper()

	payload, err := os.ReadFile(filepath.Join("testdata", "collections.json"))
	if err != nil {
		t.Fatalf("reading fixture: %v", err)
	}

	var collections []Collection
	if err := json.Unmarshal(payload, &collections); err != nil {
		t.Fatalf("parsing fixture: %v", err)
	}

	return collections
}

func loadBaseline(t *testing.T, name string) string {
	t.Helper()

	content, err := os.ReadFile(filepath.Join("testdata", name))
	if err != nil {
		t.Fatalf("reading baseline %s: %v", name, err)
	}

	return string(content)
}

// assertRendersBaseline compares rendered output byte for byte, reporting the
// first differing line rather than dumping both files.
//
// current is nil for single-file languages.
func assertRendersBaseline(t *testing.T, language Language, current *Collection, strict bool, baseline string) {
	t.Helper()

	rendered, err := language.Render(loadCollections(t), current, strict, baselineInvocation)
	if err != nil {
		t.Fatalf("render: %v", err)
	}

	expected := loadBaseline(t, baseline)
	if rendered == expected {
		return
	}

	renderedLines, expectedLines := splitLines(rendered), splitLines(expected)
	for index := range max(len(renderedLines), len(expectedLines)) {
		got, want := at(renderedLines, index), at(expectedLines, index)
		if got != want {
			t.Fatalf("%s line %d:\n  want %q\n  got  %q", baseline, index+1, want, got)
		}
	}

	t.Fatalf("%s: output differs but every line matches, which means trailing bytes differ", baseline)
}

func splitLines(value string) []string {
	var lines []string
	start := 0
	for index := range len(value) {
		if value[index] == '\n' {
			lines = append(lines, value[start:index])
			start = index + 1
		}
	}

	return append(lines, value[start:])
}

func at(lines []string, index int) string {
	if index < len(lines) {
		return lines[index]
	}

	return "<missing>"
}

func TestTypeScriptMatchesBaseline(t *testing.T) {
	// @appwrite.io/console is what the capture environment resolved to, and it
	// is baked into the baseline's import line.
	language := TypeScript{Dependency: "@appwrite.io/console"}

	t.Run("loose", func(t *testing.T) {
		assertRendersBaseline(t, language, nil, false, "ts.loose.appwrite.d.ts")
	})
	t.Run("strict", func(t *testing.T) {
		assertRendersBaseline(t, language, nil, true, "ts.strict.appwrite.d.ts")
	})
}

func TestJavaScriptMatchesBaseline(t *testing.T) {
	language := JavaScript{Dependency: "appwrite"}

	t.Run("loose", func(t *testing.T) {
		assertRendersBaseline(t, language, nil, false, "js.loose.appwrite-types.js")
	})
	t.Run("strict", func(t *testing.T) {
		assertRendersBaseline(t, language, nil, true, "js.strict.appwrite-types.js")
	})
}

// assertMultiFileBaselines renders every collection and compares each against
// its own baseline, named `<prefix>.<mode>.<FileName()>`.
func assertMultiFileBaselines(t *testing.T, language Language, prefix string) {
	t.Helper()

	for _, strict := range []bool{false, true} {
		mode := "loose"
		if strict {
			mode = "strict"
		}

		t.Run(mode, func(t *testing.T) {
			collections := loadCollections(t)
			for index := range collections {
				current := &collections[index]
				name := prefix + "." + mode + "." + language.FileName(current)

				t.Run(current.Name, func(t *testing.T) {
					assertRendersBaseline(t, language, current, strict, name)
				})
			}
		})
	}
}

func TestPHPMatchesBaseline(t *testing.T) {
	assertMultiFileBaselines(t, PHP{}, "php")
}

func TestKotlinMatchesBaseline(t *testing.T) {
	assertMultiFileBaselines(t, Kotlin{}, "kotlin")
}

func TestSwiftMatchesBaseline(t *testing.T) {
	assertMultiFileBaselines(t, Swift{}, "swift")
}

func TestJavaMatchesBaseline(t *testing.T) {
	assertMultiFileBaselines(t, Java{}, "java")
}

func TestDartMatchesBaseline(t *testing.T) {
	assertMultiFileBaselines(t, Dart{}, "dart")
}

func TestCSharpMatchesBaseline(t *testing.T) {
	assertMultiFileBaselines(t, CSharp{}, "cs")
}

// TestPHPArrayNeverGainsNullBranch pins the early return in PHP.Type: an
// optional array attribute is `array`, not `array|null`, because the array
// check precedes the nullable suffix.
func TestPHPArrayNeverGainsNullBranch(t *testing.T) {
	attribute := Attribute{
		Key:     "tags",
		Type:    AttributeTypeString,
		Array:   true,
		Default: json.RawMessage("null"),
	}

	literal, err := (PHP{}).Type(attribute, nil, "Thing")
	if err != nil {
		t.Fatal(err)
	}
	if literal != "array" {
		t.Errorf("PHP array type = %q, want %q", literal, "array")
	}
}

// TestBigIntIsTypeScriptOnly pins a divergence the baselines cannot show,
// because a bigint attribute makes every other emitter throw and so had to be
// removed from the shared fixture.
func TestBigIntIsTypeScriptOnly(t *testing.T) {
	attribute := Attribute{Key: "count", Type: AttributeTypeBigInt, Required: true}

	literal, err := (TypeScript{}).Type(attribute, nil, "Thing")
	if err != nil {
		t.Fatalf("TypeScript rejected bigint: %v", err)
	}
	if literal != "bigint" {
		t.Errorf("TypeScript bigint = %q, want %q", literal, "bigint")
	}

	if _, err := (JavaScript{}).Type(attribute, nil, "Thing"); err == nil {
		t.Error("JavaScript accepted bigint; the TypeScript throws Unknown attribute type: bigint")
	}
}

func TestNullableRequiresAnExplicitNullDefault(t *testing.T) {
	absent := Attribute{Key: "a", Type: AttributeTypeString}
	explicit := Attribute{Key: "a", Type: AttributeTypeString, Default: json.RawMessage("null")}

	// `attribute.default === null` is false for undefined, so an attribute with
	// no default key does not gain `| null` even when it is optional.
	if absent.Nullable() {
		t.Error("absent default counted as null")
	}
	if !explicit.Nullable() {
		t.Error("explicit null default did not count as null")
	}

	// The distinction has to survive a decode, not just a struct literal: a
	// pointer-typed field would be nilled by encoding/json on a JSON null and
	// silently collapse the two cases.
	var decoded []Attribute
	payload := `[{"key":"a","type":"string"},{"key":"b","type":"string","default":null}]`
	if err := json.Unmarshal([]byte(payload), &decoded); err != nil {
		t.Fatal(err)
	}
	if decoded[0].Nullable() {
		t.Error("decoded absent default counted as null")
	}
	if !decoded[1].Nullable() {
		t.Error("decoded explicit null default did not count as null")
	}
}

func TestDetectLanguagePrefersTypeScriptOverJavaScript(t *testing.T) {
	directory := t.TempDir()
	for _, name := range []string{"tsconfig.json", "package.json"} {
		if err := os.WriteFile(filepath.Join(directory, name), []byte("{}"), 0o600); err != nil {
			t.Fatal(err)
		}
	}

	detected, err := DetectLanguage(directory)
	if err != nil {
		t.Fatal(err)
	}
	if detected != "ts" {
		t.Errorf("detected %q, want ts", detected)
	}
}

func TestDetectLanguageFindsCsprojByExtension(t *testing.T) {
	directory := t.TempDir()
	if err := os.WriteFile(filepath.Join(directory, "Whatever.csproj"), nil, 0o600); err != nil {
		t.Fatal(err)
	}

	detected, err := DetectLanguage(directory)
	if err != nil {
		t.Fatal(err)
	}
	if detected != "dotnet" {
		t.Errorf("detected %q, want dotnet", detected)
	}
}

func TestDetectLanguageReportsFailure(t *testing.T) {
	if _, err := DetectLanguage(t.TempDir()); err == nil {
		t.Fatal("expected an error for a directory with no markers")
	}
}

func TestAppwriteDependencyPrefersConsole(t *testing.T) {
	directory := t.TempDir()
	manifest := `{"dependencies":{"node-appwrite":"1.0.0","@appwrite.io/console":"2.0.0"}}`
	if err := os.WriteFile(filepath.Join(directory, "package.json"), []byte(manifest), 0o600); err != nil {
		t.Fatal(err)
	}

	if got := AppwriteDependency(directory); got != "@appwrite.io/console" {
		t.Errorf("AppwriteDependency = %q, want @appwrite.io/console", got)
	}

	// The JavaScript emitter has its own narrower lookup that cannot return
	// @appwrite.io/console at all.
	if got := JavaScriptDependency(directory); got != "node-appwrite" {
		t.Errorf("JavaScriptDependency = %q, want node-appwrite", got)
	}
}

func TestEnumKeysDisambiguateOnCollision(t *testing.T) {
	members := generateEnumMembers([]string{"in-active", "in active", "in_active"})

	want := []string{"IN_ACTIVE", "IN_ACTIVE_1", "IN_ACTIVE_2"}
	for index, member := range members {
		if member.Key != want[index] {
			t.Errorf("member %d key = %q, want %q", index, member.Key, want[index])
		}
	}
}

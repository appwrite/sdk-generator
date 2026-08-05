package cmd

import (
	"os"
	"path/filepath"
	"testing"
)

// The runtime id yields TWO different names and they are not interchangeable.
// A live `init function` run only ever exercises whichever runtime is first in
// the list, so the multi-segment cases are only covered here.
func TestRuntimeChoiceSplitsDirectoryAndLanguageDifferently(t *testing.T) {
	cases := []struct {
		id        string
		directory string
		language  string
	}{
		// The common case, where the two happen to agree -- which is exactly
		// why the divergence below is easy to miss.
		{"node-22", "node", "node"},
		{"node-16.0", "node", "node"},
		// Directory takes the FIRST segment, language drops the LAST.
		{"python-ml-3.11", "python", "python-ml"},
		// No dash at all: both fall back to the whole id.
		{"dart", "dart", "dart"},
	}

	for _, test := range cases {
		choice := newRuntimeChoice(test.id)
		if choice.Directory != test.directory {
			t.Errorf("%s: directory = %q, want %q",
				test.id, choice.Directory, test.directory)
		}
		if choice.Language != test.language {
			t.Errorf("%s: language = %q, want %q",
				test.id, choice.Language, test.language)
		}
	}
}

// python-ml is the case the two-name split exists for: it must be looked up by
// its language, not by its template directory, or it silently gets no
// entrypoint at all.
func TestRuntimeChoiceResolvesPythonMLThroughItsLanguage(t *testing.T) {
	choice := newRuntimeChoice("python-ml-3.11")

	if choice.Entrypoint != "src/main.py" {
		t.Errorf("entrypoint = %q, want %q", choice.Entrypoint, "src/main.py")
	}
	if choice.Commands != "pip install -r requirements.txt" {
		t.Errorf("commands = %q, want the pip install", choice.Commands)
	}
	if len(choice.Ignore) != 1 || choice.Ignore[0] != "__pypackages__" {
		t.Errorf("ignore = %v, want [__pypackages__]", choice.Ignore)
	}
}

// The TypeScript writes `ignore: runtime.ignore || null`, but an empty array is
// truthy in JavaScript, so that fallback never fires. A runtime with no ignores
// is written as `[]`, and a nil slice here would marshal to null instead.
func TestIgnoresIsAlwaysAnArrayNeverNil(t *testing.T) {
	for _, runtime := range []string{"deno", "go", "some-unknown-runtime"} {
		if ignores := ignoresFor(runtime); ignores == nil {
			t.Errorf("%s: ignores is nil, want an empty slice", runtime)
		}
	}
}

// A compiled language has no install step, which is a different fact from a
// runtime this CLI has never heard of. Both render as "" and both warn.
func TestInstallCommandSeparatesEmptyFromUnknown(t *testing.T) {
	if command, known := installCommandFor("swift"); command != "" || !known {
		t.Errorf("swift = (%q, %v), want (\"\", true)", command, known)
	}
	if command, known := installCommandFor("brainfuck"); command != "" || known {
		t.Errorf("unknown = (%q, %v), want (\"\", false)", command, known)
	}
}

// Ports the `newReadmeFile.splice(1, 2)` behaviour: the heading is replaced and
// the TWO lines after it are dropped.
func TestRetitleReadmeReplacesTitleAndDropsTwoLines(t *testing.T) {
	path := filepath.Join(t.TempDir(), "README.md")
	original := "# Starter\n\n![badge](https://example.test/badge.svg)\n\n## Usage\n"
	if err := os.WriteFile(path, []byte(original), 0o644); err != nil {
		t.Fatal(err)
	}

	if err := retitleReadme(path, "My Function"); err != nil {
		t.Fatal(err)
	}

	contents, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}

	want := "# My Function\n\n## Usage\n"
	if string(contents) != want {
		t.Errorf("readme =\n%q\nwant\n%q", contents, want)
	}
}

// splice() on a short array removes what it can rather than erroring, so a
// two-line README must not panic or lose the file.
func TestRetitleReadmeHandlesAShortFile(t *testing.T) {
	path := filepath.Join(t.TempDir(), "README.md")
	if err := os.WriteFile(path, []byte("# Starter\ntagline"), 0o644); err != nil {
		t.Fatal(err)
	}

	if err := retitleReadme(path, "Renamed"); err != nil {
		t.Fatal(err)
	}

	contents, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if string(contents) != "# Renamed" {
		t.Errorf("readme = %q, want %q", contents, "# Renamed")
	}
}

func TestParseSkillFrontmatter(t *testing.T) {
	name, description := parseSkillFrontmatter(
		"---\nname: Appwrite CLI\ndescription: Drive the CLI\n---\n\n# Body\n")

	if name != "Appwrite CLI" {
		t.Errorf("name = %q", name)
	}
	if description != "Drive the CLI" {
		t.Errorf("description = %q", description)
	}
}

// A SKILL.md with no frontmatter is not an error; the caller falls back to the
// directory name.
func TestParseSkillFrontmatterWithoutABlock(t *testing.T) {
	name, description := parseSkillFrontmatter("# Just a heading\n")

	if name != "" || description != "" {
		t.Errorf("got (%q, %q), want two empty strings", name, description)
	}
}

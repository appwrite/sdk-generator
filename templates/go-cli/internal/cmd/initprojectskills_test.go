package cmd

import (
	"os"
	"path/filepath"
	"slices"
	"testing"
)

func skillsFor(dirNames ...string) []skillInfo {
	skills := make([]skillInfo, 0, len(dirNames))
	for _, name := range dirNames {
		skills = append(skills, skillInfo{Name: name, DirName: name})
	}

	return skills
}

func detectedNames(skills []skillInfo) []string {
	names := make([]string, 0, len(skills))
	for _, skill := range skills {
		names = append(names, skill.DirName)
	}
	slices.Sort(names)

	return names
}

// A marker file is what proves a project uses a language, and the skill that
// matches it is the one worth installing without asking.
func TestProjectSkillsAreDetectedFromMarkerFiles(t *testing.T) {
	root := t.TempDir()
	if err := os.WriteFile(filepath.Join(root, "pyproject.toml"), nil, 0o600); err != nil {
		t.Fatal(err)
	}

	available := skillsFor("appwrite-python", "appwrite-php", "appwrite-cli")
	got := detectedNames(detectProjectSkills(root, available))

	if !slices.Equal(got, []string{"appwrite-cli", "appwrite-python"}) {
		t.Errorf("detected %v, want the python and cli skills", got)
	}
}

// The CLI skill needs no evidence: a project initialised by this command uses
// the CLI by definition.
func TestTheCLISkillIsAlwaysDetected(t *testing.T) {
	got := detectedNames(detectProjectSkills(t.TempDir(),
		skillsFor("appwrite-cli", "appwrite-go")))

	if !slices.Equal(got, []string{"appwrite-cli"}) {
		t.Errorf("detected %v in an empty directory, want only the cli skill", got)
	}
}

// A `*.csproj` is matched by extension, because its name is the project's and
// this cannot know it.
func TestGlobMarkersMatchByExtension(t *testing.T) {
	root := t.TempDir()
	if err := os.WriteFile(filepath.Join(root, "MyThing.csproj"), nil, 0o600); err != nil {
		t.Fatal(err)
	}

	got := detectedNames(detectProjectSkills(root, skillsFor("appwrite-dotnet", "appwrite-cli")))
	if !slices.Contains(got, "appwrite-dotnet") {
		t.Errorf("detected %v, want the dotnet skill", got)
	}
}

// A glob must not match the extension anywhere but the end -- `csproj.backup`
// is not a project file.
func TestGlobMarkersDoNotMatchMidName(t *testing.T) {
	root := t.TempDir()
	if err := os.WriteFile(filepath.Join(root, "notes.csproj.backup"), nil, 0o600); err != nil {
		t.Fatal(err)
	}

	got := detectedNames(detectProjectSkills(root, skillsFor("appwrite-dotnet", "appwrite-cli")))
	if slices.Contains(got, "appwrite-dotnet") {
		t.Errorf("a backup file was taken for a project file: %v", got)
	}
}

// Several languages in one directory get all of their skills -- a repo with a
// package.json and a go.mod is both.
func TestSeveralLanguagesAreAllDetected(t *testing.T) {
	root := t.TempDir()
	for _, marker := range []string{"package.json", "go.mod"} {
		if err := os.WriteFile(filepath.Join(root, marker), nil, 0o600); err != nil {
			t.Fatal(err)
		}
	}

	got := detectedNames(detectProjectSkills(root,
		skillsFor("appwrite-typescript", "appwrite-go", "appwrite-cli", "appwrite-ruby")))

	if !slices.Equal(got, []string{"appwrite-cli", "appwrite-go", "appwrite-typescript"}) {
		t.Errorf("detected %v", got)
	}
}

// A project that has already installed skills has made a choice, and a
// scaffolding step must not silently redo it.
func TestHasSkillsInstalled(t *testing.T) {
	root := t.TempDir()
	if hasSkillsInstalled(root) {
		t.Error("an empty project reported installed skills")
	}

	// An empty skills directory is not an installation.
	agents := filepath.Join(root, ".agents", "skills")
	if err := os.MkdirAll(agents, 0o755); err != nil {
		t.Fatal(err)
	}
	if hasSkillsInstalled(root) {
		t.Error("an empty skills directory counted as installed")
	}

	if err := os.MkdirAll(filepath.Join(agents, "appwrite-cli"), 0o755); err != nil {
		t.Fatal(err)
	}
	if !hasSkillsInstalled(root) {
		t.Error("an installed skill was not found")
	}
}

// Either agent directory counts, because a project may be opened with either.
func TestHasSkillsInstalledChecksBothAgents(t *testing.T) {
	root := t.TempDir()
	if err := os.MkdirAll(filepath.Join(root, ".claude", "skills", "x"), 0o755); err != nil {
		t.Fatal(err)
	}

	if !hasSkillsInstalled(root) {
		t.Error("a skill under .claude was not found")
	}
}

func TestPluralSuffix(t *testing.T) {
	if pluralSuffix(1) != "" {
		t.Error("1 skill was pluralised")
	}
	if pluralSuffix(2) != "s" {
		t.Error("2 skills were singularised")
	}
}

func TestHeadlessSkillSelection(t *testing.T) {
	available := skillsFor("appwrite-cli", "appwrite-go", "appwrite-typescript")

	all, err := resolveSkillSelection(available, nil, true)
	if err != nil {
		t.Fatal(err)
	}
	if !slices.Equal(all, []string{"appwrite-cli", "appwrite-go", "appwrite-typescript"}) {
		t.Errorf("--all selected %v", all)
	}

	selected, err := resolveSkillSelection(available,
		[]string{"appwrite-go", "appwrite-go", "appwrite-cli"}, false)
	if err != nil {
		t.Fatal(err)
	}
	if !slices.Equal(selected, []string{"appwrite-go", "appwrite-cli"}) {
		t.Errorf("--skill selected %v", selected)
	}
}

func TestHeadlessSkillSelectionRejectsInvalidFlags(t *testing.T) {
	available := skillsFor("appwrite-cli", "appwrite-go")

	if _, err := resolveSkillSelection(available, []string{"appwrite-go"}, true); err == nil {
		t.Error("--all with --skill was accepted")
	}
	if _, err := resolveSkillSelection(available, []string{"missing"}, false); err == nil {
		t.Error("an unknown skill was accepted")
	}
	if _, err := resolveSkillAgents([]string{"unknown"}); err == nil {
		t.Error("an unknown agent directory was accepted")
	}
	if _, err := resolveSkillMethod("archive"); err == nil {
		t.Error("an unknown installation method was accepted")
	}
}

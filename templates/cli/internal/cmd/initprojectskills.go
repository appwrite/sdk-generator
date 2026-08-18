//go:build !browser

package cmd

import (
	"io"
	"os"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
)

// The skills half of `init project`.
//
// Installing the skills a project wants, detecting which those are, and
// noticing when some are already there. Fetching and placing them is shared
// with `init skill`, which is why this is
// mostly the DECIDING: which skills a project wants, and whether it has any
// already.
//
// The point of doing it here rather than leaving it to `init skill` is that a
// project is scaffolded once and the skills are per-language: a Python project
// wants the Python skill, and asking the user to know that is asking them to
// read a list they have no way to evaluate yet.

// languageMarkers maps a language to the files that prove a project uses it.
//
// `*` globs included -- a `*.csproj` is matched by extension because its name
// is the project's, which this cannot know.
var languageMarkers = map[string][]string{
	"typescript": {"package.json", "tsconfig.json", "bun.lockb", "yarn.lock", "package-lock.json"},
	"python":     {"requirements.txt", "pyproject.toml", "setup.py", "Pipfile"},
	"php":        {"composer.json"},
	"dart":       {"pubspec.yaml"},
	"swift":      {"Package.swift", "*.xcodeproj"},
	"kotlin":     {"build.gradle.kts", "build.gradle"},
	"go":         {"go.mod"},
	"ruby":       {"Gemfile"},
	"dotnet":     {"*.csproj", "*.sln", "*.fsproj"},
}

// skillAgents are the directories a skill is installed into.
//
// Two, and the second is a symlink to the first: a project may be opened with
// either agent and both must see the same skill without a second copy to keep in
// step. placeSkills does the linking; this only names them.
var skillAgents = []string{".agents", ".claude"}

// hasSkillsInstalled reports whether a project already carries skills.
//
// A non-empty directory is enough: this runs during
// `init project`, and a project that has already installed skills has made a
// choice that a scaffolding step must not silently redo.
func hasSkillsInstalled(root string) bool {
	for _, agent := range skillAgents {
		entries, err := os.ReadDir(filepath.Join(root, agent, "skills"))
		if err == nil && len(entries) > 0 {
			return true
		}
	}

	return false
}

// detectProjectSkills picks the skills a project's own files ask for.
//
// Implements detectProjectSkills, `cli` included unconditionally: every project
// initialised by this command uses the CLI by definition, so it is the one skill
// that needs no evidence.
func detectProjectSkills(root string, skills []skillInfo) []skillInfo {
	detected := map[string]bool{"cli": true}

	// Read the directory ONCE: re-reading it inside the marker loop would be a
	// directory listing per glob per language.
	var names []string
	if entries, err := os.ReadDir(root); err == nil {
		names = make([]string, 0, len(entries))
		for _, entry := range entries {
			names = append(names, entry.Name())
		}
	}

	for language, markers := range languageMarkers {
		for _, marker := range markers {
			if extension, isGlob := strings.CutPrefix(marker, "*"); isGlob {
				for _, name := range names {
					if strings.HasSuffix(name, extension) {
						detected[language] = true

						break
					}
				}

				continue
			}

			if _, err := os.Stat(filepath.Join(root, marker)); err == nil {
				detected[language] = true
			}
		}
	}

	// Substring matching on the directory name: the skills repository names
	// them `appwrite-python`, not `python`.
	matched := make([]skillInfo, 0, len(skills))
	for _, skill := range skills {
		lowered := strings.ToLower(skill.DirName)
		for language := range detected {
			if strings.Contains(lowered, language) {
				matched = append(matched, skill)

				break
			}
		}
	}

	return matched
}

// installInitProjectSkills installs the skills a freshly initialised project
// wants.
//
// Every failure is reported and swallowed. The project IS initialised by the
// time this runs -- the config is written and the success line printed -- so
// failing the command here would report a failure for work that succeeded, and
// leave the user unsure whether to run it again. The hint names the command that
// does this on its own, which is the recovery.
func installInitProjectSkills(out io.Writer, root string) {
	if hasSkillsInstalled(root) {
		output.Log(out, "Agent skills already found. Skipping installation.")

		return
	}

	skills, tempDir, err := fetchAvailableSkills()
	if err != nil {
		reportSkillFailure(out, err)

		return
	}
	defer os.RemoveAll(tempDir)

	detected := detectProjectSkills(root, skills)
	if len(detected) == 0 {
		return
	}

	names := make([]string, 0, len(detected))
	labels := make([]string, 0, len(detected))
	for _, skill := range detected {
		names = append(names, skill.DirName)
		labels = append(labels, skill.Name)
	}

	if err := placeSkills(root, tempDir, names, skillAgents, true); err != nil {
		reportSkillFailure(out, err)

		return
	}

	output.Success(out, "Installed %d agent skill%s: %s",
		len(names), pluralSuffix(len(names)), strings.Join(labels, ", "))
}

func reportSkillFailure(out io.Writer, err error) {
	output.Failure(out, "Failed to install skills: %s", err)
	output.Hint(out, "You can install them later with '%s init skill'.", app.ExecutableName)
}

// pluralSuffix is the "s" in "2 skills".
func pluralSuffix(count int) string {
	if count == 1 {
		return ""
	}

	return "s"
}

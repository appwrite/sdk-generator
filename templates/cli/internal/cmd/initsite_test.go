package cmd

import (
	"encoding/json"
	"errors"
	"io"
	"os"
	"os/exec"
	"path/filepath"
	"testing"
)

func runFixtureGit(t *testing.T, directory string, arguments ...string) {
	t.Helper()

	command := exec.Command("git", arguments...)
	command.Dir = directory
	if output, err := command.CombinedOutput(); err != nil {
		t.Fatalf("git %v: %v\n%s", arguments, err, output)
	}
}

func siteTemplateFixture(t *testing.T) (string, string) {
	t.Helper()

	base := t.TempDir()
	work := filepath.Join(base, "work")
	if err := os.MkdirAll(filepath.Join(work, "nextjs", "starter"), 0o755); err != nil {
		t.Fatal(err)
	}
	runFixtureGit(t, work, "init", "--quiet")
	runFixtureGit(t, work, "config", "user.email", "fixture@example.test")
	runFixtureGit(t, work, "config", "user.name", "Fixture")

	if err := os.WriteFile(filepath.Join(work, "README.md"), []byte("# Monorepo\n"), 0o644); err != nil {
		t.Fatal(err)
	}
	for _, version := range []string{"0.1.2", "0.1.10", "0.2.0"} {
		if err := os.WriteFile(filepath.Join(work, "nextjs", "starter", "version.txt"),
			[]byte(version), 0o644); err != nil {
			t.Fatal(err)
		}
		if err := os.WriteFile(filepath.Join(work, "nextjs", "starter", "README.md"),
			[]byte("# Starter\n\n![badge](https://example.test/badge.svg)\n\n## Usage\n"), 0o644); err != nil {
			t.Fatal(err)
		}
		runFixtureGit(t, work, "add", ".")
		runFixtureGit(t, work, "commit", "--quiet", "-m", version)
		runFixtureGit(t, work, "tag", version)
	}

	owner := filepath.Join(base, "owner")
	if err := os.MkdirAll(owner, 0o755); err != nil {
		t.Fatal(err)
	}
	runFixtureGit(t, base, "clone", "--quiet", "--bare", work, filepath.Join(owner, "templates-for-sites"))

	return base, "file://" + filepath.ToSlash(owner) + "/"
}

func TestDownloadSiteTemplateFetchesNewestMatchingTagWithoutGlobalGitConfig(t *testing.T) {
	base, ownerURL := siteTemplateFixture(t)

	home := filepath.Join(base, "home")
	if err := os.MkdirAll(home, 0o755); err != nil {
		t.Fatal(err)
	}
	globalConfig := filepath.Join(home, "global.gitconfig")
	t.Setenv("HOME", home)
	t.Setenv("USERPROFILE", home)
	t.Setenv("XDG_CONFIG_HOME", filepath.Join(home, ".config"))
	t.Setenv("GIT_CONFIG_GLOBAL", globalConfig)
	t.Setenv("GIT_CONFIG_NOSYSTEM", "1")
	t.Setenv("GIT_CONFIG_COUNT", "1")
	t.Setenv("GIT_CONFIG_KEY_0", "url."+ownerURL+".insteadOf")
	t.Setenv("GIT_CONFIG_VALUE_0", "https://github.com/appwrite/")

	var template siteTemplate
	if err := json.Unmarshal([]byte(`{
		"providerOwner": "appwrite",
		"providerRepositoryId": "templates-for-sites",
		"providerVersion": "0.1.*",
		"frameworks": [{"key": "nextjs", "providerRootDirectory": "./nextjs/starter"}]
	}`), &template); err != nil {
		t.Fatal(err)
	}

	siteDir := filepath.Join(base, "project", "sites", "my-site")
	templatesDir := filepath.Join(base, "project", ".appwrite", "templates")
	if err := downloadSiteTemplate(io.Discard, &template, "nextjs", siteDir,
		siteDir, templatesDir, "My Site"); err != nil {
		t.Fatal(err)
	}

	version, err := os.ReadFile(filepath.Join(siteDir, "version.txt"))
	if err != nil {
		t.Fatal(err)
	}
	if string(version) != "0.1.10" {
		t.Errorf("version = %q, want the newest tag matching 0.1.*, 0.1.10", version)
	}

	readme, err := os.ReadFile(filepath.Join(siteDir, "README.md"))
	if err != nil {
		t.Fatal(err)
	}
	if string(readme) != "# My Site\n\n## Usage\n" {
		t.Errorf("readme = %q", readme)
	}

	if _, err := os.Stat(filepath.Join(siteDir, ".git")); !errors.Is(err, os.ErrNotExist) {
		t.Errorf(".git copied into the site: %v", err)
	}
	if _, err := os.Stat(templatesDir); !errors.Is(err, os.ErrNotExist) {
		t.Errorf("templates directory left behind: %v", err)
	}

	for _, path := range []string{globalConfig, filepath.Join(home, ".gitconfig")} {
		if contents, err := os.ReadFile(path); !errors.Is(err, os.ErrNotExist) {
			t.Errorf("global git config written to %s: %s", path, contents)
		}
	}
}

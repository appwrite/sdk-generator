package cmd

import (
	"os"
	"path/filepath"
	"testing"
)

// The install paths are the part of `completion install` that has to match the
// TypeScript exactly. Writing cobra's script anywhere else would leave both
// installed, and whichever the shell found first would win.
//
// Captured by running the shipping TypeScript CLI with a sandboxed HOME.
func TestCompletionInstallPathsMatchTheTypeScript(t *testing.T) {
	home := t.TempDir()
	t.Setenv("HOME", home)

	cases := map[string]string{
		"zsh":  filepath.Join(home, ".zfunc", "_appwrite"),
		"bash": filepath.Join(home, ".local", "share", "bash-completion", "completions", "appwrite"),
		"fish": filepath.Join(home, ".config", "fish", "completions", "appwrite.fish"),
	}

	for shell, want := range cases {
		got, err := completionInstallPath(shell)
		if err != nil {
			t.Fatalf("%s: %v", shell, err)
		}
		if got != want {
			t.Errorf("%s path = %q, want %q", shell, got, want)
		}
	}
}

// Each shell has its own override rather than one shared variable, so setting
// one must not move the others.
func TestCompletionInstallPathHonoursOneOverrideAtATime(t *testing.T) {
	home := t.TempDir()
	t.Setenv("HOME", home)
	t.Setenv("ZSH_COMPLETION_DIR", "/somewhere/else")

	zsh, err := completionInstallPath("zsh")
	if err != nil {
		t.Fatal(err)
	}
	if zsh != filepath.Join("/somewhere/else", "_appwrite") {
		t.Errorf("zsh path = %q, want the override to apply", zsh)
	}

	bash, err := completionInstallPath("bash")
	if err != nil {
		t.Fatal(err)
	}
	want := filepath.Join(home, ".local", "share", "bash-completion", "completions", "appwrite")
	if bash != want {
		t.Errorf("bash path = %q, want %q -- the zsh override must not move it", bash, want)
	}
}

// $SHELL is a path, not a bare name, so the basename is what matters. An
// unsupported shell yields "" rather than a guess, which is what makes
// `completion install` fail loudly instead of writing somewhere invented.
func TestNormalizeShell(t *testing.T) {
	cases := map[string]string{
		"/bin/zsh":       "zsh",
		"/usr/bin/fish":  "fish",
		"bash":           "bash",
		"/bin/BASH":      "bash",
		"/bin/nushell":   "",
		"":               "",
		"/usr/bin/pwsh":  "",
		"powershell.exe": "",
	}

	for value, want := range cases {
		if got := normalizeShell(value); got != want {
			t.Errorf("normalizeShell(%q) = %q, want %q", value, got, want)
		}
	}
}

// The three cobra generators must actually produce a non-empty script, since
// an empty file would install cleanly and silently break completion.
func TestInstallCompletionWritesEachShell(t *testing.T) {
	for _, shell := range completionShells {
		home := t.TempDir()
		t.Setenv("HOME", home)

		path, err := installCompletion(NewRootCommand(), shell)
		if err != nil {
			t.Fatalf("%s: %v", shell, err)
		}

		contents, err := os.ReadFile(path)
		if err != nil {
			t.Fatalf("%s: %v", shell, err)
		}
		if len(contents) == 0 {
			t.Errorf("%s: wrote an empty completion script", shell)
		}
	}
}

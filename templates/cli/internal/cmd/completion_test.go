package cmd

import (
	"os"
	"path/filepath"
	"strings"
	"testing"
)

// The install paths are the part of `completion install` that is contractual:
// writing the script anywhere else would leave two installed, and whichever the
// shell found first would win.
//
// Captured by running the command with a sandboxed HOME.
// setHome points os.UserHomeDir at a temporary directory on every platform.
//
// t.Setenv("HOME", ...) alone is a Unix-only assumption: os.UserHomeDir reads
// USERPROFILE on Windows and ignores HOME entirely, so the test would silently
// assert against the real user profile and fail with a path it never chose.
func setHome(t *testing.T) string {
	t.Helper()

	home := t.TempDir()
	t.Setenv("HOME", home)
	t.Setenv("USERPROFILE", home)

	return home
}

func TestCompletionInstallPathsMatchTheBaseline(t *testing.T) {
	home := setHome(t)

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
	home := setHome(t)
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
		setHome(t)

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

// Writing the file is not installing it. zsh autoloads completions only from a
// directory on its fpath, and ~/.zfunc is not on it, so `completion install`
// reported success while Tab did nothing -- and after an earlier install had
// been removed, Tab reported `_appwrite: function definition file not found`
// from compinit's cached index.
func TestZshInstallSaysWhatIsStillNeeded(t *testing.T) {
	home := setHome(t)

	path, err := installCompletion(NewRootCommand(), "zsh")
	if err != nil {
		t.Fatal(err)
	}

	follow := completionFollowUp("zsh", filepath.Dir(path))

	for _, required := range []string{
		"fpath",
		filepath.Join(home, ".zfunc"),
		"compinit",
		".zcompdump",
	} {
		if !strings.Contains(follow, required) {
			t.Errorf("the zsh follow-up does not mention %q:\n%s", required, follow)
		}
	}
}

// The directory is the one that was actually written to, so an override is
// reflected in the advice rather than the default being printed back.
func TestZshFollowUpNamesTheOverriddenDirectory(t *testing.T) {
	setHome(t)
	t.Setenv("ZSH_COMPLETION_DIR", filepath.Join(t.TempDir(), "elsewhere"))

	path, err := completionInstallPath("zsh")
	if err != nil {
		t.Fatal(err)
	}

	follow := completionFollowUp("zsh", filepath.Dir(path))

	if !strings.Contains(follow, filepath.Dir(path)) {
		t.Errorf("the follow-up does not name %q:\n%s", filepath.Dir(path), follow)
	}
	if strings.Contains(follow, ".zfunc") {
		t.Errorf("the follow-up names the default directory, not the one written:\n%s", follow)
	}
}

// fish loads its completions directory itself, so there is nothing to say.
func TestFishNeedsNoFollowUp(t *testing.T) {
	if follow := completionFollowUp("fish", "/anywhere"); follow != "" {
		t.Errorf("fish was given setup instructions it does not need:\n%s", follow)
	}
}

// These lines are meant to be pasted into a shell. A home directory with a
// space in it -- "/Users/My Name" -- turned one fpath entry into three broken
// ones, so the advice for fixing completion was itself unusable.
func TestFollowUpQuotesPathsAShellWouldSplit(t *testing.T) {
	awkward := "/tmp/my home/.zfunc"

	zsh := completionFollowUp("zsh", awkward)
	if !strings.Contains(zsh, "fpath=('"+awkward+"' $fpath)") {
		t.Errorf("the zsh fpath line is unquoted:\n%s", zsh)
	}

	bash := completionFollowUp("bash", "/tmp/my home/completions")
	if !strings.Contains(bash, "source '/tmp/my home/completions/appwrite'") {
		t.Errorf("the bash source line is unquoted:\n%s", bash)
	}
}

// Quoting every path would put quotes around the one line most users copy.
func TestOrdinaryPathsAreNotQuoted(t *testing.T) {
	follow := completionFollowUp("zsh", "/home/someone/.zfunc")

	if strings.Contains(follow, "'") {
		t.Errorf("an ordinary path was quoted for no reason:\n%s", follow)
	}
}

func TestShellQuote(t *testing.T) {
	cases := map[string]string{
		"/home/a/.zfunc":    "/home/a/.zfunc",
		"/tmp/my home/x":    "'/tmp/my home/x'",
		"/tmp/it's/x":       `'/tmp/it'\''s/x'`,
		"/tmp/$HOME/x":      "'/tmp/$HOME/x'",
		"/tmp/a;rm -rf ~/x": "'/tmp/a;rm -rf ~/x'",
		"/tmp/glob*/x":      "'/tmp/glob*/x'",
		"":                  "''",
	}

	for path, want := range cases {
		if got := shellQuote(path); got != want {
			t.Errorf("shellQuote(%q) = %q, want %q", path, got, want)
		}
	}
}

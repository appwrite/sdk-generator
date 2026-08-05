package cmd

import (
	"fmt"
	"os"
	"path"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/spf13/cobra"
)

// install.sh runs `<executable> completion install` as step 4 of every
// install, so this subcommand has to exist or shell completion quietly stops
// working on upgrade -- the installer treats a failure there as non-fatal and
// prints "Skipped". The Phase 3 surface contract could not have caught it:
// `completion` is hand-written in the TypeScript, not spec-derived, so it is
// absent from a spec-derived contract the same way `client` was.
//
// WHAT IS DELIBERATELY NOT PORTED: the TypeScript renders its own completion
// scripts, and cobra generates its own. The two differ in content, and this
// keeps cobra's -- they are dynamically aware of flags and subcommands in a way
// the hand-rolled ones are not, and a completion script's internals are not
// user-visible output. Base::CLI_COMPLETION_RESPONSES pins the TypeScript's
// script text and is therefore NOT part of the Go conformance run.
//
// The install PATHS are ported exactly, including the three env overrides.
// Those are the part that has to match: writing cobra's zsh script anywhere
// other than where the TypeScript put its own would leave both installed, and
// whichever the shell found first would win.

// completionShells is the set `completion install` supports.
//
// Only three. cobra also generates powershell, and `completion powershell`
// still works -- but the TypeScript has no install path for it and neither
// does this, so `install powershell` is an error rather than a silent write
// to a guessed location.
var completionShells = []string{"zsh", "bash", "fish"}

// registerCompletionInstall hangs `install` off cobra's own `completion`
// command rather than replacing it.
//
// cobra builds that command lazily, during Execute, so it has to be forced
// into existence first -- otherwise there is nothing to attach to and `install`
// would silently land at the root, where nothing looks for it.
func registerCompletionInstall(root *cobra.Command) {
	root.InitDefaultCompletionCmd()

	for _, command := range root.Commands() {
		if command.Name() == "completion" {
			command.AddCommand(newCompletionInstallCommand(root))

			return
		}
	}
}

func newCompletionInstallCommand(root *cobra.Command) *cobra.Command {
	return &cobra.Command{
		Use:   "install [shell]",
		Short: "Install shell completion script",
		Args:  cobra.MaximumNArgs(1),
		RunE: func(command *cobra.Command, args []string) error {
			requested := ""
			if len(args) == 1 {
				requested = args[0]
			}

			shell := normalizeShell(requested)
			if shell == "" {
				// Falls back to $SHELL, matching detectShell().
				shell = normalizeShell(os.Getenv("SHELL"))
			}

			if shell == "" {
				return fmt.Errorf(
					"unable to detect shell. Run %s completion install zsh, bash, or fish",
					app.ExecutableName)
			}

			path, err := installCompletion(root, shell)
			if err != nil {
				return fmt.Errorf("failed to install %s completion: %w", shell, err)
			}

			fmt.Fprintf(command.OutOrStdout(), "Installed %s completion to %s\n", shell, path)

			if follow := completionFollowUp(shell, filepath.Dir(path)); follow != "" {
				fmt.Fprint(command.OutOrStdout(), "\n"+follow)
			}

			return nil
		},
	}
}

// normalizeShell accepts a bare name or a path such as /bin/zsh, and returns
// "" for anything unsupported.
func normalizeShell(value string) string {
	if value == "" {
		return ""
	}

	name := strings.ToLower(filepath.Base(value))
	for _, shell := range completionShells {
		if name == shell {
			return name
		}
	}

	return ""
}

// completionInstallPath ports completionInstallPath() exactly, env overrides
// included. Each shell has its own variable rather than one shared override,
// because a user may relocate one shell's completions without the others.
func completionInstallPath(shell string) (string, error) {
	home, err := os.UserHomeDir()
	if err != nil {
		return "", err
	}

	name := app.ExecutableName

	switch shell {
	case "zsh":
		// Underscore prefix: zsh's own convention for a compdef file.
		return filepath.Join(
			overridePath("ZSH_COMPLETION_DIR", filepath.Join(home, ".zfunc")),
			"_"+name,
		), nil
	case "bash":
		return filepath.Join(
			overridePath("BASH_COMPLETION_DIR",
				filepath.Join(home, ".local", "share", "bash-completion", "completions")),
			name,
		), nil
	default:
		return filepath.Join(
			overridePath("FISH_COMPLETION_DIR",
				filepath.Join(home, ".config", "fish", "completions")),
			name+".fish",
		), nil
	}
}

// completionFollowUp is what the user still has to do for the script to be
// loaded, or "" when the shell needs nothing.
//
// Writing the file is not installing it. zsh only autoloads completions from a
// directory on its `fpath`, and `~/.zfunc` -- the path both CLIs write to -- is
// not on it by default. So `completion install` reported success and tab
// completion did nothing, with no way for the user to tell which half had
// failed.
//
// The stale-index note is not hypothetical: `_appwrite` autoloaded from a
// directory that has since gone, and compinit's cached index still named it, so
// pressing Tab printed
//
//	(eval):1: _appwrite: function definition file not found
//
// three times and completed nothing. Adding the directory to fpath does not
// clear that cache.
//
// The CLI advises rather than edits: a shell profile is the user's file, and
// appending to it from an installer is how profiles end up with six copies of
// the same line.
func completionFollowUp(shell, directory string) string {
	name := app.ExecutableName

	// Quoted: these lines are meant to be pasted into a shell, and a home
	// directory with a space in it -- "/Users/My Name" -- would otherwise turn
	// one fpath entry into three broken ones.
	quoted := shellQuote(directory)

	switch shell {
	case "zsh":
		return fmt.Sprintf(`zsh loads completions from its fpath, which does not include %s by default.
Add these to ~/.zshrc, then open a new shell:

  fpath=(%s $fpath)
  autoload -Uz compinit && compinit

If Tab then reports "_%s: function definition file not found", compinit is
using a cached index from an earlier install -- clear it with:

  rm -f ~/.zcompdump*
`, directory, quoted, name)
	case "bash":
		// path.Join, not filepath.Join. This line is pasted into a SHELL, and on
		// Windows filepath.Join produced `source '...\appwrite'` -- a backslash
		// is an escape to bash, not a separator, so the one line that has to be
		// copyable was the one that would not work. Both bashes that exist on
		// Windows take forward slashes.
		return fmt.Sprintf(`This directory is loaded by bash-completion. If Tab does nothing, either
bash-completion is not installed, or ~/.bashrc does not source it:

  source /usr/share/bash-completion/bash_completion

You can also source the script directly:

  source %s
`, shellQuote(path.Join(directory, name)))
	default:
		// fish loads ~/.config/fish/completions itself, with no setup.
		return ""
	}
}

// shellQuote makes a path safe to paste into zsh or bash.
//
// Only when it needs it: the overwhelming majority of paths need no quoting,
// and wrapping every one of them would put quotes around the single line most
// users copy for no reason at all.
func shellQuote(path string) string {
	if path != "" && !strings.ContainsAny(path, " \t\n'\"\\$`*?()[]{};&|<>#~!") {
		return path
	}

	// Single quotes suppress every expansion; the only character they cannot
	// hold is a single quote, which is closed, escaped and reopened.
	return "'" + strings.ReplaceAll(path, "'", `'\''`) + "'"
}

func overridePath(variable, fallback string) string {
	if configured := os.Getenv(variable); configured != "" {
		return configured
	}

	return fallback
}

func installCompletion(root *cobra.Command, shell string) (string, error) {
	path, err := completionInstallPath(shell)
	if err != nil {
		return "", err
	}

	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		return "", err
	}

	file, err := os.Create(path)
	if err != nil {
		return "", err
	}
	defer file.Close()

	switch shell {
	case "zsh":
		err = root.GenZshCompletion(file)
	case "bash":
		err = root.GenBashCompletionV2(file, true)
	default:
		err = root.GenFishCompletion(file, true)
	}

	if err != nil {
		return "", err
	}

	return path, file.Close()
}

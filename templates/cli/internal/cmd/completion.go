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

// install.sh runs `<executable> completion install` as step 4 of every install
// and treats a failure as non-fatal, so this subcommand has to exist or shell
// completion quietly stops working on upgrade.
//
// The script CONTENT is cobra's: cobra's scripts are dynamically aware of flags
// and subcommands, and a completion script's internals are not user-visible
// output.
//
// The install PATHS are ported exactly, including the three env overrides --
// writing cobra's zsh script anywhere else would leave both installed, and
// whichever the shell found first would win.

// completionShells is the set `completion install` supports.
//
// Only three. cobra also generates powershell, and `completion powershell`
// still works -- but there is no install path for it, so `install powershell`
// is an error rather than a silent write to a guessed location.
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
// Writing the file is not installing it: zsh autoloads only from a directory on
// its `fpath`, and `~/.zfunc` is not on it by default, so `completion install`
// reported success while tab completion did nothing. compinit's cached index
// also outlives a directory that has gone, and adding to fpath does not clear
// it.
//
// The CLI advises rather than edits -- appending to a shell profile from an
// installer is how profiles end up with six copies of the same line.
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

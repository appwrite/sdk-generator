package cmd

import (
	"fmt"
	"os"
	"path/filepath"
	"strings"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/spf13/cobra"
)

// Ports the `completion install` half of templates/cli/lib/completions.ts.
//
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

//go:build !browser

package cmd

import (
	"errors"
	"fmt"
	"io"
	"io/fs"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strings"
)

// Shared scaffolding for `init function`, `init site` and `init skill`.
//
// All three fetch starter code with git and copy a subtree into the project,
// so the clone, the error translation and the recursive copy live here once.

// runGit runs a git invocation with its output captured.
//
// Output is captured rather than streamed: a sparse checkout prints progress
// that means nothing to someone running `init function`, and the text is only
// wanted when it fails.
func runGit(directory string, script string) error {
	var command *exec.Cmd

	switch {
	case runtime.GOOS == "windows":
		// cmd rather than PowerShell, which has no `&&`.
		command = exec.Command("cmd", "/c", script)
	default:
		command = exec.Command("sh", "-c", script)
	}

	return executeGit(command, directory)
}

// runGitCommand invokes Git without a shell. Use it when arguments contain
// values received from an API or remote repository, so ref names and paths are
// passed literally rather than interpreted as shell syntax.
func runGitCommand(directory string, arguments ...string) error {
	return executeGit(exec.Command("git", arguments...), directory)
}

func executeGit(command *exec.Cmd, directory string) error {
	command.Dir = directory

	output, err := command.CombinedOutput()
	if err == nil {
		return nil
	}

	return gitError(fmt.Sprintf("%s\n%s", err, strings.TrimSpace(string(output))))
}

// resolveGitTag turns a tag pattern from template metadata (for example,
// "0.3.*") into the newest matching concrete tag. Git clone accepts a tag or
// branch name for --branch, but does not expand ref patterns itself.
func resolveGitTag(directory, repository, pattern string) (string, error) {
	if !strings.ContainsAny(pattern, "*?[") {
		return pattern, nil
	}

	command := exec.Command("git", "ls-remote", "--refs", "--tags",
		"--sort=-version:refname", repository, pattern)
	command.Dir = directory
	output, err := command.CombinedOutput()
	if err != nil {
		return "", gitError(fmt.Sprintf("%s\n%s", err,
			strings.TrimSpace(string(output))))
	}

	return gitTagFromRemoteOutput(pattern, string(output))
}

func gitTagFromRemoteOutput(pattern, output string) (string, error) {
	for _, line := range strings.Split(output, "\n") {
		fields := strings.Fields(line)
		if len(fields) != 2 {
			continue
		}
		if tag := strings.TrimPrefix(fields[1], "refs/tags/"); tag != fields[1] {
			return tag, nil
		}
	}

	return "", fmt.Errorf("no git tag matches template version %s", pattern)
}

// gitError translates a git failure into an actionable suggestion.
//
// Two failures are common enough to be worth naming: a git too old for
// `--sparse`, and no git at all. Both otherwise surface as a bare non-zero exit
// that says nothing about what to do next.
func gitError(message string) error {
	switch {
	case strings.Contains(message, "error: unknown option"):
		return fmt.Errorf("%s \n\nSuggestion: Try updating your git to the "+
			"latest version, then trying to run this command again", message)
	case strings.Contains(message, "is not recognized as an internal or external command,"),
		strings.Contains(message, "command not found"):
		return fmt.Errorf("%s \n\nSuggestion: It appears that git is not "+
			"installed, try installing git then trying to run this command again", message)
	}

	return errors.New(message)
}

// copyTree copies a directory recursively.
//
// Symlinks are copied as symlinks: a template that
// ships one is describing its own layout, and resolving it would silently turn
// a link into a duplicate of its target.
func copyTree(source, destination string) error {
	info, err := os.Lstat(source)
	if err != nil {
		return err
	}

	switch {
	case info.Mode()&os.ModeSymlink != 0:
		target, err := os.Readlink(source)
		if err != nil {
			return err
		}
		_ = os.Remove(destination)

		return os.Symlink(target, destination)

	case info.IsDir():
		if err := os.MkdirAll(destination, 0o755); err != nil {
			return err
		}

		entries, err := os.ReadDir(source)
		if err != nil {
			return err
		}

		for _, entry := range entries {
			if err := copyTree(
				filepath.Join(source, entry.Name()),
				filepath.Join(destination, entry.Name()),
			); err != nil {
				return err
			}
		}

		return nil
	}

	return copyFile(source, destination, info.Mode().Perm())
}

func copyFile(source, destination string, mode fs.FileMode) error {
	from, err := os.Open(source)
	if err != nil {
		return err
	}
	defer from.Close()

	to, err := os.OpenFile(destination, os.O_WRONLY|os.O_CREATE|os.O_TRUNC, mode)
	if err != nil {
		return err
	}
	defer to.Close()

	if _, err := io.Copy(to, from); err != nil {
		return err
	}

	return to.Close()
}

// retitleReadme rewrites a template README's heading to the resource's name.
//
// Implements the `newReadmeFile.splice(1, 2)` dance: line 0 becomes the title and
// the TWO lines after it are dropped, which is how the templates' badge row and
// the blank line under it disappear. A README shorter than that is left with
// whatever remains rather than erroring -- splice() on a short array simply
// removes fewer elements.
func retitleReadme(path, name string) error {
	contents, err := os.ReadFile(path)
	if err != nil {
		return err
	}

	lines := strings.Split(string(contents), "\n")
	if len(lines) == 0 {
		return nil
	}

	lines[0] = "# " + name

	remove := min(2, len(lines)-1)
	lines = append(lines[:1], lines[1+remove:]...)

	return os.WriteFile(path, []byte(strings.Join(lines, "\n")), 0o644)
}

// isEmptyDirectory reports whether a path holds no entries.
func isEmptyDirectory(path string) bool {
	entries, err := os.ReadDir(path)

	return err == nil && len(entries) == 0
}

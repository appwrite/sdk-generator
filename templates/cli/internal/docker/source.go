package docker

import (
	"fmt"
	"io/fs"
	"os"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/ignore"
)

// AppwriteDirectory is the per-function scratch directory the emulation uses
// for logs, the built bundle and hot-swap staging.
const AppwriteDirectory = ".appwrite"

// Source is a function's directory and the files that survive its ignore rules.
type Source struct {
	Directory string
	// Files are relative to Directory and slash-separated, in walk order.
	Files   []string
	Matcher *ignore.Matcher
}

// matcherFor builds the ignore rules for a function.
//
// `.appwrite` is always excluded -- it holds the build output, and packing a
// previous build into the next one would grow without bound. The function's own
// `ignore` field wins over .gitignore; they are not combined, so a config that
// sets `ignore` silently stops honouring .gitignore.
func matcherFor(function config.Function, directory string) *ignore.Matcher {
	matcher := ignore.New().Add(AppwriteDirectory)

	if !function.Ignore.IsEmpty() {
		return matcher.AddAll(function.Ignore.Rules())
	}

	if contents, err := os.ReadFile(filepath.Join(directory, ".gitignore")); err == nil {
		matcher.Add(string(contents))
	}

	return matcher
}

// CollectSource walks a function's directory and applies its ignore rules.
func CollectSource(local *config.Local, function config.Function) (Source, error) {
	directory := local.ResolveResourcePath("functions", function.Path)
	matcher := matcherFor(function, directory)

	files, err := walk(directory)
	if err != nil {
		return Source{}, err
	}

	return Source{Directory: directory, Files: matcher.Filter(files), Matcher: matcher}, nil
}

// walk lists every file under a directory, relative and slash-separated.
//
// Dotfiles are included: a function that needs a `.npmrc` at build time would
// otherwise fail to build with no indication why.
func walk(directory string) ([]string, error) {
	var files []string

	err := filepath.WalkDir(directory, func(path string, entry fs.DirEntry, err error) error {
		if err != nil {
			return err
		}
		if entry.IsDir() {
			return nil
		}

		// Only regular files and symlinks are packed.
		if !entry.Type().IsRegular() && entry.Type()&os.ModeSymlink == 0 {
			return nil
		}

		relative, err := filepath.Rel(directory, path)
		if err != nil {
			return err
		}
		files = append(files, filepath.ToSlash(relative))

		return nil
	})
	if err != nil {
		return nil, err
	}

	return files, nil
}

// AssertSource checks a function can be built before Docker is involved.
//
// Every failure here is a configuration mistake the user can fix, and each is
// reported with the field to change. Ports assertFunctionSourceCode().
func AssertSource(local *config.Local, function config.Function) error {
	directory := local.ResolveResourcePath("functions", function.Path)

	if _, err := os.Stat(directory); err != nil {
		return fmt.Errorf(
			"function path '%s' was not found. Add your source code before running locally",
			function.Path)
	}

	source, err := CollectSource(local, function)
	if err != nil {
		return err
	}

	if function.Entrypoint == "" {
		return fmt.Errorf(
			"function '%s' is missing an entrypoint. Update appwrite.config.json before running locally",
			function.Name)
	}

	entrypoint := filepath.ToSlash(filepath.Clean(function.Entrypoint))

	// Checked before existence: an entrypoint that exists but is ignored fails
	// at build time inside the container, where the cause is invisible.
	if source.Matcher.Ignores(entrypoint) {
		return fmt.Errorf(
			"entrypoint '%s' is ignored by your local ignore rules. "+
				"Update appwrite.config.json or your ignore file before running locally",
			function.Entrypoint)
	}

	if _, err := os.Stat(filepath.Join(directory, filepath.FromSlash(entrypoint))); err != nil {
		return fmt.Errorf(
			"entrypoint '%s' was not found in '%s'. Add your source code before running locally",
			function.Entrypoint, function.Path)
	}

	if len(source.Files) == 0 {
		return fmt.Errorf(
			"no source files were found in '%s'. Add your source code before running locally",
			function.Path)
	}

	return nil
}

// ImageName is the open-runtimes image for a function's runtime.
func ImageName(function config.Function) string {
	return fmt.Sprintf("openruntimes/%s:%s-%s",
		function.RuntimeName(), OpenRuntimesVersion, function.RuntimeVersion())
}

// CopyInto copies the given relative files from one directory to another,
// creating parent directories as it goes.
func CopyInto(destination, sourceDirectory string, files []string) error {
	for _, file := range files {
		target := filepath.Join(destination, filepath.FromSlash(file))
		if err := os.MkdirAll(filepath.Dir(target), 0o755); err != nil {
			return err
		}

		contents, err := os.ReadFile(filepath.Join(sourceDirectory, filepath.FromSlash(file)))
		if err != nil {
			return err
		}

		// Mode is taken from the source so an executable helper script stays
		// executable inside the container.
		mode := fs.FileMode(0o644)
		if info, err := os.Stat(filepath.Join(sourceDirectory, filepath.FromSlash(file))); err == nil {
			mode = info.Mode().Perm()
		}

		if err := os.WriteFile(target, contents, mode); err != nil {
			return err
		}
	}

	return nil
}

// ScratchPath is a path inside a function's .appwrite directory.
func ScratchPath(functionDirectory string, parts ...string) string {
	return filepath.Join(append([]string{functionDirectory, AppwriteDirectory}, parts...)...)
}

// IsDependencyChange reports whether any changed file forces a rebuild rather
// than a hot swap.
func IsDependencyChange(tool SystemTool, changed []string) bool {
	for _, file := range changed {
		normalized := filepath.ToSlash(file)
		for _, dependency := range tool.DependencyFiles {
			// The watcher's path is compared to the bare filename, so only a
			// dependency file at the function root triggers a rebuild -- one in
			// a subdirectory does not.
			if normalized == dependency {
				return true
			}
		}
	}

	return false
}

// quoteShellArgument escapes a config value interpolated into a shell command
// run inside the container.
//
// `helpers/build.sh "<commands>"` is assembled by concatenation, so a double
// quote in `commands` would end the argument early and the rest would be
// reinterpreted as shell. This escapes instead, which cannot regress a working
// config: any `commands` value it changes the behaviour of is one that would
// otherwise be mangled.
//
// The argument shape is unchanged, because the container's helper expects
// exactly one quoted argument.
func quoteShellArgument(value string) string {
	return strings.ReplaceAll(value, `"`, `\"`)
}

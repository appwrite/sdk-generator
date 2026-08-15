package config

import (
	"errors"
	"fmt"
	"os"
	"path/filepath"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// This file lives in user repositories and is code-reviewed, so reading and
// rewriting it must not change a byte that the user did not ask to change.

// LocalFileName is the project config the CLI reads from the working directory.
const LocalFileName = "appwrite.config.json"

// LegacyLocalFileName is the name the config had before it was renamed.
//
// Projects
// created by older CLIs still carry it.
const LegacyLocalFileName = "appwrite.json"

// configKeyOrder is the order top-level keys are written in.
//
// Without it every
// write would reorder the file according to whatever order the data happened to
// be built in, producing a diff on every command that touches the config.
var configKeyOrder = []string{
	"organizationId",
	"projectId",
	"projectName",
	"endpoint",
	"includes",
	"settings",
	"functions",
	"sites",
	"databases",
	"collections",
	"tablesDB",
	"tables",
	"buckets",
	"teams",
	"webhooks",
	"topics",
	"messages",
}

// resourceKeys are the top-level arrays that can be split into their own files
// and that are dropped from the root document when empty.
var resourceKeys = []string{
	"databases",
	"functions",
	"topics",
	"messages",
	"sites",
	"buckets",
	"tablesDB",
	"tables",
	"teams",
	"webhooks",
	"collections",
}

func isResourceKey(key string) bool {
	for _, resource := range resourceKeys {
		if resource == key {
			return true
		}
	}

	return false
}

// Local is a project's appwrite.config.json.
//
// Data holds the document as read, with any `includes` resources merged in, so
// callers see one config regardless of how it is split on disk. Write puts the
// split back.
type Local struct {
	path     string
	Data     *jsonx.Object
	includes map[string]string
}

// LocalPath returns the config path for a directory.
func LocalPath(directory string) string {
	return filepath.Join(directory, LocalFileName)
}

// FindLocalPath locates the project config, searching upwards from the working
// directory, because people run `push` from inside `functions/<name>/` as
// readily as from the root.
//
// The walk stops at the home directory, so a stray config there cannot capture
// every unrelated project. When nothing is found the working directory's path is
// returned, so callers report a missing config where the user is.
func FindLocalPath() string {
	working, err := os.Getwd()
	if err != nil {
		return LocalPath(".")
	}

	home, _ := os.UserHomeDir()

	for directory := working; ; {
		// A config in the home directory counts only when that is where the
		// command was run.
		if home != "" && directory == home && working != home {
			break
		}

		for _, name := range []string{LocalFileName, LegacyLocalFileName} {
			candidate := filepath.Join(directory, name)
			if info, err := os.Stat(candidate); err == nil && !info.IsDir() {
				return candidate
			}
		}

		parent := filepath.Dir(directory)
		if parent == directory {
			break
		}
		directory = parent
	}

	return LocalPath(working)
}

// LoadLocal reads a project config, resolving `includes`.
//
// Unlike the global preferences, a malformed project config is an error rather
// than an empty document: silently treating a typo'd config as absent would let
// `push` deploy nothing and report success.
func LoadLocal(path string) (*Local, error) {
	contents, err := os.ReadFile(path)
	if err != nil {
		return nil, err
	}

	root := jsonx.NewObject()
	if err := root.UnmarshalJSON(contents); err != nil {
		return nil, fmt.Errorf("failed to read config file %q: %w", path, err)
	}

	local := &Local{path: path, Data: root, includes: map[string]string{}}
	if err := local.resolveIncludes(); err != nil {
		return nil, err
	}

	return local, nil
}

// LoadOrCreateLocal is LoadLocal, but an ABSENT file yields an empty config
// rather than an error.
//
// For `init project`, which is the command a user runs in a directory that has
// no config yet. A malformed file is still an error: silently starting fresh
// would discard whatever the user had.
func LoadOrCreateLocal(path string) (*Local, error) {
	local, err := LoadLocal(path)
	if errors.Is(err, os.ErrNotExist) {
		return &Local{path: path, Data: jsonx.NewObject(), includes: map[string]string{}}, nil
	}

	return local, err
}

// Path returns the file backing this config.
func (l *Local) Path() string {
	return l.path
}

// Includes reports which resources are stored in separate files.
func (l *Local) Includes() map[string]string {
	copied := make(map[string]string, len(l.includes))
	for resource, path := range l.includes {
		copied[resource] = path
	}

	return copied
}

// resolveIncludes replaces the `includes` map with the resource arrays it
// points at, so callers see a single merged document.
func (l *Local) resolveIncludes() error {
	includes := l.Data.GetObject("includes")
	if includes == nil {
		return nil
	}

	for _, resource := range includes.Keys() {
		includePath := includes.GetString(resource)
		if includePath == "" {
			continue
		}

		resolved, err := l.resolveIncludePath(resource, includePath)
		if err != nil {
			return err
		}
		l.includes[resource] = includePath

		contents, err := os.ReadFile(resolved)
		if err != nil {
			return fmt.Errorf("failed to read included config %q: %w", includePath, err)
		}

		decoded, err := jsonx.DecodeValue(contents)
		if err != nil {
			return fmt.Errorf("failed to read included config %q: %w", includePath, err)
		}
		items, ok := decoded.([]any)
		if !ok {
			return fmt.Errorf("config resource %q must be an array", resource)
		}
		l.Data.Set(resource, items)
	}

	return nil
}

// resolveIncludePath resolves an include relative to the config file and
// refuses to escape the project directory.
//
// A config that points at
// `../../.ssh/id_rsa` would otherwise have that file overwritten on the next
// write.
func (l *Local) resolveIncludePath(resource, includePath string) (string, error) {
	root, err := filepath.Abs(filepath.Dir(l.path))
	if err != nil {
		return "", err
	}
	// Both sides are resolved, not just the include. A project directory is
	// often reached through a symlink -- macOS puts temp directories behind one,
	// and so does any checkout under a symlinked home -- and comparing a
	// resolved include against an unresolved root would read every ordinary
	// include as an escape.
	root = resolveSymlinks(root)

	resolved := filepath.Join(root, includePath)
	if filepath.IsAbs(includePath) {
		resolved = includePath
	}
	resolved = resolveSymlinks(filepath.Clean(resolved))

	relative, err := filepath.Rel(root, resolved)
	if err != nil || relative == ".." || strings.HasPrefix(relative, ".."+string(filepath.Separator)) {
		return "", fmt.Errorf("config include for %q must stay inside the project directory: %s",
			resource, includePath)
	}

	return resolved, nil
}

// resolveSymlinks resolves path as far as it exists on disk. Comparing cleaned
// path strings is not containment: a link named `functions.json` inside the
// project can point anywhere, and the read and write would follow it.
//
// EvalSymlinks fails outright when the leaf does not exist -- ordinary for an
// include written for the first time -- so the deepest existing ancestor is
// resolved and the missing tail rejoined.
func resolveSymlinks(path string) string {
	tail := ""
	for current := path; ; {
		if resolved, err := filepath.EvalSymlinks(current); err == nil {
			return filepath.Join(resolved, tail)
		}

		parent := filepath.Dir(current)
		if parent == current {
			return path
		}
		tail = filepath.Join(filepath.Base(current), tail)
		current = parent
	}
}

// Write persists the config, splitting `includes` back out, dropping empty
// resource arrays and restoring the canonical key order.
func (l *Local) Write() error {
	root := jsonx.NewObject()
	for _, key := range l.Data.Keys() {
		value, _ := l.Data.Get(key)
		root.Set(key, value)
	}

	// Split included resources back into their own files and drop them from the
	// root document.
	for resource, includePath := range l.includes {
		items, _ := root.Get(resource)
		if items == nil {
			items = []any{}
		}
		list, ok := items.([]any)
		if !ok {
			return fmt.Errorf("config resource %q must be an array", resource)
		}

		resolved, err := l.resolveIncludePath(resource, includePath)
		if err != nil {
			return err
		}
		if err := writeJSONFile(resolved, list); err != nil {
			return err
		}
		root.Delete(resource)
	}

	if len(l.includes) > 0 {
		includes := jsonx.NewObject()
		// Written in configKeyOrder's resource order so the includes map itself
		// is stable rather than following Go's map iteration.
		for _, resource := range resourceKeys {
			if path, ok := l.includes[resource]; ok {
				includes.Set(resource, path)
			}
		}
		root.Set("includes", includes)
	} else {
		root.Delete("includes")
	}

	return writeJSONFile(l.path, orderConfigKeys(pruneEmptyResourceArrays(root)))
}

// pruneEmptyResourceArrays drops resource keys whose array is empty.
//
// An empty array and an absent key
// mean the same thing to the CLI, and writing `"teams": []` into a config the
// user never gave teams to is noise in their diff.
func pruneEmptyResourceArrays(data *jsonx.Object) *jsonx.Object {
	pruned := jsonx.NewObject()
	for _, key := range data.Keys() {
		value, _ := data.Get(key)
		if isResourceKey(key) {
			if list, ok := value.([]any); ok && len(list) == 0 {
				continue
			}
		}
		pruned.Set(key, value)
	}

	return pruned
}

// orderConfigKeys emits known keys in canonical order, then anything else in
// its existing order.
//
// Unknown keys are preserved rather than dropped: a
// newer CLI may have written a field this build does not know about, and losing
// it on the next write would be data loss.
func orderConfigKeys(data *jsonx.Object) *jsonx.Object {
	ordered := jsonx.NewObject()

	for _, key := range configKeyOrder {
		if value, ok := data.Get(key); ok {
			ordered.Set(key, value)
		}
	}
	for _, key := range data.Keys() {
		if !ordered.Has(key) {
			value, _ := data.Get(key)
			ordered.Set(key, value)
		}
	}

	return ordered
}

// writeJSONFile writes a config document with four-space indentation, mode
// 0600.
func writeJSONFile(path string, data any) error {
	if err := os.MkdirAll(filepath.Dir(path), 0o700); err != nil {
		return err
	}

	encoded, err := Marshal(data)
	if err != nil {
		return err
	}

	return writeFileAtomically(path, encoded, 0o600)
}

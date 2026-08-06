package config

import (
	"encoding/json"
	"fmt"
	"path/filepath"
)

// The typed view of the `functions` array in appwrite.config.json, and the
// path resolution the emulation needs.
//
// Local keeps the config as an ordered document so a write is byte-faithful
// (invariant 2). Commands that only read a resource want fields, not key
// lookups, so those decode out of that document here.

// Function is one entry of the config's `functions` array.
//
// Only the fields `run` reads are declared. The rest stay in the underlying
// document, which is what Write persists -- decoding into this struct never
// round-trips, so a field missing here cannot be lost.
type Function struct {
	ID         string   `json:"$id"`
	Name       string   `json:"name"`
	Runtime    string   `json:"runtime"`
	Entrypoint string   `json:"entrypoint"`
	Path       string   `json:"path"`
	Commands   string   `json:"commands"`
	Scopes     []string `json:"scopes"`
	// Ignore accepts both shapes the field appears in; see IgnoreRules.
	Ignore IgnoreRules `json:"ignore"`
}

// RuntimeName is the runtime without its version suffix.
//
// `node-22.0` yields `node`, and `python-ml-3.11` yields `python-ml` -- which
// is why this drops the LAST segment rather than taking the first.
func (f Function) RuntimeName() string {
	for index := len(f.Runtime) - 1; index >= 0; index-- {
		if f.Runtime[index] == '-' {
			return f.Runtime[:index]
		}
	}

	return f.Runtime
}

// RuntimeVersion is the version suffix of the runtime.
func (f Function) RuntimeVersion() string {
	for index := len(f.Runtime) - 1; index >= 0; index-- {
		if f.Runtime[index] == '-' {
			return f.Runtime[index+1:]
		}
	}

	return ""
}

// Functions decodes the config's `functions` array.
func (l *Local) Functions() ([]Function, error) {
	value, ok := l.Data.Get("functions")
	if !ok {
		return nil, nil
	}

	// Re-encoded rather than walked field by field: the ordered document is
	// built for faithful rewriting, and decoding through JSON keeps this
	// accessor short enough to be obviously correct.
	encoded, err := Marshal(value)
	if err != nil {
		return nil, err
	}

	var functions []Function
	if err := json.Unmarshal(encoded, &functions); err != nil {
		return nil, fmt.Errorf("reading functions from %s: %w", l.Path(), err)
	}

	return functions, nil
}

// Function looks up one function by id.
func (l *Local) Function(id string) (Function, error) {
	functions, err := l.Functions()
	if err != nil {
		return Function{}, err
	}

	for _, function := range functions {
		if function.ID == id {
			return function, nil
		}
	}

	return Function{}, fmt.Errorf("function '%s' not found", id)
}

// Dirname is the directory holding the config file.
func (l *Local) Dirname() string {
	return filepath.Dir(l.Path())
}

// ResourceDirname is the directory a resource's relative paths are resolved
// against.
//
// A resource split into its own file via `includes` resolves against THAT
// file's directory, not the root config's. Getting this wrong points the
// emulation at a directory that does not exist whenever a project splits its
// functions out.
func (l *Local) ResourceDirname(resource string) string {
	include, ok := l.Includes()[resource]
	if !ok || include == "" {
		return l.Dirname()
	}

	resolved, err := l.resolveIncludePath(resource, include)
	if err != nil {
		return l.Dirname()
	}

	return filepath.Dir(resolved)
}

// ResolveResourcePath turns a resource's configured path into an absolute one.
//
// Absolute, not merely joined. The TypeScript uses path.resolve(), which
// resolves against the working directory; filepath.Join does not, and a config
// loaded by its bare filename leaves a relative result. Docker then rejects the
// bind mount -- it reads a relative source as a named volume, and the error
// names volume-naming rules rather than the path.
func (l *Local) ResolveResourcePath(resource, path string) string {
	if path == "" {
		return path
	}
	if filepath.IsAbs(path) {
		return path
	}

	joined := filepath.Join(l.ResourceDirname(resource), path)

	absolute, err := filepath.Abs(joined)
	if err != nil {
		return joined
	}

	return absolute
}

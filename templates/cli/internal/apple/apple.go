// Package apple creates APNs keys through the Apple Developer portal.
//
// Apple has no public API for APNs keys, so this runs a small Node script on
// top of @expo/apple-utils, the library eas-cli uses for the same job. The
// script, its package.json and its lockfile are embedded in the binary and
// installed on first use, so no Apple sign-in code ships inside the CLI itself.
package apple

import (
	"bytes"
	"context"
	"embed"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strconv"
)

//go:embed helper/helper.js helper/package.json helper/package-lock.json
var helperFiles embed.FS

const minimumNodeMajor = 18

// ErrUnavailable means the helper cannot run here: Node.js or npm is missing,
// too old, or the install failed. The caller falls back to another way of
// getting the key.
var ErrUnavailable = errors.New("automatic APNs key creation is unavailable")

// Key is a newly created APNs key. Apple lets its contents be downloaded once.
type Key struct {
	KeyID  string `json:"keyId"`
	TeamID string `json:"teamId"`
	P8     string `json:"p8"`
}

// ExistingKey is a key already on the team, listed when no new one fits.
type ExistingKey struct {
	ID        string `json:"id"`
	Name      string `json:"name"`
	CanRevoke bool   `json:"canRevoke"`
}

// MaxKeysError means the team already has as many APNs keys as Apple allows.
type MaxKeysError struct {
	Keys []ExistingKey
}

func (e *MaxKeysError) Error() string {
	return "the Apple team already has the maximum number of APNs keys"
}

// Helper runs the embedded script.
type Helper struct {
	// Dir is where the script and its dependencies are installed.
	Dir string
	// Node and Npm are the commands to run. Empty means "node" and "npm".
	Node string
	Npm  string
	// The script prompts for the Apple ID, password and two-factor code, so
	// these are normally the terminal.
	Stdin  io.Reader
	Stdout io.Writer
	Stderr io.Writer
}

type result struct {
	Key
	Error   string        `json:"error"`
	Message string        `json:"message"`
	Keys    []ExistingKey `json:"keys"`
}

// CreateKey signs in to the Apple Developer portal, creates a team-scoped
// APNs key named name on teamID, and downloads it. An empty teamID lets the
// sign-in choose the team, asking when the account has several.
func (h Helper) CreateKey(ctx context.Context, teamID, name string) (Key, error) {
	if err := h.checkNode(ctx); err != nil {
		return Key{}, err
	}
	if err := h.install(ctx); err != nil {
		return Key{}, err
	}

	scratch, err := os.MkdirTemp("", "appwrite-apple-")
	if err != nil {
		return Key{}, err
	}
	defer os.RemoveAll(scratch)
	out := filepath.Join(scratch, "result.json")

	arguments := []string{filepath.Join(h.Dir, "helper.js"), "--name", name, "--out", out}
	if teamID != "" {
		arguments = append(arguments, "--team-id", teamID)
	}
	command := exec.CommandContext(ctx, h.node(), arguments...)
	command.Dir = h.Dir
	command.Stdin, command.Stdout, command.Stderr = h.Stdin, h.Stdout, h.Stderr
	// apple-utils stores the Apple password in the macOS Keychain unless told
	// not to. The CLI never keeps the password.
	command.Env = append(os.Environ(), "EXPO_NO_KEYCHAIN=1")
	runErr := command.Run()

	contents, err := os.ReadFile(out)
	if err != nil {
		if runErr != nil {
			return Key{}, fmt.Errorf("the Apple sign-in helper failed: %w", runErr)
		}

		return Key{}, fmt.Errorf("the Apple sign-in helper returned no result: %w", err)
	}

	return decodeResult(contents)
}

func decodeResult(contents []byte) (Key, error) {
	var decoded result
	if err := json.Unmarshal(contents, &decoded); err != nil {
		return Key{}, fmt.Errorf("the Apple sign-in helper returned an unreadable result: %w", err)
	}
	switch decoded.Error {
	case "":
	case "max-keys":
		return Key{}, &MaxKeysError{Keys: decoded.Keys}
	default:
		if decoded.Message == "" {
			decoded.Message = decoded.Error
		}

		return Key{}, errors.New(decoded.Message)
	}
	if decoded.KeyID == "" || decoded.P8 == "" {
		return Key{}, errors.New("the Apple sign-in helper returned no key")
	}

	return decoded.Key, nil
}

func (h Helper) node() string {
	if h.Node != "" {
		return h.Node
	}

	return "node"
}

func (h Helper) npm() string {
	if h.Npm != "" {
		return h.Npm
	}

	return "npm"
}

var nodeVersion = regexp.MustCompile(`^v?(\d+)\.`)

func (h Helper) checkNode(ctx context.Context) error {
	output, err := exec.CommandContext(ctx, h.node(), "--version").Output()
	if err != nil {
		return fmt.Errorf("%w: Node.js %d or later is required", ErrUnavailable, minimumNodeMajor)
	}

	return supportedNode(string(bytes.TrimSpace(output)))
}

func supportedNode(version string) error {
	match := nodeVersion.FindStringSubmatch(version)
	if match == nil {
		return fmt.Errorf("%w: could not read the Node.js version %q", ErrUnavailable, version)
	}
	if major, _ := strconv.Atoi(match[1]); major < minimumNodeMajor {
		return fmt.Errorf("%w: Node.js %d or later is required, found %s", ErrUnavailable, minimumNodeMajor, version)
	}

	return nil
}

// install writes the embedded files into Dir and runs npm ci when the
// lockfile changed or the dependencies are missing. The lockfile pins the
// exact package hashes, and --ignore-scripts keeps install scripts from running.
func (h Helper) install(ctx context.Context) error {
	if err := os.MkdirAll(h.Dir, 0o700); err != nil {
		return err
	}
	changed := false
	for _, name := range []string{"helper.js", "package.json", "package-lock.json"} {
		contents, err := helperFiles.ReadFile("helper/" + name)
		if err != nil {
			return err
		}
		path := filepath.Join(h.Dir, name)
		if current, err := os.ReadFile(path); err == nil && bytes.Equal(current, contents) {
			continue
		}
		if err := os.WriteFile(path, contents, 0o600); err != nil {
			return err
		}
		changed = true
	}

	if _, err := os.Stat(filepath.Join(h.Dir, "node_modules", "@expo", "apple-utils", "package.json")); err == nil && !changed {
		return nil
	}
	command := exec.CommandContext(ctx, h.npm(), "ci", "--ignore-scripts", "--no-audit", "--no-fund", "--loglevel=error")
	command.Dir = h.Dir
	command.Stdout, command.Stderr = h.Stderr, h.Stderr
	if err := command.Run(); err != nil {
		// A failed install must not look complete on the next run.
		_ = os.Remove(filepath.Join(h.Dir, "package-lock.json"))

		return fmt.Errorf("%w: npm ci failed in %s: %v", ErrUnavailable, h.Dir, err)
	}

	return nil
}

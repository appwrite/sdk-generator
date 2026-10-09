// Package expo is the APNs key setup built on @expo/apple-utils, the library
// eas-cli creates APNs keys with.
//
// It runs a small Node script embedded in the binary, with its package.json
// and a lockfile that pins @expo/apple-utils 2.2.1 (MIT) by hash. They are
// installed into the session folder on first use with npm ci
// --ignore-scripts. It runs on Node.js 18 or later, and downloads the Node.js
// LTS into the session folder when the machine has none it can use.
package expo

import (
	"bytes"
	"context"
	"embed"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strconv"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)

//go:embed helper/helper.js helper/package.json helper/package-lock.json
var helperFiles embed.FS

const minimumNodeMajor = 18

// Adapter creates APNs keys with @expo/apple-utils.
type Adapter struct {
	// Node and Npm are the commands to run. Empty means "node" and "npm"
	// from PATH, or a downloaded Node.js when those are missing or too old.
	Node string
	Npm  string
	// For tests: where Node.js builds are downloaded from, the client, and
	// the platform. Zero values mean nodejs.org and this machine.
	NodeDist string
	HTTP     *http.Client
	GOOS     string
	GOARCH   string
	// apple-utils prompts for anything the request does not answer, and for
	// the two-factor code, on the terminal. Nil means the process's own.
	Stdin  io.Reader
	Stdout io.Writer
	Stderr io.Writer
}

// New returns the adapter using node and npm from PATH and the terminal.
func New() *Adapter {
	return &Adapter{}
}

// Name is how people pick this setup.
func (a *Adapter) Name() string {
	return "expo"
}

type result struct {
	KeyID   string `json:"keyId"`
	TeamID  string `json:"teamId"`
	P8      string `json:"p8"`
	Error   string `json:"error"`
	Message string `json:"message"`
	Keys    []struct {
		ID        string `json:"id"`
		Name      string `json:"name"`
		CanRevoke bool   `json:"canRevoke"`
	} `json:"keys"`
}

// CreateKey installs the helper if needed and runs it. apple-utils always
// creates keys for both APNs environments; for a single-environment request
// that key works too.
func (a *Adapter) CreateKey(ctx context.Context, request apns.Request) (apns.Key, error) {
	log := request.Log
	if log == nil {
		log = func(string, ...any) {}
	}
	dir := request.SessionDir
	if dir == "" {
		scratch, err := os.MkdirTemp("", "appwrite-apns-expo-")
		if err != nil {
			return apns.Key{}, err
		}
		defer os.RemoveAll(scratch)
		dir = scratch
	}
	tools, err := a.toolchain(ctx, dir, log)
	if err != nil {
		return apns.Key{}, err
	}
	if err := a.install(ctx, dir, tools); err != nil {
		return apns.Key{}, err
	}
	if request.Environment != apns.EnvironmentAll {
		log("The expo setup creates keys for both APNs environments; the key also works for %s.", request.Environment)
	}

	scratch, err := os.MkdirTemp("", "appwrite-apns-expo-result-")
	if err != nil {
		return apns.Key{}, err
	}
	defer os.RemoveAll(scratch)
	out := filepath.Join(scratch, "result.json")

	arguments := []string{filepath.Join(dir, "helper.js"), "--name", request.Name, "--out", out}
	if request.TeamID != "" {
		arguments = append(arguments, "--team-id", request.TeamID)
	}
	if request.Reset {
		arguments = append(arguments, "--reset", "true")
	}
	command := exec.CommandContext(ctx, tools.node, arguments...)
	command.Dir = dir
	command.Stdin, command.Stdout, command.Stderr = a.terminal()
	// apple-utils reads the Apple ID and password from these instead of
	// asking, and stores the password in the macOS Keychain unless told not
	// to. The CLI never keeps the password.
	command.Env = append(tools.env(), "EXPO_NO_KEYCHAIN=1")
	if request.AppleID != "" {
		command.Env = append(command.Env, "EXPO_APPLE_ID="+request.AppleID)
	}
	if request.Password != "" {
		command.Env = append(command.Env, "EXPO_APPLE_PASSWORD="+request.Password)
	}
	runErr := command.Run()

	contents, err := os.ReadFile(out)
	if err != nil {
		if runErr != nil {
			return apns.Key{}, fmt.Errorf("the @expo/apple-utils helper failed: %w", runErr)
		}

		return apns.Key{}, fmt.Errorf("the @expo/apple-utils helper returned no result: %w", err)
	}

	return decodeResult(contents)
}

func decodeResult(contents []byte) (apns.Key, error) {
	var decoded result
	if err := json.Unmarshal(contents, &decoded); err != nil {
		return apns.Key{}, fmt.Errorf("the @expo/apple-utils helper returned an unreadable result: %w", err)
	}
	switch decoded.Error {
	case "":
	case "max-keys":
		keys := make([]apns.ExistingKey, len(decoded.Keys))
		for index, key := range decoded.Keys {
			keys[index] = apns.ExistingKey{ID: key.ID, Name: key.Name, CanRevoke: key.CanRevoke}
		}

		return apns.Key{}, &apns.MaxKeysError{Keys: keys}
	case "invalid-credentials":
		return apns.Key{}, fmt.Errorf("%w: %s", apns.ErrInvalidCredentials, decoded.Message)
	default:
		if decoded.Message == "" {
			decoded.Message = decoded.Error
		}

		return apns.Key{}, errors.New(decoded.Message)
	}
	if decoded.KeyID == "" || decoded.P8 == "" {
		return apns.Key{}, errors.New("the @expo/apple-utils helper returned no key")
	}

	return apns.Key{KeyID: decoded.KeyID, TeamID: decoded.TeamID, P8: decoded.P8}, nil
}

func (a *Adapter) terminal() (io.Reader, io.Writer, io.Writer) {
	stdin, stdout, stderr := a.Stdin, a.Stdout, a.Stderr
	if stdin == nil {
		stdin = os.Stdin
	}
	if stdout == nil {
		stdout = os.Stdout
	}
	if stderr == nil {
		stderr = os.Stderr
	}

	return stdin, stdout, stderr
}

func (a *Adapter) npm() string {
	if a.Npm != "" {
		return a.Npm
	}

	return "npm"
}

var nodeVersion = regexp.MustCompile(`^v?(\d+)\.`)

func checkNode(ctx context.Context, node string) error {
	output, err := exec.CommandContext(ctx, node, "--version").Output()
	if err != nil {
		return fmt.Errorf("%w: the expo setup needs Node.js %d or later", apns.ErrUnavailable, minimumNodeMajor)
	}

	return supportedNode(string(bytes.TrimSpace(output)))
}

// toolchain is how node and npm are run.
type toolchain struct {
	node string
	npm  []string
	// bin is put first on PATH when node is a downloaded one.
	bin string
}

func (t toolchain) env() []string {
	env := os.Environ()
	if t.bin != "" {
		env = append(env, "PATH="+t.bin+string(os.PathListSeparator)+os.Getenv("PATH"))
	}

	return env
}

// toolchain uses the configured node, then node and npm from PATH, then the
// Node.js downloaded into dir. A configured node is never replaced.
func (a *Adapter) toolchain(ctx context.Context, dir string, log func(string, ...any)) (toolchain, error) {
	if a.Node != "" {
		if err := checkNode(ctx, a.Node); err != nil {
			return toolchain{}, err
		}

		return toolchain{node: a.Node, npm: []string{a.npm()}}, nil
	}
	if checkNode(ctx, "node") == nil {
		if _, err := exec.LookPath(a.npm()); err == nil {
			return toolchain{node: "node", npm: []string{a.npm()}}, nil
		}
	}

	node, npm, err := a.downloadNode(ctx, filepath.Join(dir, "runtime"), log)
	if err != nil {
		return toolchain{}, fmt.Errorf("%w: the expo setup needs Node.js %d or later, and downloading it failed: %v", apns.ErrUnavailable, minimumNodeMajor, err)
	}
	if err := checkNode(ctx, node); err != nil {
		return toolchain{}, err
	}

	return toolchain{node: node, npm: []string{node, npm}, bin: filepath.Dir(node)}, nil
}

func supportedNode(version string) error {
	match := nodeVersion.FindStringSubmatch(version)
	if match == nil {
		return fmt.Errorf("%w: could not read the Node.js version %q", apns.ErrUnavailable, version)
	}
	if major, _ := strconv.Atoi(match[1]); major < minimumNodeMajor {
		return fmt.Errorf("%w: the expo setup needs Node.js %d or later, found %s", apns.ErrUnavailable, minimumNodeMajor, version)
	}

	return nil
}

// install writes the embedded files into dir and runs npm ci when the
// lockfile changed or the dependencies are missing. The lockfile pins the
// exact package hashes, and --ignore-scripts keeps install scripts from
// running.
func (a *Adapter) install(ctx context.Context, dir string, tools toolchain) error {
	if err := os.MkdirAll(dir, 0o700); err != nil {
		return err
	}
	changed := false
	for _, name := range []string{"helper.js", "package.json", "package-lock.json"} {
		contents, err := helperFiles.ReadFile("helper/" + name)
		if err != nil {
			return err
		}
		path := filepath.Join(dir, name)
		if current, err := os.ReadFile(path); err == nil && bytes.Equal(current, contents) {
			continue
		}
		if err := os.WriteFile(path, contents, 0o600); err != nil {
			return err
		}
		changed = true
	}

	if _, err := os.Stat(filepath.Join(dir, "node_modules", "@expo", "apple-utils", "package.json")); err == nil && !changed {
		return nil
	}
	_, _, stderr := a.terminal()
	arguments := append(append([]string{}, tools.npm[1:]...), "ci", "--ignore-scripts", "--no-audit", "--no-fund", "--loglevel=error")
	command := exec.CommandContext(ctx, tools.npm[0], arguments...)
	command.Dir = dir
	command.Env = tools.env()
	command.Stdout, command.Stderr = stderr, stderr
	if err := command.Run(); err != nil {
		// A failed install must not look complete on the next run.
		_ = os.Remove(filepath.Join(dir, "package-lock.json"))

		return fmt.Errorf("%w: npm ci failed in %s: %v", apns.ErrUnavailable, dir, err)
	}

	return nil
}

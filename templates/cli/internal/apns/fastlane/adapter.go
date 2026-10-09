// Package fastlane is the APNs key setup built on fastlane's spaceship, the
// open-source Apple Developer portal client.
//
// It runs a lane from a Fastfile embedded in the binary with the fastlane the
// person already has installed and brings no Ruby of its own: the project's
// Bundler fastlane when its Gemfile lists one, otherwise fastlane from PATH
// (a gem or Homebrew installation). It needs fastlane 2.225.0 or
// later, the first release whose spaceship signs in with SRP.
package fastlane

import (
	"context"
	_ "embed"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strconv"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)

//go:embed helper/Fastfile
var fastfile []byte

// fastlane 2.225.0 (October 2024) is the first that signs in with SRP, which
// Apple has required since then; earlier versions get "503 Service
// Temporarily Unavailable" when signing in.
var minimumVersion = [3]int{2, 225, 0}

// Adapter creates APNs keys with an installed fastlane.
type Adapter struct {
	// Fastlane is the command to run. Empty means the project's Bundler
	// fastlane when there is one, otherwise "fastlane" from PATH.
	Fastlane string
	// Bundle is Bundler's command. Empty means "bundle" from PATH.
	Bundle string
	// WorkDir is the project folder whose Gemfile may list fastlane. Empty
	// means the current directory.
	WorkDir string
	// spaceship prompts for the two-factor code, and for a team when the
	// account has several, on the terminal. Nil means the process's own.
	Stdin  io.Reader
	Stdout io.Writer
	Stderr io.Writer
}

// New returns the adapter using fastlane from PATH and the terminal.
func New() *Adapter {
	return &Adapter{}
}

// Name is how people pick this setup.
func (a *Adapter) Name() string {
	return "fastlane"
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

// CreateKey runs the embedded lane. The Apple ID and password are asked here
// when the request has none, and handed to fastlane as FASTLANE_USER and
// FASTLANE_PASSWORD, so fastlane never offers to keep them in the Keychain.
func (a *Adapter) CreateKey(ctx context.Context, request apns.Request) (apns.Key, error) {
	runner := a.runner()
	if err := runner.check(ctx); err != nil {
		return apns.Key{}, err
	}
	if runner.gemfile != "" && request.Log != nil {
		request.Log("Using fastlane from %s with bundle exec.", runner.gemfile)
	}

	appleID := strings.TrimSpace(request.AppleID)
	if appleID == "" {
		answer, err := request.Asker.Ask("Apple ID (email)", false)
		if err != nil {
			return apns.Key{}, err
		}
		appleID = strings.TrimSpace(answer)
	}
	password := request.Password
	if password == "" {
		answer, err := request.Asker.Ask("Password for "+appleID, true)
		if err != nil {
			return apns.Key{}, err
		}
		password = answer
	}

	// fastlane runs the Fastfile in ./fastlane of its working directory.
	scratch, err := os.MkdirTemp("", "appwrite-apns-fastlane-")
	if err != nil {
		return apns.Key{}, err
	}
	defer os.RemoveAll(scratch)
	if err := os.MkdirAll(filepath.Join(scratch, "fastlane"), 0o700); err != nil {
		return apns.Key{}, err
	}
	if err := os.WriteFile(filepath.Join(scratch, "fastlane", "Fastfile"), fastfile, 0o600); err != nil {
		return apns.Key{}, err
	}
	out := filepath.Join(scratch, "result.json")

	arguments := []string{"create_apns_key", "out:" + out, "name:" + request.Name, "environment:" + string(request.Environment)}
	if request.TeamID != "" {
		arguments = append(arguments, "team_id:"+request.TeamID)
	}
	command := runner.command(ctx, arguments...)
	command.Dir = scratch
	command.Stdin, command.Stdout, command.Stderr = a.terminal()
	command.Env = append(command.Env,
		"FASTLANE_USER="+appleID,
		"FASTLANE_PASSWORD="+password,
		"FASTLANE_SKIP_UPDATE_CHECK=1",
		"FASTLANE_OPT_OUT_USAGE=1",
		"FASTLANE_HIDE_CHANGELOG=1",
	)
	runErr := command.Run()

	contents, err := os.ReadFile(out)
	if err != nil {
		if runErr != nil {
			return apns.Key{}, fmt.Errorf("the fastlane lane failed: %w", runErr)
		}

		return apns.Key{}, fmt.Errorf("the fastlane lane returned no result: %w", err)
	}

	return decodeResult(contents)
}

func decodeResult(contents []byte) (apns.Key, error) {
	var decoded result
	if err := json.Unmarshal(contents, &decoded); err != nil {
		return apns.Key{}, fmt.Errorf("the fastlane lane returned an unreadable result: %w", err)
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
		return apns.Key{}, errors.New("the fastlane lane returned no key")
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

// runner is how fastlane is started: a command, the arguments before the
// lane's, and the environment it needs.
type runner struct {
	name    string
	prefix  []string
	env     []string
	gemfile string
}

var gemfileFastlane = regexp.MustCompile(`(?m)^\s*gem\s+['"]fastlane['"]`)

// runner picks the configured command, then the project's Bundler fastlane,
// then fastlane from PATH. A Bundler fastlane must run with bundle exec and
// the project's Gemfile, since the lane runs in a temporary folder.
func (a *Adapter) runner() runner {
	base := append(os.Environ(), "FASTLANE_SKIP_UPDATE_CHECK=1", "FASTLANE_OPT_OUT_USAGE=1")
	if a.Fastlane != "" {
		return runner{name: a.Fastlane, env: base}
	}
	bundle := a.Bundle
	if bundle == "" {
		bundle = "bundle"
	}
	if gemfile := projectGemfile(a.WorkDir); gemfile != "" {
		if _, err := exec.LookPath(bundle); err == nil {
			return runner{name: bundle, prefix: []string{"exec", "fastlane"}, env: append(base, "BUNDLE_GEMFILE="+gemfile), gemfile: gemfile}
		}
	}

	return runner{name: "fastlane", env: base}
}

// projectGemfile returns the Gemfile in dir, or in dir/ios as Flutter and
// React Native projects keep it, when it lists fastlane.
func projectGemfile(dir string) string {
	if dir == "" {
		dir, _ = os.Getwd()
	}
	for _, candidate := range []string{filepath.Join(dir, "Gemfile"), filepath.Join(dir, "ios", "Gemfile")} {
		contents, err := os.ReadFile(candidate)
		if err != nil || !gemfileFastlane.Match(contents) {
			continue
		}
		if absolute, err := filepath.Abs(candidate); err == nil {
			return absolute
		}
	}

	return ""
}

func (r runner) command(ctx context.Context, arguments ...string) *exec.Cmd {
	command := exec.CommandContext(ctx, r.name, append(append([]string{}, r.prefix...), arguments...)...)
	command.Env = append([]string{}, r.env...)

	return command
}

func (r runner) check(ctx context.Context) error {
	output, err := r.command(ctx, "--version").Output()
	if err != nil {
		if r.gemfile != "" {
			return fmt.Errorf("%w: bundle exec fastlane failed with %s. Run bundle install there first", apns.ErrUnavailable, r.gemfile)
		}

		return fmt.Errorf("%w: the fastlane setup needs fastlane %s or later installed (https://docs.fastlane.tools)", apns.ErrUnavailable, versionString(minimumVersion))
	}

	return supportedVersion(string(output))
}

var versionPattern = regexp.MustCompile(`fastlane (\d+)\.(\d+)\.(\d+)`)

func supportedVersion(output string) error {
	match := versionPattern.FindStringSubmatch(output)
	if match == nil {
		return fmt.Errorf("%w: could not read the fastlane version", apns.ErrUnavailable)
	}
	var version [3]int
	for index := range version {
		version[index], _ = strconv.Atoi(match[index+1])
	}
	for index := range version {
		if version[index] != minimumVersion[index] {
			if version[index] < minimumVersion[index] {
				return fmt.Errorf("%w: the fastlane setup needs fastlane %s or later, found %s. Run fastlane update_fastlane", apns.ErrUnavailable, versionString(minimumVersion), versionString(version))
			}

			break
		}
	}

	return nil
}

func versionString(version [3]int) string {
	return fmt.Sprintf("%d.%d.%d", version[0], version[1], version[2])
}

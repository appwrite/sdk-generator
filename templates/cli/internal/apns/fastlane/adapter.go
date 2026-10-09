// Package fastlane is the APNs key setup built on fastlane's spaceship, the
// open-source Apple Developer portal client.
//
// It runs a lane from a Fastfile embedded in the binary with the fastlane the
// person already has installed, so it works with a gem, Homebrew or bundler
// installation and brings no Ruby of its own. spaceship signs in with SRP,
// since fastlane 2.226.0.
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

// The first fastlane that signs in with SRP, which Apple has required since
// October 2024.
var minimumVersion = [3]int{2, 226, 0}

// Adapter creates APNs keys with an installed fastlane.
type Adapter struct {
	// Fastlane is the command to run. Empty means "fastlane" from PATH.
	Fastlane string
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
	if err := a.checkFastlane(ctx); err != nil {
		return apns.Key{}, err
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
	command := exec.CommandContext(ctx, a.fastlane(), arguments...)
	command.Dir = scratch
	command.Stdin, command.Stdout, command.Stderr = a.terminal()
	command.Env = append(os.Environ(),
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

func (a *Adapter) fastlane() string {
	if a.Fastlane != "" {
		return a.Fastlane
	}

	return "fastlane"
}

var versionPattern = regexp.MustCompile(`fastlane (\d+)\.(\d+)\.(\d+)`)

func (a *Adapter) checkFastlane(ctx context.Context) error {
	command := exec.CommandContext(ctx, a.fastlane(), "--version")
	command.Env = append(os.Environ(), "FASTLANE_SKIP_UPDATE_CHECK=1", "FASTLANE_OPT_OUT_USAGE=1")
	output, err := command.Output()
	if err != nil {
		return fmt.Errorf("%w: the fastlane setup needs fastlane %s or later installed (https://docs.fastlane.tools)", apns.ErrUnavailable, versionString(minimumVersion))
	}

	return supportedVersion(string(output))
}

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

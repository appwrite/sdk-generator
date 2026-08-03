package cmd

import (
	"fmt"
	"io"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"time"

	"github.com/appwrite/appwrite-cli-go/internal/app"
	"github.com/spf13/cobra"
)

// Ports templates/cli/lib/commands/update.ts.
//
// The update path has to work for someone whose current install is the
// TypeScript CLI: an npm or Homebrew install is updated through that tool, and
// a standalone binary is replaced in place. The release asset names match the
// TypeScript's exactly, so a Go build lands where a TypeScript one was.

// releaseBaseURL is where standalone binaries are published.
const releaseBaseURL = "https://github.com/appwrite/appwrite-cli/releases/latest/download/"

func newUpdateCommand() *cobra.Command {
	var force bool

	command := &cobra.Command{
		Use:   "update",
		Short: "Update the CLI to the latest version",
		RunE: func(command *cobra.Command, args []string) error {
			method := app.DetectInstallMethod()

			switch method {
			case app.InstallNPM:
				command.Printf("Updating via npm...\n")

				return runUpdater(command, "npm", "install", "-g", app.NPMPackageName+"@latest")
			case app.InstallHomebrew:
				formula := app.HomebrewFormula()
				if formula == "" {
					formula = app.ExecutableName
				}
				command.Printf("Updating via Homebrew (%s)...\n", formula)

				return runUpdater(command, "brew", "upgrade", formula)
			case app.InstallStandalone:
				return updateStandalone(command, force)
			}

			// Better to say so than to guess and overwrite something that is
			// not ours.
			return fmt.Errorf(
				"could not determine how %s was installed; reinstall from https://appwrite.io/docs/tooling/command-line/installation",
				app.ExecutableName)
		},
	}
	command.Flags().BoolVarP(&force, "force", "f", false,
		"Replace the binary even if its location looks unexpected.")

	return command
}

// runUpdater shells out to the package manager that owns this install.
func runUpdater(command *cobra.Command, name string, args ...string) error {
	binary, err := exec.LookPath(name)
	if err != nil {
		return fmt.Errorf("%s is not on PATH, so the CLI cannot update itself: %w", name, err)
	}

	updater := exec.Command(binary, args...)
	updater.Stdout = command.OutOrStdout()
	updater.Stderr = command.ErrOrStderr()

	if err := updater.Run(); err != nil {
		return fmt.Errorf("%s failed: %w", name, err)
	}

	command.Println("Updated to the latest version.")

	return nil
}

// updateStandalone downloads the current release and replaces this binary.
//
// Written to a temporary file beside the target and renamed into place: a
// partial download must never leave the user without a working CLI, and rename
// within a directory is atomic.
func updateStandalone(command *cobra.Command, force bool) error {
	asset, err := app.ReleaseAssetName()
	if err != nil {
		return err
	}

	target, err := os.Executable()
	if err != nil {
		return err
	}
	if resolved, err := filepath.EvalSymlinks(target); err == nil {
		target = resolved
	}

	if !force && filepath.Base(target) != app.ExecutableName &&
		filepath.Base(target) != app.ExecutableName+".exe" {
		return fmt.Errorf(
			"refusing to replace %q: it is not named %s. Re-run with --force if this is correct",
			target, app.ExecutableName)
	}

	directory := filepath.Dir(target)
	if err := checkWritable(directory); err != nil {
		return fmt.Errorf("cannot write to %s: %w. Re-run with elevated permissions", directory, err)
	}

	command.Printf("Downloading %s...\n", asset)
	body, err := download(releaseBaseURL + asset)
	if err != nil {
		return err
	}
	defer body.Close()

	temporary, err := os.CreateTemp(directory, ".appwrite-update-")
	if err != nil {
		return err
	}
	temporaryPath := temporary.Name()
	defer os.Remove(temporaryPath)

	if _, err := io.Copy(temporary, body); err != nil {
		temporary.Close()

		return err
	}
	if err := temporary.Close(); err != nil {
		return err
	}
	if err := os.Chmod(temporaryPath, 0o755); err != nil {
		return err
	}

	// Windows will not rename over a running executable, so the old one is
	// moved aside first and cleaned up on the next run.
	if runtime.GOOS == "windows" {
		_ = os.Rename(target, target+".old")
	}
	if err := os.Rename(temporaryPath, target); err != nil {
		return err
	}

	command.Printf("Updated %s.\n", target)

	return nil
}

func download(url string) (io.ReadCloser, error) {
	client := &http.Client{Timeout: 5 * time.Minute}

	response, err := client.Get(url)
	if err != nil {
		return nil, err
	}
	if response.StatusCode != http.StatusOK {
		response.Body.Close()

		return nil, fmt.Errorf("download failed with status %d: %s", response.StatusCode, url)
	}

	return response.Body, nil
}

// checkWritable verifies the install directory can be written to, so the
// failure is reported before anything is downloaded.
func checkWritable(directory string) error {
	probe, err := os.CreateTemp(directory, ".appwrite-write-probe-")
	if err != nil {
		return err
	}
	name := probe.Name()
	probe.Close()

	return os.Remove(name)
}

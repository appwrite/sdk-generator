package app

import (
	"runtime"
	"strings"
	"testing"
)

// The asset name must match what the TypeScript CLI publishes, or a user on a
// TypeScript install updates into a 404. Ports getStandaloneBinaryArtifactName.
func TestReleaseAssetName(t *testing.T) {
	name, err := ReleaseAssetName()
	if err != nil {
		t.Skipf("no published binary for %s/%s", runtime.GOOS, runtime.GOARCH)
	}

	if !strings.HasPrefix(name, NPMPackageName+"-") {
		t.Errorf("asset %q does not start with the package name", name)
	}

	// The TypeScript spells amd64 as x64 and windows as win; matching matters
	// more than being idiomatic, because the artifacts already exist.
	switch runtime.GOARCH {
	case "amd64":
		if !strings.Contains(name, "-x64") {
			t.Errorf("asset %q should spell amd64 as x64", name)
		}
	case "arm64":
		if !strings.Contains(name, "-arm64") {
			t.Errorf("asset %q should contain arm64", name)
		}
	}

	switch runtime.GOOS {
	case "windows":
		if !strings.Contains(name, "-win") || !strings.HasSuffix(name, ".exe") {
			t.Errorf("asset %q should be a windows .exe", name)
		}
	case "darwin", "linux":
		if strings.HasSuffix(name, ".exe") {
			t.Errorf("asset %q should not be an .exe", name)
		}
	}
}

// An unrecognised install must report unknown rather than guess: `update` uses
// this to decide what to overwrite.
func TestDetectInstallMethodReturnsSomethingUsable(t *testing.T) {
	method := DetectInstallMethod()

	switch method {
	case InstallNPM, InstallHomebrew, InstallStandalone, InstallUnknown:
	default:
		t.Errorf("unexpected install method %q", method)
	}
}

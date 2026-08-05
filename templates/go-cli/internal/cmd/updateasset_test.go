package cmd

import (
	"strings"
	"testing"
)

// Releases are tagged with the bare version -- `25.1.0`, not `v25.1.0`. A `v`
// in the download path 404s on every asset, which breaks `appwrite update` for
// every standalone install while leaving npm and Homebrew working, so nothing
// else in the tree notices.
//
// Asserted against releasesPageURL rather than a literal URL: the owner and
// repository are generated, and the bug was in the segment after them.
func TestReleaseAssetURLUsesTheBareTag(t *testing.T) {
	tests := []struct {
		name    string
		version string
	}{
		{name: "bare version", version: "25.1.0"},
		{name: "v-prefixed version is normalised", version: "v25.1.0"},
		{name: "prerelease", version: "26.0.0-rc.1"},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			asset := "appwrite-cli-linux-x64"
			got := releaseAssetURL(test.version, asset)
			want := releasesPageURL + "/download/" + strings.TrimPrefix(test.version, "v") + "/" + asset

			if got != want {
				t.Errorf("releaseAssetURL(%q)\n got %q\nwant %q", test.version, got, want)
			}
			if strings.Contains(got, "/download/v") {
				t.Errorf("releaseAssetURL(%q) = %q, tags carry no v prefix", test.version, got)
			}
		})
	}
}

// The asset has to survive verbatim: it is the release asset name, and the
// installer scripts and goreleaser derive the same string independently.
func TestReleaseAssetURLKeepsTheAssetName(t *testing.T) {
	got := releaseAssetURL("25.1.0", "appwrite-cli-win-x64.exe")

	if !strings.HasSuffix(got, "/appwrite-cli-win-x64.exe") {
		t.Errorf("releaseAssetURL dropped or rewrote the asset name: %q", got)
	}
}

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

// A real goreleaser manifest: two spaces between digest and filename, one line
// per asset, Windows assets carrying their extension.
const checksumsManifest = `d1e8a70b5ccab1dc2f56bbf7e99f064a2fc6dc2d3c9d4b1e0a7f2c3b4a596877  appwrite-cli-darwin-arm64
5f4dcc3b5aa765d61d8327deb882cf99a1b2c3d4e5f60718293a4b5c6d7e8f90  appwrite-cli-linux-x64
9a0364b9e99bb480dd25e1f0284c8555f5c2e0b1a2b3c4d5e6f708192a3b4c5d  appwrite-cli-win-x64.exe
`

func TestChecksumForFindsTheAsset(t *testing.T) {
	tests := []struct {
		asset string
		want  string
	}{
		{asset: "appwrite-cli-linux-x64", want: "5f4dcc3b5aa765d61d8327deb882cf99a1b2c3d4e5f60718293a4b5c6d7e8f90"},
		{asset: "appwrite-cli-win-x64.exe", want: "9a0364b9e99bb480dd25e1f0284c8555f5c2e0b1a2b3c4d5e6f708192a3b4c5d"},
	}

	for _, test := range tests {
		got, err := checksumFor(strings.NewReader(checksumsManifest), test.asset)
		if err != nil {
			t.Fatalf("checksumFor(%q): %v", test.asset, err)
		}
		if got != test.want {
			t.Errorf("checksumFor(%q) = %q, want %q", test.asset, got, test.want)
		}
	}
}

// An asset the manifest does not mention must be an error, not an empty string
// that then compares equal to nothing and lets the install through.
func TestChecksumForRejectsAnUnlistedAsset(t *testing.T) {
	got, err := checksumFor(strings.NewReader(checksumsManifest), "appwrite-cli-linux-arm64")

	if err == nil {
		t.Fatalf("an unlisted asset returned %q and no error", got)
	}
	if got != "" {
		t.Errorf("returned a checksum for an unlisted asset: %q", got)
	}
	if !strings.Contains(err.Error(), "appwrite-cli-linux-arm64") {
		t.Errorf("the error does not name the asset: %v", err)
	}
}

// A filename must match exactly. `appwrite-cli-linux-x64` is a prefix of
// `appwrite-cli-linux-x64-something`, and matching loosely would accept the
// wrong digest.
func TestChecksumForMatchesTheWholeName(t *testing.T) {
	manifest := "aaaa  appwrite-cli-linux-x64-extra\n"

	if got, err := checksumFor(strings.NewReader(manifest), "appwrite-cli-linux-x64"); err == nil {
		t.Errorf("a partial name matched, returning %q", got)
	}
}

func TestChecksumForIgnoresAnEmptyManifest(t *testing.T) {
	if _, err := checksumFor(strings.NewReader(""), "appwrite-cli-linux-x64"); err == nil {
		t.Error("an empty manifest verified successfully")
	}
}

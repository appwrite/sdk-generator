package app

import "testing"

// devVersion is the sentinel init() compares against to decide whether a release
// stamped a version over it. If the generator ever templates it from
// `sdk.version` again, a release stamps the same string it was compiled with, the
// comparison sees no change, and the build-info fallback overwrites the correct
// version with the build checkout's VCS tag -- so `appwrite --version` reports
// the PREVIOUS release.
//
// That failure only appears in a stamped release binary, which no test builds, so
// this asserts the one property that prevents it: the sentinel is a constant no
// release will ever carry.
func TestDevVersionIsNotAReleaseVersion(t *testing.T) {
	if devVersion != "0.0.0" {
		t.Errorf("devVersion = %q, want the unreleasable 0.0.0 -- see the comment above", devVersion)
	}
}

// A plain build compiles the sentinel in, so anything else means a release stamped
// this binary and init kept it.
func TestVersionStartsFromTheSentinel(t *testing.T) {
	if Version == "" {
		t.Error("Version is empty, so `--version` would print nothing")
	}
}

// Build info carries the module version as Go spells it -- `v0.7.7-preview` --
// while the release ldflag carries goreleaser's `.Version`, which has already
// dropped the "v". So one release reported itself two ways:
//
//	go install ...@latest  ->  appwrite version v0.7.7-preview
//	a downloaded release   ->  appwrite version 0.7.7-preview
//
// `--version` is the first thing a bug report quotes, so the two have to agree.
//
// Asserted on the helper rather than on Version: under `go test` the ldflag
// never fires and build info reports no module version, so Version is the
// sentinel and a check on it could not fail for the reason it exists.
func TestNormalizeBuildVersionDropsTheLeadingV(t *testing.T) {
	cases := map[string]string{
		"v0.7.7-preview": "0.7.7-preview",
		"v1.0.0":         "1.0.0",
		"0.7.7-preview":  "0.7.7-preview",
		// What a plain `go build` in a working tree reports, and what the
		// fallback returns when there is nothing to report.
		"(devel)": "(devel)",
		"":        "",
	}

	for raw, want := range cases {
		if got := normalizeBuildVersion(raw); got != want {
			t.Errorf("normalizeBuildVersion(%q) = %q, want %q", raw, got, want)
		}
	}
}

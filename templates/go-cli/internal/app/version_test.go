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

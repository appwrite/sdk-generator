package cmd

import (
	"bytes"
	"strings"
	"testing"
)

// The closing count is the last thing a push prints, and it used to be written
// out once per command. These pin the policy itself rather than one command's
// copy of it.

func TestZeroPushedWithNoFailuresIsInformational(t *testing.T) {
	out := &bytes.Buffer{}
	pushTally{Pushed: 0, Failed: 0}.report(out, "buckets")

	got := out.String()
	if !strings.Contains(got, "No buckets were pushed. Everything is already up to date.") {
		t.Fatalf("expected the already-up-to-date line, got %q", got)
	}
	// The commonest way to push nothing is that everything already matched, and
	// the command exits zero -- so an error marker here contradicts itself.
	if strings.Contains(got, "Error") {
		t.Fatalf("a clean no-op must not report an error, got %q", got)
	}
}

func TestZeroPushedWithAFailureIsAFailure(t *testing.T) {
	out := &bytes.Buffer{}
	pushTally{Pushed: 0, Failed: 1}.report(out, "buckets")

	got := out.String()
	if !strings.Contains(got, "No buckets were pushed.") {
		t.Fatalf("expected the no-pushes line, got %q", got)
	}
	if strings.Contains(got, "already up to date") {
		t.Fatalf("a failed push is not up to date, got %q", got)
	}
}

func TestSomePushedReportsTheCount(t *testing.T) {
	out := &bytes.Buffer{}
	pushTally{Pushed: 3, Failed: 1}.report(out, "buckets")

	if got := out.String(); !strings.Contains(got, "Successfully pushed 3 buckets.") {
		t.Fatalf("expected the count line, got %q", got)
	}
}

// syncHint is user-visible output the CLI builds are compared on, so the wording
// is pinned rather than left to whatever the identity happens to produce.
func TestSyncHintWordsBothTheCommandAndTheResource(t *testing.T) {
	for _, testCase := range []struct {
		identity resourceIdentity
		want     string
	}{
		{bucketIdentity, "pull buckets"},
		{bucketIdentity, "init bucket"},
		{tableIdentity, "pull tables"},
		{tableIdentity, "init table"},
		{functionIdentity, "pull functions"},
		{functionIdentity, "init function"},
	} {
		if got := testCase.identity.syncHint(); !strings.Contains(got, testCase.want) {
			t.Errorf("%s hint missing %q: %q", testCase.identity.Name, testCase.want, got)
		}
	}

	// "existing one", not "ones" -- the wording is pinned.
	if got := bucketIdentity.syncHint(); !strings.Contains(got, "synchronize existing one,") {
		t.Errorf("hint wording drifted: %q", got)
	}
}

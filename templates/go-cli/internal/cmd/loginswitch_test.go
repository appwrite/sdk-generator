package cmd

import (
	"errors"
	"strings"
	"testing"
)

// A reason is the whole answer. Without one the only thing this could say was
// "run `login --switch` again" -- the command that just failed -- so a locked
// keyring, an unreachable endpoint and a genuinely dead session all pointed at
// the same dead end.
func TestUnusableSessionErrorCarriesTheReason(t *testing.T) {
	reason := errors.New("cannot read the saved credential: keyring is locked")

	message := unusableSessionError(reason).Error()
	if !strings.Contains(message, "keyring is locked") {
		t.Errorf("the reason was dropped: %q", message)
	}
	if strings.Contains(message, "login --switch") {
		t.Errorf("a known reason still sent the user back round the loop: %q", message)
	}
}

// The generic wording is kept for the one case that has no error behind it,
// which is what the TypeScript keeps it for.
func TestUnusableSessionErrorWithoutAReason(t *testing.T) {
	message := unusableSessionError(nil).Error()

	if !strings.Contains(message, "no longer valid") ||
		!strings.Contains(message, "login --switch") {
		t.Errorf("the fallback lost its advice: %q", message)
	}
}

// Wrapped, not formatted into the string: callers up the stack still get to
// match on what actually failed.
func TestUnusableSessionErrorUnwraps(t *testing.T) {
	reason := errors.New("session expired")

	if !errors.Is(unusableSessionError(reason), reason) {
		t.Error("the reason cannot be unwrapped")
	}
}

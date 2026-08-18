package cmd

import (
	"strings"
	"testing"
)

// A push with --with-variables deletes the remote set before rebuilding it from
// .env, so a key the API will not accept has to be caught before anything is
// deleted. dotenv's key pattern is wider than the API's, which is what makes
// this reachable from an ordinary file rather than only from a typo.

func TestValidateVariableKeysAcceptsIdentifiers(t *testing.T) {
	names := []string{
		"API_URL",
		"_PRIVATE",
		"key1",
		strings.Repeat("A", variableKeyMaxLength),
	}

	if err := validateVariableKeys(names); err != nil {
		t.Fatalf("expected identifiers to be accepted, got %v", err)
	}
}

func TestValidateVariableKeysRejectsUnusableKeys(t *testing.T) {
	// The first two are the ones dotenv hands over: its key pattern allows dots
	// and hyphens, so these parse locally and fail at the API.
	names := []string{
		"MY-KEY",
		"MY.KEY",
		"MY KEY",
		"9KEY",
		"KÉY",
		"KEY\t",
		"",
		strings.Repeat("A", variableKeyMaxLength+1),
	}

	for _, name := range names {
		t.Run(name, func(t *testing.T) {
			if err := validateVariableKeys([]string{name}); err == nil {
				t.Errorf("expected %q to be rejected", name)
			}
		})
	}
}

func TestValidateVariableKeysNamesEveryOffendingKey(t *testing.T) {
	err := validateVariableKeys([]string{"API_URL", "MY-KEY", "MY.KEY"})
	if err == nil {
		t.Fatal("expected an error naming the invalid keys")
	}

	got := err.Error()
	for _, want := range []string{"MY-KEY", "MY.KEY"} {
		if !strings.Contains(got, want) {
			t.Errorf("expected %q to name %q", got, want)
		}
	}

	// Reporting a key that is fine would send the user looking in the wrong
	// place in their .env.
	if strings.Contains(got, "API_URL") {
		t.Errorf("expected %q not to name the valid key", got)
	}
}

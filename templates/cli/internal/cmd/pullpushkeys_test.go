package cmd

import (
	"slices"
	"testing"
)

// pull writes the config and push compares against it. The two key lists are
// declared in different files and nothing tied them together, so `pull site`
// could stop writing a field push still compared -- which is exactly what
// happened: the site list was missing nine fields, and every push after a pull
// reported them as local deletions of values nobody had touched.
//
//	id    │ key                │ remote      │ local
//	layby │ providerSilentMode │ false       │
//	layby │ buildSpecification │ s-2vcpu-2gb │
//
// Answering "yes" to that table pushed empty values over live settings.
//
// localOnlyKeys are the config fields that have no counterpart on the API, so
// pull cannot write them and their absence is not drift.
var localOnlyKeys = []string{"vars", "ignore"}

func TestPullWritesEveryKeyPushCompares(t *testing.T) {
	for _, resource := range deployables {
		t.Run(resource.Name, func(t *testing.T) {
			written := pullKeysFor(t, resource.Name)

			for _, key := range resource.ApproveKeys {
				if slices.Contains(localOnlyKeys, key) {
					continue
				}
				if !slices.Contains(written, key) {
					t.Errorf("push compares %q but pull never writes it, so every "+
						"push reports it as a local deletion", key)
				}
			}
		})
	}
}

// The other direction. The TypeScript's config schema is `.strict()`
// (config.ts:143), so a key it does not declare is not merely ignored -- it
// fails validation, and a config this CLI wrote stops loading in the one it is
// replacing.
func TestPullWritesNothingPushDoesNotKnow(t *testing.T) {
	for _, resource := range deployables {
		t.Run(resource.Name, func(t *testing.T) {
			for _, key := range pullKeysFor(t, resource.Name) {
				if !slices.Contains(resource.ApproveKeys, key) {
					t.Errorf("pull writes %q, which is not in the config schema", key)
				}
			}
		})
	}
}

func pullKeysFor(t *testing.T, name string) []string {
	t.Helper()

	for _, resource := range codeResources {
		if resource.Name == name {
			return resource.Keys
		}
	}

	t.Fatalf("no pull resource named %q", name)

	return nil
}

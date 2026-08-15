package config

import (
	"encoding/json"
	"fmt"
	"strings"
)

// IgnoreRules is a resource's `ignore` field.
//
// IT IS BOTH SHAPES. `init function` writes a JSON array -- in this CLI and in
// the TypeScript one -- while the config schema documents a single
// newline-separated string, and a hand-written config may use either. The
// TypeScript never notices because `ignore().add()` accepts a string or an
// array, so both have always worked there.
//
// A plain `string` field here does not just mis-read an array, it fails the
// whole decode: `json: cannot unmarshal array into Go struct field
// Function.ignore of type string`. That took out `run` and every other command
// reading the typed view, against a config this CLI had written itself.
type IgnoreRules struct {
	rules []string
}

// Rules is the patterns, in order. Never nil-checked by callers, so an absent
// field yields an empty slice rather than a surprise.
func (i IgnoreRules) Rules() []string {
	return i.rules
}

// IsEmpty reports whether there is anything to match against.
func (i IgnoreRules) IsEmpty() bool {
	return len(i.rules) == 0
}

func (i *IgnoreRules) UnmarshalJSON(data []byte) error {
	trimmed := strings.TrimSpace(string(data))

	// `null` and an absent field mean the same thing: no rules.
	if trimmed == "null" {
		i.rules = nil

		return nil
	}

	var list []string
	if err := json.Unmarshal(data, &list); err == nil {
		i.rules = make([]string, 0, len(list))
		for _, rule := range list {
			if trimmedRule := strings.TrimSpace(rule); trimmedRule != "" {
				i.rules = append(i.rules, trimmedRule)
			}
		}

		return nil
	}

	var text string
	if err := json.Unmarshal(data, &text); err == nil {
		i.rules = nil
		for _, line := range strings.Split(text, "\n") {
			if trimmedLine := strings.TrimSpace(line); trimmedLine != "" {
				i.rules = append(i.rules, trimmedLine)
			}
		}

		return nil
	}

	return fmt.Errorf("ignore must be a string or a list of strings, got %s", trimmed)
}

// MarshalJSON writes the array form, which is what all CLI builds' `init` produces.
//
// Only reachable if something re-encodes the typed view; the ordered document
// in Local is what actually gets written back to disk, so a user's chosen
// shape survives a round-trip regardless.
func (i IgnoreRules) MarshalJSON() ([]byte, error) {
	if i.rules == nil {
		return []byte("[]"), nil
	}

	return json.Marshal(i.rules)
}

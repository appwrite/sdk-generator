package keys

import (
	"reflect"
	"slices"
	"strings"
	"testing"

	"charm.land/bubbles/v2/key"
)

// names are the help names of keys that are not written as they are matched.
var names = map[string]string{
	"up":     "↑",
	"down":   "↓",
	"left":   "←",
	"right":  "→",
	"pgdown": "pgdn",
	"C":      "shift+c",
}

// bindings is every binding of the key map, by its field name.
func bindings() map[string]key.Binding {
	out := map[string]key.Binding{}
	v := reflect.ValueOf(Keys)
	for i := range v.NumField() {
		out[v.Type().Field(i).Name] = v.Field(i).Interface().(key.Binding)
	}
	return out
}

// TestHelp checks every binding has keys and help, its help names each of its keys, a run of
// digits as 1-9, and its description is lowercase.
func TestHelp(t *testing.T) {
	for name, b := range bindings() {
		t.Run(name, func(t *testing.T) {
			h := b.Help()
			if len(b.Keys()) == 0 || h.Key == "" || h.Desc == "" {
				t.Fatalf("keys %q, help %q %q", b.Keys(), h.Key, h.Desc)
			}
			if h.Desc != strings.ToLower(h.Desc) {
				t.Errorf("description %q is not lowercase", h.Desc)
			}
			shown := strings.Split(h.Key, "/")
			for _, s := range slices.Clone(shown) {
				if from, to, ok := strings.Cut(s, "-"); ok && len(from) == 1 && len(to) == 1 && from < to {
					for d := from[0]; d <= to[0]; d++ {
						shown = append(shown, string(d))
					}
				}
			}
			for _, k := range b.Keys() {
				if n, ok := names[k]; ok {
					k = n
				}
				if k != h.Key && !slices.Contains(shown, k) {
					t.Errorf("help %q does not name %q", h.Key, k)
				}
			}
		})
	}
}

// shared are the keys bindings share, each working on pages the others do nothing on. None do yet.
var shared = map[string][]string{}

// TestKeysAreUnique checks no key is bound twice, but the ones shared, so no binding shadows
// another.
func TestKeysAreUnique(t *testing.T) {
	seen := map[string]string{}
	for name, b := range bindings() {
		for _, k := range b.Keys() {
			if other, ok := seen[k]; ok {
				if set := shared[k]; !slices.Contains(set, name) || !slices.Contains(set, other) {
					t.Errorf("%q is bound to %s and %s", k, other, name)
				}
			}
			seen[k] = name
		}
	}
}

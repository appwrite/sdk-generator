package config

import (
	"encoding/json"
	"reflect"
	"testing"
)

// Regression for `appwrite run function` failing with
//
//	json: cannot unmarshal array into Go struct field Function.ignore of type string
//
// against a config this CLI's own `init function` had written. The field was
// typed as a string while both CLIs write an array, and a decode error there
// takes out the whole command, not just the field.
func TestFunctionIgnoreAcceptsAnArray(t *testing.T) {
	raw := `{
		"$id": "fn",
		"name": "My Function",
		"runtime": "node-22",
		"ignore": ["node_modules", ".npm"]
	}`

	var function Function
	if err := json.Unmarshal([]byte(raw), &function); err != nil {
		t.Fatalf("decoding a config with an array ignore: %v", err)
	}

	want := []string{"node_modules", ".npm"}
	if got := function.Ignore.Rules(); !reflect.DeepEqual(got, want) {
		t.Errorf("rules = %v, want %v", got, want)
	}
}

// The schema documents a single newline-separated string, and a hand-written
// config may still use it. The TypeScript accepts both because
// `ignore().add()` does.
func TestFunctionIgnoreAcceptsAString(t *testing.T) {
	raw := `{"$id": "fn", "ignore": "node_modules\n.npm\n\n  vendor  "}`

	var function Function
	if err := json.Unmarshal([]byte(raw), &function); err != nil {
		t.Fatal(err)
	}

	want := []string{"node_modules", ".npm", "vendor"}
	if got := function.Ignore.Rules(); !reflect.DeepEqual(got, want) {
		t.Errorf("rules = %v, want %v", got, want)
	}
}

func TestFunctionIgnoreToleratesAbsentAndNull(t *testing.T) {
	for name, raw := range map[string]string{
		"absent": `{"$id": "fn"}`,
		"null":   `{"$id": "fn", "ignore": null}`,
		"empty":  `{"$id": "fn", "ignore": []}`,
	} {
		var function Function
		if err := json.Unmarshal([]byte(raw), &function); err != nil {
			t.Errorf("%s: %v", name, err)

			continue
		}
		if !function.Ignore.IsEmpty() {
			t.Errorf("%s: rules = %v, want none", name, function.Ignore.Rules())
		}
	}
}

// A shape neither CLI can use is worth an error rather than a silent empty
// list, which would quietly stop ignoring anything.
func TestFunctionIgnoreRejectsAnUnusableShape(t *testing.T) {
	var function Function
	err := json.Unmarshal([]byte(`{"$id": "fn", "ignore": {"pattern": "x"}}`), &function)

	if err == nil {
		t.Fatal("an object ignore decoded without error")
	}
}

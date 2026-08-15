package jsonx

import (
	"encoding/json"
	"testing"
)

// Overwriting an existing key must not move it to the end -- that is what keeps
// a rewritten config diff-free.
func TestSetPreservesKeyPosition(t *testing.T) {
	object := NewObject()
	object.Set("a", "1")
	object.Set("b", "2")
	object.Set("c", "3")
	object.Set("a", "changed")

	keys := object.Keys()
	want := []string{"a", "b", "c"}
	for i := range want {
		if keys[i] != want[i] {
			t.Fatalf("keys = %v, want %v", keys, want)
		}
	}
}

// Nested objects and arrays must round-trip in order too, not just the root.
func TestRoundTripPreservesNestedOrder(t *testing.T) {
	const document = `{"z":1,"a":{"y":2,"b":[{"w":3,"c":4}]}}`

	object := NewObject()
	if err := object.UnmarshalJSON([]byte(document)); err != nil {
		t.Fatal(err)
	}
	encoded, err := object.MarshalJSON()
	if err != nil {
		t.Fatal(err)
	}
	if string(encoded) != document {
		t.Errorf("round-trip = %s, want %s", encoded, document)
	}
}

// JSON.stringify does not escape <, > or &; neither may Marshal, or any URL or
// description containing them would be rewritten.
func TestMarshalDoesNotEscapeHTML(t *testing.T) {
	object := NewObject()
	object.Set("url", "https://example.com/?a=1&b=2")
	object.Set("html", "<b>bold</b>")

	encoded, err := Marshal(object)
	if err != nil {
		t.Fatal(err)
	}
	for _, want := range []string{"&b=2", "<b>bold</b>"} {
		if !contains(string(encoded), want) {
			t.Errorf("%s missing from output:\n%s", want, encoded)
		}
	}
}

func contains(haystack, needle string) bool {
	for i := 0; i+len(needle) <= len(haystack); i++ {
		if haystack[i:i+len(needle)] == needle {
			return true
		}
	}

	return false
}

// TestGetInt64AcceptsProgrammaticValues pins the widening that a paginated walk
// exposed: a decoded document holds json.Number, but a value Set in code holds
// whatever Go type the caller had, and reading those back as zero is silent.
func TestGetInt64AcceptsProgrammaticValues(t *testing.T) {
	object := NewObject()
	object.Set("decoded", json.Number("250"))
	object.Set("int64", int64(250))
	object.Set("int", 250)
	object.Set("float", float64(250))

	for _, key := range []string{"decoded", "int64", "int", "float"} {
		if got := object.GetInt64(key); got != 250 {
			t.Errorf("GetInt64(%q) = %d, want 250", key, got)
		}
	}

	// A fractional value is not an integer; truncating would be worse than
	// reporting zero.
	object.Set("fraction", 1.5)
	if got := object.GetInt64("fraction"); got != 0 {
		t.Errorf("GetInt64(\"fraction\") = %d, want 0", got)
	}

	object.Set("text", "250")
	if got := object.GetInt64("text"); got != 0 {
		t.Errorf("GetInt64(\"text\") = %d, want 0", got)
	}
	if got := object.GetInt64("absent"); got != 0 {
		t.Errorf("GetInt64(\"absent\") = %d, want 0", got)
	}
}

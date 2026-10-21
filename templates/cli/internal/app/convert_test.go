package app

import (
	"encoding/json"
	"testing"
)

func TestStringSliceSendsAnEmptyArrayForTheEmptyArrayValue(t *testing.T) {
	encoded, err := json.Marshal(StringSlice([]string{"[]"}))
	if err != nil {
		t.Fatal(err)
	}
	if string(encoded) != "[]" {
		t.Errorf("--flag [] sends %s, want []", encoded)
	}
}

func TestStringSliceKeepsTheValuesGiven(t *testing.T) {
	encoded, err := json.Marshal(StringSlice([]string{"first", "[]"}))
	if err != nil {
		t.Fatal(err)
	}
	if string(encoded) != `["first","[]"]` {
		t.Errorf("repeated values send %s, want both entries", encoded)
	}
}

func TestStringSliceSendsNothingWithoutValues(t *testing.T) {
	if StringSlice(nil) != nil {
		t.Error("an unset flag must stay nil so the API keeps the current list")
	}
}

func TestDecodeSliceSendsAnEmptyArrayForTheEmptyArrayValue(t *testing.T) {
	decoded, err := DecodeSlice[interface{}]([]string{"[]"})
	if err != nil {
		t.Fatal(err)
	}
	encoded, err := json.Marshal(decoded)
	if err != nil {
		t.Fatal(err)
	}
	if string(encoded) != "[]" {
		t.Errorf("--flag [] sends %s, want []", encoded)
	}
}

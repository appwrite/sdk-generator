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

func TestDecodeReadsTheFlagIntoTheModel(t *testing.T) {
	type player struct {
		Name  string `json:"name"`
		Score int    `json:"score"`
	}

	decoded, err := Decode[player](`{"name":"John Doe","score":100}`)
	if err != nil {
		t.Fatal(err)
	}
	if decoded.Name != "John Doe" || decoded.Score != 100 {
		t.Errorf("decoded %+v, want the name and score given", decoded)
	}
}

func TestDecodeRejectsAValueThatIsNotJSON(t *testing.T) {
	if _, err := Decode[map[string]any]("name=John"); err == nil {
		t.Error("a non-JSON value was accepted")
	}
}

func TestDecodeLeavesAnUnsetFlagEmpty(t *testing.T) {
	decoded, err := Decode[map[string]any]("")
	if err != nil {
		t.Fatalf("an unset optional flag failed to decode: %v", err)
	}
	if decoded != nil {
		t.Errorf("decoded %v, want no value", decoded)
	}
}

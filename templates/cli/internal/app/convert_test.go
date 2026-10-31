package app

import (
	"encoding/json"
	"testing"

	"github.com/spf13/cobra"
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

func decodedFlag(t *testing.T, value *string) (map[string]any, error) {
	t.Helper()

	command := &cobra.Command{}
	command.Flags().String("player", "", "")
	if value != nil {
		if err := command.Flags().Set("player", *value); err != nil {
			t.Fatal(err)
		}
	}

	return DecodeFlag[map[string]any](command, "player", command.Flags().Lookup("player").Value.String())
}

func TestDecodeFlagReadsTheFlagIntoTheModel(t *testing.T) {
	value := `{"name":"John Doe","score":100}`
	decoded, err := decodedFlag(t, &value)
	if err != nil {
		t.Fatal(err)
	}
	if decoded["name"] != "John Doe" || decoded["score"].(json.Number).String() != "100" {
		t.Errorf("decoded %v, want the name and score given", decoded)
	}
}

func TestDecodeFlagLeavesAnUnsetFlagEmpty(t *testing.T) {
	decoded, err := decodedFlag(t, nil)
	if err != nil {
		t.Fatalf("an unset optional flag failed to decode: %v", err)
	}
	if decoded != nil {
		t.Errorf("decoded %v, want no value", decoded)
	}
}

func TestDecodeFlagRejectsInvalidValues(t *testing.T) {
	for name, value := range map[string]string{
		"empty":         "",
		"not json":      "name=John",
		"trailing data": `{"name":"x"} garbage`,
		"two values":    `{"name":"x"} {"name":"y"}`,
	} {
		if _, err := decodedFlag(t, &value); err == nil {
			t.Errorf("%s: %q was accepted", name, value)
		}
	}
}

package models

import (
	"encoding/json"
	"reflect"
	"testing"
)

func TestNullableScalars(t *testing.T) {
	var model Zznullablescalars
	for _, input := range []string{
		`{"text":null,"enabled":null,"count":null,"price":null}`,
		`{"text":"","enabled":false,"count":0,"price":0}`,
		`{"text":"value","enabled":true,"count":7,"price":2.5}`,
		`{"text":null,"enabled":null,"count":null,"price":null}`,
	} {
		if err := json.Unmarshal([]byte(input), &model); err != nil {
			t.Fatal(err)
		}
		output, err := json.Marshal(model)
		if err != nil {
			t.Fatal(err)
		}
		var got, want map[string]interface{}
		if err := json.Unmarshal(output, &got); err != nil {
			t.Fatal(err)
		}
		if err := json.Unmarshal([]byte(input), &want); err != nil {
			t.Fatal(err)
		}
		if !reflect.DeepEqual(got, want) {
			t.Fatalf("JSON round-trip: got %s, want %s", output, input)
		}
	}
}

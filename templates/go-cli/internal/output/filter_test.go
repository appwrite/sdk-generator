package output

import (
	"bytes"
	"testing"

	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
)

func decode(t *testing.T, document string) *jsonx.Object {
	t.Helper()

	object := jsonx.NewObject()
	if err := object.UnmarshalJSON([]byte(document)); err != nil {
		t.Fatal(err)
	}

	return object
}

func render(t *testing.T, value any) string {
	t.Helper()

	var buffer bytes.Buffer
	if err := RenderJSON(&buffer, value); err != nil {
		t.Fatal(err)
	}

	return buffer.String()
}

func TestIsNormalViewHiddenKey(t *testing.T) {
	hidden := []string{"$createdAt", "$updatedAt", "$permissions", "onboarding", "billingPlanId"}
	for _, key := range hidden {
		if !IsNormalViewHiddenKey(key) {
			t.Errorf("IsNormalViewHiddenKey(%q) = false, want true", key)
		}
	}

	// $id is the one internal field a user actually needs.
	visible := []string{"$id", "name", "email", "billingPlan"}
	for _, key := range visible {
		if IsNormalViewHiddenKey(key) {
			t.Errorf("IsNormalViewHiddenKey(%q) = true, want false", key)
		}
	}
}

// Ports filterData()'s decision table: nulls, nested objects and blank strings
// are dropped; numbers become strings; arrays keep their elements with objects
// flattened.
func TestFilterData(t *testing.T) {
	input := decode(t, `{
		"name": "proj",
		"blank": "   ",
		"missing": null,
		"count": 42,
		"nested": {"a": 1},
		"rows": [
			{"id": "1", "nested": {"b": 2}, "blank": "", "n": 7},
			"scalar",
			9
		]
	}`)

	got := render(t, FilterData(input))
	want := `{
  "name": "proj",
  "count": "42",
  "rows": [
    {
      "id": "1",
      "n": "7"
    },
    "scalar",
    "9"
  ]
}
`

	if got != want {
		t.Errorf("FilterData output.\n--- want ---\n%s\n--- got ---\n%s", want, got)
	}
}

// Field order must survive filtering, or --json consumers see fields move.
func TestFilterDataPreservesOrder(t *testing.T) {
	input := decode(t, `{"z":"1","a":"2","m":"3"}`)

	keys := FilterData(input).Keys()
	want := []string{"z", "a", "m"}
	for i := range want {
		if keys[i] != want[i] {
			t.Fatalf("keys = %v, want %v", keys, want)
		}
	}
}

func TestApplyDisplayFields(t *testing.T) {
	rows := []*jsonx.Object{
		decode(t, `{"$id":"1","name":"a","region":"fra"}`),
		decode(t, `{"$id":"2","name":"b","region":"nyc"}`),
	}

	narrowed := ApplyDisplayFields(rows, []string{"name", "$id"})
	if got := narrowed[0].Keys(); len(got) != 2 || got[0] != "name" || got[1] != "$id" {
		t.Errorf("keys = %v, want [name $id] in the requested order", got)
	}
}

// A row with none of the requested fields comes back whole: showing nothing
// would look like the row does not exist.
func TestApplyDisplayFieldsKeepsRowsWithNoMatch(t *testing.T) {
	rows := []*jsonx.Object{decode(t, `{"other":"value"}`)}

	narrowed := ApplyDisplayFields(rows, []string{"name"})
	if narrowed[0].Len() != 1 {
		t.Errorf("row was blanked instead of preserved: %v", narrowed[0].Keys())
	}
	if _, ok := narrowed[0].Get("other"); !ok {
		t.Error("original field was lost")
	}
}

func TestApplyDisplayFieldsIsIdentityWithoutFields(t *testing.T) {
	rows := []*jsonx.Object{decode(t, `{"a":"1"}`)}

	if got := ApplyDisplayFields(rows, nil); len(got) != 1 || got[0] != rows[0] {
		t.Error("no display fields should return the rows untouched")
	}
}

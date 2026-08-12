package output

import (
	"bytes"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
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
// are dropped; numbers stay numbers; arrays keep their elements with objects
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
  "count": 42,
  "rows": [
    {
      "id": "1",
      "n": 7
    },
    "scalar",
    9
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

// --json is scripted against, so a number has to stay a number. This shipped
// stringifying every one of them -- `jq '.total'` answered "0" instead of 0 --
// because the port read json-bigint's BigNumber handling as applying to all
// numbers rather than to the ones outside JavaScript's exact-integer range.
func TestFilterDataKeepsNumbersAsNumbers(t *testing.T) {
	input := decode(t, `{
		"total": 42,
		"zero": 0,
		"negative": -17,
		"ratio": 1.5,
		"exponent": 1e3,
		"flag": true
	}`)

	got := render(t, FilterData(input))
	want := `{
  "total": 42,
  "zero": 0,
  "negative": -17,
  "ratio": 1.5,
  "exponent": 1e3,
  "flag": true
}
`

	if got != want {
		t.Errorf("FilterData output.\n--- want ---\n%s\n--- got ---\n%s", want, got)
	}
}

// Past 2^53 JavaScript cannot hold the integer exactly, json-bigint hands the
// TypeScript a BigNumber, and filterData renders it with String(). Emitting a
// number there would silently round the id.
func TestFilterDataStringifiesIntegersPastTheSafeRange(t *testing.T) {
	input := decode(t, `{
		"safe": 9007199254740991,
		"unsafe": 9007199254740993,
		"negativeUnsafe": -9007199254740993,
		"huge": 123456789012345678901234567890
	}`)

	got := render(t, FilterData(input))
	want := `{
  "safe": 9007199254740991,
  "unsafe": "9007199254740993",
  "negativeUnsafe": "-9007199254740993",
  "huge": "123456789012345678901234567890"
}
`

	if got != want {
		t.Errorf("FilterData output.\n--- want ---\n%s\n--- got ---\n%s", want, got)
	}
}

// The same rule inside arrays and inside the objects they hold, which is where
// list responses put every number a script reads.
func TestFilterDataAppliesTheNumberRuleInsideArrays(t *testing.T) {
	input := decode(t, `{"rows": [7, 9007199254740993, {"n": 3, "big": 9007199254740993}]}`)

	got := render(t, FilterData(input))
	want := `{
  "rows": [
    7,
    "9007199254740993",
    {
      "n": 3,
      "big": "9007199254740993"
    }
  ]
}
`

	if got != want {
		t.Errorf("FilterData output.\n--- want ---\n%s\n--- got ---\n%s", want, got)
	}
}

func TestFilterDataPreservesGraphQLResponseEnvelope(t *testing.T) {
	input := decode(t, `{
		"data": {
			"localeGet": {
				"countryCode": "in"
			}
		}
	}`)

	got := render(t, FilterData(input))
	want := `{
  "data": {
    "localeGet": {
      "countryCode": "in"
    }
  }
}
`

	if got != want {
		t.Errorf("FilterData GraphQL output.\n--- want ---\n%s\n--- got ---\n%s", want, got)
	}
}

func TestJSONModePreservesGraphQLBatchResponses(t *testing.T) {
	input, err := DecodeOrdered([]byte(`[
		{"data":{"id":9007199254740993}},
		{"errors":[{"message":"bad","extensions":{"code":"x"}}]}
	]`))
	if err != nil {
		t.Fatal(err)
	}

	buffer := &bytes.Buffer{}
	renderer := &Renderer{Mode: ModeJSON, Writer: buffer}
	if err := renderer.Render(input); err != nil {
		t.Fatal(err)
	}

	want := `[
  {
    "data": {
      "id": "9007199254740993"
    }
  },
  {
    "errors": [
      {
        "message": "bad",
        "extensions": {
          "code": "x"
        }
      }
    ]
  }
]
`

	if got := buffer.String(); got != want {
		t.Errorf("GraphQL batch output.\n--- want ---\n%s\n--- got ---\n%s", want, got)
	}
}

// --raw keeps every field, including ones no generated model declares -- that
// is what "raw" means and why the body is captured rather than re-encoded from
// the struct. Big integers are still quoted, because the TypeScript's
// json-bigint round trip quotes them and a bare literal reads back as a
// rounded float.
func TestRawModeKeepsEverythingAndQuotesBigIntegers(t *testing.T) {
	input := decode(t, `{
		"total": 42,
		"undeclared": "kept",
		"nested": {"big": 9007199254740993, "small": 7},
		"rows": [9007199254740993, 7],
		"big": 9007199254740993
	}`)

	buffer := &bytes.Buffer{}
	renderer := &Renderer{Mode: ModeRaw, Writer: buffer}
	if err := renderer.Render(input); err != nil {
		t.Fatal(err)
	}

	want := `{
  "total": 42,
  "undeclared": "kept",
  "nested": {
    "big": "9007199254740993",
    "small": 7
  },
  "rows": [
    "9007199254740993",
    7
  ],
  "big": "9007199254740993"
}
`

	if got := buffer.String(); got != want {
		t.Errorf("raw output.\n--- want ---\n%s\n--- got ---\n%s", want, got)
	}
}

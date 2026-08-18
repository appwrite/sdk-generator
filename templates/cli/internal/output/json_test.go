package output

import (
	"bytes"
	"testing"
)

// nodeBaseline is the exact rendering of the document below -- two-space
// indentation, as `JSON.stringify(value, null, 2)` produces.
//
// --json and --raw are scripted against, so this has to match byte for byte.
// The interesting parts are the ampersand in
// the URL, which Go's encoder escapes by default and JSON.stringify does not,
// and the field order, which a Go map would sort.
const nodeBaseline = `{
  "name": "proj",
  "url": "https://x.io/?a=1&b=2",
  "nested": {
    "apiKey": "standard_0123456789abcdefghij"
  },
  "list": [
    {
      "secret": "short"
    },
    1,
    null,
    true
  ]
}
`

const sourceDocument = `{"name":"proj","url":"https://x.io/?a=1&b=2","nested":{"apiKey":"standard_0123456789abcdefghij"},"list":[{"secret":"short"},1,null,true]}`

func TestRenderJSONMatchesNodeByteForByte(t *testing.T) {
	value, err := DecodeOrdered([]byte(sourceDocument))
	if err != nil {
		t.Fatal(err)
	}

	var buffer bytes.Buffer
	if err := RenderJSON(&buffer, value); err != nil {
		t.Fatal(err)
	}

	if buffer.String() != nodeBaseline {
		t.Errorf("output differs from JSON.stringify(value, null, 2).\n--- want ---\n%s\n--- got ---\n%s",
			nodeBaseline, buffer.String())
	}
}

// The ampersand case on its own, because it is the one a reviewer is most
// likely to "fix" by dropping SetEscapeHTML(false).
func TestRenderJSONDoesNotEscapeHTML(t *testing.T) {
	value, err := DecodeOrdered([]byte(`{"url":"https://x.io/?a=1&b=2","tag":"<b>"}`))
	if err != nil {
		t.Fatal(err)
	}

	var buffer bytes.Buffer
	if err := RenderJSON(&buffer, value); err != nil {
		t.Fatal(err)
	}

	// Go's default encoder would write these escapes; JSON.stringify never does.
	for _, forbidden := range []string{`\u0026`, `\u003c`, `\u003e`} {
		if bytes.Contains(buffer.Bytes(), []byte(forbidden)) {
			t.Errorf("output contains the escape %s, which JSON.stringify would not emit:\n%s",
				forbidden, buffer.String())
		}
	}
	for _, required := range []string{`?a=1&b=2`, `<b>`} {
		if !bytes.Contains(buffer.Bytes(), []byte(required)) {
			t.Errorf("output is missing the literal %s:\n%s", required, buffer.String())
		}
	}
}

// A large integer must survive rendering. Decoding through float64 would round
// it, which for an ID or a timestamp is silent data loss.
func TestRenderJSONPreservesLargeIntegers(t *testing.T) {
	value, err := DecodeOrdered([]byte(`{"id":9007199254740993}`))
	if err != nil {
		t.Fatal(err)
	}

	var buffer bytes.Buffer
	if err := RenderJSON(&buffer, value); err != nil {
		t.Fatal(err)
	}

	if !bytes.Contains(buffer.Bytes(), []byte("9007199254740993")) {
		t.Errorf("large integer was not preserved:\n%s", buffer.String())
	}
}

// Redaction must not reorder a response.
func TestMaskPreservesResponseOrder(t *testing.T) {
	value, err := DecodeOrdered([]byte(sourceDocument))
	if err != nil {
		t.Fatal(err)
	}

	redactor := &Redactor{}
	var buffer bytes.Buffer
	if err := RenderJSON(&buffer, redactor.Mask(value, "")); err != nil {
		t.Fatal(err)
	}

	if !redactor.Applied {
		t.Error("expected redaction to fire on apiKey")
	}

	// Order is unchanged even though two fields were rewritten.
	want := []string{`"name"`, `"url"`, `"nested"`, `"apiKey"`, `"list"`, `"secret"`}
	previous := -1
	for _, key := range want {
		index := bytes.Index(buffer.Bytes(), []byte(key))
		if index < 0 {
			t.Fatalf("%s missing from output:\n%s", key, buffer.String())
		}
		if index < previous {
			t.Errorf("%s appears out of order:\n%s", key, buffer.String())
		}
		previous = index
	}
}

func TestDecodeOrderedHandlesArraysAndEmptyBodies(t *testing.T) {
	value, err := DecodeOrdered([]byte(`[1,2,3]`))
	if err != nil {
		t.Fatal(err)
	}
	if items, ok := value.([]any); !ok || len(items) != 3 {
		t.Errorf("array decode = %#v", value)
	}

	empty, err := DecodeOrdered([]byte("   "))
	if err != nil {
		t.Fatal(err)
	}
	if empty != nil {
		t.Errorf("empty body = %#v, want nil", empty)
	}
}

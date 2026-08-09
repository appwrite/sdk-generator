package output

import (
	"bytes"
	"encoding/json"
	"io"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// These two modes are scripted against, so the bytes must match the TypeScript
// CLI's exactly.

// Mode selects how a response is rendered.
type Mode int

const (
	// ModeTable is the default human-readable rendering.
	ModeTable Mode = iota
	// ModeJSON emits filtered JSON (--json).
	ModeJSON
	// ModeRaw emits the unfiltered response as JSON (--raw).
	ModeRaw
)

// Renderer writes command results.
type Renderer struct {
	Mode        Mode
	ShowSecrets bool
	Writer      io.Writer
}

// RenderJSON writes a value as JSON.stringify(value, null, 2) does.
//
// Two details make this byte-compatible rather than merely valid:
//
//   - Two-space indentation, not the four the config files use.
//   - No HTML escaping. Go's encoder rewrites <, > and & to < and friends;
//     JSON.stringify does not, so any URL with a query string would differ.
//
// Key order comes from jsonx.Object, which preserves the order the API sent.
func RenderJSON(writer io.Writer, value any) error {
	var buffer bytes.Buffer
	encoder := json.NewEncoder(&buffer)
	encoder.SetEscapeHTML(false)
	encoder.SetIndent("", "  ")

	if err := encoder.Encode(value); err != nil {
		return err
	}

	// Encode appends a newline, which is what console.log does too.
	_, err := writer.Write(buffer.Bytes())

	return err
}

// DecodeOrdered parses a response body preserving key order and large integers.
//
// Responses must go through this rather than encoding/json's default
// map[string]any: a map would re-emit keys sorted, which --json consumers would
// see as the field order changing between the two CLIs.
func DecodeOrdered(payload []byte) (any, error) {
	trimmed := strings.TrimSpace(string(payload))
	if trimmed == "" {
		return nil, nil
	}

	// Only objects carry key order worth preserving; arrays and scalars decode
	// normally, with UseNumber so integers keep their digits.
	if strings.HasPrefix(trimmed, "{") {
		object := jsonx.NewObject()
		if err := object.UnmarshalJSON([]byte(trimmed)); err != nil {
			return nil, err
		}

		return object, nil
	}

	decoder := json.NewDecoder(strings.NewReader(trimmed))
	decoder.UseNumber()

	var value any
	if err := decoder.Decode(&value); err != nil {
		return nil, err
	}

	return value, nil
}

// MarshalOrdered encodes a value to JSON for re-decoding through DecodeOrdered.
//
// The SDK returns typed structs. Rendering them directly would use Go's struct
// field order and lose the ordered-object handling the rest of the output path
// relies on, so results make one round trip through JSON first.
func MarshalOrdered(value any) ([]byte, error) {
	var buffer bytes.Buffer
	encoder := json.NewEncoder(&buffer)
	encoder.SetEscapeHTML(false)

	if err := encoder.Encode(value); err != nil {
		return nil, err
	}

	return buffer.Bytes(), nil
}

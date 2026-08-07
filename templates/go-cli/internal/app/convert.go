package app

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strings"
)

// Conversions between what a flag can hold and what an SDK parameter declares.
//
// A repeatable flag is []string but the SDK can declare a typed or untyped
// slice; an object parameter arrives as a JSON string. The generated call sites
// route through these so the conversion lives in one reviewable place rather
// than in 600 inlined expressions.

// JSONObject decodes a flag value that the SDK takes as an object.
//
// Numbers stay json.Number so a large integer is not rounded through float64 on
// its way to the API.
func JSONObject(raw string) (interface{}, error) {
	if raw == "" {
		return nil, nil
	}

	decoder := json.NewDecoder(strings.NewReader(raw))
	decoder.UseNumber()

	var value interface{}
	if err := decoder.Decode(&value); err != nil {
		return nil, fmt.Errorf("expected JSON, got %q: %w", raw, err)
	}

	return value, nil
}

// WriteFile saves a downloaded file.
//
// `location` methods return the bytes rather than a URL, so there is nothing to
// fetch -- the SDK already did. The parent directory is created so a
// --destination pointing into a new folder works without a prior mkdir.
func WriteFile(destination string, content *[]byte) error {
	if destination == "" {
		return fmt.Errorf("a --destination is required")
	}
	if content == nil {
		return fmt.Errorf("the server returned no content")
	}

	if directory := filepath.Dir(destination); directory != "." {
		if err := os.MkdirAll(directory, 0o755); err != nil {
			return err
		}
	}

	return os.WriteFile(destination, *content, 0o644)
}

// DecodeSlice parses each value of a repeatable flag into T.
//
// Needed where the SDK declares a slice such as []interface{}, []float64, or
// [][]interface{}. Go's static typing does not allow the CLI to hand an API a
// string where it declares an object or number, so the parse happens here
// instead. A malformed value therefore fails locally with a clear message
// rather than as a server-side validation error.
func DecodeSlice[T any](raws []string) ([]T, error) {
	if len(raws) == 0 {
		return nil, nil
	}

	decoded := make([]T, 0, len(raws))
	for _, raw := range raws {
		decoder := json.NewDecoder(strings.NewReader(raw))
		decoder.UseNumber()

		var value T
		if err := decoder.Decode(&value); err != nil {
			return nil, fmt.Errorf("expected JSON for this flag, got %q: %w", raw, err)
		}
		decoded = append(decoded, value)
	}

	return decoded, nil
}

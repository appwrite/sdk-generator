package app

import (
	"encoding/json"
	"fmt"
	"os"
	"path/filepath"
	"strings"

	sdkfile "github.com/appwrite/sdk-for-go/file"
)

// Conversions between what a flag can hold and what an SDK parameter declares.
//
// A repeatable flag is []string but an untyped array parameter is
// []interface{}; an object parameter arrives as a JSON string. The generated
// call sites route through these so the conversion lives in one reviewable
// place rather than in 600 inlined expressions.

// ToAnySlice widens a repeatable string flag to an untyped slice.
//
// Returns nil for an empty input so an unset flag stays absent rather than
// becoming an empty array, which the API treats differently.
func ToAnySlice(values []string) []interface{} {
	if len(values) == 0 {
		return nil
	}

	widened := make([]interface{}, 0, len(values))
	for _, value := range values {
		widened = append(widened, value)
	}

	return widened
}

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

// JSONObjectSlice decodes repeated JSON objects, used by parameters the SDK
// declares as []map[string]any.
func JSONObjectSlice(raws []string) ([]map[string]any, error) {
	if len(raws) == 0 {
		return nil, nil
	}

	decoded := make([]map[string]any, 0, len(raws))
	for _, raw := range raws {
		value, err := JSONObject(raw)
		if err != nil {
			return nil, err
		}
		object, ok := value.(map[string]any)
		if !ok {
			return nil, fmt.Errorf("expected a JSON object, got %q", raw)
		}
		decoded = append(decoded, object)
	}

	return decoded, nil
}

// InputFile turns a path into the SDK's upload type.
//
// The path is checked here rather than at upload time so a typo fails before
// any request is made.
func InputFile(path string) (sdkfile.InputFile, error) {
	if path == "" {
		return sdkfile.InputFile{}, fmt.Errorf("a file path is required")
	}

	resolved, err := filepath.Abs(path)
	if err != nil {
		return sdkfile.InputFile{}, err
	}
	if _, err := os.Stat(resolved); err != nil {
		return sdkfile.InputFile{}, fmt.Errorf("cannot read %q: %w", path, err)
	}

	return sdkfile.NewInputFile(resolved, filepath.Base(resolved)), nil
}

// DecodeSlice parses each value of a repeatable flag into T.
//
// Needed where the SDK declares a typed slice such as []float64 or
// [][]interface{}. The TypeScript CLI hands the API raw strings and lets it
// coerce them; Go's static typing does not allow that, so the parse happens
// here instead. A malformed value therefore fails locally with a clear message
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

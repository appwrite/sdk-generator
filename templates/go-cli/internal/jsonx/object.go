package jsonx

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"math"
	"strings"
)

// Package jsonx provides JSON handling shared by config and output.
//
// Object is a JSON object that remembers the order its keys were read in.
//
// Go's map[string]any marshals keys sorted; JavaScript preserves insertion
// order. The TypeScript CLI reads a config, mutates a field and writes the
// whole document back, so a Go port backed by a plain map would silently
// reorder every key in every user's appwrite.config.json the first time it
// wrote one. That file lives in user repositories and gets code-reviewed, so
// the churn would be real and blamed on us.
//
// Numbers are kept as json.Number rather than float64 for the same reason the
// TypeScript CLI uses json-bigint: float64 cannot hold a millisecond timestamp
// past 2^53 without losing digits, and `tokenExpiry` is exactly that.
type Object struct {
	keys   []string
	values map[string]any
}

// NewObject returns an empty ordered object.
func NewObject() *Object {
	return &Object{values: map[string]any{}}
}

// Keys returns the keys in their original order.
func (o *Object) Keys() []string {
	if o == nil {
		return nil
	}

	return append([]string(nil), o.keys...)
}

// Get returns the value stored under key.
func (o *Object) Get(key string) (any, bool) {
	if o == nil {
		return nil, false
	}
	value, ok := o.values[key]

	return value, ok
}

// GetString returns a string value, or "" when absent or another type.
func (o *Object) GetString(key string) string {
	value, ok := o.Get(key)
	if !ok {
		return ""
	}
	if s, ok := value.(string); ok {
		return s
	}

	return ""
}

// GetObject returns a nested object, or nil when absent or another type.
func (o *Object) GetObject(key string) *Object {
	value, ok := o.Get(key)
	if !ok {
		return nil
	}
	if nested, ok := value.(*Object); ok {
		return nested
	}

	return nil
}

// GetInt64 returns an integer value, or 0 when absent or not a number.
//
// A decoded document holds json.Number, but a value Set programmatically holds
// whatever Go type the caller had. Handling only json.Number made those read
// back as zero -- silently, which is how a paginated walk stopped after one
// page against a hand-built response.
func (o *Object) GetInt64(key string) int64 {
	value, ok := o.Get(key)
	if !ok {
		return 0
	}

	switch typed := value.(type) {
	case json.Number:
		parsed, err := typed.Int64()
		if err != nil {
			return 0
		}

		return parsed
	case int64:
		return typed
	case int:
		return int64(typed)
	case float64:
		// Only exact integers. A fractional value is not an integer, and
		// truncating one would be worse than reporting zero.
		if typed == math.Trunc(typed) {
			return int64(typed)
		}
	}

	return 0
}

// Objects keeps the entries of items that are objects.
//
// A list endpoint returns objects, so anything else is a malformed entry rather
// than a value to reason about. Dropping it keeps one bad row from failing an
// entire pull.
func Objects(items []any) []*Object {
	objects := make([]*Object, 0, len(items))
	for _, item := range items {
		if object, ok := item.(*Object); ok {
			objects = append(objects, object)
		}
	}

	return objects
}

// GetObjects reads a named array and keeps its object entries.
func (o *Object) GetObjects(key string) []*Object {
	if o == nil {
		return nil
	}

	value, ok := o.Get(key)
	if !ok {
		return nil
	}

	items, ok := value.([]any)
	if !ok {
		return nil
	}

	return Objects(items)
}

// Set stores a value, appending the key if it is new and leaving its position
// untouched if it already exists. Overwriting must not move a key: that is what
// keeps a rewritten config diff-free.
func (o *Object) Set(key string, value any) {
	if o.values == nil {
		o.values = map[string]any{}
	}
	if _, exists := o.values[key]; !exists {
		o.keys = append(o.keys, key)
	}
	o.values[key] = value
}

// Delete removes a key, preserving the order of the rest.
func (o *Object) Delete(key string) {
	if o == nil {
		return
	}
	if _, exists := o.values[key]; !exists {
		return
	}
	delete(o.values, key)
	for i, existing := range o.keys {
		if existing == key {
			o.keys = append(o.keys[:i], o.keys[i+1:]...)
			break
		}
	}
}

// Has reports whether key is present.
func (o *Object) Has(key string) bool {
	if o == nil {
		return false
	}
	_, ok := o.values[key]

	return ok
}

// Len returns the number of keys.
func (o *Object) Len() int {
	if o == nil {
		return 0
	}

	return len(o.keys)
}

// UnmarshalJSON decodes an object while recording key order.
func (o *Object) UnmarshalJSON(data []byte) error {
	decoder := json.NewDecoder(bytes.NewReader(data))
	decoder.UseNumber()

	token, err := decoder.Token()
	if err != nil {
		return err
	}
	if delim, ok := token.(json.Delim); !ok || delim != '{' {
		return fmt.Errorf("jsonx: expected object, got %v", token)
	}

	o.keys = nil
	o.values = map[string]any{}

	return o.decodeInto(decoder)
}

func (o *Object) decodeInto(decoder *json.Decoder) error {
	for decoder.More() {
		token, err := decoder.Token()
		if err != nil {
			return err
		}
		key, ok := token.(string)
		if !ok {
			return fmt.Errorf("jsonx: expected object key, got %v", token)
		}

		value, err := decodeValue(decoder)
		if err != nil {
			return err
		}
		o.Set(key, value)
	}

	// Consume the closing brace.
	if _, err := decoder.Token(); err != nil && err != io.EOF {
		return err
	}

	return nil
}

func decodeValue(decoder *json.Decoder) (any, error) {
	token, err := decoder.Token()
	if err != nil {
		return nil, err
	}

	delim, isDelim := token.(json.Delim)
	if !isDelim {
		return token, nil
	}

	switch delim {
	case '{':
		nested := NewObject()
		if err := nested.decodeInto(decoder); err != nil {
			return nil, err
		}

		return nested, nil
	case '[':
		items := []any{}
		for decoder.More() {
			item, err := decodeValue(decoder)
			if err != nil {
				return nil, err
			}
			items = append(items, item)
		}
		if _, err := decoder.Token(); err != nil {
			return nil, err
		}

		return items, nil
	}

	return nil, fmt.Errorf("jsonx: unexpected delimiter %v", delim)
}

// MarshalJSON re-encodes the object in its recorded key order.
func (o *Object) MarshalJSON() ([]byte, error) {
	if o == nil {
		return []byte("null"), nil
	}

	var buffer bytes.Buffer
	buffer.WriteByte('{')
	for i, key := range o.keys {
		if i > 0 {
			buffer.WriteByte(',')
		}
		encoded, err := marshalUnescaped(key)
		if err != nil {
			return nil, err
		}
		buffer.Write(encoded)
		buffer.WriteByte(':')

		encoded, err = marshalUnescaped(o.values[key])
		if err != nil {
			return nil, err
		}
		buffer.Write(encoded)
	}
	buffer.WriteByte('}')

	return buffer.Bytes(), nil
}

// marshalUnescaped encodes a value without Go's HTML escaping.
//
// json.Marshal always escapes <, > and & to \u003c and friends, and there is no
// option to turn that off -- only json.Encoder has one. Nested values have to
// go through an encoder too, because the outer encoder's SetEscapeHTML(false)
// does not reach inside a custom MarshalJSON's output: it is copied through
// compact(), which does not undo escapes already written.
//
// Without this, any URL with a query string comes out as `?a=1\u0026b=2` --
// valid JSON, but not the bytes JSON.stringify produces, which breaks
// invariant 4 and rewrites URLs in config files.
func marshalUnescaped(value any) ([]byte, error) {
	var buffer bytes.Buffer
	encoder := json.NewEncoder(&buffer)
	encoder.SetEscapeHTML(false)

	if err := encoder.Encode(value); err != nil {
		return nil, err
	}

	return bytes.TrimRight(buffer.Bytes(), "\n"), nil
}

// DecodeValue parses any JSON document, preserving object key order and keeping
// numbers as json.Number.
//
// Use this rather than json.Unmarshal into an any: that produces map[string]any
// for objects, which re-emits keys sorted, and float64 for numbers, which loses
// digits past 2^53.
func DecodeValue(payload []byte) (any, error) {
	trimmed := bytes.TrimSpace(payload)
	if len(trimmed) == 0 {
		return nil, nil
	}

	if trimmed[0] == '{' {
		object := NewObject()
		if err := object.UnmarshalJSON(trimmed); err != nil {
			return nil, err
		}

		return object, nil
	}

	decoder := json.NewDecoder(bytes.NewReader(trimmed))
	decoder.UseNumber()

	return decodeValue(decoder)
}

// Marshal renders the object the way the TypeScript CLI writes config files:
// four-space indentation, no HTML escaping, and a trailing newline left off.
//
// json.Encoder escapes <, > and & by default, which would rewrite any URL or
// description containing them. JSON.stringify does not, so neither does this.
func Marshal(value any) ([]byte, error) {
	var buffer bytes.Buffer
	encoder := json.NewEncoder(&buffer)
	encoder.SetEscapeHTML(false)
	encoder.SetIndent("", "    ")

	if err := encoder.Encode(value); err != nil {
		return nil, err
	}

	return []byte(strings.TrimRight(buffer.String(), "\n")), nil
}

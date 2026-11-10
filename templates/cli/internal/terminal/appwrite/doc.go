package appwrite

import (
	"bytes"
	"encoding/json"
	"fmt"
	"slices"
	"strconv"
	"strings"
	"time"
)

// Doc is one resource as the API answers it: a database, a row, an execution. Its fields are
// read by their names in the API's json, as the columns of a row are only known at run time, and
// Raw keeps the json as it came, for the json view.
type Doc struct {
	Raw    json.RawMessage
	fields map[string]any
}

// NewDoc decodes one resource, numbers kept as they were written.
func NewDoc(raw json.RawMessage) (Doc, error) {
	d := json.NewDecoder(bytes.NewReader(raw))
	d.UseNumber()
	var fields map[string]any
	if err := d.Decode(&fields); err != nil {
		return Doc{}, err
	}
	return Doc{Raw: raw, fields: fields}, nil
}

// MustDoc decodes json known to be an object, for tests and fakes.
func MustDoc(raw string) Doc {
	d, err := NewDoc(json.RawMessage(raw))
	if err != nil {
		panic(err)
	}
	return d
}

// ID is the resource's id: its $id, or the key of a column or an index, which have none.
func (d Doc) ID() string {
	if id := d.Str("$id"); id != "" {
		return id
	}
	return d.Str("key")
}

// Has reports whether the field is there, also when it is null.
func (d Doc) Has(field string) bool {
	_, ok := d.fields[field]
	return ok
}

// Value is the field as decoded: a string, json.Number, bool, []any, map[string]any or nil.
func (d Doc) Value(field string) any { return d.fields[field] }

// Str is a field that holds a string, or the field written out when it holds something else.
func (d Doc) Str(field string) string {
	v, ok := d.fields[field]
	if !ok {
		return ""
	}
	return Format(v)
}

// Bool is a field that holds a boolean, false when it holds anything else.
func (d Doc) Bool(field string) bool {
	b, _ := d.fields[field].(bool)
	return b
}

// Int is a field that holds a whole number, 0 when it holds anything else.
func (d Doc) Int(field string) int64 {
	if n, ok := d.fields[field].(json.Number); ok {
		if i, err := n.Int64(); err == nil {
			return i
		}
		if f, err := n.Float64(); err == nil {
			return int64(f)
		}
	}
	return 0
}

// Float is a field that holds a number, 0 when it holds anything else.
func (d Doc) Float(field string) float64 {
	if n, ok := d.fields[field].(json.Number); ok {
		f, _ := n.Float64()
		return f
	}
	return 0
}

// Strings is a field that holds a list, each element written out.
func (d Doc) Strings(field string) []string {
	list, _ := d.fields[field].([]any)
	out := make([]string, 0, len(list))
	for _, v := range list {
		out = append(out, Format(v))
	}
	return out
}

// Time is a field that holds an ISO 8601 date, the zero time when it holds none.
func (d Doc) Time(field string) time.Time {
	t, err := time.Parse(time.RFC3339Nano, d.Str(field))
	if err != nil {
		return time.Time{}
	}
	return t
}

// Object is a field that holds an object, as a Doc.
func (d Doc) Object(field string) (Doc, bool) {
	m, ok := d.fields[field].(map[string]any)
	if !ok {
		return Doc{}, false
	}
	raw, err := json.Marshal(m)
	if err != nil {
		return Doc{}, false
	}
	return Doc{Raw: raw, fields: m}, true
}

// Fields are the names of the fields, the API's own, the ones that start with $, last.
func (d Doc) Fields() []string {
	names := make([]string, 0, len(d.fields))
	for name := range d.fields {
		names = append(names, name)
	}
	slices.SortFunc(names, func(a, b string) int {
		if da, db := strings.HasPrefix(a, "$"), strings.HasPrefix(b, "$"); da != db {
			if da {
				return 1
			}
			return -1
		}
		return strings.Compare(a, b)
	})
	return names
}

// Format writes a decoded json value on one line: a string as it is, null as an empty string,
// and a list or an object as compact json.
func Format(v any) string {
	switch v := v.(type) {
	case nil:
		return ""
	case string:
		return v
	case json.Number:
		return v.String()
	case bool:
		return strconv.FormatBool(v)
	default:
		data, err := json.Marshal(v)
		if err != nil {
			return fmt.Sprint(v)
		}
		return string(data)
	}
}

// Page is one page of a list, and how many the list holds in all.
type Page struct {
	Total int
	Docs  []Doc
}

// decodePage reads a list's answer, its resources under key.
func decodePage(data []byte, key string) (Page, error) {
	var envelope map[string]json.RawMessage
	if err := json.Unmarshal(data, &envelope); err != nil {
		return Page{}, err
	}
	var p Page
	if total, ok := envelope["total"]; ok {
		if err := json.Unmarshal(total, &p.Total); err != nil {
			return Page{}, fmt.Errorf("reading the total: %w", err)
		}
	}
	var items []json.RawMessage
	if list, ok := envelope[key]; ok && string(list) != "null" {
		if err := json.Unmarshal(list, &items); err != nil {
			return Page{}, fmt.Errorf("reading the %s: %w", key, err)
		}
	}
	p.Docs = make([]Doc, 0, len(items))
	for _, item := range items {
		d, err := NewDoc(item)
		if err != nil {
			return Page{}, fmt.Errorf("reading one of the %s: %w", key, err)
		}
		p.Docs = append(p.Docs, d)
	}
	return p, nil
}

package appwrite

import (
	"fmt"
	"strconv"
	"strings"
)

// operators are the comparisons a filter takes, longest first so >= is not read as >.
var operators = []struct {
	op     string
	method string
}{
	{"!=", "notEqual"}, {">=", "greaterThanEqual"}, {"<=", "lessThanEqual"},
	{"=", "equal"}, {">", "greaterThan"}, {"<", "lessThan"}, {"~", "contains"},
}

// IsFilter reports whether text is a filter of queries rather than text to find: it holds a
// comparison or a sort.
func IsFilter(text string) bool {
	for _, term := range splitTerms(text) {
		if strings.HasPrefix(term, "sort:") {
			return true
		}
		for _, o := range operators {
			if i := strings.Index(term, o.op); i > 0 {
				return true
			}
		}
	}
	return false
}

// ParseFilter turns a filter into the queries of a list of rows: terms apart by spaces, each a
// column, a comparison and a value, all of which a row must meet, and sort:column or
// sort:-column to order by. A value is a number, true, false or null where it reads as one, and
// text else; quotes keep spaces in it and make it text.
//
//	customer=ada total>=10 status!=null name~"lin" sort:-total
//
// = with null is isNull and != with null isNotNull; ~ is contains.
//
// docs: https://appwrite.io/docs/products/databases/queries
func ParseFilter(text string) ([]string, error) {
	var out []string
	for _, term := range splitTerms(text) {
		if col, ok := strings.CutPrefix(term, "sort:"); ok {
			method := "orderAsc"
			if c, desc := strings.CutPrefix(col, "-"); desc {
				method, col = "orderDesc", c
			}
			if col == "" {
				return nil, fmt.Errorf("sort: needs a column, as sort:-total")
			}
			out = append(out, query{Method: method, Attribute: col}.String())
			continue
		}
		q, err := parseTerm(term)
		if err != nil {
			return nil, err
		}
		out = append(out, q.String())
	}
	return out, nil
}

// parseTerm reads one comparison, as total>=10.
func parseTerm(term string) (query, error) {
	for _, o := range operators {
		i := strings.Index(term, o.op)
		if i <= 0 {
			continue
		}
		col, raw := term[:i], term[i+len(o.op):]
		quoted := len(raw) >= 2 && raw[0] == '"' && raw[len(raw)-1] == '"'
		if quoted {
			raw = raw[1 : len(raw)-1]
		}
		if !quoted && raw == "null" {
			switch o.op {
			case "=":
				return query{Method: "isNull", Attribute: col}, nil
			case "!=":
				return query{Method: "isNotNull", Attribute: col}, nil
			}
		}
		return query{Method: o.method, Attribute: col, Values: []any{filterValue(raw, quoted)}}, nil
	}
	return query{}, fmt.Errorf("%q is not a comparison: write it as column=value, column>value or column~text", term)
}

// filterValue is a value of a filter as the api takes it: a number or a boolean where it reads
// as one and is not quoted, text else.
func filterValue(raw string, quoted bool) any {
	if quoted {
		return raw
	}
	if n, err := strconv.ParseInt(raw, 10, 64); err == nil {
		return n
	}
	if f, err := strconv.ParseFloat(raw, 64); err == nil {
		return f
	}
	if b, err := strconv.ParseBool(raw); err == nil && (raw == "true" || raw == "false") {
		return b
	}
	return raw
}

// splitTerms splits a filter at its spaces, but for those inside quotes.
func splitTerms(text string) []string {
	var terms []string
	var b strings.Builder
	quoted := false
	for _, r := range text {
		switch {
		case r == '"':
			quoted = !quoted
			b.WriteRune(r)
		case r == ' ' && !quoted:
			if b.Len() > 0 {
				terms = append(terms, b.String())
				b.Reset()
			}
		default:
			b.WriteRune(r)
		}
	}
	if b.Len() > 0 {
		terms = append(terms, b.String())
	}
	return terms
}

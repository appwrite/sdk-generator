package query

import "testing"

// These baselines were captured from real runs rather than written from
// expectation. The strings go on the wire, so they are a contract.
func TestParseFilterMatchesTheBaseline(t *testing.T) {
	cases := []struct{ expression, want string }{
		{"name=hello", `{"method":"equal","attribute":"name","values":["hello"]}`},
		{"age>=30", `{"method":"greaterThanEqual","attribute":"age","values":[30]}`},
		{"score<1.5", `{"method":"lessThan","attribute":"score","values":[1.5]}`},
		{"flag=true", `{"method":"equal","attribute":"flag","values":[true]}`},
		{"missing=null", `{"method":"equal","attribute":"missing","values":[null]}`},
		// An array value becomes the values list itself, not a nested element.
		{`tags=["a","b"]`, `{"method":"equal","attribute":"tags","values":["a","b"]}`},
		{"n!=5", `{"method":"notEqual","attribute":"n","values":[5]}`},
		// & must not be escaped -- JSON.stringify does not escape it.
		{"url=https://x.io/?a=1&b=2",
			`{"method":"equal","attribute":"url","values":["https://x.io/?a=1&b=2"]}`},
	}

	for _, tc := range cases {
		got, err := ParseFilter(tc.expression)
		if err != nil {
			t.Errorf("ParseFilter(%q) errored: %v", tc.expression, err)

			continue
		}
		if got != tc.want {
			t.Errorf("ParseFilter(%q)\n got %s\nwant %s", tc.expression, got, tc.want)
		}
	}
}

// Two-character operators must be tried before one-character ones, or `a >= 1`
// parses its attribute as `a >`.
func TestParseFilterPrefersLongerOperators(t *testing.T) {
	got, err := ParseFilter("a>=1")
	if err != nil {
		t.Fatal(err)
	}
	if got != `{"method":"greaterThanEqual","attribute":"a","values":[1]}` {
		t.Errorf("got %s", got)
	}
}

func TestParseFilterRejectsGarbage(t *testing.T) {
	for _, expression := range []string{"nooperator", "=novalue"} {
		if _, err := ParseFilter(expression); err == nil {
			t.Errorf("ParseFilter(%q) should have failed", expression)
		}
	}
}

func TestBuildOrdering(t *testing.T) {
	limit, offset := 25, 5
	after := "abc"

	got, err := Build(Options{
		Queries:     []string{`{"method":"raw"}`},
		Filter:      []string{`{"method":"equal","attribute":"a","values":[1]}`},
		Where:       []string{`{"method":"equal","attribute":"b","values":[2]}`},
		SortAsc:     []string{"name"},
		SortDesc:    []string{"created"},
		Limit:       &limit,
		Offset:      &offset,
		CursorAfter: &after,
		Select:      []string{"a", "b"},
	})
	if err != nil {
		t.Fatal(err)
	}

	// Order is documented in the --queries help text: raw first, then filter,
	// then the deprecated where, then sorts, pagination, cursors, select.
	want := []string{
		`{"method":"raw"}`,
		`{"method":"equal","attribute":"a","values":[1]}`,
		`{"method":"equal","attribute":"b","values":[2]}`,
		`{"method":"orderAsc","attribute":"name"}`,
		`{"method":"orderDesc","attribute":"created"}`,
		`{"method":"limit","values":[25]}`,
		`{"method":"offset","values":[5]}`,
		`{"method":"cursorAfter","values":["abc"]}`,
		`{"method":"select","values":["a","b"]}`,
	}

	if len(got) != len(want) {
		t.Fatalf("got %d queries, want %d:\n%v", len(got), len(want), got)
	}
	for i := range want {
		if got[i] != want[i] {
			t.Errorf("query %d\n got %s\nwant %s", i, got[i], want[i])
		}
	}
}

// Nothing requested means the parameter is omitted, not sent empty.
func TestBuildReturnsNilWhenEmpty(t *testing.T) {
	got, err := Build(Options{})
	if err != nil {
		t.Fatal(err)
	}
	if got != nil {
		t.Errorf("Build({}) = %v, want nil", got)
	}
}

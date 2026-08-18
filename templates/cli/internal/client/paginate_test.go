package client

import (
	"encoding/json"
	"fmt"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// pager serves a fixed row set one page at a time, recording the queries it
// was called with so the limit/offset the walker emits can be asserted.
type pager struct {
	rows []string
	// total is what the server reports, which is not always len(rows) -- a
	// stale count is exactly the case the empty-page guard exists for.
	total    int64
	queries  [][]string
	pageSize int
}

func (p *pager) list(queries []string) (*jsonx.Object, error) {
	p.queries = append(p.queries, queries)

	offset := 0
	for _, query := range queries {
		var decoded struct {
			Method string  `json:"method"`
			Values []int64 `json:"values"`
		}
		if err := json.Unmarshal([]byte(query), &decoded); err != nil {
			continue
		}
		if decoded.Method == "offset" && len(decoded.Values) == 1 {
			offset = int(decoded.Values[0])
		}
		if decoded.Method == "limit" && len(decoded.Values) == 1 {
			p.pageSize = int(decoded.Values[0])
		}
	}

	end := min(offset+p.pageSize, len(p.rows))
	page := []string{}
	if offset < len(p.rows) {
		page = p.rows[offset:end]
	}

	items := make([]any, 0, len(page))
	for _, row := range page {
		items = append(items, row)
	}

	response := jsonx.NewObject()
	response.Set("total", p.total)
	response.Set("things", items)

	return response, nil
}

func rows(count int) []string {
	values := make([]string, 0, count)
	for index := range count {
		values = append(values, fmt.Sprintf("row-%d", index))
	}

	return values
}

func TestPaginateWalksEveryPage(t *testing.T) {
	server := &pager{rows: rows(250), total: 250}

	results, total, err := Paginate(server.list, "things", nil, 100)
	if err != nil {
		t.Fatal(err)
	}

	if len(results) != 250 {
		t.Errorf("got %d rows, want 250", len(results))
	}
	if total != 250 {
		t.Errorf("total = %d, want 250", total)
	}
	// Three pages: 100, 100, 50. The third is short, so reaching the total
	// stops the walk without a fourth request.
	if len(server.queries) != 3 {
		t.Errorf("made %d requests, want 3", len(server.queries))
	}
}

// TestPaginateStopsOnAFullFinalPage pins the reason the total check exists: a
// row count that is an exact multiple of the page size would otherwise cost one
// extra request per list.
func TestPaginateStopsOnAFullFinalPage(t *testing.T) {
	server := &pager{rows: rows(200), total: 200}

	results, _, err := Paginate(server.list, "things", nil, 100)
	if err != nil {
		t.Fatal(err)
	}

	if len(results) != 200 {
		t.Fatalf("got %d rows, want 200", len(results))
	}
	if len(server.queries) != 2 {
		t.Errorf("made %d requests, want 2 -- the total should stop the walk", len(server.queries))
	}
}

// TestPaginateStopsOnAnEmptyPage pins the other guard. A server reporting a
// total larger than it can serve would loop forever on the total check alone.
func TestPaginateStopsOnAnEmptyPage(t *testing.T) {
	server := &pager{rows: rows(150), total: 9999}

	results, _, err := Paginate(server.list, "things", nil, 100)
	if err != nil {
		t.Fatal(err)
	}

	if len(results) != 150 {
		t.Errorf("got %d rows, want 150", len(results))
	}
	// 100, 50, then an empty page that ends it.
	if len(server.queries) != 3 {
		t.Errorf("made %d requests, want 3", len(server.queries))
	}
}

// TestPaginateDoesNotAccumulateQueries pins a bug that would be invisible until
// a large list: appending limit and offset to the caller's slice rather than a
// copy leaves one pair per page on the request.
func TestPaginateDoesNotAccumulateQueries(t *testing.T) {
	server := &pager{rows: rows(250), total: 250}
	caller := []string{`{"method":"equal","values":["x"]}`}

	if _, _, err := Paginate(server.list, "things", caller, 100); err != nil {
		t.Fatal(err)
	}

	for index, queries := range server.queries {
		if len(queries) != 3 {
			t.Errorf("request %d carried %d queries, want 3: %v", index, len(queries), queries)
		}

		limits := 0
		for _, query := range queries {
			if strings.Contains(query, `"limit"`) {
				limits++
			}
		}
		if limits != 1 {
			t.Errorf("request %d carried %d limit queries, want 1", index, limits)
		}
	}

	// And the caller's own slice is untouched.
	if len(caller) != 1 {
		t.Errorf("the caller's queries were mutated: %v", caller)
	}
}

func TestPaginateHandlesAnEmptyList(t *testing.T) {
	server := &pager{rows: nil, total: 0}

	results, total, err := Paginate(server.list, "things", nil, 100)
	if err != nil {
		t.Fatal(err)
	}

	if len(results) != 0 || total != 0 {
		t.Errorf("got %d rows and total %d, want none", len(results), total)
	}
	if len(server.queries) != 1 {
		t.Errorf("made %d requests, want 1", len(server.queries))
	}
}

func TestPaginateDefaultsThePageSize(t *testing.T) {
	server := &pager{rows: rows(10), total: 10}

	if _, _, err := Paginate(server.list, "things", nil, 0); err != nil {
		t.Fatal(err)
	}

	if server.pageSize != DefaultPageSize {
		t.Errorf("page size = %d, want %d", server.pageSize, DefaultPageSize)
	}
}

// TestPaginateStopsWhenTheWrapperIsMissing covers a response shaped differently
// from what the caller named -- it must terminate rather than spin.
func TestPaginateStopsWhenTheWrapperIsMissing(t *testing.T) {
	list := func([]string) (*jsonx.Object, error) {
		response := jsonx.NewObject()
		response.Set("total", int64(5))

		return response, nil
	}

	results, _, err := Paginate(list, "things", nil, 100)
	if err != nil {
		t.Fatal(err)
	}
	if len(results) != 0 {
		t.Errorf("got %d rows, want none", len(results))
	}
}

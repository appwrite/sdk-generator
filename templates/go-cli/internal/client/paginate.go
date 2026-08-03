package client

import (
	"fmt"
	"net/url"

	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
)

// Ports templates/cli/lib/paginate.ts.
//
// Every list endpoint caps a page at 100 rows, so `pull` and the interactive
// choice loaders have to walk. The walk is here rather than at each call site
// because the two termination conditions are easy to get subtly wrong, and
// getting them wrong means silently pulling a prefix of the user's data.

// DefaultPageSize matches the TypeScript's default.
const DefaultPageSize = 100

// Page is one response from a list endpoint.
type Page struct {
	// Items are the rows under the wrapper key.
	Items []any
	// Total is the server's count of matching rows.
	Total int64
}

// Lister fetches one page. queries are already-encoded query strings.
type Lister func(queries []string) (*jsonx.Object, error)

// Paginate walks a list endpoint until every row has been read.
//
// wrapper names the array in the response -- "functions", "rows" and so on.
//
// Two conditions stop the walk, and both are needed. An empty page stops it
// because a server that reports a stale total would otherwise loop forever;
// reaching the total stops it because a full final page would otherwise cost
// one extra request. The TypeScript checks both, and so does this.
func Paginate(list Lister, wrapper string, queries []string, pageSize int) ([]any, int64, error) {
	if pageSize <= 0 {
		pageSize = DefaultPageSize
	}

	var (
		results []any
		total   int64
	)

	for page := 0; ; page++ {
		offset := page * pageSize

		// Appended rather than merged into the caller's slice: the caller's
		// queries are reused on every iteration, and appending to them would
		// accumulate a limit and offset per page.
		paged := make([]string, 0, len(queries)+2)
		paged = append(paged, queries...)
		paged = append(paged,
			fmt.Sprintf(`{"method":"limit","values":[%d]}`, pageSize),
			fmt.Sprintf(`{"method":"offset","values":[%d]}`, offset))

		response, err := list(paged)
		if err != nil {
			return nil, 0, err
		}

		items := arrayField(response, wrapper)
		if len(items) == 0 {
			break
		}

		results = append(results, items...)
		total = response.GetInt64("total")

		if int64(len(results)) >= total {
			break
		}
	}

	return results, total, nil
}

// arrayField reads a named array out of a response.
func arrayField(response *jsonx.Object, name string) []any {
	if response == nil {
		return nil
	}

	value, ok := response.Get(name)
	if !ok {
		return nil
	}

	items, ok := value.([]any)
	if !ok {
		return nil
	}

	return items
}

// PaginateInto walks a list endpoint and returns the rows as objects.
//
// Anything that is not an object is dropped rather than erroring: a list
// endpoint returns objects, and refusing to continue because one entry is
// malformed would fail a pull over a single bad row.
func PaginateInto(list Lister, wrapper string, queries []string, pageSize int) ([]*jsonx.Object, int64, error) {
	items, total, err := Paginate(list, wrapper, queries, pageSize)
	if err != nil {
		return nil, 0, err
	}

	rows := make([]*jsonx.Object, 0, len(items))
	for _, item := range items {
		if object, ok := item.(*jsonx.Object); ok {
			rows = append(rows, object)
		}
	}

	return rows, total, nil
}

// EncodeQueries renders query strings as the API expects them.
//
// `queries[0]=`, indexed -- NOT `queries[]=`. Both happen to work on the list
// endpoints, but the TypeScript sends the indexed form and a request trace is
// how the two CLIs are compared, so the wire has to match too. Found by
// diffing traces on a command whose config output was already identical.
func EncodeQueries(queries []string) string {
	values := url.Values{}
	for index, query := range queries {
		values.Add(fmt.Sprintf("queries[%d]", index), query)
	}

	return values.Encode()
}

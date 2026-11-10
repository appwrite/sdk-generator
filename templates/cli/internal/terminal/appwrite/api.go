package appwrite

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strconv"
)

// PageSize is how many resources a list reads at a time, the most the API sends.
const PageSize = 100

// ListOptions narrow a list: a search, and where the page starts, after the resource of an id
// or, for a kind keyed rather than identified, at an offset.
type ListOptions struct {
	Search string
	// Queries narrow and order a list, as ParseFilter writes them.
	Queries []string
	After   string
	Offset  int
	Limit   int
}

// query is a query of a list, as the API takes them, one json object each.
//
// docs: https://appwrite.io/docs/products/databases/queries
type query struct {
	Method    string `json:"method"`
	Attribute string `json:"attribute,omitempty"`
	Values    []any  `json:"values,omitempty"`
}

// equal is a query that the attribute holds the value.
func equal(attribute string, value any) query {
	return query{Method: "equal", Attribute: attribute, Values: []any{value}}
}

func (q query) String() string {
	data, _ := json.Marshal(q)
	return string(data)
}

// listQuery is what a list of the ref sends.
func listQuery(r Ref, opts ListOptions) url.Values {
	info := kinds[r.Kind]
	v := url.Values{}
	var queries []query
	if r.Kind == Projects && len(r.Parents) > 0 {
		queries = append(queries, equal("teamId", r.Parents[0]))
	}
	if info.paged {
		limit := opts.Limit
		if limit <= 0 {
			limit = PageSize
		}
		queries = append(queries, query{Method: "limit", Values: []any{limit}})
		switch {
		case info.offset && opts.Offset > 0:
			queries = append(queries, query{Method: "offset", Values: []any{opts.Offset}})
		case !info.offset && opts.After != "":
			queries = append(queries, query{Method: "cursorAfter", Values: []any{opts.After}})
		}
		if info.newest {
			queries = append(queries, query{Method: "orderDesc", Attribute: "$createdAt"})
		}
	}
	encoded := make([]string, 0, len(queries)+len(opts.Queries))
	for _, q := range queries {
		encoded = append(encoded, q.String())
	}
	if info.paged {
		encoded = append(encoded, opts.Queries...)
	}
	for i, q := range encoded {
		v.Set("queries["+strconv.Itoa(i)+"]", q)
	}
	if info.search && opts.Search != "" {
		v.Set("search", opts.Search)
	}
	return v
}

// clientFor is the client a ref is read with: the console's own routes as the console project.
func (c *Client) clientFor(r Ref) *Client {
	if kinds[r.Kind].console && c.project != ConsoleProject {
		return c.AsProject(ConsoleProject)
	}
	return c
}

// List reads a page of the resources of a ref.
func (c *Client) List(ctx context.Context, r Ref, opts ListOptions) (Page, error) {
	path, err := r.path()
	if err != nil {
		return Page{}, err
	}
	data, err := c.clientFor(r).raw(ctx, http.MethodGet, path, listQuery(r, opts), nil)
	if err != nil {
		return Page{}, err
	}
	p, err := decodePage(data, kinds[r.Kind].key)
	if err != nil {
		return Page{}, fmt.Errorf("reading the answer to GET %s: %w", path, err)
	}
	return p, nil
}

// Count is how many resources a ref lists, read as the total of a page of one.
func (c *Client) Count(ctx context.Context, r Ref) (int, error) {
	p, err := c.List(ctx, r, ListOptions{Limit: 1})
	if err != nil {
		return 0, err
	}
	if !kinds[r.Kind].paged {
		return max(p.Total, len(p.Docs)), nil
	}
	return p.Total, nil
}

// Get reads one resource of a ref again, by its id.
func (c *Client) Get(ctx context.Context, r Ref, id string) (Doc, error) {
	if !kinds[r.Kind].get {
		return Doc{}, fmt.Errorf("a single one of the %s cannot be read", kinds[r.Kind].key)
	}
	path, err := r.path()
	if err != nil {
		return Doc{}, err
	}
	data, err := c.clientFor(r).raw(ctx, http.MethodGet, path+"/"+segment(id), nil, nil)
	if err != nil {
		return Doc{}, err
	}
	return NewDoc(data)
}

// Delete deletes one resource of a ref, by its id.
func (c *Client) Delete(ctx context.Context, r Ref, id string) error {
	if !kinds[r.Kind].remove {
		return fmt.Errorf("the %s cannot be deleted from here", kinds[r.Kind].key)
	}
	path, err := r.path()
	if err != nil {
		return err
	}
	return c.do(ctx, http.MethodDelete, path+"/"+segment(id), nil, nil, nil)
}

// Update changes the values of a resource the kind lets be written, a row's columns, and returns
// the resource as it is now.
//
// docs: https://appwrite.io/docs/references/cloud/server-rest/tablesdb#updateRow
func (c *Client) Update(ctx context.Context, r Ref, id string, data map[string]any) (Doc, error) {
	if kinds[r.Kind].write == "" {
		return Doc{}, fmt.Errorf("the %s cannot be changed from here", kinds[r.Kind].key)
	}
	path, err := r.path()
	if err != nil {
		return Doc{}, err
	}
	raw, err := c.raw(ctx, http.MethodPatch, path+"/"+segment(id), nil, map[string]any{"data": data})
	if err != nil {
		return Doc{}, err
	}
	return NewDoc(raw)
}

// Create makes a resource of a kind that lets it, a row, under an id the API picks.
//
// docs: https://appwrite.io/docs/references/cloud/server-rest/tablesdb#createRow
func (c *Client) Create(ctx context.Context, r Ref, data map[string]any) (Doc, error) {
	info := kinds[r.Kind]
	if info.write == "" {
		return Doc{}, fmt.Errorf("the %s cannot be created from here", info.key)
	}
	path, err := r.path()
	if err != nil {
		return Doc{}, err
	}
	raw, err := c.raw(ctx, http.MethodPost, path, nil, map[string]any{info.write: "unique()", "data": data})
	if err != nil {
		return Doc{}, err
	}
	return NewDoc(raw)
}

// Execution is a request to execute a function with.
type Execution struct {
	Method  string            `json:"method,omitempty"`
	Path    string            `json:"path,omitempty"`
	Body    string            `json:"body"`
	Headers map[string]string `json:"headers,omitempty"`
	Async   bool              `json:"async"`
}

// Execute runs a function and, unless it is async, waits for what it returned.
//
// docs: https://appwrite.io/docs/references/cloud/server-rest/functions#createExecution
func (c *Client) Execute(ctx context.Context, function string, e Execution) (Doc, error) {
	data, err := c.raw(ctx, http.MethodPost, "/functions/"+segment(function)+"/executions", nil, e)
	if err != nil {
		return Doc{}, err
	}
	return NewDoc(data)
}

// Download writes a file of a bucket to w, and returns how many bytes it wrote.
//
// docs: https://appwrite.io/docs/references/cloud/server-rest/storage#getFileDownload
func (c *Client) Download(ctx context.Context, bucket, file string, w io.Writer) (int64, error) {
	path := "/storage/buckets/" + segment(bucket) + "/files/" + segment(file) + "/download"
	req, err := http.NewRequestWithContext(ctx, http.MethodGet, c.endpoint+path, nil)
	if err != nil {
		return 0, err
	}
	if err := c.authorise(req); err != nil {
		return 0, err
	}
	resp, err := c.http.Do(req)
	if err != nil {
		return 0, err
	}
	defer func() { _ = resp.Body.Close() }()
	if resp.StatusCode >= 300 {
		return 0, decodeError(resp)
	}
	return io.Copy(w, resp.Body)
}

// Project reads the project the client calls as. An instance older than the project route
// answers 404, which the caller takes as the project's id being all there is to show.
//
// docs: https://appwrite.io/docs/references/cloud/server-rest/project#get
func (c *Client) Project(ctx context.Context) (Doc, error) {
	data, err := c.raw(ctx, http.MethodGet, "/project", nil, nil)
	if err != nil {
		return Doc{}, err
	}
	return NewDoc(data)
}

// Account reads the account of a console login, which says it is still signed in.
//
// docs: https://appwrite.io/docs/references/cloud/client-rest/account#get
func (c *Client) Account(ctx context.Context) (Doc, error) {
	data, err := c.raw(ctx, http.MethodGet, "/account", nil, nil)
	if err != nil {
		return Doc{}, err
	}
	return NewDoc(data)
}

package appwrite

import (
	"fmt"
	"strings"
)

// Kind is a kind of resource the interface lists, as databases or the executions of a function.
type Kind int

// The kinds of resource. A kind under another is listed for one of them, its parent, whose id
// and those of its own parents a Ref carries.
const (
	Databases Kind = iota
	Tables
	Rows
	Columns
	Indexes
	Functions
	FunctionDeployments
	Executions
	FunctionVariables
	Sites
	SiteDeployments
	SiteLogs
	Buckets
	Files
	Users
	Sessions
	UserMemberships
	Teams
	Memberships
	Organisations
	Projects
	Providers
	Topics
	Subscribers
	Messages
	MessageTargets
	UserTargets
)

// kindInfo is where the API lists a kind and what its list takes.
type kindInfo struct {
	// path is the route of the list, its parents' ids in place of the {}s in order.
	path string
	// key is the field of a list's answer that holds the resources.
	key string
	// docs is the reference of the list.
	docs string
	// search says the list takes a search, which the API matches against the kind's own fields.
	search bool
	// paged says the list takes queries: a limit, and a cursor or an offset.
	paged bool
	// offset pages by an offset, for a kind whose resources have a key rather than an id.
	offset bool
	// newest lists the latest first, as executions and deployments read.
	newest bool
	// get says a single resource can be read again by its id, below the list's path.
	get bool
	// remove says a resource can be deleted by its id, below the list's path.
	remove bool
	// console says the route is the console's own, answered for the console project.
	console bool
	// write is the field a create names a resource's id in, for a kind whose values can be
	// written, as a row's rowId.
	write string
}

var kinds = map[Kind]kindInfo{
	Databases: {path: "/tablesdb", key: "databases", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/tablesdb#list"},
	Tables: {path: "/tablesdb/{}/tables", key: "tables", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/tablesdb#listTables"},
	Rows: {path: "/tablesdb/{}/tables/{}/rows", key: "rows", paged: true, get: true, remove: true, write: "rowId",
		docs: "https://appwrite.io/docs/references/cloud/server-rest/tablesdb#listRows"},
	Columns: {path: "/tablesdb/{}/tables/{}/columns", key: "columns", paged: true, offset: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/tablesdb#listColumns"},
	Indexes: {path: "/tablesdb/{}/tables/{}/indexes", key: "indexes", paged: true, offset: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/tablesdb#listIndexes"},
	Functions: {path: "/functions", key: "functions", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/functions#list"},
	FunctionDeployments: {path: "/functions/{}/deployments", key: "deployments", search: true, paged: true, newest: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/functions#listDeployments"},
	Executions: {path: "/functions/{}/executions", key: "executions", paged: true, newest: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/functions#listExecutions"},
	FunctionVariables: {path: "/functions/{}/variables", key: "variables", get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/functions#listVariables"},
	Sites: {path: "/sites", key: "sites", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/sites#list"},
	SiteDeployments: {path: "/sites/{}/deployments", key: "deployments", search: true, paged: true, newest: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/sites#listDeployments"},
	SiteLogs: {path: "/sites/{}/logs", key: "executions", paged: true, newest: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/sites#listLogs"},
	Buckets: {path: "/storage/buckets", key: "buckets", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/storage#listBuckets"},
	Files: {path: "/storage/buckets/{}/files", key: "files", search: true, paged: true, get: true, remove: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/storage#listFiles"},
	Users: {path: "/users", key: "users", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/users#list"},
	Sessions: {path: "/users/{}/sessions", key: "sessions", remove: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/users#listSessions"},
	UserMemberships: {path: "/users/{}/memberships", key: "memberships", search: true, paged: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/users#listMemberships"},
	Teams: {path: "/teams", key: "teams", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/teams#list"},
	Memberships: {path: "/teams/{}/memberships", key: "memberships", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/teams#listMemberships"},
	Organisations: {path: "/teams", key: "teams", search: true, paged: true, get: true, console: true,
		docs: "https://appwrite.io/docs/references/cloud/client-rest/teams#list"},
	Projects: {path: "/projects", key: "projects", search: true, paged: true, get: true, console: true,
		docs: "https://appwrite.io/docs/references/cloud/client-rest/projects"},
	Providers: {path: "/messaging/providers", key: "providers", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/messaging#listProviders"},
	Topics: {path: "/messaging/topics", key: "topics", search: true, paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/messaging#listTopics"},
	Subscribers: {path: "/messaging/topics/{}/subscribers", key: "subscribers", search: true, paged: true, get: true, remove: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/messaging#listSubscribers"},
	Messages: {path: "/messaging/messages", key: "messages", search: true, paged: true, newest: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/messaging#listMessages"},
	MessageTargets: {path: "/messaging/messages/{}/targets", key: "targets", paged: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/messaging#listTargets"},
	UserTargets: {path: "/users/{}/targets", key: "targets", paged: true, get: true,
		docs: "https://appwrite.io/docs/references/cloud/server-rest/users#listTargets"},
}

// Parents is how many parents' ids a ref of the kind carries.
func (k Kind) Parents() int { return strings.Count(kinds[k].path, "{}") }

// Searchable reports whether a list of the kind takes a search.
func (k Kind) Searchable() bool { return kinds[k].search }

// Paged reports whether a list of the kind comes a page at a time.
func (k Kind) Paged() bool { return kinds[k].paged }

// IDOf is what names a resource of the kind in its routes: its key for a column or an index,
// whose $id the API makes up, and its id for the rest.
func (k Kind) IDOf(d Doc) string {
	if kinds[k].offset {
		return d.Str("key")
	}
	return d.ID()
}

// Writable reports whether resources of the kind can be created and their values changed.
func (k Kind) Writable() bool { return kinds[k].write != "" }

// Gettable reports whether one resource of the kind can be read again by its id.
func (k Kind) Gettable() bool { return kinds[k].get }

// Deletable reports whether a resource of the kind can be deleted.
func (k Kind) Deletable() bool { return kinds[k].remove }

// Ref names a list: its kind and the ids of its parents, from the top, as the database and the
// table of rows. Projects carry the organisation's id, which the list filters on.
type Ref struct {
	Kind    Kind
	Parents []string
}

// path is the route of the list.
func (r Ref) path() (string, error) {
	info, ok := kinds[r.Kind]
	if !ok {
		return "", fmt.Errorf("no route for kind %d", r.Kind)
	}
	if r.Kind == Projects {
		return info.path, nil
	}
	if len(r.Parents) != r.Kind.Parents() {
		return "", fmt.Errorf("%s needs %d parent ids, got %d", info.key, r.Kind.Parents(), len(r.Parents))
	}
	var b strings.Builder
	parts := strings.Split(info.path, "{}")
	for i, part := range parts {
		b.WriteString(part)
		if i < len(r.Parents) {
			b.WriteString(segment(r.Parents[i]))
		}
	}
	return b.String(), nil
}

// ItemPath is the route of one resource of the list, as usage records the requests to it:
// /v1/tablesdb/main/tables/orders. It is empty for the console's own routes, which usage does not
// record.
func (r Ref) ItemPath(id string) string {
	path, err := r.path()
	if err != nil || kinds[r.Kind].console {
		return ""
	}
	return "/v1" + path + "/" + segment(id)
}

// Child is the ref of a kind under the resource id of this list, as the tables of a database.
func (r Ref) Child(kind Kind, id string) Ref {
	parents := append(append(make([]string, 0, len(r.Parents)+1), r.Parents...), id)
	return Ref{Kind: kind, Parents: parents}
}

package config

import (
	"encoding/json"
	"fmt"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// Each adds or replaces one entry in a top-level resource array. The config
// lives in the user's repository and is code-reviewed, so nothing outside the
// touched array may change -- the document stays an ordered jsonx.Object
// throughout -- and an existing entry is replaced in place rather than appended,
// so re-running `init` does not duplicate a resource.
//
// Field order is part of the output. Each struct below is ordered to match its
// `init` call site, today the only caller of each add helper.

// Bucket is a storage bucket as the config records it.
//
// The field set is the whitelist: the TypeScript filters props through
// whitelistKeys(BucketSchema) so an API response can be handed straight in
// without dragging $createdAt and friends into the config. A typed struct does
// the same thing by construction.
type Bucket struct {
	ID                    string   `json:"$id"`
	Name                  string   `json:"name"`
	Enabled               *bool    `json:"enabled,omitempty"`
	MaximumFileSize       *int64   `json:"maximumFileSize,omitempty"`
	AllowedFileExtensions []string `json:"allowedFileExtensions,omitempty"`
	Compression           string   `json:"compression,omitempty"`
	Encryption            *bool    `json:"encryption,omitempty"`
	Antivirus             *bool    `json:"antivirus,omitempty"`
	FileSecurity          *bool    `json:"fileSecurity,omitempty"`
	Permissions           []string `json:"$permissions,omitempty"`
}

// Team is a team as the config records it.
type Team struct {
	ID   string `json:"$id"`
	Name string `json:"name"`
}

// Topic is a messaging topic as the config records it.
type Topic struct {
	ID          string   `json:"$id"`
	Name        string   `json:"name"`
	Subscribe   []string `json:"subscribe,omitempty"`
	Description string   `json:"description,omitempty"`
}

// AddBucket adds or replaces a bucket.
func (l *Local) AddBucket(bucket Bucket) error {
	return l.upsert("buckets", bucket, identityByID(bucket.ID))
}

// AddTeam adds or replaces a team.
func (l *Local) AddTeam(team Team) error {
	return l.upsert("teams", team, identityByID(team.ID))
}

// AddTopic adds or replaces a messaging topic.
func (l *Local) AddTopic(topic Topic) error {
	return l.upsert("topics", topic, identityByID(topic.ID))
}

// AddCollection adds or replaces a collection.
//
// Identity is $id AND databaseId: two databases may hold a collection with the
// same id, and matching on id alone would overwrite the wrong one.
func (l *Local) AddCollection(collection Collection) error {
	// A nil slice would marshal as null, which is neither "declared with
	// none" nor "not yet pulled".
	collection.Attributes = orEmpty(collection.Attributes)
	collection.Indexes = orEmpty(collection.Indexes)

	return l.upsert("collections", collection,
		identityByIDAndDatabase(collection.ID, collection.DatabaseID))
}

// AddTable adds or replaces a table.
func (l *Local) AddTable(table Table) error {
	table.Columns = orEmpty(table.Columns)
	table.Indexes = orEmpty(table.Indexes)
	if table.Permissions == nil {
		table.Permissions = []string{}
	}

	return l.upsert("tables", table, identityByIDAndDatabase(table.ID, table.DatabaseID))
}

// AddFunction adds or replaces a function.
//
// The entry type lives in internal/cmd beside the command that builds it, so
// this takes `any` and relies on toObject's JSON round trip for both the key
// names and their order.
func (l *Local) AddFunction(function any) error {
	return l.upsert("functions", function, identityByID(entryID(function)))
}

// AddSite adds or replaces a site.
func (l *Local) AddSite(site any) error {
	return l.upsert("sites", site, identityByID(entryID(site)))
}

// entryID reads the $id off a value that is about to be upserted.
//
// A failure here yields "", which matches nothing and so appends rather than
// replaces. That is the safe direction: a duplicated entry is visible in the
// config and fixable, whereas matching the wrong one overwrites a resource the
// user did not name.
func entryID(value any) string {
	object, err := toObject(value)
	if err != nil {
		return ""
	}

	return object.GetString("$id")
}

// orEmpty replaces a nil slice with an empty one.
func orEmpty(values []any) []any {
	if values == nil {
		return []any{}
	}

	return values
}

// AddDatabase adds or replaces a database.
func (l *Local) AddDatabase(database Database) error {
	return l.upsert("databases", database, identityByID(database.ID))
}

// AddTablesDB adds or replaces a tablesDB entry.
func (l *Local) AddTablesDB(database Database) error {
	return l.upsert("tablesDB", database, identityByID(database.ID))
}

// Collection is a collection as the config records it.
type Collection struct {
	ID               string   `json:"$id"`
	DatabaseID       string   `json:"databaseId"`
	Name             string   `json:"name"`
	DocumentSecurity *bool    `json:"documentSecurity,omitempty"`
	Permissions      []string `json:"$permissions,omitempty"`
	// No omitempty: `"attributes": []` means "declared with none", while a
	// missing key reads as "not yet pulled". AddCollection normalises nil to
	// an empty slice so the distinction cannot be lost by accident.
	Attributes []any `json:"attributes"`
	Indexes    []any `json:"indexes"`
	Enabled    *bool `json:"enabled,omitempty"`
}

// Table is a table as the config records it.
type Table struct {
	ID          string   `json:"$id"`
	Permissions []string `json:"$permissions"`
	DatabaseID  string   `json:"databaseId"`
	Name        string   `json:"name"`
	Enabled     *bool    `json:"enabled,omitempty"`
	RowSecurity *bool    `json:"rowSecurity,omitempty"`
	Columns     []any    `json:"columns"`
	Indexes     []any    `json:"indexes"`
}

// Database is a database as the config records it.
type Database struct {
	ID      string `json:"$id"`
	Name    string `json:"name"`
	Enabled *bool  `json:"enabled,omitempty"`
}

// identity decides whether an existing entry is the one being written.
type identity func(*jsonx.Object) bool

func identityByID(id string) identity {
	return func(entry *jsonx.Object) bool {
		return entry.GetString("$id") == id
	}
}

func identityByIDAndDatabase(id, databaseID string) identity {
	return func(entry *jsonx.Object) bool {
		return entry.GetString("$id") == id && entry.GetString("databaseId") == databaseID
	}
}

// upsert writes one entry into a top-level resource array.
func (l *Local) upsert(resource string, value any, matches identity) error {
	entry, err := toObject(value)
	if err != nil {
		return err
	}

	existing, _ := l.Data.Get(resource)

	entries, ok := existing.([]any)
	if !ok {
		// Absent, null, or something that is not an array. The last case is a
		// corrupted config; replacing it loses less than appending to it would
		// and matches the TypeScript's `?? []`.
		entries = nil
	}

	for index, candidate := range entries {
		object, ok := candidate.(*jsonx.Object)
		if !ok || !matches(object) {
			continue
		}

		entries[index] = entry
		l.Data.Set(resource, entries)

		return nil
	}

	l.Data.Set(resource, append(entries, entry))

	return nil
}

// toObject renders a typed resource as an ordered document.
//
// Through JSON so the struct tags decide both the key names and their order --
// declaration order is what ends up in the file, and it is chosen to match the
// order the TypeScript writes.
func toObject(value any) (*jsonx.Object, error) {
	// Already ordered: the pulls build their entry with FilterBySchema and
	// must not have it re-ordered by a round trip through a struct.
	if object, ok := value.(*jsonx.Object); ok {
		return object, nil
	}

	encoded, err := json.Marshal(value)
	if err != nil {
		return nil, err
	}

	object := jsonx.NewObject()
	if err := object.UnmarshalJSON(encoded); err != nil {
		return nil, fmt.Errorf("encoding resource: %w", err)
	}

	return object, nil
}

// SetProject records the linked project.
//
// projectName is written only when non-empty, matching setProject(): linking to
// a project whose name the API did not return must not blank an existing one.
func (l *Local) SetProject(projectID, projectName string) {
	l.Data.Set("projectId", projectID)
	if projectName != "" {
		l.Data.Set("projectName", projectName)
	}
}

// SetOrganizationID records the owning organization.
func (l *Local) SetOrganizationID(organizationID string) {
	l.Data.Set("organizationId", organizationID)
}

// SetEndpoint records the endpoint this project lives on.
func (l *Local) SetEndpoint(endpoint string) {
	l.Data.Set("endpoint", endpoint)
}

// Clear empties the config, and empties every file `includes` points at, so a
// directory previously linked elsewhere cannot keep another project's resources.
// The include files are emptied rather than deleted.
func (l *Local) Clear() error {
	for resource, include := range l.Includes() {
		path, err := l.resolveIncludePath(resource, include)
		if err != nil {
			return err
		}
		if err := writeJSONFile(path, []any{}); err != nil {
			return err
		}
	}

	l.Data = jsonx.NewObject()

	return writeJSONFile(l.Path(), l.Data)
}

// Schema key orders, used by `pull` to shape an API response into the config.
// The emitted order is the schema's, not the response's -- a different rule from
// the add helpers above, which follow the caller. A key the API omits is omitted
// here too, not written as null.
var (
	// BucketKeys orders a pulled bucket.
	BucketKeys = []string{
		"$id", "$permissions", "fileSecurity", "name", "enabled",
		"maximumFileSize", "allowedFileExtensions", "compression",
		"encryption", "antivirus",
	}
	// TeamKeys orders a pulled team.
	TeamKeys = []string{"$id", "name"}
	// TopicKeys orders a pulled messaging topic.
	TopicKeys = []string{"$id", "name", "subscribe"}
	// WebhookKeys orders a pulled webhook.
	WebhookKeys = []string{"$id", "name", "url", "events", "enabled", "tls"}
)

// FilterBySchema keeps the named keys, in the order given.
func FilterBySchema(source *jsonx.Object, keys []string) *jsonx.Object {
	filtered := jsonx.NewObject()
	for _, key := range keys {
		if value, ok := source.Get(key); ok {
			filtered.Set(key, value)
		}
	}

	return filtered
}

// ReplaceResource swaps a whole top-level array.
//
// `pull` replaces rather than merges: the remote is the source of truth, and a
// resource deleted remotely has to disappear locally. The command confirms that
// with the user first.
func (l *Local) ReplaceResource(resource string, entries []*jsonx.Object) {
	values := make([]any, 0, len(entries))
	for _, entry := range entries {
		values = append(values, entry)
	}

	l.Data.Set(resource, values)
}

// ResourceEntries reads a top-level array as objects.
func (l *Local) ResourceEntries(resource string) []*jsonx.Object {
	return l.Data.GetObjects(resource)
}

// Schema key orders for the database resources.
var (
	// DatabaseKeys orders a pulled database or tablesDB entry.
	DatabaseKeys = []string{"$id", "name", "enabled"}
	// TableKeys orders a pulled table.
	TableKeys = []string{
		"$id", "$permissions", "databaseId", "name", "enabled", "rowSecurity",
		"columns", "indexes",
	}
	// ColumnKeys orders a pulled column.
	ColumnKeys = []string{
		"key", "type", "required", "array", "size", "default", "min", "max",
		"format", "elements", "relatedTable", "relationType", "twoWay",
		"twoWayKey", "onDelete", "side", "columns", "orders", "encrypt",
		"previousKey",
	}
	// IndexTableKeys orders a pulled table index.
	IndexTableKeys = []string{"key", "type", "status", "columns", "orders"}
	// CollectionKeys orders a pulled collection.
	CollectionKeys = []string{
		"$id", "$permissions", "databaseId", "name", "enabled",
		"documentSecurity", "attributes", "indexes",
	}
	// AttributeKeys orders a pulled attribute.
	//
	// Note both relatedCollection AND relatedTable: the collection schema
	// accepts either, and dropping one would lose a relationship on pull.
	AttributeKeys = []string{
		"key", "type", "required", "array", "size", "default", "min", "max",
		"format", "elements", "relatedCollection", "relatedTable",
		"relationType", "twoWay", "twoWayKey", "onDelete", "side",
		"attributes", "orders", "encrypt", "previousKey",
	}
	// IndexKeys orders a pulled collection index.
	IndexKeys = []string{"key", "type", "status", "attributes", "orders"}
)

// UpsertByID adds or replaces an entry in a resource array, keyed on $id.
//
// Used by the pulls that write per-resource entries rather than replacing the
// whole array -- `pull function` may be given a subset, and the entries it does
// not touch have to survive.
func (l *Local) UpsertByID(resource string, entry *jsonx.Object) {
	_ = l.upsert(resource, entry, identityByID(entry.GetString("$id")))
}

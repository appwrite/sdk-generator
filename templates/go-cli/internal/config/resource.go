package config

import (
	"encoding/json"
	"fmt"

	"github.com/appwrite/appwrite-cli-go/internal/jsonx"
)

// Ports the addX helpers on Local (templates/cli/lib/config.ts:909+).
//
// Each adds or replaces one entry in a top-level resource array. Two properties
// matter and both come from invariant 2 -- this file lives in the user's
// repository and is code-reviewed:
//
//   - nothing outside the touched array may change, so the document stays an
//     ordered jsonx.Object throughout rather than being decoded and re-encoded
//   - an entry is REPLACED in place when its identity already exists, not
//     appended, so re-running `init` twice does not duplicate a resource
//
// FIELD ORDER IS PART OF THE OUTPUT. whitelistKeys() iterates the CALLER's keys,
// so the TypeScript's key order is per-call-site rather than per-schema; a Go
// struct has one order for every caller. Each struct below is ordered to match
// its `init` call site, which today is the only caller of each add helper.
// When `pull` starts writing these, compare against the TypeScript again --
// if it uses a different order there, one of the two paths has to give.

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

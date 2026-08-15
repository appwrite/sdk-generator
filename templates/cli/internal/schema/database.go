package schema

import (
	"fmt"
	"io"
	"net/url"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
)

// `push table` reconciles the DATABASES before the tables, because a table
// cannot be pushed into a database that does not exist and a database deleted
// locally has to take its tables with it. Deleting a database is the one
// destructive step here, which is why it is warned about separately and applied
// first: if it fails, nothing else has run.

// DatabaseSyncResult reports what the database pass did.
type DatabaseSyncResult struct {
	// Applied is true when any change was made.
	Applied bool
	// ResyncNeeded is true when a database was DELETED, which orphans the
	// tables the config still lists under it. The caller prunes them.
	ResyncNeeded bool
}

// TablesDBPath is the route the tablesDB resource lives at.
const TablesDBPath = "/tablesdb"

// SyncTablesDBs reconciles the tablesDB entries in the config against the
// project.
func SyncTablesDBs(
	api *client.Client,
	out io.Writer,
	prompter prompt.Prompter,
	local *config.Local,
) (DatabaseSyncResult, error) {
	output.Log(out, "Checking for tablesDB changes ...")

	localDatabases := local.ResourceEntries("tablesDB")

	remoteRows, err := api.List(TablesDBPath, "databases", nil)
	if err != nil {
		return DatabaseSyncResult{}, err
	}

	// The TypeScript guards each remote row with a type predicate requiring a
	// string $id, a string name and a BOOLEAN enabled, and silently drops the
	// rest. Reproduced: a row the guard rejects is invisible to the diff, so it
	// is neither updated nor deleted.
	remoteDatabases := make([]*jsonx.Object, 0, len(remoteRows))
	for _, row := range remoteRows {
		if !isTablesDBResource(row) {
			continue
		}
		remoteDatabases = append(remoteDatabases, row)
	}

	if len(localDatabases) == 0 && len(remoteDatabases) == 0 {
		return DatabaseSyncResult{}, nil
	}

	var (
		rows      [][]string
		toCreate  []*jsonx.Object
		toUpdate  []*jsonx.Object
		toDelete  []*jsonx.Object
		headers   = []string{"id", "action", "key", "remote", "local"}
		findLocal = func(id string) *jsonx.Object { return byID(localDatabases, id) }
	)

	for _, remote := range remoteDatabases {
		id := remote.GetString("$id")
		if findLocal(id) != nil {
			continue
		}
		toDelete = append(toDelete, remote)
		rows = append(rows, []string{
			id, "deleting", "Database", remote.GetString("name"), "(deleted locally)",
		})
	}

	for _, localDatabase := range localDatabases {
		id := localDatabase.GetString("$id")
		remote := byID(remoteDatabases, id)

		if remote == nil {
			toCreate = append(toCreate, localDatabase)
			rows = append(rows, []string{
				id, "creating", "Database", "(does not exist)", localDatabase.GetString("name"),
			})

			continue
		}

		changed := false
		if remote.GetString("name") != localDatabase.GetString("name") {
			changed = true
			rows = append(rows, []string{
				id, "updating", "Name",
				remote.GetString("name"), localDatabase.GetString("name"),
			})
		}

		remoteEnabled := field(remote, "enabled")
		localEnabled := field(localDatabase, "enabled")
		if jsString(remoteEnabled) != jsString(localEnabled) {
			changed = true
			rows = append(rows, []string{
				id, "updating", "Enabled", jsString(remoteEnabled), jsString(localEnabled),
			})
		}

		if changed {
			toUpdate = append(toUpdate, localDatabase)
		}
	}

	if len(rows) == 0 {
		return DatabaseSyncResult{}, nil
	}

	output.Log(out, "Found changes in tablesDB resource:")
	fmt.Fprintf(out, "\n%s\n\n", output.RenderTable(headers, rows))

	if len(toDelete) > 0 {
		printBanner(out, "WARNING: Database deletion will also delete all related tables")
	}

	confirmed, err := prompter.Confirm(prompt.Question{
		Message: "Would you like to apply these changes?",
		Default: false,
		Flag:    "--force",
	})
	if err != nil {
		return DatabaseSyncResult{}, err
	}
	if !confirmed {
		output.Warn(out, "Skipping push action. Changes were not applied.")

		return DatabaseSyncResult{}, nil
	}

	// Deletions first: they are the step that can leave the config describing
	// tables that no longer have a home, and the caller needs to know before
	// anything else has been written.
	resyncNeeded := false
	for _, database := range toDelete {
		name, id := database.GetString("name"), database.GetString("$id")
		output.Log(out, "Deleting database %s ( %s ) ...", name, id)
		if err := api.Call("DELETE", TablesDBPath+"/"+url.PathEscape(id), nil, nil); err != nil {
			output.Failure(out, "Failed to delete database %s ( %s ): %s", name, id, err)

			return DatabaseSyncResult{}, fmt.Errorf(
				"database sync failed during deletion of %s, some changes may have been applied", id)
		}
		output.Success(out, "Deleted %s ( %s )", name, id)
		resyncNeeded = true
	}

	for _, database := range toCreate {
		name, id := database.GetString("name"), database.GetString("$id")
		output.Log(out, "Creating database %s ( %s ) ...", name, id)

		body := jsonx.NewObject()
		body.Set("databaseId", id)
		body.Set("name", name)
		if enabled, present := database.Get("enabled"); present {
			body.Set("enabled", enabled)
		}

		if err := api.Call("POST", TablesDBPath, body, nil); err != nil {
			output.Failure(out, "Failed to create database %s ( %s ): %s", name, id, err)

			return DatabaseSyncResult{}, fmt.Errorf(
				"database sync failed during creation of %s, some changes may have been applied", id)
		}
		output.Success(out, "Created %s ( %s )", name, id)
	}

	for _, database := range toUpdate {
		name, id := database.GetString("name"), database.GetString("$id")
		output.Log(out, "Updating database %s ( %s ) ...", name, id)

		body := jsonx.NewObject()
		body.Set("name", name)
		if enabled, present := database.Get("enabled"); present {
			body.Set("enabled", enabled)
		}

		if err := api.Call("PUT", TablesDBPath+"/"+url.PathEscape(id), body, nil); err != nil {
			output.Failure(out, "Failed to update database %s ( %s ): %s", name, id, err)

			return DatabaseSyncResult{}, fmt.Errorf(
				"database sync failed during update of %s, some changes may have been applied", id)
		}
		output.Success(out, "Updated %s ( %s )", name, id)
	}

	return DatabaseSyncResult{Applied: true, ResyncNeeded: resyncNeeded}, nil
}

// isTablesDBResource ports the type predicate at database-sync.ts:20.
func isTablesDBResource(row *jsonx.Object) bool {
	if _, ok := mustString(row, "$id"); !ok {
		return false
	}
	if _, ok := mustString(row, "name"); !ok {
		return false
	}
	value, present := row.Get("enabled")
	if !present {
		return false
	}
	_, isBool := value.(bool)

	return isBool
}

// mustString reads a key that has to be a string.
func mustString(row *jsonx.Object, key string) (string, bool) {
	value, present := row.Get(key)
	if !present {
		return "", false
	}
	text, ok := value.(string)

	return text, ok
}

// byID finds an entry by its $id.
func byID(entries []*jsonx.Object, id string) *jsonx.Object {
	for _, entry := range entries {
		if entry.GetString("$id") == id {
			return entry
		}
	}

	return nil
}

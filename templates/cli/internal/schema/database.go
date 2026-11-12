package schema

import (
	"fmt"
	"io"
	"net/url"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
)

// `push table` reconciles the DATABASES before the tables, because a table
// cannot be pushed into a database that does not exist. A database the config
// does not mention is unmanaged: the plan reports it, and only the caller
// deletes it, after everything else has been pushed.

// DatabasePlan is what the database pass would change, read before anything is
// written.
type DatabasePlan struct {
	Create []*jsonx.Object
	Update []*jsonx.Object
	// Unmanaged exist in the project but neither in the config's tablesDB
	// array nor as the database of a configured table.
	Unmanaged []*jsonx.Object

	rows [][]string
}

// TablesDBPath is the route the tablesDB resource lives at.
const TablesDBPath = "/tablesdb"

// Empty reports whether the plan creates or updates nothing.
func (p DatabasePlan) Empty() bool { return len(p.rows) == 0 }

// Print renders the creations and updates.
func (p DatabasePlan) Print(out io.Writer) {
	if p.Empty() {
		return
	}

	output.Log(out, "Found changes in tablesDB resource:")
	fmt.Fprintf(out, "\n%s\n\n", output.RenderTable(
		[]string{"id", "action", "key", "remote", "local"}, p.rows))
}

// PlanTablesDBs diffs the tablesDB entries in the config against the project.
func PlanTablesDBs(api *client.Client, out io.Writer, local *config.Local) (DatabasePlan, error) {
	output.Log(out, "Checking for tablesDB changes ...")

	localDatabases := local.ResourceEntries("tablesDB")

	remoteRows, err := api.List(TablesDBPath, "databases", nil)
	if err != nil {
		return DatabasePlan{}, err
	}

	// Each remote row is guarded by a type check requiring a string $id, a
	// string name and a BOOLEAN enabled, and the rest are silently dropped: a
	// row the guard rejects is invisible to the diff, so it is neither updated
	// nor deleted.
	remoteDatabases := make([]*jsonx.Object, 0, len(remoteRows))
	for _, row := range remoteRows {
		if !isTablesDBResource(row) {
			continue
		}
		remoteDatabases = append(remoteDatabases, row)
	}

	referenced := map[string]bool{}
	for _, table := range local.ResourceEntries("tables") {
		referenced[table.GetString("databaseId")] = true
	}

	var plan DatabasePlan

	for _, remote := range remoteDatabases {
		id := remote.GetString("$id")
		if byID(localDatabases, id) == nil && !referenced[id] {
			plan.Unmanaged = append(plan.Unmanaged, remote)
		}
	}

	for _, localDatabase := range localDatabases {
		id := localDatabase.GetString("$id")
		remote := byID(remoteDatabases, id)

		if remote == nil {
			plan.Create = append(plan.Create, localDatabase)
			plan.rows = append(plan.rows, []string{
				id, "creating", "Database", "(does not exist)", localDatabase.GetString("name"),
			})

			continue
		}

		changed := false
		if remote.GetString("name") != localDatabase.GetString("name") {
			changed = true
			plan.rows = append(plan.rows, []string{
				id, "updating", "Name",
				remote.GetString("name"), localDatabase.GetString("name"),
			})
		}

		remoteEnabled := field(remote, "enabled")
		localEnabled := field(localDatabase, "enabled")
		if jsString(remoteEnabled) != jsString(localEnabled) {
			changed = true
			plan.rows = append(plan.rows, []string{
				id, "updating", "Enabled", jsString(remoteEnabled), jsString(localEnabled),
			})
		}

		if changed {
			plan.Update = append(plan.Update, localDatabase)
		}
	}

	return plan, nil
}

// ApplyTablesDBs creates and updates the databases in an approved plan.
func ApplyTablesDBs(api *client.Client, out io.Writer, plan DatabasePlan) error {
	for _, database := range plan.Create {
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

			return fmt.Errorf(
				"database sync failed during creation of %s, some changes may have been applied", id)
		}
		output.Success(out, "Created %s ( %s )", name, id)
	}

	for _, database := range plan.Update {
		name, id := database.GetString("name"), database.GetString("$id")
		output.Log(out, "Updating database %s ( %s ) ...", name, id)

		body := jsonx.NewObject()
		body.Set("name", name)
		if enabled, present := database.Get("enabled"); present {
			body.Set("enabled", enabled)
		}

		if err := api.Call("PUT", TablesDBPath+"/"+url.PathEscape(id), body, nil); err != nil {
			output.Failure(out, "Failed to update database %s ( %s ): %s", name, id, err)

			return fmt.Errorf(
				"database sync failed during update of %s, some changes may have been applied", id)
		}
		output.Success(out, "Updated %s ( %s )", name, id)
	}

	return nil
}

// isTablesDBResource reports whether a remote row is shaped like a database.
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

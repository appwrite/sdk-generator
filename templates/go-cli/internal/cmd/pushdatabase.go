package cmd

import (
	"fmt"
	"net/url"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/schema"
	"github.com/spf13/cobra"
)

// `push table` and `push collection` do the same four things in the same order,
// and the order is the design: databases first, so a table has somewhere to
// live; then the tables; then the columns, which is internal/schema's job; then
// the indexes, last, because an index names columns.
//
// `push collection` is the deprecated half of the pair and creates a missing
// database inline instead.

// pushDatabaseResource parameterises the two commands over their route and key
// names. The same distinction pull draws (see pulldatabase.go): tablesdb/tables/
// columns against databases/collections/attributes.
type pushDatabaseResource struct {
	resourceIdentity
	// DatabaseConfigKey is the config array the containers are read from.
	DatabaseConfigKey string
	// ChildPath lists a database's children.
	ChildPath string
	// ChildKeys limits the approval diff to the fields the config owns.
	ChildKeys []string
	// MembersKey is `columns` for a table and `attributes` for a collection --
	// both the config array and the field an index references them by.
	MembersKey string
}

// Named rather than a slice indexed by position: nothing iterates the pair, so
// a table of two was three files agreeing on which row came first.
var (
	pushTable = pushDatabaseResource{
		resourceIdentity:  tableIdentity,
		DatabaseConfigKey: "tablesDB",
		ChildPath:         "/tables",
		ChildKeys:         config.TableKeys,
		MembersKey:        "columns",
	}

	pushCollection = pushDatabaseResource{
		resourceIdentity:  collectionIdentity,
		DatabaseConfigKey: "databases",
		ChildPath:         "/collections",
		ChildKeys:         config.CollectionKeys,
		MembersKey:        "attributes",
	}
)

func newPushTableCommand() *cobra.Command {
	resource := pushTable
	command := &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short:   "Push tables in the current project.",
		Args:    everyResourceArgument(),
		RunE: func(command *cobra.Command, arguments []string) error {
			return runPushTable(command, resource)
		},
	}
	addAttemptsFlag(command)

	return command
}

func newPushCollectionCommand() *cobra.Command {
	resource := pushCollection
	command := &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short: "Push collections in the current project. " +
			"(deprecated, please use 'push tables' instead)",
		Args: everyResourceArgument(),
		RunE: func(command *cobra.Command, arguments []string) error {
			return runPushCollection(command, resource)
		},
	}
	addAttemptsFlag(command)

	return command
}

// addAttemptsFlag registers --attempts.
//
// It caps how long the CLI waits for asynchronous schema changes. The default
// lives in internal/schema, which also scales it for a large collection.
func addAttemptsFlag(command *cobra.Command) {
	command.Flags().IntP("attempts", "a", 0,
		"Max number of attempts before timing out. default: 30.")
}

// runPushTable ports pushTable (push.ts:3766).
func runPushTable(command *cobra.Command, resource pushDatabaseResource) error {
	out := command.OutOrStdout()

	context, err := newPushContext()
	if err != nil {
		return err
	}

	sync, err := schema.SyncTablesDBs(context.api, out, context.prompter, context.local)
	if err != nil {
		return err
	}
	if sync.ResyncNeeded {
		if err := context.resyncTables(command); err != nil {
			return err
		}
	}

	if err := context.deleteRemovedTables(command, resource); err != nil {
		return err
	}

	tables, err := context.selectDatabaseResources(resource)
	if err != nil {
		return err
	}
	if len(tables) == 0 {
		output.Log(out, "No %s found.", resource.Label)
		output.Hint(out, "%s", resource.syncHint())

		return nil
	}

	approved, err := context.approveChanges(command, approvalRequest{
		Resources: tables,
		Fetch: func(local *jsonx.Object) (*jsonx.Object, error) {
			return context.getChild(resource, local)
		},
		Keys:     resource.ChildKeys,
		SkipKeys: []string{"columns", "indexes"},
		Plural:   resource.Label,
	})
	if err != nil || !approved {
		return err
	}

	output.Log(out, "Pushing tables ...")

	// Looked up rather than read directly: `push all` fans out to this
	// function through its own command, which does not declare --attempts, and
	// GetInt on an undeclared flag is an error rather than a zero.
	attempts := 0
	if flag := command.Flags().Lookup("attempts"); flag != nil {
		parsed, err := command.Flags().GetInt("attempts")
		if err != nil {
			return err
		}
		attempts = parsed
	}

	pushed, err := context.pushDatabaseChildren(command, resource, tables, attempts)
	if err != nil {
		return err
	}

	// No Failed count: pushDatabaseChildren returns an error rather than
	// tallying failures, so the "no %s were pushed" failure branch is not
	// reachable from here.
	pushTally{Pushed: pushed}.report(out, resource.Label)

	return nil
}

// runPushCollection ports pushCollection (push.ts:3935).
func runPushCollection(command *cobra.Command, resource pushDatabaseResource) error {
	out := command.OutOrStdout()

	output.Warn(out, "%s push collection has been deprecated. Please consider using "+
		"'%s push tables' instead", app.ExecutableName, app.ExecutableName)

	context, err := newPushContext()
	if err != nil {
		return err
	}

	collections, err := context.selectDatabaseResources(resource)
	if err != nil {
		return err
	}
	if len(collections) == 0 {
		output.Log(out, "No %s found.", resource.Label)
		output.Hint(out, "%s", resource.syncHint())

		return nil
	}

	approved, err := context.approveChanges(command, approvalRequest{
		Resources: collections,
		Fetch: func(local *jsonx.Object) (*jsonx.Object, error) {
			return context.getChild(resource, local)
		},
		Keys:     resource.ChildKeys,
		SkipKeys: []string{"attributes", "indexes"},
		Plural:   resource.Label,
	})
	if err != nil || !approved {
		return err
	}

	output.Log(out, "Pushing collections ...")

	if err := context.pushCollectionDatabases(command, resource, collections); err != nil {
		return err
	}

	// --attempts is declared on `push collection` and IGNORED, matching the
	// TypeScript: the command registers the flag (push.ts:4341) but
	// pushCollections builds its pool with the default (push.ts:3022). Passing
	// it through would be a fix, and a fix belongs in the TypeScript first.
	pushed, err := context.pushDatabaseChildren(command, resource, collections, 0)
	if err != nil {
		return err
	}

	pushTally{Pushed: pushed}.report(out, resource.Label)

	return nil
}

// resyncTables drops config entries whose database no longer exists.
//
// A deleted database takes its tables with it, so the config's `tables` array
// would otherwise keep pushing into a database that is gone.
func (c *pushContext) resyncTables(command *cobra.Command) error {
	out := command.OutOrStdout()
	output.Log(out, "Resyncing configuration due to tables deletions ...")

	remote, err := c.page(schema.TablesDBPath, "databases")
	if err != nil {
		return err
	}

	live := map[string]bool{}
	for _, database := range remote {
		live[database.GetString("$id")] = true
	}

	keep := func(resource, key string) {
		entries := c.local.ResourceEntries(resource)
		filtered := make([]*jsonx.Object, 0, len(entries))
		for _, entry := range entries {
			if live[entry.GetString(key)] {
				filtered = append(filtered, entry)
			}
		}
		c.local.ReplaceResource(resource, filtered)
	}

	keep("tables", "databaseId")
	keep("tablesDB", "$id")

	if err := c.local.Write(); err != nil {
		return err
	}

	output.Success(out, "Configuration resynced successfully.")

	return nil
}

// deleteRemovedTables offers to delete tables that exist remotely but not in
// the config.
//
// A failure to
// delete one table is reported and the rest continue -- matching the TypeScript,
// which catches per table.
func (c *pushContext) deleteRemovedTables(
	command *cobra.Command,
	resource pushDatabaseResource,
) error {
	out := command.OutOrStdout()
	output.Log(out, "Checking for deleted tables ...")

	localTables := c.local.ResourceEntries(resource.ConfigKey)

	type removal struct {
		databaseID   string
		databaseName string
		id           string
		name         string
	}
	var removals []removal

	for _, database := range c.local.ResourceEntries(resource.DatabaseConfigKey) {
		databaseID := database.GetString("$id")

		remoteTables, err := c.page(
			resource.Path+"/"+url.PathEscape(databaseID)+resource.ChildPath, "tables")
		if err != nil {
			// Swallowed deliberately: the TypeScript catches and skips, so a
			// database that has just been deleted elsewhere does not abort the
			// push.
			continue
		}

		for _, remote := range remoteTables {
			id := remote.GetString("$id")
			found := false
			for _, local := range localTables {
				if local.GetString("$id") == id &&
					local.GetString("databaseId") == databaseID {
					found = true

					break
				}
			}
			if found {
				continue
			}

			removals = append(removals, removal{
				databaseID: databaseID, databaseName: database.GetString("name"),
				id: id, name: remote.GetString("name"),
			})
		}
	}

	if len(removals) == 0 {
		return nil
	}

	output.Log(out, "Found tables that exist remotely but not locally:")
	rows := make([]change, 0, len(removals))
	for _, entry := range removals {
		rows = append(rows, change{
			ID: entry.id, Key: "Table", Remote: entry.name, Local: "(deleted locally)",
		})
	}
	printChanges(command, rows)

	confirmed, err := c.prompter.Confirm(prompt.Question{
		Message: "Would you like to apply these changes?",
		Default: true,
		Flag:    "--force",
	})
	if err != nil {
		return err
	}
	if !confirmed {
		return nil
	}

	for _, entry := range removals {
		output.Log(out, "Deleting table %s ( %s ) from database %s ...",
			entry.name, entry.id, entry.databaseName)

		path := resource.Path + "/" + url.PathEscape(entry.databaseID) +
			resource.ChildPath + "/" + url.PathEscape(entry.id)
		if err := c.api.Call("DELETE", path, nil, nil); err != nil {
			output.Failure(out, "Failed to delete table %s ( %s ): %s",
				entry.name, entry.id, err)

			continue
		}
		output.Success(out, "Deleted %s ( %s )", entry.name, entry.id)
	}

	return nil
}

// pushCollectionDatabases creates or renames the databases the collections need.
//
// Ports the database pass in pushCollections (push.ts:3033). `push collection`
// has no equivalent of SyncTablesDBs -- it never deletes a database, only
// creates one that is missing.
func (c *pushContext) pushCollectionDatabases(
	command *cobra.Command,
	resource pushDatabaseResource,
	collections []*jsonx.Object,
) error {
	out := command.OutOrStdout()

	seen := map[string]bool{}
	for _, collection := range collections {
		databaseID := collection.GetString("databaseId")
		if databaseID == "" || seen[databaseID] {
			continue
		}
		seen[databaseID] = true

		// The database's name comes from the config's own databases array,
		// falling back to the id. Ports the databaseName the TypeScript
		// attaches to each collection before calling pushCollections.
		name := databaseID
		if entry := c.localDatabase(resource, databaseID); entry != nil {
			if value := entry.GetString("name"); value != "" {
				name = value
			}
		}

		path := resource.Path + "/" + url.PathEscape(databaseID)

		var remote jsonx.Object
		err := c.api.Call("GET", path, nil, &remote)
		switch {
		case err == nil:
			if remote.GetString("name") == name {
				continue
			}

			body := jsonx.NewObject()
			body.Set("name", name)
			if err := c.api.Call("PUT", path, body, nil); err != nil {
				return err
			}
			output.Success(out, "Updated %s ( %s ) name", name, databaseID)
		case isNotFound(err):
			output.Log(out, "Database %s not found. Creating it now ...", databaseID)

			body := jsonx.NewObject()
			body.Set("databaseId", databaseID)
			body.Set("name", name)
			if err := c.api.Call("POST", resource.Path, body, nil); err != nil {
				return err
			}
		default:
			return err
		}
	}

	return nil
}

// localDatabase finds a database entry in the config.
func (c *pushContext) localDatabase(
	resource pushDatabaseResource,
	databaseID string,
) *jsonx.Object {
	for _, entry := range c.local.ResourceEntries(resource.DatabaseConfigKey) {
		if entry.GetString("$id") == databaseID {
			return entry
		}
	}

	return nil
}

// pushDatabaseChildren creates or updates the tables and collections, then
// reconciles their schema. A newly created collection carries its attributes and
// indexes in the create body and is complete; a new table does not, and has its
// columns pushed afterwards.
func (c *pushContext) pushDatabaseChildren(
	command *cobra.Command,
	resource pushDatabaseResource,
	entries []*jsonx.Object,
	attempts int,
) (int, error) {
	out := command.OutOrStdout()

	poller := schema.NewPoller(c.api, out, attempts)
	reconciler := &schema.Reconciler{
		API:      c.api,
		Out:      out,
		Prompter: c.prompter,
		Poller:   poller,
		Force:    app.Flags().Force,
	}

	// state carries what the create-or-update pass learned into the schema
	// pass. The TypeScript hangs the same three fields off the config object
	// itself; a struct keeps the config immutable.
	type state struct {
		local        *jsonx.Object
		remote       *jsonx.Object
		existed      bool
		newlyCreated bool
	}

	states := make([]*state, 0, len(entries))
	changed := map[string]bool{}

	for _, entry := range entries {
		current := &state{local: entry}
		states = append(states, current)

		remote, err := c.getChild(resource, entry)
		switch {
		case err == nil:
			current.remote = remote
			current.existed = true

			updated, err := c.updateChild(command, resource, entry, remote)
			if err != nil {
				return 0, err
			}
			if updated {
				changed[entry.GetString("$id")] = true
			}
		case isNotFound(err):
			output.Log(out, "%s %s does not exist in the project. Creating ... ",
				strings.ToUpper(resource.Singular[:1])+resource.Singular[1:],
				entry.GetString("name"))

			carriedSchema, err := c.createChild(command, resource, entry)
			if err != nil {
				return 0, err
			}
			// A collection created with its attributes and indexes needs no
			// schema pass; a table does, because createTable takes neither.
			current.newlyCreated = carriedSchema
			if !carriedSchema {
				changed[entry.GetString("$id")] = true
			}
		default:
			return 0, err
		}
	}

	pushed := 0

	// Serial from here. Two schema changes against the same collection race,
	// and the poll after each phase is what makes the next one legal.
	for _, current := range states {
		entry := current.local
		id := entry.GetString("$id")
		container := schema.Container{
			DatabaseID: entry.GetString("databaseId"),
			ID:         id,
			Name:       entry.GetString("name"),
		}

		if current.newlyCreated {
			pushed++
			output.Success(out, "Successfully pushed %s ( %s )", entry.GetString("name"), id)

			continue
		}

		members := entry.GetObjects(resource.MembersKey)
		indexes := entry.GetObjects("indexes")
		hadChanges := false

		if current.existed {
			membersResult, err := reconciler.Reconcile(
				current.remote.GetObjects(resource.MembersKey), members, container, false)
			if err != nil {
				return pushed, err
			}

			// The server rewrites an index's member references when a column is
			// renamed. The remote snapshot was read BEFORE the rename, so the
			// same rewrite is mirrored onto it -- otherwise the index pass sees
			// the old name, calls it a difference, and recreates a live index.
			applyRenamesToIndexes(current.remote, resource.MembersKey, membersResult.Renames)

			indexesResult, err := reconciler.Reconcile(
				current.remote.GetObjects("indexes"), indexes, container, true)
			if err != nil {
				return pushed, err
			}

			members = membersResult.Attributes
			indexes = indexesResult.Attributes
			hadChanges = membersResult.HasChanges || indexesResult.HasChanges

			if !hadChanges && len(members) == 0 && len(indexes) == 0 {
				if !changed[id] {
					output.Log(out, "No changes detected for %s %s. Skipping.",
						resource.Singular, entry.GetString("name"))
				}

				continue
			}
		} else if resource.Singular == "collection" {
			// pushCollections skips anything it neither found nor created.
			continue
		}

		output.Log(out, "Pushing %s %s ( %s - %s ) attributes",
			resource.Singular, entry.GetString("name"),
			entry.GetString("databaseId"), id)

		if err := reconciler.CreateAttributes(members, container, resource.MembersKey); err != nil {
			return pushed, err
		}
		if err := reconciler.CreateIndexes(indexes, container); err != nil {
			return pushed, err
		}

		changed[id] = true
		pushed++
		output.Success(out, "Successfully pushed %s ( %s )", entry.GetString("name"), id)
	}

	// pushTables counts every table it TOUCHED, including one whose only change
	// was its name; pushCollections counts only the ones whose schema it pushed.
	if resource.Singular == "table" {
		return len(changed), nil
	}

	return pushed, nil
}

// getChild reads one table or collection.
func (c *pushContext) getChild(
	resource pushDatabaseResource,
	entry *jsonx.Object,
) (*jsonx.Object, error) {
	path := resource.Path + "/" + url.PathEscape(entry.GetString("databaseId")) +
		resource.ChildPath + "/" + url.PathEscape(entry.GetString("$id"))

	var remote jsonx.Object
	if err := c.api.Call("GET", path, nil, &remote); err != nil {
		return nil, err
	}

	return &remote, nil
}

// updateChild sends the table or collection update when a field differs, and
// reports whether it did.
func (c *pushContext) updateChild(
	command *cobra.Command,
	resource pushDatabaseResource,
	local, remote *jsonx.Object,
) (bool, error) {
	out := command.OutOrStdout()
	path := resource.Path + "/" + url.PathEscape(local.GetString("databaseId")) +
		resource.ChildPath + "/" + url.PathEscape(local.GetString("$id"))

	if resource.Singular == "collection" {
		// pushCollections compares the NAME only, and sends only the name.
		// Everything else on a collection -- permissions, documentSecurity --
		// is left to the deprecated command's callers.
		if remote.GetString("name") == local.GetString("name") {
			return false, nil
		}

		body := jsonx.NewObject()
		body.Set("name", local.GetString("name"))
		if err := c.api.Call("PUT", path, body, nil); err != nil {
			return false, err
		}
		output.Success(out, "Updated %s ( %s ) name",
			local.GetString("name"), local.GetString("$id"))

		return true, nil
	}

	var differing []string
	if remote.GetString("name") != local.GetString("name") {
		differing = append(differing, "name")
	}
	if !sameValue(remote, local, "rowSecurity") {
		differing = append(differing, "rowSecurity")
	}
	if !sameValue(remote, local, "enabled") {
		differing = append(differing, "enabled")
	}
	if !equalValues(valueOf(remote, "$permissions"), valueOf(local, "$permissions")) {
		differing = append(differing, "permissions")
	}

	if len(differing) == 0 {
		return false, nil
	}

	body := jsonx.NewObject()
	body.Set("name", local.GetString("name"))
	if value, present := local.Get("rowSecurity"); present {
		body.Set("rowSecurity", value)
	}
	if value, present := local.Get("$permissions"); present {
		body.Set("permissions", value)
	}
	// `enabled` is detected as a difference above and then NOT sent -- the
	// TypeScript's updateTable call omits it (push.ts:2899). Reproduced rather
	// than fixed: sending it here would be a behaviour change on a live
	// project, and the fix belongs in the TypeScript first.

	if err := c.api.Call("PUT", path, body, nil); err != nil {
		return false, err
	}

	output.Success(out, "Updated %s ( %s ) - %s",
		local.GetString("name"), local.GetString("$id"), strings.Join(differing, ", "))

	return true, nil
}

// createChild creates a missing table or collection.
//
// The boolean reports whether the create also carried the schema, which only a
// collection's does. A table's columns are pushed separately afterwards.
func (c *pushContext) createChild(
	command *cobra.Command,
	resource pushDatabaseResource,
	entry *jsonx.Object,
) (bool, error) {
	path := resource.Path + "/" + url.PathEscape(entry.GetString("databaseId")) +
		resource.ChildPath

	body := jsonx.NewObject()

	if resource.Singular == "collection" {
		body.Set("collectionId", entry.GetString("$id"))
		body.Set("name", entry.GetString("name"))
		if value, present := entry.Get("documentSecurity"); present {
			body.Set("documentSecurity", value)
		}
		if value, present := entry.Get("$permissions"); present {
			body.Set("permissions", value)
		}
		if value, present := entry.Get("attributes"); present {
			body.Set("attributes", value)
		}
		if value, present := entry.Get("indexes"); present {
			body.Set("indexes", value)
		}

		if err := c.api.Call("POST", path, body, nil); err != nil {
			return false, err
		}

		return true, nil
	}

	body.Set("tableId", entry.GetString("$id"))
	body.Set("name", entry.GetString("name"))
	if value, present := entry.Get("rowSecurity"); present {
		body.Set("rowSecurity", value)
	}
	if value, present := entry.Get("$permissions"); present {
		body.Set("permissions", value)
	}

	if err := c.api.Call("POST", path, body, nil); err != nil {
		return false, err
	}

	output.Success(command.OutOrStdout(), "Created %s ( %s )",
		entry.GetString("name"), entry.GetString("$id"))

	return false, nil
}

// selectDatabaseResources picks which tables or collections to push.
//
// Not pushContext.selectResources: a table's identity is the database id AND
// its own id, so two tables named the same in different databases stay
// distinguishable in the prompt and in the selection.
func (c *pushContext) selectDatabaseResources(
	resource pushDatabaseResource,
) ([]*jsonx.Object, error) {
	entries := c.local.ResourceEntries(resource.ConfigKey)
	if app.Flags().All {
		return entries, nil
	}

	identity := func(entry *jsonx.Object) string {
		return entry.GetString("databaseId") + "|" + entry.GetString("$id")
	}

	options := make([]prompt.Option, 0, len(entries))
	for _, entry := range entries {
		options = append(options, prompt.Option{
			Label: fmt.Sprintf("%s (%s - %s)", entry.GetString("name"),
				entry.GetString("databaseId"), entry.GetString("$id")),
			Value: identity(entry),
		})
	}

	chosen, err := c.prompter.MultiChoice(prompt.MultiChoice{
		Message: "Which " + resource.Label + " would you like to push?",
		Options: options,
		Filter:  true,
		Flag:    "--all",
	})
	if err != nil {
		return nil, err
	}

	selected := map[string]bool{}
	for _, value := range chosen {
		selected[value] = true
	}

	filtered := make([]*jsonx.Object, 0, len(chosen))
	for _, entry := range entries {
		if selected[identity(entry)] {
			filtered = append(filtered, entry)
		}
	}

	return filtered, nil
}

// applyRenamesToIndexes rewrites member references on a stale remote snapshot.
func applyRenamesToIndexes(remote *jsonx.Object, membersKey string, renames []schema.Rename) {
	if len(renames) == 0 || remote == nil {
		return
	}

	replacement := make(map[string]string, len(renames))
	for _, rename := range renames {
		replacement[rename.From] = rename.To
	}

	for _, index := range remote.GetObjects("indexes") {
		value, present := index.Get(membersKey)
		if !present {
			continue
		}
		members, ok := value.([]any)
		if !ok {
			continue
		}

		rewritten := make([]any, 0, len(members))
		for _, member := range members {
			name, ok := member.(string)
			if !ok {
				rewritten = append(rewritten, member)

				continue
			}
			if renamed, found := replacement[name]; found {
				name = renamed
			}
			rewritten = append(rewritten, name)
		}
		index.Set(membersKey, rewritten)
	}
}

// valueOf reads a key, yielding nil when it is absent.
func valueOf(source *jsonx.Object, key string) any {
	value, _ := source.Get(key)

	return value
}

// sameValue compares one field between the remote and the config.
func sameValue(remote, local *jsonx.Object, key string) bool {
	return equalValues(valueOf(remote, key), valueOf(local, key))
}

// page walks a list endpoint through the push client.
func (c *pushContext) page(path, wrapper string) ([]*jsonx.Object, error) {
	return c.api.List(path, wrapper, nil)
}

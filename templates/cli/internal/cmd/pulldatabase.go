//go:build !browser

package cmd

import (
	"net/url"
	"strings"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

// Pulls tables and collections into the local project configuration.
//
// Two levels: list the databases, then list each database's tables. The two
// commands differ only in the route and the key names -- `tablesdb`/`tables`/
// `columns` against `databases`/`collections`/`attributes` -- which is why they
// share one implementation parameterised by a descriptor.

// databaseResource describes one two-level pull.
//
// Path and ConfigKey come from the identity and name the container route and the
// child array; DatabaseConfigKey names the container array, under the same field
// name the push side uses.
type databaseResource struct {
	resourceIdentity
	// ChildPath is appended to a database's path to list its children.
	ChildPath string
	// DatabaseConfigKey is the config array the containers are written to.
	DatabaseConfigKey string
	// ChildKeys, NestedKey and NestedKeys shape a child and its inner arrays.
	ChildKeys []string
	NestedKey string
	// NestedKeys shapes the entries of NestedKey.
	NestedKeys []string
	// IndexKeys shapes the child's `indexes` array.
	IndexKeys []string
	// DatabaseLabel names the container in the success line.
	DatabaseLabel string
}

var databaseResources = []databaseResource{
	{
		resourceIdentity:  tableIdentity,
		ChildPath:         "/tables",
		DatabaseConfigKey: "tablesDB",
		ChildKeys:         config.TableKeys,
		NestedKey:         "columns", NestedKeys: config.ColumnKeys,
		IndexKeys:     config.IndexTableKeys,
		DatabaseLabel: "tableDBs",
	},
	{
		resourceIdentity:  collectionIdentity,
		ChildPath:         "/collections",
		DatabaseConfigKey: "databases",
		ChildKeys:         config.CollectionKeys,
		NestedKey:         "attributes", NestedKeys: config.AttributeKeys,
		IndexKeys:     config.IndexKeys,
		DatabaseLabel: "databases",
	},
}

func newPullDatabaseCommand(resource databaseResource) *cobra.Command {
	return &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short:   "Pull " + resource.Label + " from your Appwrite project",
		RunE: func(command *cobra.Command, args []string) error {
			return runPullDatabase(command, resource)
		},
	}
}

func runPullDatabase(command *cobra.Command, resource databaseResource) error {
	out := command.OutOrStdout()

	context, err := newProjectPull()
	if err != nil {
		return err
	}

	output.Log(out, "Fetching %s ...", resource.Label)

	databases, err := context.page(resource.Path, "databases", nil)
	if err != nil {
		return err
	}

	shapedDatabases := make([]*jsonx.Object, 0, len(databases))
	children := []*jsonx.Object{}

	for _, database := range databases {
		output.Log(out, "Pulling all %s from %s database ...",
			resource.Label, database.GetString("name"))
		shapedDatabases = append(shapedDatabases,
			config.FilterBySchema(database, config.DatabaseKeys))

		rows, err := context.page(
			resource.Path+"/"+url.PathEscape(database.GetString("$id"))+resource.ChildPath,
			resource.ConfigKey, nil)
		if err != nil {
			return err
		}

		for _, row := range rows {
			children = append(children, shapeChild(row, resource))
		}
	}

	// Only the children are checked for removal; a database that disappears
	// takes its children with it, and it is the children that are reported.
	removed := missingLocally(context.local.ResourceEntries(resource.ConfigKey), children)
	if len(removed) > 0 {
		output.Warn(out,
			"The following %s exist locally but not remotely and will be removed: %s",
			resource.Label, strings.Join(removed, ", "))

		confirmed, err := prompt.New(app.Flags().Force).Confirm(prompt.Question{
			Message: "Continue?",
			Default: false,
			Flag:    "--force",
		})
		if err != nil {
			return err
		}
		if !confirmed {
			output.Log(out, "Pull cancelled.")

			return nil
		}
	}

	context.local.ReplaceResource(resource.DatabaseConfigKey, shapedDatabases)
	context.local.ReplaceResource(resource.ConfigKey, children)
	if err := context.local.Write(); err != nil {
		return err
	}

	output.Success(out, "Successfully pulled %d %s from %d %s.",
		len(children), resource.Label, len(shapedDatabases), resource.DatabaseLabel)

	return nil
}

// shapeChild filters a table or collection and its two inner arrays.
//
// The inner arrays are set AFTER the filter, so they keep the position the
// schema gives them but take the filtered value. They are always written, even
// when absent remotely, because `[]` means "declared with none" and a missing
// key reads as "not pulled".
func shapeChild(row *jsonx.Object, resource databaseResource) *jsonx.Object {
	shaped := config.FilterBySchema(row, resource.ChildKeys)

	shaped.Set(resource.NestedKey, filterEach(row, resource.NestedKey, resource.NestedKeys))
	shaped.Set("indexes", filterEach(row, "indexes", resource.IndexKeys))

	return shaped
}

// filterEach shapes every entry of a named array.
func filterEach(row *jsonx.Object, name string, keys []string) []any {
	value, ok := row.Get(name)
	if !ok {
		return []any{}
	}

	items, ok := value.([]any)
	if !ok {
		return []any{}
	}

	filtered := make([]any, 0, len(items))
	for _, item := range items {
		object, ok := item.(*jsonx.Object)
		if !ok {
			continue
		}
		filtered = append(filtered, config.FilterBySchema(object, keys))
	}

	return filtered
}

// page walks one list endpoint.
func (p *projectPull) page(path, wrapper string, queries []string) ([]*jsonx.Object, error) {
	return p.api.List(path, wrapper, queries)
}

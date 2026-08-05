package cmd

import (
	"fmt"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
)

// What a resource IS, as opposed to what pull or push does with it.
//
// The two directions need genuinely different descriptors: a pull shapes a
// response into the config schema, a push builds a request body in the SDK's
// parameter order, and neither field set is a subset of the other. What they
// must not disagree about is the resource's name, its route, and which config
// array holds it -- so that is stated once here and embedded by both.
//
// Before this, `table` carried its route and key names in two tables under
// different field names (DatabaseKey against DatabaseConfigKey, ChildKey
// against ConfigKey), and answering "where do tables live" meant reading both.

// resourceIdentity is the half of a descriptor that pull and push share.
type resourceIdentity struct {
	// Name is the subcommand; Aliases mirror the TypeScript's plural forms.
	Name    string
	Aliases []string
	// Path is the API route that lists the resource.
	Path string
	// ConfigKey is the top-level array in appwrite.config.json. It doubles as
	// the array key in a list response -- the API and the config agree on the
	// plural for every resource here, which is why `pull` can write what it
	// read without renaming it.
	ConfigKey string
	// Label is the plural word used in log lines; Singular is the same word for
	// one resource.
	Label    string
	Singular string
}

// itemPath is the route for one resource.
//
// Every resource's item route is its collection route plus the id. It was four
// identical closures stored as data before, one per simple resource.
func (r resourceIdentity) itemPath(id string) string { return r.Path + "/" + id }

// syncHint is the line shown when a push finds nothing configured.
//
// Seven of the eight resources word this identically -- `push` wrote it out four
// times and `pushdeploy` built it from the same two words this does. A webhook
// is the exception, because it cannot be created by `init`, so its push
// descriptor keeps its own text.
//
// The sentence is user-visible output the two CLIs are compared on, so the
// wording here is transcribed, including "existing one" rather than "ones".
func (r resourceIdentity) syncHint() string {
	return fmt.Sprintf(
		"Use '%s pull %s' to synchronize existing one, "+
			"or use '%s init %s' to create a new one.",
		app.ExecutableName, r.Label, app.ExecutableName, r.Singular)
}

var (
	bucketIdentity = resourceIdentity{
		Name: "bucket", Aliases: []string{"buckets"},
		Path: "/storage/buckets", ConfigKey: "buckets",
		Label: "buckets", Singular: "bucket",
	}

	teamIdentity = resourceIdentity{
		Name: "team", Aliases: []string{"teams"},
		Path: "/teams", ConfigKey: "teams",
		Label: "teams", Singular: "team",
	}

	webhookIdentity = resourceIdentity{
		Name: "webhook", Aliases: []string{"webhooks"},
		Path: "/webhooks", ConfigKey: "webhooks",
		Label: "webhooks", Singular: "webhook",
	}

	topicIdentity = resourceIdentity{
		Name: "topic", Aliases: []string{"topics"},
		Path: "/messaging/topics", ConfigKey: "topics",
		Label: "topics", Singular: "topic",
	}

	tableIdentity = resourceIdentity{
		Name: "table", Aliases: []string{"tables"},
		Path: "/tablesdb", ConfigKey: "tables",
		Label: "tables", Singular: "table",
	}

	collectionIdentity = resourceIdentity{
		Name: "collection", Aliases: []string{"collections"},
		Path: "/databases", ConfigKey: "collections",
		Label: "collections", Singular: "collection",
	}

	functionIdentity = resourceIdentity{
		Name: "function", Aliases: []string{"functions"},
		Path: "/functions", ConfigKey: "functions",
		Label: "functions", Singular: "function",
	}

	siteIdentity = resourceIdentity{
		Name: "site", Aliases: []string{"sites"},
		Path: "/sites", ConfigKey: "sites",
		Label: "sites", Singular: "site",
	}
)

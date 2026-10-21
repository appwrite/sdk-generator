//go:build !browser

package cmd

import (
	"fmt"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
)

// resourceIdentity is the half of a descriptor that pull and push share.
type resourceIdentity struct {
	Name    string
	Aliases []string
	Path    string
	// ConfigKey is the config array, and also the array key in a list response
	// unless ListKey says otherwise.
	ConfigKey string
	// ListKey is the array key in a list response, when the config names the
	// array differently. Analytics properties are listed as `properties`, a
	// key that would read as settings at the top of a config.
	ListKey  string
	Label    string
	Singular string
}

func (r resourceIdentity) itemPath(id string) string { return r.Path + "/" + id }

func (r resourceIdentity) listKey() string {
	if r.ListKey != "" {
		return r.ListKey
	}

	return r.ConfigKey
}

// syncHint is the line shown when a push finds nothing configured. Webhooks
// word it differently because `init webhook` does not exist. The wording is
// pinned, "existing one" included.
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

	analyticsIdentity = resourceIdentity{
		Name: "analytics",
		Path: "/analytics/properties", ConfigKey: "analytics", ListKey: "properties",
		Label: "analytics properties", Singular: "analytics property",
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

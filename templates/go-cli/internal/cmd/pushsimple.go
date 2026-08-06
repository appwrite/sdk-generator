package cmd

import (
	"encoding/json"
	"fmt"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/spf13/cobra"
)

// Buckets, teams, webhooks and messaging topics are the flat resources: one
// config entry maps to one remote object, with nothing to upload and nothing to
// poll. All four follow the same five steps, so they are described as data and
// driven by one function.
//
// The remote is fetched twice per resource, once by approveChanges and once by
// the push. That matches the TypeScript, and the two CLIs are compared
// request-for-request, so collapsing them would read as a diff.

// simpleResource describes one flat push.
type simpleResource struct {
	resourceIdentity
	Short string
	// PromptNoun is what the selection prompt calls the resource. Topics read
	// "messaging topic" rather than "topics"; the wording is user-visible and
	// is reproduced rather than regularised.
	PromptNoun string
	// Hint follows the "No X found." line.
	Hint string
	// Keys scopes the approval comparison to the fields the config owns.
	Keys []string
	// UpdateMethod is PUT for everything except a messaging topic, which the
	// API exposes as a PATCH.
	UpdateMethod string
	// CreateFields and UpdateFields name the request body, in the order the
	// generated SDK builds it -- the spec's parameter order, not the order the
	// TypeScript's call site lists its arguments.
	CreateFields []requestField
	UpdateFields []requestField
	// Changed reports whether the remote differs enough to be worth a request.
	// Nil means always send, which is what three of the four do.
	Changed func(remote, local *jsonx.Object) bool
	// Announce prints a per-resource success line. Only topics do, and only
	// because pushMessagingTopics has a success() call its three siblings lack.
	Announce bool
}

// requestField maps a config key onto its request body key.
//
// The names differ: a config entry is keyed by `$id` and `$permissions`, and
// the API takes `bucketId` and `permissions`. The body is therefore built
// field by field rather than by sending the entry verbatim.
type requestField struct {
	Config string
	Wire   string
}

func simpleResources() []simpleResource {
	return []simpleResource{
		{
			resourceIdentity: bucketIdentity,
			Short:            "Push buckets in the current project.",
			PromptNoun:       "buckets",
			Hint:             bucketIdentity.syncHint(),
			Keys:             config.BucketKeys,
			// PUT, so an omitted field is reset to its default. That is the
			// route the TypeScript calls; a partial update is not on offer.
			UpdateMethod: "PUT",
			CreateFields: []requestField{
				{"$id", "bucketId"},
				{"name", "name"},
				{"$permissions", "permissions"},
				{"fileSecurity", "fileSecurity"},
				{"enabled", "enabled"},
				{"maximumFileSize", "maximumFileSize"},
				{"allowedFileExtensions", "allowedFileExtensions"},
				{"compression", "compression"},
				{"encryption", "encryption"},
				{"antivirus", "antivirus"},
			},
			UpdateFields: []requestField{
				{"name", "name"},
				{"$permissions", "permissions"},
				{"fileSecurity", "fileSecurity"},
				{"enabled", "enabled"},
				{"maximumFileSize", "maximumFileSize"},
				{"allowedFileExtensions", "allowedFileExtensions"},
				{"compression", "compression"},
				{"encryption", "encryption"},
				{"antivirus", "antivirus"},
			},
			Changed: bucketChanged,
		},
		{
			resourceIdentity: teamIdentity,
			Short:            "Push teams in the current project.",
			PromptNoun:       "teams",
			Hint:             teamIdentity.syncHint(),
			Keys:             config.TeamKeys,
			UpdateMethod:     "PUT",
			CreateFields: []requestField{
				{"$id", "teamId"},
				{"name", "name"},
			},
			// updateName takes the name and nothing else, so a team's roles
			// and prefs are never touched by a push.
			UpdateFields: []requestField{
				{"name", "name"},
			},
		},
		{
			resourceIdentity: webhookIdentity,
			Short:            "Push webhooks in the current project.",
			PromptNoun:       "webhooks",
			Hint: fmt.Sprintf(
				"Use '%s pull webhooks' to synchronize existing ones.",
				app.ExecutableName),
			Keys:         config.WebhookKeys,
			UpdateMethod: "PUT",
			CreateFields: []requestField{
				{"$id", "webhookId"},
				{"url", "url"},
				{"name", "name"},
				{"events", "events"},
				{"enabled", "enabled"},
				{"tls", "tls"},
			},
			UpdateFields: []requestField{
				{"name", "name"},
				{"url", "url"},
				{"events", "events"},
				{"enabled", "enabled"},
				{"tls", "tls"},
			},
		},
		{
			resourceIdentity: topicIdentity,
			Short:            "Push messaging topics in the current project.",
			// "messaging topic", singular, matching
			// questionsPushMessagingTopics.
			PromptNoun: "messaging topic",
			Hint:       topicIdentity.syncHint(),
			Keys:       config.TopicKeys,
			// PATCH, unlike the other three. The messaging API exposes topic
			// updates as a partial write.
			UpdateMethod: "PATCH",
			CreateFields: []requestField{
				{"$id", "topicId"},
				{"name", "name"},
				{"subscribe", "subscribe"},
			},
			UpdateFields: []requestField{
				{"name", "name"},
				{"subscribe", "subscribe"},
			},
			Announce: true,
		},
	}
}

func newPushSimpleCommand(resource simpleResource) *cobra.Command {
	return &cobra.Command{
		Use:     resource.Name,
		Aliases: resource.Aliases,
		Short:   resource.Short,
		Args:    everyResourceArgument(),
		RunE: func(command *cobra.Command, args []string) error {
			return runPushSimple(command, resource)
		},
	}
}

func runPushSimple(command *cobra.Command, resource simpleResource) error {
	out := command.OutOrStdout()

	context, err := newPushContext()
	if err != nil {
		return err
	}

	selected, err := context.selectSimple(resource)
	if err != nil {
		return err
	}
	if len(selected) == 0 {
		output.Log(out, "No %s found.", resource.Label)
		output.Hint(out, "%s", resource.Hint)

		return nil
	}

	approved, err := context.approveChanges(command, approvalRequest{
		Resources: selected,
		Fetch: func(local *jsonx.Object) (*jsonx.Object, error) {
			return context.fetch(resource.itemPath(local.GetString("$id")))
		},
		Keys:   resource.Keys,
		Plural: resource.Label,
	})
	if err != nil {
		return err
	}
	if !approved {
		return nil
	}

	output.Log(out, "Pushing %s ...", resource.Label)

	pushed := 0
	var failures []error

	for _, local := range selected {
		output.Log(out, "Pushing %s %s ...", resource.Singular, local.GetString("name"))

		unchanged, err := context.pushOne(resource, local)
		if err != nil {
			failures = append(failures, err)
			output.Failure(out, "Failed to push %s %s: %s",
				resource.Singular, local.GetString("name"), err)

			continue
		}
		if unchanged {
			// Skipped, so it counts as neither a success nor a failure. A
			// push where every resource already matches therefore reports
			// "No buckets were pushed."
			output.Log(out, "No changes detected for %s %s. Skipping.",
				resource.Singular, local.GetString("name"))

			continue
		}

		if resource.Announce {
			output.Success(out, "Pushed %s ( %s )",
				local.GetString("name"), local.GetString("$id"))
		}
		pushed++
	}

	// A failed push is reported and counted, never returned: the TypeScript
	// exits zero here, and a partial push has already changed the project.
	pushTally{Pushed: pushed, Failed: len(failures)}.report(out, resource.Label)

	if app.Flags().Verbose {
		for _, failure := range failures {
			output.Failure(out, "%s", failure)
		}
	}

	return nil
}

// selectSimple picks which configured entries to push.
func (c *pushContext) selectSimple(resource simpleResource) ([]*jsonx.Object, error) {
	entries := c.local.ResourceEntries(resource.ConfigKey)

	if app.Flags().All {
		if err := checkDeployConditions(c.local); err != nil {
			return nil, err
		}

		return entries, nil
	}

	// The question carries a `when` guard, so with nothing configured it is
	// never asked. Reproduced rather than left to selectResources: a
	// non-interactive shell would otherwise be told to pass --all for a list
	// that is empty either way.
	if len(entries) == 0 {
		return nil, nil
	}

	return c.selectResources(resource.PromptNoun, resource.Singular, entries)
}

// pushOne creates or updates a single resource, reporting whether it was
// skipped because the remote already matched.
//
// A 404 from EITHER the existence check or the update means create. The
// TypeScript wraps both calls in one try, so an update that races a deletion
// falls through to a create rather than failing; that is reproduced here.
func (c *pushContext) pushOne(
	resource simpleResource,
	local *jsonx.Object,
) (unchanged bool, err error) {
	id := local.GetString("$id")

	remote, err := c.fetch(resource.itemPath(id))
	if err == nil {
		if resource.Changed != nil && !resource.Changed(remote, local) {
			return true, nil
		}

		err = c.api.Call(resource.UpdateMethod, resource.itemPath(id),
			requestBody(local, resource.UpdateFields), nil)
	}
	if err == nil {
		return false, nil
	}
	if !isNotFound(err) {
		return false, err
	}

	return false, c.api.Call("POST", resource.Path,
		requestBody(local, resource.CreateFields), nil)
}

// fetch reads one remote resource as an ordered document.
func (c *pushContext) fetch(path string) (*jsonx.Object, error) {
	response := jsonx.NewObject()
	if err := c.api.Call("GET", path, nil, response); err != nil {
		return nil, err
	}

	return response, nil
}

// requestBody builds a request body from the config entry.
//
// A field the config does not declare is OMITTED rather than sent as a zero
// value. The generated TypeScript SDK skips an undefined argument, so an unset
// `compression` never reaches the wire -- sending "" instead would overwrite a
// bucket's compression on every push.
func requestBody(local *jsonx.Object, fields []requestField) *jsonx.Object {
	body := jsonx.NewObject()
	for _, field := range fields {
		if value, present := local.Get(field.Config); present {
			body.Set(field.Wire, value)
		}
	}

	return body
}

// bucketChanged reports whether a bucket is worth an update request.
//
// Buckets are the only one of the four
// with this check; the other three send an update unconditionally.
func bucketChanged(remote, local *jsonx.Object) bool {
	scalars := []string{
		"name", "fileSecurity", "enabled", "maximumFileSize",
		"encryption", "antivirus", "compression",
	}
	for _, key := range scalars {
		if !strictEqual(remote, local, key) {
			return true
		}
	}

	// Permissions and extensions compare UNORDERED here, because the API
	// returns them in its own order and a reordering alone is not worth a
	// request. The approval table compares the same fields ORDERED. Both are
	// intentional and they are deliberately not unified.
	remotePermissions, _ := remote.Get("$permissions")
	localPermissions, _ := local.Get("$permissions")
	if !arrayEqualsUnordered(remotePermissions, localPermissions) {
		return true
	}

	remoteExtensions, _ := remote.Get("allowedFileExtensions")
	localExtensions, _ := local.Get("allowedFileExtensions")

	return !arrayEqualsUnordered(remoteExtensions, localExtensions)
}

// strictEqual compares one field the way JavaScript's === would.
//
// Presence is part of the comparison: a field the config omits is `undefined`,
// which never equals a value the API returned, so an unset `enabled` against a
// remote `true` counts as a change. isEmpty() is deliberately not consulted --
// hasBucketChanges compares raw, and only the approval table softens it.
func strictEqual(remote, local *jsonx.Object, key string) bool {
	remoteValue, remotePresent := remote.Get(key)
	localValue, localPresent := local.Get(key)

	if !remotePresent || !localPresent {
		return remotePresent == localPresent
	}

	return strictToken(remoteValue) == strictToken(localValue)
}

// strictToken renders a value so two tokens match only where === would.
//
// The type is part of the token because 1 === "1" is false in JavaScript, and
// a config that quotes maximumFileSize really is a change against a numeric
// remote.
func strictToken(value any) string {
	switch typed := value.(type) {
	case nil:
		return "null"
	case bool:
		return fmt.Sprintf("bool:%t", typed)
	case json.Number:
		// Through float64 so 1 and 1.0 are one value, as they are in
		// JavaScript, where both are the same double.
		if number, err := typed.Float64(); err == nil {
			return fmt.Sprintf("number:%v", number)
		}

		return "number:" + typed.String()
	case string:
		return "string:" + typed
	}

	return fmt.Sprintf("other:%v", value)
}

// checkDeployConditions refuses to push from a config with nothing in it.
//
// Ports checkDeployConditions (utils.ts:1009). newPushContext already fails
// when the file is missing; this catches the file that exists and is empty,
// which is what a user gets after running the command in the wrong directory
// alongside an unrelated appwrite.config.json.
func checkDeployConditions(local *config.Local) error {
	if local.Data.Len() > 0 {
		return nil
	}

	return fmt.Errorf(
		"no %s file found in the current directory. Please run this command "+
			"again in the folder containing your %s file, or run '%s init project' "+
			"to link current directory to an Appwrite project",
		config.LocalFileName, config.LocalFileName, app.ExecutableName)
}

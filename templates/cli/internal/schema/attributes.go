// Package schema reconciles the attributes, columns and indexes of a table or
// collection against appwrite.config.json.
//
// Four properties drive the design: some fields update in place and some need a
// delete-and-recreate that loses data, which is why the rules are a per-type
// table; creation is asynchronous, so Poller waits for "available"; indexes
// reference attributes by name, so attributes are always reconciled first; and
// a `previousKey` rename must be resolved before diffing, or it reads as a
// delete plus an add -- the same schema and none of the data.
package schema

import (
	"fmt"
	"io"
	"strings"
	"sync"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
)

// Attribute types, as the API reports them in an attribute's `type`.
const (
	TypeString       = "string"
	TypeVarchar      = "varchar"
	TypeText         = "text"
	TypeMediumText   = "mediumtext"
	TypeLongText     = "longtext"
	TypeBoolean      = "boolean"
	TypeDatetime     = "datetime"
	TypeInteger      = "integer"
	TypeBigInt       = "bigint"
	TypeDouble       = "double"
	TypeRelationship = "relationship"
	TypePoint        = "point"
	TypeLineString   = "linestring"
	TypePolygon      = "polygon"
)

// String sub-formats, which decide both the route and the field rules.
const (
	FormatEmail = "email"
	FormatURL   = "url"
	FormatIP    = "ip"
	FormatEnum  = "enum"
)

// sideChild marks the auto-generated half of a two-way relationship.
const sideChild = "child"

// FieldRules splits an attribute's fields by how a change to them is applied.
//
// A field in neither list is
// ignored: it is either server-derived ($createdAt, status) or irrelevant to the
// schema.
type FieldRules struct {
	// Updatable can be changed in place through the update endpoint.
	Updatable []string
	// Recreate has no update endpoint and forces a delete followed by a
	// create, which loses the column's data.
	Recreate []string
}

// commonRecreateKeys are the fields no update endpoint accepts, whatever the
// attribute's type.
var commonRecreateKeys = []string{"type", "array", "encrypt", "format"}

// AttributeFieldRules returns the rules for one attribute, switching on its type
// and, for strings, its format -- an enum updates `elements` where a plain
// string updates `size`, and neither accepts the other's field.
func AttributeFieldRules(attribute *jsonx.Object) FieldRules {
	switch attribute.GetString("type") {
	case TypeString:
		switch attribute.GetString("format") {
		case FormatEnum:
			return FieldRules{
				Updatable: []string{"required", "default", "elements"},
				Recreate:  commonRecreateKeys,
			}
		case FormatEmail, FormatURL, FormatIP:
			return FieldRules{
				Updatable: []string{"required", "default"},
				Recreate:  commonRecreateKeys,
			}
		default:
			return FieldRules{
				Updatable: []string{"required", "default", "size"},
				Recreate:  commonRecreateKeys,
			}
		}
	case TypeVarchar:
		return FieldRules{
			Updatable: []string{"required", "default", "size"},
			Recreate:  commonRecreateKeys,
		}
	case TypeText, TypeMediumText, TypeLongText, TypeBoolean, TypeDatetime,
		TypePoint, TypeLineString, TypePolygon:
		return FieldRules{
			Updatable: []string{"required", "default"},
			Recreate:  commonRecreateKeys,
		}
	case TypeInteger, TypeBigInt, TypeDouble:
		return FieldRules{
			Updatable: []string{"required", "default", "min", "max"},
			Recreate:  commonRecreateKeys,
		}
	case TypeRelationship:
		// Only onDelete is updatable. Changing the related table or the
		// direction rebuilds the junction, which the API cannot do in place.
		return FieldRules{
			Updatable: []string{"onDelete"},
			Recreate: []string{
				"type", "relatedTable", "relatedCollection", "relationType",
				"twoWay", "twoWayKey",
			},
		}
	default:
		return FieldRules{
			Updatable: []string{"required", "default"},
			Recreate:  commonRecreateKeys,
		}
	}
}

// IndexFieldRules is the rule set for an index. Nothing about an index is
// updatable, so every difference is a recreation. Both `attributes` and
// `columns` appear because collection and table indexes name them differently.
var IndexFieldRules = FieldRules{
	Recreate: []string{"type", "attributes", "columns", "orders"},
}

// Change is one attribute the push will act on, with the reason shown to the
// user before they approve it.
type Change struct {
	// Key is the display label, "<key> in <name> (<id>)".
	Key string
	// Attribute is the object the action is applied to: the REMOTE snapshot
	// for a deletion or a recreation, the LOCAL config entry for an update.
	Attribute *jsonx.Object
	Reason    string
	Action    string
}

// Rename is a pending in-place key change.
type Rename struct {
	From string
	To   string
	// Attribute is the remote snapshot the update call is built from, still
	// carrying the old key. Copied before the snapshot is patched, so it keeps
	// the value the API needs in the path.
	Attribute *jsonx.Object
}

// Container identifies the table or collection being reconciled.
//
// Reduced to the three fields reconciliation actually reads.
type Container struct {
	DatabaseID string
	ID         string
	Name       string
}

// Result is what a reconciliation leaves for the caller to create.
type Result struct {
	// Attributes are the entries that do not exist remotely and must now be
	// created -- including the ones just deleted for a recreation.
	Attributes []*jsonx.Object
	// HasChanges reports whether anything was applied. Distinct from a
	// non-empty Attributes: an in-place update changes the schema without
	// leaving anything to create.
	HasChanges bool
	Renames    []Rename
}

// Plan is the classification of a diff, before any of it is applied.
//
// Separated from the application so the ordering rules can be tested without a
// server: everything up to Plan is a pure function of the two snapshots.
type Plan struct {
	// Renames are resolved previousKey hints, applied before anything else.
	Renames       []Rename
	RenameChanges []Change
	// Deleting exists remotely and not locally.
	Deleting []Change
	// Adding exists locally and not remotely.
	Adding []Change
	// Conflicts differ in a field that cannot be changed in place.
	Conflicts []Change
	// Changes differ only in updatable fields.
	Changes []Change

	container Container
	isIndex   bool
	// remote is the filtered remote snapshot, with rename hints already
	// patched into it so classification matches by the new key.
	remote []*jsonx.Object
	// local is the filtered config entries.
	local []*jsonx.Object
}

// All lists every change in the order the approval table shows them.
func (p Plan) All() []Change {
	all := make([]Change, 0,
		len(p.RenameChanges)+len(p.Deleting)+len(p.Adding)+len(p.Conflicts)+len(p.Changes))
	all = append(all, p.RenameChanges...)
	all = append(all, p.Deleting...)
	all = append(all, p.Adding...)
	all = append(all, p.Conflicts...)
	all = append(all, p.Changes...)

	return all
}

// Empty reports whether the plan would do nothing.
func (p Plan) Empty() bool { return len(p.All()) == 0 }

// BuildPlan classifies a remote snapshot against the config.
//
// warn receives the one situation the plan cannot resolve on its own.
func BuildPlan(
	remote, local []*jsonx.Object,
	container Container,
	isIndex bool,
	warn func(format string, arguments ...any),
) Plan {
	// Child-side relationships are generated by the API when the parent side
	// of a two-way relationship is created. Comparing them would report a
	// difference on both sides and creating one directly is rejected, so they
	// are dropped from both snapshots.
	plan := Plan{
		container: container,
		isIndex:   isIndex,
		remote:    withoutChildRelationships(remote),
		local:     withoutChildRelationships(local),
	}

	// Renames resolve first, so a pure rename never surfaces as delete plus
	// add. Indexes have no rename API, so the hint is skipped for them
	// entirely rather than resolved and then failed on.
	if !isIndex {
		plan.Renames, plan.RenameChanges = resolveRenames(plan.remote, plan.local, container, warn)
	}

	for _, attribute := range plan.remote {
		if contains(plan.local, attribute) == nil {
			plan.Deleting = append(plan.Deleting, changeFor(attribute, container, false))
		}
	}
	for _, attribute := range plan.local {
		if contains(plan.remote, attribute) == nil {
			plan.Adding = append(plan.Adding, changeFor(attribute, container, true))
		}
	}

	for _, attribute := range plan.remote {
		if conflict := checkChanges(
			attribute, contains(plan.local, attribute), container, true, isIndex,
		); conflict != nil {
			plan.Conflicts = append(plan.Conflicts, *conflict)
		}
	}

	for _, attribute := range plan.remote {
		change := checkChanges(
			attribute, contains(plan.local, attribute), container, false, isIndex)
		if change == nil {
			continue
		}

		// An attribute already being recreated is not also updated -- the
		// update would target an attribute about to be deleted. The count is
		// compared against exactly one, not zero: with the display label as the
		// identity a second match cannot occur, so the two are equivalent in
		// practice.
		matches := 0
		for _, conflict := range plan.Conflicts {
			if conflict.Key == change.Key {
				matches++
			}
		}
		if matches == 1 {
			continue
		}

		plan.Changes = append(plan.Changes, *change)
	}

	return plan
}

// withoutChildRelationships drops the auto-generated half of every two-way
// relationship.
func withoutChildRelationships(attributes []*jsonx.Object) []*jsonx.Object {
	filtered := make([]*jsonx.Object, 0, len(attributes))
	for _, attribute := range attributes {
		if attribute.GetString("type") == TypeRelationship &&
			attribute.GetString("side") == sideChild {
			continue
		}
		filtered = append(filtered, attribute)
	}

	return filtered
}

// contains finds the entry with the same key, or nil.
//
// The key is the identity: an
// attribute has no id of its own.
func contains(attributes []*jsonx.Object, attribute *jsonx.Object) *jsonx.Object {
	key := attribute.GetString("key")
	for _, candidate := range attributes {
		if candidate.GetString("key") == key {
			return candidate
		}
	}

	return nil
}

// resolveRenames matches previousKey hints against the remote snapshot.
//
// The matched remote entry is PATCHED
// IN PLACE with the new key before classification runs, which is what stops the
// rename reading as a delete plus an add. The API calls happen later.
func resolveRenames(
	remote, local []*jsonx.Object,
	container Container,
	warn func(format string, arguments ...any),
) ([]Rename, []Change) {
	var (
		renames []Rename
		changes []Change
	)

	for _, entry := range local {
		previousKey := entry.GetString("previousKey")
		key := entry.GetString("key")
		if previousKey == "" || previousKey == key {
			continue
		}

		remotePrevious := byKey(remote, previousKey)
		remoteCurrent := byKey(remote, key)

		switch {
		case remotePrevious != nil && remoteCurrent == nil:
			snapshot := copyObject(remotePrevious)
			renames = append(renames, Rename{From: previousKey, To: key, Attribute: snapshot})
			changes = append(changes, Change{
				Key:       label(key, container),
				Attribute: snapshot,
				Reason:    fmt.Sprintf("key renamed from %s to %s", previousKey, key),
				Action:    "renaming",
			})
			remotePrevious.Set("key", key)
		case remoteCurrent != nil && remotePrevious == nil:
			// Already renamed on the server; the hint is stale and harmless.
		case remotePrevious == nil && remoteCurrent == nil:
			// Fresh create; the add path handles it.
		default:
			// Both keys exist remotely, so the rename would collide.
			warn("Ignoring previousKey %q for %q in %s (%s): both keys already "+
				"exist remotely. %q will be treated as a deletion if it is absent "+
				"from the local config.",
				previousKey, key, container.Name, container.ID, previousKey)
		}
	}

	return renames, changes
}

// byKey finds an entry by its key.
func byKey(attributes []*jsonx.Object, key string) *jsonx.Object {
	for _, attribute := range attributes {
		if attribute.GetString("key") == key {
			return attribute
		}
	}

	return nil
}

// copyObject takes a shallow copy.
func copyObject(source *jsonx.Object) *jsonx.Object {
	copied := jsonx.NewObject()
	for _, key := range source.Keys() {
		value, _ := source.Get(key)
		copied.Set(key, value)
	}

	return copied
}

// label renders the display key used throughout the change table.
func label(key string, container Container) string {
	return fmt.Sprintf("%s in %s (%s)", key, container.Name, container.ID)
}

// changeFor builds the entry for an attribute that exists on only one side.
func changeFor(attribute *jsonx.Object, container Container, isAdding bool) Change {
	reason := "Field isn't present on the appwrite.config.json file"
	action := "deleting"
	if isAdding {
		reason = "Field isn't present on the remote server"
		action = "adding"
	}

	return Change{
		Key:       label(attribute.GetString("key"), container),
		Attribute: attribute,
		Reason:    reason,
		Action:    action,
	}
}

// checkChanges compares one remote attribute against its config counterpart.
//
// recreating selects which fields are compared -- immutable or updatable. The
// two passes are separate because an attribute that needs recreating must not
// also be updated.
func checkChanges(
	remote, local *jsonx.Object,
	container Container,
	recreating, isIndex bool,
) *Change {
	if local == nil {
		return nil
	}

	rules := IndexFieldRules
	if !isIndex {
		// The LOCAL entry picks the rules. The config states the intended
		// type, and a type change is exactly the case that must be caught.
		rules = AttributeFieldRules(local)
	}

	keys := rules.Updatable
	action := "changing"
	attribute := local
	if recreating {
		keys = rules.Recreate
		action = "recreating"
		attribute = remote
	}

	reason := ""
	for _, key := range keys {
		reason = compareField(field(remote, key), field(local, key), reason, key, recreating)
	}

	if reason == "" {
		return nil
	}

	return &Change{
		Key:       label(local.GetString("key"), container),
		Attribute: attribute,
		Reason:    reason,
		Action:    action,
	}
}

// compareField appends one field's difference to the accumulated reason.
func compareField(remote, local any, reason, key string, immutable bool) string {
	// An omitted local field means "leave the remote as it is". A config that
	// does not mention `encrypt` is not asking for it to be turned off.
	if _, isUndefined := local.(undefinedValue); isUndefined {
		return reason
	}

	if isEmpty(remote) && isEmpty(local) {
		return reason
	}

	remoteItems, remoteIsArray := remote.([]any)
	localItems, localIsArray := local.([]any)

	differs := false
	if remoteIsArray && localIsArray {
		differs = jsStringify(remoteItems) != jsStringify(localItems)
	} else {
		differs = !isEqual(remote, local)
	}
	if !differs {
		return reason
	}

	suffix := ""
	if immutable {
		suffix = " (cannot be changed in place, requires recreation)"
	}

	separator := "\n"
	if reason == "" {
		separator = ""
	}

	return fmt.Sprintf("%s%s%s changed from %s to %s%s",
		reason, separator, key, jsString(remote), jsString(local), suffix)
}

// Reconciler applies a plan against one table or collection.
type Reconciler struct {
	API      *client.Client
	Out      io.Writer
	Prompter prompt.Prompter
	Poller   *Poller
	// Force mirrors --force: it suppresses the warnings and the confirmation.
	Force bool
	// SkipConfirmation answers the confirmation without suppressing the
	// warnings. `push all` sets it, having already approved the whole run.
	SkipConfirmation bool
}

// Reconcile diffs a remote snapshot against the config and applies everything
// that is not a creation, returning what still has to be created.
//
// The order is the contract -- renames, in-place updates, deletions, then a
// wait -- so that a failure at any step cannot destroy data the next step
// needed, and no create races a key still being deleted.
func (r *Reconciler) Reconcile(
	remote, local []*jsonx.Object,
	container Container,
	isIndex bool,
) (Result, error) {
	noun := "attribute"
	if isIndex {
		noun = "index"
	}

	plan := BuildPlan(remote, local, container, isIndex, func(format string, arguments ...any) {
		output.Warn(r.Out, format, arguments...)
	})

	if plan.Empty() {
		return Result{}, nil
	}

	if r.Force {
		output.Log(r.Out, "List of applied changes")
	} else {
		output.Log(r.Out, "There are pending changes in your collection deployment")
	}
	printChanges(r.Out, plan.All())

	if !r.Force {
		if len(plan.Deleting) > 0 && !isIndex {
			printBanner(r.Out, "WARNING: Attribute deletion may cause loss of data")
		}
		if len(plan.Conflicts) > 0 && !isIndex {
			printBanner(r.Out, "WARNING: Attribute recreation may cause loss of data")
		}

		confirmed, err := r.confirm()
		if err != nil {
			return Result{}, err
		}
		if !confirmed {
			return Result{}, nil
		}
	}

	if err := r.applyRenames(plan); err != nil {
		return Result{}, err
	}
	if err := r.applyUpdates(plan, noun); err != nil {
		return Result{}, err
	}

	remaining, err := r.applyDeletions(plan, isIndex, noun)
	if err != nil {
		return Result{}, err
	}

	created := make([]*jsonx.Object, 0, len(plan.local))
	for _, entry := range plan.local {
		if contains(remaining, entry) == nil {
			created = append(created, entry)
		}
	}

	return Result{Attributes: created, HasChanges: true, Renames: plan.Renames}, nil
}

// confirm asks whether to apply the changes.
//
// --force is checked by the caller as well: it both suppresses the warnings on
// the way in and short-circuits the question here.
func (r *Reconciler) confirm() (bool, error) {
	if r.Force || r.SkipConfirmation {
		return true, nil
	}

	return r.Prompter.Confirm(prompt.Question{
		Message: "Would you like to apply these changes?",
		Default: false,
		Flag:    "--force",
	})
}

// applyRenames issues the update calls for resolved previousKey hints and waits
// for the new keys to become available.
func (r *Reconciler) applyRenames(plan Plan) error {
	if len(plan.Renames) == 0 {
		return nil
	}

	newKeys := make([]string, 0, len(plan.Renames))
	failures := runConcurrently(len(plan.Renames), func(index int) error {
		rename := plan.Renames[index]

		return r.UpdateAttribute(plan.container, rename.Attribute, rename.To)
	})

	messages := make([]string, 0, len(failures))
	for index, err := range failures {
		newKeys = append(newKeys, plan.Renames[index].To)
		if err != nil {
			messages = append(messages, formatUpdateError(plan.Renames[index].To, err))
		}
	}
	if len(messages) > 0 {
		return fmt.Errorf("error renaming attribute for %s:\n%s",
			plan.container.ID, strings.Join(messages, "\n"))
	}

	ready, err := r.Poller.ExpectAttributes(plan.container, newKeys)
	if err != nil {
		return err
	}
	if !ready {
		return fmt.Errorf("attribute rename timed out waiting for keys: %s",
			strings.Join(newKeys, ", "))
	}

	return nil
}

// applyUpdates issues the in-place update calls.
//
// Every failure is collected rather than the first one returned, so a user with
// several bad fields sees all of them in one run.
func (r *Reconciler) applyUpdates(plan Plan, noun string) error {
	if len(plan.Changes) == 0 {
		return nil
	}

	failures := runConcurrently(len(plan.Changes), func(index int) error {
		return r.UpdateAttribute(plan.container, plan.Changes[index].Attribute, "")
	})

	messages := make([]string, 0, len(failures))
	for index, err := range failures {
		if err != nil {
			messages = append(messages,
				formatUpdateError(plan.Changes[index].Attribute.GetString("key"), err))
		}
	}
	if len(messages) > 0 {
		return fmt.Errorf("error updating %s for %s:\n%s",
			noun, plan.container.ID, strings.Join(messages, "\n"))
	}

	return nil
}

// applyDeletions removes the recreated and the deleted entries, waits for the
// removals to land, and returns the remote entries that survive.
func (r *Reconciler) applyDeletions(plan Plan, isIndex bool, noun string) ([]*jsonx.Object, error) {
	remaining := plan.remote

	recreated := make([]*jsonx.Object, 0, len(plan.Conflicts))
	for _, conflict := range plan.Conflicts {
		recreated = append(recreated, conflict.Attribute)
	}

	if len(recreated) > 0 {
		if err := r.deleteAll(plan.container, recreated, isIndex); err != nil {
			return nil, err
		}

		// Dropped from the remote snapshot so the entries just deleted are
		// classified as missing, and therefore created again below.
		survivors := make([]*jsonx.Object, 0, len(remaining))
		for _, attribute := range remaining {
			if contains(recreated, attribute) == nil {
				survivors = append(survivors, attribute)
			}
		}
		remaining = survivors
	}

	deleted := make([]*jsonx.Object, 0, len(plan.Deleting))
	for _, change := range plan.Deleting {
		deleted = append(deleted, change.Attribute)
	}
	if err := r.deleteAll(plan.container, deleted, isIndex); err != nil {
		return nil, err
	}

	keys := make([]string, 0, len(deleted)+len(recreated))
	for _, attribute := range deleted {
		keys = append(keys, attribute.GetString("key"))
	}
	for _, attribute := range recreated {
		keys = append(keys, attribute.GetString("key"))
	}

	if len(keys) > 0 {
		gone, err := r.Poller.WaitForDeletion(plan.container, keys, isIndex)
		if err != nil {
			return nil, err
		}
		if !gone {
			return nil, fmt.Errorf("%s deletion timed out", noun)
		}
	}

	return remaining, nil
}

// deleteAll removes a set of attributes or indexes.
func (r *Reconciler) deleteAll(
	container Container,
	attributes []*jsonx.Object,
	isIndex bool,
) error {
	if len(attributes) == 0 {
		return nil
	}

	failures := runConcurrently(len(attributes), func(index int) error {
		return r.DeleteAttribute(container, attributes[index], isIndex)
	})
	for _, err := range failures {
		if err != nil {
			return err
		}
	}

	return nil
}

// CreateAttributes creates every entry and waits for all of them to become
// available. Serial: the API rejects concurrent schema changes on one
// collection.
func (r *Reconciler) CreateAttributes(
	entries []*jsonx.Object,
	container Container,
	noun string,
) error {
	output.Log(r.Out, "Creating %s ...", noun)

	keys := make([]string, 0, len(entries))
	for _, entry := range entries {
		if entry.GetString("side") == sideChild {
			continue
		}
		if err := r.CreateAttribute(container, entry); err != nil {
			return err
		}
		keys = append(keys, entry.GetString("key"))
	}

	ready, err := r.Poller.ExpectAttributes(container, keys)
	if err != nil {
		return err
	}
	if !ready {
		return fmt.Errorf("%s creation timed out", strings.TrimSuffix(noun, "s"))
	}

	if len(keys) > 0 {
		output.Success(r.Out, "Created %d %s", len(keys), noun)
	}

	return nil
}

// CreateIndexes creates every index and waits for all of them to become
// available.
//
// Always called AFTER the attributes
// pass: an index over an attribute that is still processing is rejected.
func (r *Reconciler) CreateIndexes(indexes []*jsonx.Object, container Container) error {
	output.Log(r.Out, "Creating indexes ...")

	keys := make([]string, 0, len(indexes))
	for _, index := range indexes {
		if err := r.CreateIndex(container, index); err != nil {
			return err
		}
		keys = append(keys, index.GetString("key"))
	}

	ready, err := r.Poller.ExpectIndexes(container, keys)
	if err != nil {
		return err
	}
	if !ready {
		return fmt.Errorf("index creation timed out")
	}

	if len(indexes) > 0 {
		output.Success(r.Out, "Created %d indexes", len(indexes))
	}

	return nil
}

// formatUpdateError explains the one update failure a user can act on.
//
// Shrinking a string attribute
// fails when a stored value is longer than the new size, and the API's
// `attribute_invalid_resize` says nothing about what to do next.
func formatUpdateError(key string, err error) string {
	message := err.Error()
	if strings.Contains(message, "attribute_invalid_resize") ||
		strings.Contains(message, "column_invalid_resize") ||
		strings.Contains(message, "invalid_resize") {
		return fmt.Sprintf("Failed to update %q: existing values exceed the new size. "+
			"Increase the size, shorten existing data, or recreate the attribute. (%s)",
			key, message)
	}

	return fmt.Sprintf("Failed to update %q: %s", key, message)
}

// runConcurrently runs count operations and returns every result by index.
//
// Implements Promise.allSettled: a failure does not cancel the rest, because the
// user needs to see all of them. The limit stands in for errgroup.SetLimit --
// an unbounded fan-out over a user's schema is a way to get rate limited.
func runConcurrently(count int, operation func(index int) error) []error {
	const limit = 10

	results := make([]error, count)
	semaphore := make(chan struct{}, limit)

	var group sync.WaitGroup
	for index := range count {
		semaphore <- struct{}{}
		group.Add(1)
		go func() {
			defer group.Done()
			defer func() { <-semaphore }()
			results[index] = operation(index)
		}()
	}
	group.Wait()

	return results
}

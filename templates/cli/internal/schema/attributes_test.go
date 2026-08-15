package schema

import (
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
)

// object builds an attribute from alternating key/value pairs, preserving the
// order they are given in -- which is what jsonx.Object exists to do.
func object(pairs ...any) *jsonx.Object {
	built := jsonx.NewObject()
	for index := 0; index+1 < len(pairs); index += 2 {
		built.Set(pairs[index].(string), pairs[index+1])
	}

	return built
}

func objects(entries ...*jsonx.Object) []*jsonx.Object { return entries }

var testContainer = Container{DatabaseID: "main", ID: "posts", Name: "Posts"}

func TestAttributeFieldRules(t *testing.T) {
	cases := []struct {
		name      string
		attribute *jsonx.Object
		updatable []string
		recreate  []string
	}{
		{
			name:      "a plain string can be resized in place",
			attribute: object("type", "string"),
			updatable: []string{"required", "default", "size"},
			recreate:  commonRecreateKeys,
		},
		{
			name:      "an enum trades size for elements",
			attribute: object("type", "string", "format", "enum"),
			updatable: []string{"required", "default", "elements"},
			recreate:  commonRecreateKeys,
		},
		{
			name:      "an email has neither",
			attribute: object("type", "string", "format", "email"),
			updatable: []string{"required", "default"},
			recreate:  commonRecreateKeys,
		},
		{
			name:      "a number carries its bounds",
			attribute: object("type", "integer"),
			updatable: []string{"required", "default", "min", "max"},
			recreate:  commonRecreateKeys,
		},
		{
			name:      "a relationship can only change onDelete",
			attribute: object("type", "relationship"),
			updatable: []string{"onDelete"},
			recreate: []string{
				"type", "relatedTable", "relatedCollection", "relationType",
				"twoWay", "twoWayKey",
			},
		},
		{
			name:      "an unknown type falls back to the common rules",
			attribute: object("type", "something-new"),
			updatable: []string{"required", "default"},
			recreate:  commonRecreateKeys,
		},
	}

	for _, testCase := range cases {
		t.Run(testCase.name, func(t *testing.T) {
			rules := AttributeFieldRules(testCase.attribute)
			if strings.Join(rules.Updatable, ",") != strings.Join(testCase.updatable, ",") {
				t.Errorf("updatable = %v, want %v", rules.Updatable, testCase.updatable)
			}
			if strings.Join(rules.Recreate, ",") != strings.Join(testCase.recreate, ",") {
				t.Errorf("recreate = %v, want %v", rules.Recreate, testCase.recreate)
			}
		})
	}
}

// TestNeedsRecreation is the test that matters most in this package: a false
// positive deletes a live column.
func TestNeedsRecreation(t *testing.T) {
	cases := []struct {
		name     string
		remote   *jsonx.Object
		local    *jsonx.Object
		conflict bool
		change   bool
	}{
		{
			name:   "identical attributes are left alone",
			remote: object("key", "title", "type", "string", "size", 128, "required", true),
			local:  object("key", "title", "type", "string", "size", 128, "required", true),
		},
		{
			name:   "a changed type forces a recreation",
			remote: object("key", "views", "type", "string", "size", 32),
			local:  object("key", "views", "type", "integer"),
			// `size` is not in the integer rules, so only `type` is compared.
			conflict: true,
		},
		{
			name:     "toggling array forces a recreation",
			remote:   object("key", "tags", "type", "string", "array", false),
			local:    object("key", "tags", "type", "string", "array", true),
			conflict: true,
		},
		{
			name:   "a changed size is an in-place update",
			remote: object("key", "title", "type", "string", "size", 128),
			local:  object("key", "title", "type", "string", "size", 256),
			change: true,
		},
		{
			name:   "a changed required flag is an in-place update",
			remote: object("key", "title", "type", "string", "required", false),
			local:  object("key", "title", "type", "string", "required", true),
			change: true,
		},
		{
			name:   "a string size and a numeric size are the same size",
			remote: object("key", "title", "type", "string", "size", 128),
			local:  object("key", "title", "type", "string", "size", "128"),
		},
		{
			name:   "an omitted local field leaves the remote alone",
			remote: object("key", "title", "type", "string", "encrypt", true, "size", 128),
			local:  object("key", "title", "type", "string", "size", 128),
		},
		{
			name:   "null and absent both read as empty",
			remote: object("key", "title", "type", "string", "default", nil),
			local:  object("key", "title", "type", "string", "default", ""),
		},
		{
			name: "reordered enum elements are a change",
			remote: object("key", "state", "type", "string", "format", "enum",
				"elements", []any{"draft", "live"}),
			local: object("key", "state", "type", "string", "format", "enum",
				"elements", []any{"live", "draft"}),
			change: true,
		},
		{
			name: "a relationship changing its target is recreated",
			remote: object("key", "author", "type", "relationship",
				"relatedCollection", "users", "relationType", "oneToOne"),
			local: object("key", "author", "type", "relationship",
				"relatedCollection", "authors", "relationType", "oneToOne"),
			conflict: true,
		},
		{
			name: "a relationship changing onDelete is updated in place",
			remote: object("key", "author", "type", "relationship",
				"relatedCollection", "users", "onDelete", "restrict"),
			local: object("key", "author", "type", "relationship",
				"relatedCollection", "users", "onDelete", "cascade"),
			change: true,
		},
	}

	for _, testCase := range cases {
		t.Run(testCase.name, func(t *testing.T) {
			plan := BuildPlan(
				objects(testCase.remote), objects(testCase.local),
				testContainer, false, func(string, ...any) {})

			if got := len(plan.Conflicts) == 1; got != testCase.conflict {
				t.Errorf("conflict = %v, want %v (reasons: %v)",
					got, testCase.conflict, reasons(plan.Conflicts))
			}
			if got := len(plan.Changes) == 1; got != testCase.change {
				t.Errorf("change = %v, want %v (reasons: %v)",
					got, testCase.change, reasons(plan.Changes))
			}
			if len(plan.Adding) != 0 || len(plan.Deleting) != 0 {
				t.Errorf("matched keys must not be added or deleted, got %d/%d",
					len(plan.Adding), len(plan.Deleting))
			}
		})
	}
}

func reasons(changes []Change) []string {
	all := make([]string, 0, len(changes))
	for _, entry := range changes {
		all = append(all, entry.Reason)
	}

	return all
}

// TestRecreationSuppressesUpdate covers the rule that an attribute about to be
// dropped is not also updated -- the update would target a key being deleted.
func TestRecreationSuppressesUpdate(t *testing.T) {
	remote := object("key", "views", "type", "string", "size", 32, "required", false)
	local := object("key", "views", "type", "integer", "required", true)

	plan := BuildPlan(objects(remote), objects(local), testContainer, false, func(string, ...any) {})

	if len(plan.Conflicts) != 1 {
		t.Fatalf("conflicts = %d, want 1", len(plan.Conflicts))
	}
	if len(plan.Changes) != 0 {
		t.Fatalf("changes = %d, want 0 (%v)", len(plan.Changes), reasons(plan.Changes))
	}
}

func TestPlanClassifiesAdditionsAndDeletions(t *testing.T) {
	remote := objects(
		object("key", "title", "type", "string", "size", 128),
		object("key", "legacy", "type", "string", "size", 32),
	)
	local := objects(
		object("key", "title", "type", "string", "size", 128),
		object("key", "summary", "type", "string", "size", 512),
	)

	plan := BuildPlan(remote, local, testContainer, false, func(string, ...any) {})

	if len(plan.Deleting) != 1 || !strings.HasPrefix(plan.Deleting[0].Key, "legacy ") {
		t.Errorf("deleting = %v, want legacy", reasons(plan.Deleting))
	}
	if len(plan.Adding) != 1 || !strings.HasPrefix(plan.Adding[0].Key, "summary ") {
		t.Errorf("adding = %v, want summary", plan.Adding)
	}
	if plan.Deleting[0].Action != "deleting" || plan.Adding[0].Action != "adding" {
		t.Errorf("actions = %q/%q", plan.Deleting[0].Action, plan.Adding[0].Action)
	}
}

// TestChildSideRelationshipsAreInvisible guards the case that would otherwise
// try to create the auto-generated half of a two-way relationship, which the
// API rejects.
func TestChildSideRelationshipsAreInvisible(t *testing.T) {
	remote := objects(
		object("key", "author", "type", "relationship", "side", "parent",
			"relatedCollection", "users"),
		object("key", "posts", "type", "relationship", "side", "child",
			"relatedCollection", "posts"),
	)
	local := objects(
		object("key", "author", "type", "relationship", "side", "parent",
			"relatedCollection", "users"),
	)

	plan := BuildPlan(remote, local, testContainer, false, func(string, ...any) {})

	if !plan.Empty() {
		t.Fatalf("plan should be empty, got %v", plan.All())
	}
}

func TestResolveRenames(t *testing.T) {
	t.Run("a pending rename patches the snapshot instead of deleting", func(t *testing.T) {
		remote := objects(object("key", "title", "type", "string", "size", 128))
		local := objects(object("key", "heading", "previousKey", "title",
			"type", "string", "size", 128))

		plan := BuildPlan(remote, local, testContainer, false, func(string, ...any) {})

		if len(plan.Renames) != 1 {
			t.Fatalf("renames = %d, want 1", len(plan.Renames))
		}
		if plan.Renames[0].From != "title" || plan.Renames[0].To != "heading" {
			t.Errorf("rename = %+v", plan.Renames[0])
		}
		// The snapshot the update call is built from keeps the OLD key: it goes
		// in the path, with the new one in the body.
		if got := plan.Renames[0].Attribute.GetString("key"); got != "title" {
			t.Errorf("rename snapshot key = %q, want title", got)
		}
		if len(plan.Deleting) != 0 || len(plan.Adding) != 0 {
			t.Errorf("a rename must not read as delete+add, got %d/%d",
				len(plan.Deleting), len(plan.Adding))
		}
	})

	t.Run("a stale hint is ignored", func(t *testing.T) {
		remote := objects(object("key", "heading", "type", "string", "size", 128))
		local := objects(object("key", "heading", "previousKey", "title",
			"type", "string", "size", 128))

		plan := BuildPlan(remote, local, testContainer, false, func(string, ...any) {})

		if len(plan.Renames) != 0 || !plan.Empty() {
			t.Fatalf("stale hint should do nothing, got %v", plan.All())
		}
	})

	t.Run("a colliding hint warns and falls back to delete", func(t *testing.T) {
		remote := objects(
			object("key", "title", "type", "string", "size", 128),
			object("key", "heading", "type", "string", "size", 128),
		)
		local := objects(object("key", "heading", "previousKey", "title",
			"type", "string", "size", 128))

		warned := 0
		plan := BuildPlan(remote, local, testContainer, false, func(string, ...any) { warned++ })

		if warned != 1 {
			t.Errorf("warnings = %d, want 1", warned)
		}
		if len(plan.Renames) != 0 {
			t.Errorf("renames = %d, want 0", len(plan.Renames))
		}
		if len(plan.Deleting) != 1 {
			t.Errorf("deleting = %d, want 1 (title)", len(plan.Deleting))
		}
	})

	t.Run("indexes have no rename API", func(t *testing.T) {
		remote := objects(object("key", "by_title", "type", "key",
			"attributes", []any{"title"}))
		local := objects(object("key", "by_heading", "previousKey", "by_title",
			"type", "key", "attributes", []any{"title"}))

		plan := BuildPlan(remote, local, testContainer, true, func(string, ...any) {})

		if len(plan.Renames) != 0 {
			t.Fatalf("indexes must not be renamed, got %v", plan.Renames)
		}
		if len(plan.Deleting) != 1 || len(plan.Adding) != 1 {
			t.Fatalf("an index rename is delete+add, got %d/%d",
				len(plan.Deleting), len(plan.Adding))
		}
	})
}

// TestIndexRulesAreAllRecreate is the dependency-ordering guarantee in rule
// form: an index has no updatable field, so it can never be changed in place
// while the attributes it references are moving underneath it.
func TestIndexRulesAreAllRecreate(t *testing.T) {
	if len(IndexFieldRules.Updatable) != 0 {
		t.Fatalf("indexes must have no updatable fields, got %v", IndexFieldRules.Updatable)
	}

	remote := objects(object("key", "by_title", "type", "key",
		"columns", []any{"title"}, "orders", []any{"ASC"}))
	local := objects(object("key", "by_title", "type", "key",
		"columns", []any{"title", "views"}, "orders", []any{"ASC", "ASC"}))

	plan := BuildPlan(remote, local, testContainer, true, func(string, ...any) {})

	if len(plan.Conflicts) != 1 {
		t.Fatalf("conflicts = %d, want 1", len(plan.Conflicts))
	}
	if len(plan.Changes) != 0 {
		t.Fatalf("changes = %d, want 0", len(plan.Changes))
	}
	if plan.Conflicts[0].Action != "recreating" {
		t.Errorf("action = %q, want recreating", plan.Conflicts[0].Action)
	}
}

// TestPlanOrder pins the order the approval table shows changes in. Renames
// come first because they are applied first, and a user reading the table has
// to see the rename before the deletion that would otherwise look like it.
func TestPlanOrder(t *testing.T) {
	remote := objects(
		object("key", "title", "type", "string", "size", 128),
		object("key", "legacy", "type", "string", "size", 32),
		object("key", "views", "type", "string", "size", 8),
		object("key", "summary", "type", "string", "size", 128),
	)
	local := objects(
		object("key", "heading", "previousKey", "title", "type", "string", "size", 128),
		object("key", "views", "type", "integer"),
		object("key", "summary", "type", "string", "size", 512),
		object("key", "author", "type", "string", "size", 64),
	)

	plan := BuildPlan(remote, local, testContainer, false, func(string, ...any) {})

	actions := make([]string, 0, len(plan.All()))
	for _, entry := range plan.All() {
		actions = append(actions, entry.Action)
	}

	want := "renaming,deleting,adding,recreating,changing"
	if strings.Join(actions, ",") != want {
		t.Errorf("actions = %v, want %s", actions, want)
	}
}

func TestCreateRoute(t *testing.T) {
	cases := []struct {
		attribute *jsonx.Object
		route     string
	}{
		{object("type", "string"), "string"},
		{object("type", "string", "format", "email"), "email"},
		{object("type", "string", "format", "enum"), "enum"},
		{object("type", "double"), "float"},
		{object("type", "linestring"), "line"},
		{object("type", "relationship"), "relationship"},
	}

	for _, testCase := range cases {
		t.Run(testCase.route, func(t *testing.T) {
			route, _, err := createRoute(testCase.attribute)
			if err != nil {
				t.Fatal(err)
			}
			if route != testCase.route {
				t.Errorf("route = %q, want %q", route, testCase.route)
			}
		})
	}

	if _, _, err := createRoute(object("type", "quaternion")); err == nil {
		t.Error("an unknown type must be rejected rather than posted somewhere")
	}
}

// TestRequestBodyOmitsAbsentFields pins the rule the recorded trace shows: a
// field the config does not mention is not in the request, and an explicit null
// is.
func TestRequestBodyOmitsAbsentFields(t *testing.T) {
	attribute := object("key", "title", "type", "string", "size", 128,
		"required", true, "array", false)

	_, parameters, err := createRoute(attribute)
	if err != nil {
		t.Fatal(err)
	}

	body := jsonx.NewObject()
	for _, parameter := range parameters {
		parameter.apply(attribute, body)
	}

	if body.Has("default") || body.Has("encrypt") {
		t.Errorf("absent fields leaked into the body: %v", body.Keys())
	}

	withNull := object("key", "views", "type", "integer", "required", false,
		"array", false, "default", nil)
	_, numeric, err := createRoute(withNull)
	if err != nil {
		t.Fatal(err)
	}

	nullBody := jsonx.NewObject()
	for _, parameter := range numeric {
		parameter.apply(withNull, nullBody)
	}
	if !nullBody.Has("default") {
		t.Error("an explicit null default must be sent")
	}
}

// TestRelationshipTargetFallsBack covers the one parameter with two possible
// config names.
func TestRelationshipTargetFallsBack(t *testing.T) {
	cases := []struct {
		name      string
		attribute *jsonx.Object
		want      string
	}{
		{
			name: "a table names relatedTable",
			attribute: object("type", "relationship", "relatedTable", "users",
				"relationType", "oneToOne"),
			want: "users",
		},
		{
			name: "a collection names relatedCollection",
			attribute: object("type", "relationship", "relatedCollection", "users",
				"relationType", "oneToOne"),
			want: "users",
		},
		{
			name: "a null relatedTable falls through",
			attribute: object("type", "relationship", "relatedTable", nil,
				"relatedCollection", "users", "relationType", "oneToOne"),
			want: "users",
		},
	}

	for _, testCase := range cases {
		t.Run(testCase.name, func(t *testing.T) {
			_, parameters, err := createRoute(testCase.attribute)
			if err != nil {
				t.Fatal(err)
			}

			body := jsonx.NewObject()
			for _, parameter := range parameters {
				parameter.apply(testCase.attribute, body)
			}

			if got := body.GetString("relatedCollectionId"); got != testCase.want {
				t.Errorf("relatedCollectionId = %q, want %q", got, testCase.want)
			}
			if got := body.GetString("type"); got != "oneToOne" {
				t.Errorf("type = %q, want the relationType", got)
			}
		})
	}
}

func TestPollerScalesItsTimeoutOnce(t *testing.T) {
	poller := NewPoller(nil, nil, 0)
	if poller.maxDebounces != pollDefaultMaxDebounces {
		t.Fatalf("default = %d, want %d", poller.maxDebounces, pollDefaultMaxDebounces)
	}

	poller.out = discard{}
	poller.scaleTimeout(1, 250, "x")
	if poller.maxDebounces != pollDefaultMaxDebounces*3 {
		t.Errorf("scaled = %d, want %d", poller.maxDebounces, pollDefaultMaxDebounces*3)
	}

	// Only once: the field is compared against the default, which it no longer
	// equals.
	poller.scaleTimeout(1, 250, "x")
	if poller.maxDebounces != pollDefaultMaxDebounces*3 {
		t.Errorf("scaled twice to %d", poller.maxDebounces)
	}

	// An explicit --attempts is never scaled.
	explicit := NewPoller(nil, discard{}, 5)
	explicit.scaleTimeout(1, 1000, "x")
	if explicit.maxDebounces != 5 {
		t.Errorf("explicit attempts = %d, want 5", explicit.maxDebounces)
	}
}

type discard struct{}

func (discard) Write(payload []byte) (int, error) { return len(payload), nil }

package cmd

import (
	"bytes"
	"encoding/json"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"slices"
	"strings"
	"sync"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/jsonx"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
	"github.com/spf13/cobra"
)

const configWithOneSite = `{
    "projectId": "probe",
    "projectName": "probe",
    "sites": [
        { "$id": "web", "name": "Marketing site", "path": "sites/web" }
    ]
}`

// Regression for `push site` answering "No sites found." on a config that has
// one.
//
// A multi-select starts with nothing ticked and Enter accepts that, so the
// prompt handed back an empty list; the caller read that as "the config has
// none" and pointed the reader at `init site` for a site already in front of
// them. prompt.RequiredSelection exists for exactly this and was never wired
// up.
func TestPushSelectionRejectsAnEmptyChoice(t *testing.T) {
	local := loadProbeConfig(t)

	context := &pushContext{
		local: local,
		// What pressing Enter on an untouched list produces.
		prompter: &prompt.Scripted{MultiChoices: map[string][]string{
			"Which sites would you like to push?": {},
		}},
	}

	_, err := context.selectResources("sites", "site", local.ResourceEntries("sites"))
	if err == nil {
		t.Fatal("an empty selection was accepted, so the caller will report the site as missing")
	}
	if !strings.Contains(err.Error(), "at least one site") {
		t.Errorf("error = %q, want it to name the resource", err)
	}
}

// The prompt still has to return what was actually chosen.
func TestPushSelectionKeepsTheChosenEntries(t *testing.T) {
	local := loadProbeConfig(t)

	context := &pushContext{
		local: local,
		prompter: &prompt.Scripted{MultiChoices: map[string][]string{
			"Which sites would you like to push?": {"web"},
		}},
	}

	chosen, err := context.selectResources("sites", "site", local.ResourceEntries("sites"))
	if err != nil {
		t.Fatal(err)
	}
	if len(chosen) != 1 || chosen[0].GetString("$id") != "web" {
		t.Errorf("chosen = %v, want the one site", chosen)
	}
}

// Every selected deployment must start before any one of them finishes. The
// sequential loop waited for a function's entire build before it even packaged
// the next one, making a multi-function push take the sum of all build times.
func TestSelectedDeploymentsStartInParallel(t *testing.T) {
	entries := make([]*jsonx.Object, 2)
	for index, id := range []string{"first", "second"} {
		entry := jsonx.NewObject()
		entry.Set("$id", id)
		entries[index] = entry
	}

	started := make(chan string, len(entries))
	release := make(chan struct{})
	finished := make(chan pushSummary, 1)

	go func() {
		finished <- pushDeployablesInParallel(entries,
			func(entry *jsonx.Object, summary *pushSummary) {
				started <- entry.GetString("$id")
				<-release
				summary.Pushed++
			})
	}()

	timer := time.NewTimer(5 * time.Second)
	defer timer.Stop()

	seen := map[string]bool{}
	for range entries {
		select {
		case id := <-started:
			seen[id] = true
		case <-timer.C:
			close(release)
			t.Fatal("a selected deployment waited for the previous one to finish")
		}
	}
	close(release)

	summary := <-finished
	if len(seen) != len(entries) {
		t.Errorf("started %v, want both selected deployments", seen)
	}
	if summary.Pushed != len(entries) {
		t.Errorf("summary counted %d pushes, want %d", summary.Pushed, len(entries))
	}
}

// Packaging and uploading a large function can consume eight HTTP requests on
// its own. Selecting a large project must not multiply that by every configured
// function at once.
func TestSelectedDeploymentConcurrencyIsBounded(t *testing.T) {
	entries := make([]*jsonx.Object, functionPushConcurrency+2)
	for index := range entries {
		entries[index] = jsonx.NewObject()
	}

	started := make(chan struct{}, len(entries))
	release := make(chan struct{})
	finished := make(chan pushSummary, 1)

	go func() {
		finished <- pushDeployablesInParallel(entries,
			func(_ *jsonx.Object, summary *pushSummary) {
				started <- struct{}{}
				<-release
				summary.Pushed++
			})
	}()

	timer := time.NewTimer(5 * time.Second)
	defer timer.Stop()
	for range functionPushConcurrency {
		select {
		case <-started:
		case <-timer.C:
			close(release)
			t.Fatalf("only %d of %d deployment workers started",
				len(started), functionPushConcurrency)
		}
	}

	select {
	case <-started:
		close(release)
		t.Fatalf("more than %d deployment workers ran concurrently",
			functionPushConcurrency)
	case <-time.After(100 * time.Millisecond):
	}
	close(release)

	summary := <-finished
	if summary.Pushed != len(entries) {
		t.Errorf("summary counted %d pushes, want %d", summary.Pushed, len(entries))
	}
}

// `appwrite push site all` prompted for a selection, because cobra accepts
// surplus positionals by default and the word was thrown away. `push function
// all` looked like it worked only because that project had no functions.
func TestPushResourcesAcceptAllAsAnArgument(t *testing.T) {
	root := NewRootCommand()

	for _, path := range []string{
		"push site", "push function", "push bucket", "push team",
		"push webhook", "push topic", "push analytics", "push table", "push collection",
	} {
		command := resolveCommand(root, path)
		if command == nil {
			t.Fatalf("`%s` is not in the tree", path)
		}
		if command.Args == nil {
			t.Errorf("`%s` validates no arguments, so `all` is still discarded", path)

			continue
		}

		restore := app.Flags().All
		app.Flags().All = false

		if err := command.Args(command, []string{"all"}); err != nil {
			t.Errorf("`%s all` was rejected: %v", path, err)
		}
		if !app.Flags().All {
			t.Errorf("`%s all` did not select every resource", path)
		}

		app.Flags().All = restore
	}
}

// And anything that is not `all` is refused rather than ignored, naming both
// forms that work.
func TestPushResourcesRejectAnUnknownArgument(t *testing.T) {
	command := resolveCommand(NewRootCommand(), "push site")

	err := command.Args(command, []string{"marketing"})
	if err == nil {
		t.Fatal("`push site marketing` was accepted, and pushes whatever the prompt is left on")
	}
	for _, want := range []string{"`all`", "marketing", "choose from a list"} {
		if !strings.Contains(err.Error(), want) {
			t.Errorf("error does not mention %s:\n  %s", want, err)
		}
	}
}

const configWithOnlyFinance = `{
    "projectId": "probe",
    "projectName": "probe",
    "tablesDB": [
        { "$id": "finance", "name": "Finance", "enabled": true }
    ]
}`

const configWithOrders = `{
    "projectId": "probe",
    "projectName": "probe",
    "tablesDB": [
        { "$id": "main", "name": "Main", "enabled": true }
    ],
    "tables": [
        {
            "$id": "orders",
            "databaseId": "main",
            "name": "Orders",
            "columns": [
                { "key": "total", "type": "integer", "required": false, "array": false }
            ],
            "indexes": []
        }
    ]
}`

const ordersAttributes = "/databases/main/collections/orders/attributes"

type fakeTablesDB struct {
	mutex    sync.Mutex
	requests []string
	columns  []map[string]any
}

func (f *fakeTablesDB) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	f.mutex.Lock()
	defer f.mutex.Unlock()

	f.requests = append(f.requests, r.Method+" "+r.URL.Path)

	switch {
	case r.Method == "GET" && r.URL.Path == "/tablesdb":
		_ = json.NewEncoder(w).Encode(map[string]any{"total": 1, "databases": []map[string]any{
			{"$id": "main", "name": "Main", "enabled": true},
		}})
	case r.Method == "GET" && r.URL.Path == "/tablesdb/main/tables":
		_ = json.NewEncoder(w).Encode(map[string]any{"total": 1, "tables": []map[string]any{
			{"$id": "orders", "name": "Orders"},
		}})
	case r.Method == "GET" && r.URL.Path == "/tablesdb/main/tables/orders":
		_ = json.NewEncoder(w).Encode(map[string]any{
			"$id": "orders", "databaseId": "main", "name": "Orders",
			"columns": f.columns, "indexes": []any{},
		})
	case r.Method == "GET" && r.URL.Path == "/tablesdb/main/tables/orders/rows":
		_ = json.NewEncoder(w).Encode(map[string]any{"total": 5, "rows": []any{map[string]any{"$id": "1"}}})
	case r.Method == "GET" && r.URL.Path == ordersAttributes:
		_ = json.NewEncoder(w).Encode(map[string]any{"total": len(f.columns), "attributes": f.columns})
	case r.Method == "GET" && r.URL.Path == "/databases/main/collections/orders/indexes":
		_ = json.NewEncoder(w).Encode(map[string]any{"total": 0, "indexes": []any{}})
	case r.Method == "GET" && r.URL.Path == "/backups/policies":
		_ = json.NewEncoder(w).Encode(map[string]any{"total": 0, "policies": []map[string]any{}})
	case r.Method == "DELETE" && strings.HasPrefix(r.URL.Path, ordersAttributes+"/"):
		key := strings.TrimPrefix(r.URL.Path, ordersAttributes+"/")
		f.columns = slices.DeleteFunc(f.columns, func(column map[string]any) bool {
			return column["key"] == key
		})
		w.WriteHeader(http.StatusNoContent)
	case r.Method == "POST" && strings.HasPrefix(r.URL.Path, ordersAttributes+"/"):
		var column map[string]any
		_ = json.NewDecoder(r.Body).Decode(&column)
		column["type"] = strings.TrimPrefix(r.URL.Path, ordersAttributes+"/")
		column["status"] = "available"
		f.columns = append(f.columns, column)
		w.WriteHeader(http.StatusAccepted)
		_ = json.NewEncoder(w).Encode(column)
	case r.Method == "POST" || r.Method == "DELETE":
		w.WriteHeader(http.StatusCreated)
		_ = json.NewEncoder(w).Encode(map[string]any{})
	default:
		w.WriteHeader(http.StatusNotFound)
		_ = json.NewEncoder(w).Encode(map[string]any{"message": "not found", "code": 404})
	}
}

func (f *fakeTablesDB) mutations() []string {
	var mutations []string
	for _, request := range f.requests {
		if !strings.HasPrefix(request, "GET ") {
			mutations = append(mutations, request)
		}
	}

	return mutations
}

func pushTablesWith(
	t *testing.T,
	contents string,
	prune bool,
	reason string,
	interactive bool,
	prompter prompt.Prompter,
) (*fakeTablesDB, string, error) {
	t.Helper()

	fake := &fakeTablesDB{columns: []map[string]any{
		{"key": "total", "type": "double", "required": false, "array": false, "status": "available"},
		{"key": "legacy", "type": "string", "size": 64, "required": false, "array": false, "status": "available"},
	}}
	server := httptest.NewServer(fake)
	t.Cleanup(server.Close)

	restore := client.Reason
	client.Reason = reason
	t.Cleanup(func() { client.Reason = restore })

	all := app.Flags().All
	app.Flags().All = true
	t.Cleanup(func() { app.Flags().All = all })

	context := &pushContext{
		api:         client.New(server.URL, "test").SetProject("project"),
		local:       loadConfig(t, contents),
		prompter:    prompter,
		interactive: interactive,
	}

	var out bytes.Buffer
	command := &cobra.Command{}
	command.SetOut(&out)
	addPruneFlag(command)
	if prune {
		if err := command.Flags().Set("prune", "true"); err != nil {
			t.Fatal(err)
		}
	}

	err := context.pushTables(command, pushTable)

	return fake, out.String(), err
}

// A config that lists only `finance` deleted every other database in the
// project under --force. A database the config does not mention is unmanaged:
// listed, never deleted.
func TestPushTableLeavesDatabasesMissingFromTheConfig(t *testing.T) {
	fake, out, err := pushTablesWith(t, configWithOnlyFinance, false, "", false, prompt.Forced{Prompter: prompt.NonInteractive{}})
	if err != nil {
		t.Fatal(err)
	}

	if mutations := fake.mutations(); len(mutations) != 1 || mutations[0] != "POST /tablesdb" {
		t.Errorf("mutations = %v, want only the finance database created", mutations)
	}
	for _, want := range []string{"(not in config)", "--prune"} {
		if !strings.Contains(out, want) {
			t.Errorf("output does not mention %s:\n%s", want, out)
		}
	}
}

// --prune without a terminal is refused without --reason, before anything is
// written.
func TestPushTablePruneWithoutATerminalNeedsAReason(t *testing.T) {
	fake, _, err := pushTablesWith(t, configWithOnlyFinance, true, "", false, prompt.Forced{Prompter: prompt.NonInteractive{}})
	if err == nil || !strings.Contains(err.Error(), "--reason") {
		t.Fatalf("error = %v, want one naming --reason", err)
	}
	if mutations := fake.mutations(); len(mutations) != 0 {
		t.Errorf("mutations = %v, want none", mutations)
	}
}

// --prune deletes last, after showing what the deletion takes with it.
func TestPushTablePruneDeletesLastAndShowsTheImpact(t *testing.T) {
	fake, out, err := pushTablesWith(t, configWithOnlyFinance, true, "drop the old schema", false, prompt.Forced{Prompter: prompt.NonInteractive{}})
	if err != nil {
		t.Fatal(err)
	}

	mutations := fake.mutations()
	if len(mutations) != 2 || mutations[0] != "POST /tablesdb" || mutations[1] != "DELETE /tablesdb/main" {
		t.Errorf("mutations = %v, want finance created, then main deleted", mutations)
	}
	for _, want := range []string{"1 tables, 5 rows, backup: none", "no backup policy covers it"} {
		if !strings.Contains(out, want) {
			t.Errorf("output does not mention %q:\n%s", want, out)
		}
	}
}

// At a terminal, --prune still asks before deleting, and the answer defaults
// to no.
func TestPushTablePruneAtATerminalAsksBeforeDeleting(t *testing.T) {
	prompter := &prompt.Scripted{Confirms: map[string]bool{
		"Would you like to apply these changes?": true,
	}}

	fake, _, err := pushTablesWith(t, configWithOnlyFinance, true, "", true, prompter)
	if err != nil {
		t.Fatal(err)
	}

	if mutations := fake.mutations(); len(mutations) != 1 || mutations[0] != "POST /tablesdb" {
		t.Errorf("mutations = %v, want only the finance database created", mutations)
	}
	if len(prompter.Asked) != 2 || !strings.HasPrefix(prompter.Asked[1], "Delete the 1 resources") {
		t.Errorf("asked = %v, want the deletion confirmed separately", prompter.Asked)
	}
}

// Without --prune, a column the config dropped is not deleted and a column
// whose type changed is not recreated. Both are listed as left alone.
func TestPushTableLeavesColumnChangesThatLoseData(t *testing.T) {
	fake, out, err := pushTablesWith(t, configWithOrders, false, "", false, prompt.Forced{Prompter: prompt.NonInteractive{}})
	if err != nil {
		t.Fatal(err)
	}

	if mutations := fake.mutations(); len(mutations) != 0 {
		t.Errorf("mutations = %v, want none", mutations)
	}
	for _, want := range []string{"Left as they are", "legacy", "type changed from double to integer", "--prune"} {
		if !strings.Contains(out, want) {
			t.Errorf("output does not mention %q:\n%s", want, out)
		}
	}
}

// --prune with a reason deletes the dropped column and recreates the retyped
// one.
func TestPushTablePruneDeletesAndRecreatesColumns(t *testing.T) {
	fake, _, err := pushTablesWith(t, configWithOrders, true, "retype the totals", false, prompt.Forced{Prompter: prompt.NonInteractive{}})
	if err != nil {
		t.Fatal(err)
	}

	mutations := fake.mutations()
	want := []string{
		"DELETE " + ordersAttributes + "/legacy",
		"DELETE " + ordersAttributes + "/total",
		"POST " + ordersAttributes + "/integer",
	}
	if len(mutations) != len(want) {
		t.Fatalf("mutations = %v, want %v", mutations, want)
	}
	deletions := slices.Sorted(slices.Values(mutations[:2]))
	if !slices.Equal(append(deletions, mutations[2]), want) {
		t.Errorf("mutations = %v, want both columns deleted, then total created", mutations)
	}
}

// At a terminal, --prune asks separately before deleting columns, and
// declining leaves them as they are.
func TestPushTablePruneAtATerminalAsksBeforeDeletingColumns(t *testing.T) {
	prompter := &prompt.Scripted{}

	fake, out, err := pushTablesWith(t, configWithOrders, true, "", true, prompter)
	if err != nil {
		t.Fatal(err)
	}

	if mutations := fake.mutations(); len(mutations) != 0 {
		t.Errorf("mutations = %v, want none", mutations)
	}
	if len(prompter.Asked) != 1 || !strings.HasPrefix(prompter.Asked[0], "Delete the 2 resources") {
		t.Errorf("asked = %v, want the deletion of both columns confirmed", prompter.Asked)
	}
	if !strings.Contains(out, "Nothing was deleted") {
		t.Errorf("output does not say nothing was deleted:\n%s", out)
	}
}

func loadProbeConfig(t *testing.T) *config.Local {
	t.Helper()

	return loadConfig(t, configWithOneSite)
}

func loadConfig(t *testing.T, contents string) *config.Local {
	t.Helper()

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}

	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}

	return local
}

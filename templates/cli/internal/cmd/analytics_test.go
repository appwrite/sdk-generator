package cmd

import (
	"bytes"
	"encoding/json"
	"io"
	"net/http"
	"net/http/httptest"
	"os"
	"path/filepath"
	"strings"
	"sync"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/app"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
	"github.com/spf13/cobra"
)

// The list response names the array `properties` while the config names it
// `analytics`. Reading the response under the config's key finds nothing, so
// the pull reports an empty project and wipes the configured properties.
func TestPullAnalyticsReadsThePropertiesArray(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodGet || !strings.HasSuffix(r.URL.Path, "/analytics/properties") {
			http.NotFound(w, r)

			return
		}
		_, _ = w.Write([]byte(`{"total":1,"properties":[{
			"$id":"web","$createdAt":"2026-10-01T00:00:00.000+00:00",
			"$updatedAt":"2026-10-01T00:00:00.000+00:00","name":"Web",
			"domain":"example.com","enabled":true,"allowedOrigins":["https://example.com"],
			"accessedAt":"","firstAccessedAt":""}]}`))
	}))
	t.Cleanup(server.Close)

	path := analyticsProject(t, server.URL, `[]`)
	out := runAnalytics(t, func(command *cobra.Command) error {
		return runPullResource(command, pullResourceNamed(t, "analytics"))
	})

	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	entries := local.ResourceEntries("analytics")
	if len(entries) != 1 {
		t.Fatalf("pulled %d properties, want 1:\n%s", len(entries), out)
	}
	if got := strings.Join(entries[0].Keys(), ","); got != "$id,name,domain,enabled,allowedOrigins" {
		t.Errorf("pulled keys = %s, want only the ones the config owns", got)
	}
	if !strings.Contains(out, "Pulling analytics property Web") {
		t.Errorf("progress line does not name the property:\n%s", out)
	}
}

// Analytics is not a service every server runs. A 404 on the list must skip
// the pull and leave the configured properties alone, or `pull all` against
// such a server stops before the resources after it.
func TestPullAnalyticsSkipsAServerWithoutTheService(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, _ *http.Request) {
		w.WriteHeader(http.StatusNotFound)
		_, _ = w.Write([]byte(`{"message":"Route not found","code":404,"type":"general_route_not_found"}`))
	}))
	t.Cleanup(server.Close)

	path := analyticsProject(t, server.URL, `[{"$id":"web","name":"Web"}]`)

	var failure error
	out := runAnalytics(t, func(command *cobra.Command) error {
		failure = runPullResource(command, pullResourceNamed(t, "analytics"))

		return nil
	})
	if failure != nil {
		t.Fatalf("pull failed on a server without analytics: %v", failure)
	}
	if !strings.Contains(out, "does not offer analytics properties") {
		t.Errorf("the skip was not explained:\n%s", out)
	}

	local, err := config.LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if entries := local.ResourceEntries("analytics"); len(entries) != 1 {
		t.Errorf("config holds %d properties after a skipped pull, want the 1 it had", len(entries))
	}
}

// A new property is created with its id; an existing one is patched without
// it, and a field the config leaves out is not sent.
func TestPushAnalyticsCreatesAndPatches(t *testing.T) {
	type request struct {
		Method string
		Path   string
		Body   map[string]any
	}

	var mutex sync.Mutex
	var writes []request

	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodGet {
			if strings.HasSuffix(r.URL.Path, "/analytics/properties/existing") {
				_, _ = w.Write([]byte(`{"$id":"existing","name":"Old","domain":"old.example.com",` +
					`"enabled":true,"allowedOrigins":[]}`))

				return
			}
			w.WriteHeader(http.StatusNotFound)
			_, _ = w.Write([]byte(`{"message":"not found","code":404}`))

			return
		}

		raw, _ := io.ReadAll(r.Body)
		body := map[string]any{}
		_ = json.Unmarshal(raw, &body)

		mutex.Lock()
		writes = append(writes, request{Method: r.Method, Path: r.URL.Path, Body: body})
		mutex.Unlock()

		_, _ = w.Write([]byte(`{}`))
	}))
	t.Cleanup(server.Close)

	analyticsProject(t, server.URL, `[
		{"$id":"fresh","name":"Fresh","domain":"fresh.example.com","enabled":true},
		{"$id":"existing","name":"Renamed"}
	]`)

	runAnalytics(t, func(command *cobra.Command) error {
		return runPushSimple(command, simpleResourceNamed(t, "analytics"))
	})

	if len(writes) != 2 {
		t.Fatalf("push sent %d writes, want a create and a patch: %+v", len(writes), writes)
	}

	create := writes[0]
	if create.Method != http.MethodPost || !strings.HasSuffix(create.Path, "/analytics/properties") {
		t.Errorf("first write = %s %s, want POST /analytics/properties", create.Method, create.Path)
	}
	if create.Body["propertyId"] != "fresh" || create.Body["domain"] != "fresh.example.com" {
		t.Errorf("create body = %v, want the id and domain from the config", create.Body)
	}

	patch := writes[1]
	if patch.Method != http.MethodPatch || !strings.HasSuffix(patch.Path, "/analytics/properties/existing") {
		t.Errorf("second write = %s %s, want PATCH /analytics/properties/existing", patch.Method, patch.Path)
	}
	if patch.Body["name"] != "Renamed" {
		t.Errorf("patch body = %v, want the new name", patch.Body)
	}
	for _, key := range []string{"propertyId", "domain", "enabled", "allowedOrigins"} {
		if _, sent := patch.Body[key]; sent {
			t.Errorf("patch sent %q, which the config does not set, so it would overwrite the server's value", key)
		}
	}
}

// analyticsProject writes a logged-in session and a project config pointing at
// endpoint, with the given analytics array, and returns the config path.
func analyticsProject(t *testing.T, endpoint, analytics string) string {
	t.Helper()

	endpoint += "/v1"
	preferencesWith(t, `{"current":"s","s":{"endpoint":"`+endpoint+`","key":"secret"}}`)

	path := filepath.Join(t.TempDir(), config.LocalFileName)
	contents := `{"projectId":"probe","endpoint":"` + endpoint + `","analytics":` + analytics + `}`
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}

	restore := config.LocalFile
	config.LocalFile = path
	t.Cleanup(func() { config.LocalFile = restore })

	return path
}

// runAnalytics runs a pull or push non-interactively and returns its output.
func runAnalytics(t *testing.T, run func(*cobra.Command) error) string {
	t.Helper()

	restoreAll, restoreForce := app.Flags().All, app.Flags().Force
	app.Flags().All, app.Flags().Force = true, true
	t.Cleanup(func() { app.Flags().All, app.Flags().Force = restoreAll, restoreForce })

	out := &bytes.Buffer{}
	command := &cobra.Command{}
	command.SetOut(out)
	command.SetErr(out)

	if err := run(command); err != nil {
		t.Fatalf("%v\n%s", err, out)
	}

	return out.String()
}

func pullResourceNamed(t *testing.T, name string) flatResource {
	t.Helper()

	for _, resource := range flatResources {
		if resource.Name == name {
			return resource
		}
	}
	t.Fatalf("no pull resource named %q", name)

	return flatResource{}
}

func simpleResourceNamed(t *testing.T, name string) simpleResource {
	t.Helper()

	for _, resource := range simpleResources() {
		if resource.Name == name {
			return resource
		}
	}
	t.Fatalf("no push resource named %q", name)

	return simpleResource{}
}

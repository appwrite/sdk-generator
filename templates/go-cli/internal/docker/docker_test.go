package docker

import (
	"os"
	"path/filepath"
	"strings"
	"testing"
	"time"

	"github.com/appwrite/appwrite-cli-go/internal/config"
)

// These cover the parts of the emulation that are logic rather than
// subprocess management: runtime naming, source collection, the debounce
// queue, and the tar round trip. Anything that actually invokes docker is
// exercised by hand against a real engine, not here.

func TestRuntimeNameDropsOnlyTheVersion(t *testing.T) {
	for runtime, wantName := range map[string]string{
		"node-22.0":      "node",
		"python-ml-3.11": "python-ml",
		"go-1.23":        "go",
		"dart":           "dart",
	} {
		function := config.Function{Runtime: runtime}
		if got := function.RuntimeName(); got != wantName {
			t.Errorf("RuntimeName(%q) = %q, want %q", runtime, got, wantName)
		}
	}

	// python-ml is the case a naive split-on-first-dash gets wrong, and it is
	// a real runtime with its own image.
	if _, ok := Tool(config.Function{Runtime: "python-ml-3.11"}.RuntimeName()); !ok {
		t.Error("python-ml did not resolve to a known tool")
	}
}

// TestResolveResourcePathIsAbsolute pins the fix for a bug the unit tests could
// not see: a config loaded by its bare filename left a relative function
// directory, and Docker read the relative bind-mount source as a NAMED VOLUME.
// The error it prints names volume-naming rules, not the path, so nothing about
// it points at the cause.
func TestResolveResourcePathIsAbsolute(t *testing.T) {
	local, function := writeProject(t, map[string]string{
		"appwrite.config.json":     projectConfig,
		"functions/fn/src/main.js": "x",
	})

	resolved := local.ResolveResourcePath("functions", function.Path)
	if !filepath.IsAbs(resolved) {
		t.Errorf("ResolveResourcePath = %q, want an absolute path", resolved)
	}
}

func TestImageName(t *testing.T) {
	function := config.Function{Runtime: "node-22.0"}

	want := "openruntimes/node:" + OpenRuntimesVersion + "-22.0"
	if got := ImageName(function); got != want {
		t.Errorf("ImageName = %q, want %q", got, want)
	}
}

// writeProject lays out a config plus a function directory and returns the
// loaded config.
func writeProject(t *testing.T, files map[string]string) (*config.Local, config.Function) {
	t.Helper()

	root := t.TempDir()
	for name, contents := range files {
		path := filepath.Join(root, filepath.FromSlash(name))
		if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
			t.Fatal(err)
		}
		if err := os.WriteFile(path, []byte(contents), 0o644); err != nil {
			t.Fatal(err)
		}
	}

	local, err := config.LoadLocal(filepath.Join(root, config.LocalFileName))
	if err != nil {
		t.Fatal(err)
	}

	function, err := local.Function("fn")
	if err != nil {
		t.Fatal(err)
	}

	return local, function
}

const projectConfig = `{
    "projectId": "p",
    "functions": [
        {
            "$id": "fn",
            "name": "Fn",
            "runtime": "node-22.0",
            "entrypoint": "src/main.js",
            "path": "functions/fn",
            "commands": "npm install"
        }
    ]
}`

func TestCollectSourceAppliesGitignoreAndAlwaysDropsAppwrite(t *testing.T) {
	local, function := writeProject(t, map[string]string{
		"appwrite.config.json":                projectConfig,
		"functions/fn/src/main.js":            "x",
		"functions/fn/.gitignore":             "node_modules\n*.log\n",
		"functions/fn/node_modules/dep/i.js":  "x",
		"functions/fn/debug.log":              "x",
		"functions/fn/.appwrite/build.tar.gz": "x",
		"functions/fn/.env":                   "A=1",
	})

	source, err := CollectSource(local, function)
	if err != nil {
		t.Fatal(err)
	}

	kept := map[string]bool{}
	for _, file := range source.Files {
		kept[file] = true
	}

	for _, want := range []string{"src/main.js", ".gitignore", ".env"} {
		if !kept[want] {
			t.Errorf("%q should have been collected (got %v)", want, source.Files)
		}
	}
	for _, unwanted := range []string{
		"node_modules/dep/i.js", "debug.log", ".appwrite/build.tar.gz",
	} {
		if kept[unwanted] {
			t.Errorf("%q should have been ignored (got %v)", unwanted, source.Files)
		}
	}
}

// TestFunctionIgnoreReplacesGitignore pins that the two are not combined: a
// config that sets `ignore` silently stops honouring .gitignore.
func TestFunctionIgnoreReplacesGitignore(t *testing.T) {
	configWithIgnore := `{
    "projectId": "p",
    "functions": [
        {
            "$id": "fn",
            "name": "Fn",
            "runtime": "node-22.0",
            "entrypoint": "src/main.js",
            "path": "functions/fn",
            "ignore": "*.md"
        }
    ]
}`

	local, function := writeProject(t, map[string]string{
		"appwrite.config.json":        configWithIgnore,
		"functions/fn/src/main.js":    "x",
		"functions/fn/.gitignore":     "node_modules\n",
		"functions/fn/node_modules/a": "x",
		"functions/fn/README.md":      "x",
	})

	source, err := CollectSource(local, function)
	if err != nil {
		t.Fatal(err)
	}

	kept := map[string]bool{}
	for _, file := range source.Files {
		kept[file] = true
	}

	if kept["README.md"] {
		t.Error("*.md from the config's ignore field was not applied")
	}
	if !kept["node_modules/a"] {
		t.Error(".gitignore should be ignored entirely once `ignore` is set")
	}
}

func TestAssertSourceRejectsAnIgnoredEntrypoint(t *testing.T) {
	local, function := writeProject(t, map[string]string{
		"appwrite.config.json":     projectConfig,
		"functions/fn/src/main.js": "x",
		"functions/fn/.gitignore":  "src\n",
	})

	err := AssertSource(local, function)
	if err == nil {
		t.Fatal("an ignored entrypoint should be rejected")
	}
	// The message has to name the ignore rules, or the user looks for a
	// missing file that is right there.
	if !strings.Contains(err.Error(), "ignored by your local ignore rules") {
		t.Errorf("error was %q; it should point at the ignore rules", err)
	}
}

func TestAssertSourceAcceptsAValidFunction(t *testing.T) {
	local, function := writeProject(t, map[string]string{
		"appwrite.config.json":     projectConfig,
		"functions/fn/src/main.js": "x",
	})

	if err := AssertSource(local, function); err != nil {
		t.Fatalf("a valid function was rejected: %v", err)
	}
}

func TestAssertSourceRejectsAMissingEntrypoint(t *testing.T) {
	local, function := writeProject(t, map[string]string{
		"appwrite.config.json":      projectConfig,
		"functions/fn/src/other.js": "x",
	})

	if err := AssertSource(local, function); err == nil {
		t.Fatal("a missing entrypoint should be rejected")
	}
}

func TestIsDependencyChangeOnlyMatchesTheFunctionRoot(t *testing.T) {
	tool := SystemTools["node"]

	if !IsDependencyChange(tool, []string{"src/main.js", "package.json"}) {
		t.Error("a root package.json should force a rebuild")
	}
	// Matching at any depth would rebuild whenever a vendored package.json is
	// touched, which is most of node_modules.
	if IsDependencyChange(tool, []string{"vendor/lib/package.json"}) {
		t.Error("a nested package.json should not force a rebuild")
	}
	if IsDependencyChange(SystemTools["deno"], []string{"package.json"}) {
		t.Error("deno declares no dependency files")
	}
}

func TestQueueDebouncesAndCoalesces(t *testing.T) {
	queue := NewQueue()

	queue.Push("a")
	queue.Push("b")
	queue.Push("a")

	select {
	case files := <-queue.Events():
		if len(files) != 2 {
			t.Fatalf("files = %v, want two distinct entries", files)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("no reload fired")
	}
}

// TestQueueLockDefersUntilUnlock pins the behaviour that makes a mid-build edit
// survive: pushes during a lock are held, and unlocking fires them.
func TestQueueLockDefersUntilUnlock(t *testing.T) {
	queue := NewQueue()
	queue.Lock()

	queue.Push("a")
	if queue.Empty() {
		t.Error("a push during a lock should still be recorded")
	}

	select {
	case files := <-queue.Events():
		t.Fatalf("a locked queue fired %v", files)
	case <-time.After(500 * time.Millisecond):
	}

	queue.Unlock()

	select {
	case files := <-queue.Events():
		if len(files) != 1 || files[0] != "a" {
			t.Fatalf("files = %v, want [a]", files)
		}
	case <-time.After(2 * time.Second):
		t.Fatal("unlocking did not fire the pending reload")
	}
}

func TestQueueLockClearsPendingFiles(t *testing.T) {
	queue := NewQueue()
	queue.Push("a")
	queue.Lock()

	if !queue.Empty() {
		t.Error("Lock should clear the pending set")
	}
}

// TestTarRoundTripPreservesContentAndMode covers the hot-swap path: unpack the
// bundle, overlay sources, repack. A mode lost here makes an executable helper
// script inside the bundle non-executable, and the container fails to start.
func TestTarRoundTripPreservesContentAndMode(t *testing.T) {
	source := t.TempDir()
	writeFile(t, filepath.Join(source, "a.txt"), "hello", 0o644)
	writeFile(t, filepath.Join(source, "bin", "run.sh"), "#!/bin/sh\n", 0o755)

	archive := filepath.Join(t.TempDir(), "build.tar.gz")
	if err := createTarGz(archive, source); err != nil {
		t.Fatal(err)
	}

	destination := t.TempDir()
	if err := extractTarGz(archive, destination); err != nil {
		t.Fatal(err)
	}

	contents, err := os.ReadFile(filepath.Join(destination, "a.txt"))
	if err != nil {
		t.Fatal(err)
	}
	if string(contents) != "hello" {
		t.Errorf("a.txt = %q, want %q", contents, "hello")
	}

	info, err := os.Stat(filepath.Join(destination, "bin", "run.sh"))
	if err != nil {
		t.Fatal(err)
	}
	if info.Mode().Perm() != 0o755 {
		t.Errorf("run.sh mode = %v, want 0755", info.Mode().Perm())
	}
}

// TestExtractRefusesPathTraversal pins the guard on archive entry names. The
// bundle is the user's own build output, but it is still untrusted input.
func TestExtractRefusesPathTraversal(t *testing.T) {
	destination := t.TempDir()

	if _, err := safeJoin(destination, "../escaped.txt"); err == nil {
		t.Error("../escaped.txt should be refused")
	}
	if _, err := safeJoin(destination, "a/../../escaped.txt"); err == nil {
		t.Error("a/../../escaped.txt should be refused")
	}
	if _, err := safeJoin(destination, "a/b.txt"); err != nil {
		t.Errorf("a/b.txt should be allowed: %v", err)
	}
}

func TestQuoteShellArgumentEscapesQuotes(t *testing.T) {
	// The TypeScript concatenates this into `helpers/build.sh "<commands>"`,
	// so an unescaped quote ends the argument and the rest becomes shell.
	if got := quoteShellArgument(`npm run "build"`); got != `npm run \"build\"` {
		t.Errorf("got %q", got)
	}
}

func writeFile(t *testing.T, path, contents string, mode os.FileMode) {
	t.Helper()

	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte(contents), mode); err != nil {
		t.Fatal(err)
	}
}

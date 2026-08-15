package docker

import (
	"context"
	"net"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
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

func TestQuoteShellArgumentEscapesQuotes(t *testing.T) {
	// The TypeScript concatenates this into `helpers/build.sh "<commands>"`,
	// so an unescaped quote ends the argument and the rest becomes shell.
	if got := quoteShellArgument(`npm run "build"`); got != `npm run \"build\"` {
		t.Errorf("got %q", got)
	}
}

// `localhost` is not one address. A dev server bound only to [::1] leaves the
// v4 loopback free, so a v4-only probe called the port available; docker then
// published it on 0.0.0.0 without complaint and the browser, resolving localhost
// to ::1 first, reached the other service instead of the function.
func TestPortAvailableSeesAPortHeldOnIPv6Only(t *testing.T) {
	listener, err := net.Listen("tcp6", "[::1]:0")
	if err != nil {
		t.Skipf("no IPv6 loopback on this host: %v", err)
	}
	defer listener.Close()

	port := listener.Addr().(*net.TCPAddr).Port

	// The v4 loopback really is free, which is what made this invisible.
	v4, err := net.Listen("tcp4", net.JoinHostPort("127.0.0.1", strconv.Itoa(port)))
	if err != nil {
		t.Skipf("port %d is not v4-free, so this is not the case under test", port)
	}
	v4.Close()

	if PortAvailable(port) {
		t.Errorf("port %d reported available while held on [::1]", port)
	}
}

// And a free port is still free -- the v6 probe must not reject everything on a
// host where that family is unavailable.
func TestPortAvailableAcceptsAFreePort(t *testing.T) {
	listener, err := net.Listen("tcp", "127.0.0.1:0")
	if err != nil {
		t.Fatal(err)
	}
	port := listener.Addr().(*net.TCPAddr).Port
	listener.Close()

	if !PortAvailable(port) {
		t.Errorf("port %d reported in use after being released", port)
	}
}

// FindPort has to walk past what is taken.
func TestFindPortSkipsAPortInUse(t *testing.T) {
	listener, err := net.Listen("tcp", "127.0.0.1:0")
	if err != nil {
		t.Fatal(err)
	}
	defer listener.Close()

	taken := listener.Addr().(*net.TCPAddr).Port
	found, ok := FindPort(taken, taken+10)
	if !ok {
		t.Fatal("no port found in the range")
	}
	if found == taken {
		t.Errorf("returned the port that is in use (%d)", taken)
	}
}

// Readiness is a transport check, not a function invocation. A function may
// validly ignore GET /, require browser headers, stream indefinitely, or have
// side effects. Accepting the connection is enough to prove the published port
// is reachable.
func TestWaitForPortDoesNotRequireAnHTTPReply(t *testing.T) {
	listener, err := net.Listen("tcp", "127.0.0.1:0")
	if err != nil {
		t.Fatal(err)
	}
	defer listener.Close()

	accepted := make(chan struct{})
	go func() {
		connection, err := listener.Accept()
		if err != nil {
			return
		}
		defer connection.Close()
		close(accepted)

		// Keep the connection open without sending a reply. The old readiness
		// probe blocked on this until its read deadline and rejected the runtime.
		time.Sleep(time.Second)
	}()

	ctx, cancel := context.WithTimeout(context.Background(), 2*time.Second)
	defer cancel()

	port := listener.Addr().(*net.TCPAddr).Port
	if err := waitForPort(ctx, port); err != nil {
		t.Fatalf("an accepted connection was treated as not ready: %v", err)
	}

	select {
	case <-accepted:
	case <-ctx.Done():
		t.Fatal("readiness did not connect to the published port")
	}
}

// A function's variables carry a one-hour project key and a user JWT, packed
// into OPEN_RUNTIMES_HEADERS by `run`. argv is world-readable through
// /proc/<pid>/cmdline, so no value may appear there; the process environment is
// readable only by its owner, which is where they belong.
func TestEnvironmentArgumentsCarryNoValues(t *testing.T) {
	const secret = "standard_1a2b3c4d5e6f708192a3b4c5d6e7f8091a2b3c4d"

	keys := []string{"OPEN_RUNTIMES_HEADERS", "APPWRITE_FUNCTION_API_KEY"}
	variables := map[string]string{
		"OPEN_RUNTIMES_HEADERS":     secret,
		"APPWRITE_FUNCTION_API_KEY": secret,
	}

	arguments := environmentArguments(keys)
	for _, argument := range arguments {
		if strings.Contains(argument, secret) {
			t.Errorf("credential reached argv: %q", argument)
		}
		if strings.Contains(argument, "=") {
			t.Errorf("argv carries a value: %q", argument)
		}
	}

	// Bare `-e KEY` only forwards anything if docker can find KEY in its own
	// environment, so the two halves have to agree for the function to work.
	entries := environmentEntries(keys, variables)
	for _, key := range keys {
		forwarded := false
		for index, argument := range arguments {
			if argument == key && index > 0 && arguments[index-1] == "-e" {
				forwarded = true
			}
		}
		if !forwarded {
			t.Errorf("%s is not forwarded as `-e %s`", key, key)
		}

		present := false
		for _, entry := range entries {
			if entry == key+"="+secret {
				present = true
			}
		}
		if !present {
			t.Errorf("%s is missing from the environment entries", key)
		}
	}
}

// Order comes from keys rather than from map iteration, so a rerun with the same
// function produces the same command line.
func TestEnvironmentArgumentsKeepKeyOrder(t *testing.T) {
	arguments := environmentArguments([]string{"BRAVO", "ALPHA", "CHARLIE"})
	want := []string{"-e", "BRAVO", "-e", "ALPHA", "-e", "CHARLIE"}

	if strings.Join(arguments, " ") != strings.Join(want, " ") {
		t.Errorf("got %v, want %v", arguments, want)
	}
}

// An empty value must still be exported. Docker skips a bare `-e KEY` that is
// unset in its environment, so an empty entry is what keeps OPEN_RUNTIMES_SECRET
// set-but-empty in the container rather than absent.
func TestEnvironmentEntriesKeepsAnEmptyValue(t *testing.T) {
	entries := environmentEntries(
		[]string{"OPEN_RUNTIMES_SECRET"},
		map[string]string{"OPEN_RUNTIMES_SECRET": ""},
	)

	if len(entries) != 1 || entries[0] != "OPEN_RUNTIMES_SECRET=" {
		t.Errorf("got %v, want [OPEN_RUNTIMES_SECRET=]", entries)
	}
}

// Conformance harness for the CLI.
//
// Drives the built binary against the mock API and prints the values
// tests/e2e/RuntimeTest.php compares positionally. It asserts observable CLI behaviour
// only -- what the binary prints and what it exits with -- because that is the
// whole of the CLI's contract. The Go internals are covered by
// `go test ./internal/...`, which the same test's build step runs.
//
// Deliberately standalone: no shared file with the established CLI's harness and
// no Node. A harness written to drive two implementations has to tolerate both,
// and every tolerance is a divergence it can no longer see -- the shared version
// of this accepted `result <value>` and `result : <value>` interchangeably, so
// neither implementation was pinned to either.
//
// stdlib only, so it runs in a bare golang container with nothing installed.
package main

import (
	"archive/tar"
	"compress/gzip"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/rand"
	"crypto/x509"
	"encoding/json"
	"encoding/pem"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/http/httptest"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"slices"
	"sort"
	"strings"
)

// binary is the CLI under test, built by the test's build step next to this file.
const binary = "./appwrite"

// resultLabel is what the Go table renderer puts in front of a single value.
var resultLabel = regexp.MustCompile(`^result\s*:?\s+`)

func main() {
	// A sandboxed HOME keeps the run from reading or writing real credentials,
	// and keeps repeat runs independent of each other.
	home, err := os.MkdirTemp("", "appwrite-cli-conformance-")
	if err != nil {
		fail(err)
	}
	defer os.RemoveAll(home)

	harness := &harness{home: home}

	// Point the CLI at the mock API. Every command below reads this.
	harness.run("client",
		"--endpoint", "http://mockapi/v1",
		"--project-id", "console",
		"--key", "35y3h5h345",
		"--self-signed", "true",
	)

	// RuntimeTest.php discards every line up to and including `Test Started`, then
	// compares the rest positionally. Order matters, and nothing may be printed
	// before this.
	fmt.Println("Test Started")

	// Index 0 is the SDK header line RuntimeTest prepends -- the header string without the
	// trailing `accept`. Read out of the binary's own output rather than
	// hardcoded, so it fails if the CLI stops sending them.
	headers := harness.value("general", "headers")
	fmt.Println(strings.Split(headers, "; accept:")[0])

	for _, method := range []string{"get", "post", "put", "patch", "delete"} {
		fmt.Println(harness.value("foo", method,
			"--x", "string", "--y", "123", "--z", "string in array"))
	}

	for _, method := range []string{"get", "post", "put", "patch", "delete"} {
		fmt.Println(harness.value("bar", method,
			"--required", "string", "--xdefault", "123", "--z", "string in array"))
	}

	for _, file := range []string{"file.png", "large_file.mp4"} {
		fmt.Println(harness.value("general", "upload",
			"--x", "string", "--y", "123", "--z", "string in array",
			"--file", filepath.Join("..", "..", "..", "resources", file)))
	}
	fmt.Println(headers)

	// Run for their exit status, not their output: neither is in the expectation
	// list, so printing them would shift every later index.
	harness.run("general", "redirect")
	harness.run("general", "empty")

	// A non-finite numeric filter must be rejected locally rather than sent as
	// Infinity.
	for _, flag := range []string{"--filter", "--where"} {
		output := harness.runExpectingFailure("general", "list-rows", flag, "count>1e999")
		if !strings.Contains(strings.ToLower(output), "finite") {
			fail(fmt.Errorf("%s with a non-finite value should be rejected, got: %s", flag, output))
		}
	}

	harness.configValidation()
	fmt.Println("CLI_CONFIG_VALIDATION:passed")

	harness.typesDependencies()
	fmt.Println("CLI_TYPES_DEPENDENCIES:passed")
	harness.initApns()
	fmt.Println("CLI_INIT_APNS:passed")
	harness.initApnsApple()
	fmt.Println("CLI_INIT_APNS_APPLE:passed")
	harness.unicodeIgnoreDeployment()
	fmt.Println("CLI_UNICODE_IGNORE_DEPLOYMENT:passed")
	fmt.Println("CLI_CONFORMANCE:passed")
}

type harness struct {
	home string
}

func (h *harness) command(args ...string) *exec.Cmd {
	command := exec.Command(binary, args...)
	command.Env = append(os.Environ(),
		"HOME="+h.home,
		"USERPROFILE="+h.home,
		"XDG_CONFIG_HOME="+filepath.Join(h.home, ".config"),
	)

	return command
}

// Config rewrites must reject malformed documents before changing either the
// project file or any included resource file.
func (h *harness) configValidation() {
	cli, err := filepath.Abs(binary)
	if err != nil {
		fail(err)
	}

	cases := []struct {
		name     string
		document string
		included bool
	}{
		{"stray-brace", `{"projectId":"before","functions":[]},"tables":[{"$id":"orders"}]}`, false},
		{"trailing-text", `{"projectId":"before"} trailing`, false},
		{"second-object", `{"projectId":"before"} {"tables":[]}`, false},
		{"open-object", `{`, false},
		{"missing-brace", `{"projectId":"before"`, false},
		{"missing-nested-brace", `{"projectId":"before","custom":{"keep":1}`, false},
		{"missing-array-close", `{"tables":[{"$id":"orders"}`, false},
		{"mismatched-close", `{"tables":[{"$id":"orders"}] ]`, false},
		{"include-trailing-text", `[{"$id":"orders"}] trailing`, true},
		{"include-second-array", `[{"$id":"orders"}] [{"$id":"lost"}]`, true},
		{"include-missing-close", `[{"$id":"orders"}`, true},
		{"include-missing-nested-close", `[{"$id":"orders","custom":{"keep":1}]`, true},
	}
	for _, test := range cases {
		directory := filepath.Join(h.home, "config-validation", test.name)
		if err := os.MkdirAll(directory, 0o700); err != nil {
			fail(err)
		}
		files := map[string]string{"appwrite.config.json": test.document}
		want := "failed to read config file"
		if test.included {
			files["appwrite.config.json"] = `{"projectId":"before","includes":{"tables":"tables.json"}}`
			files["tables.json"] = test.document
			want = "failed to read included config"
		}
		for name, contents := range files {
			if err := os.WriteFile(filepath.Join(directory, name), []byte(contents), 0o600); err != nil {
				fail(err)
			}
		}
		command := h.command("client", "--project-id", "after")
		command.Path, command.Dir = cli, directory
		output, err := command.CombinedOutput()
		if err == nil || !strings.Contains(string(output), want) {
			fail(fmt.Errorf("config %s: expected %q and failure, got %v: %s", test.name, want, err, output))
		}
		for name, before := range files {
			after, err := os.ReadFile(filepath.Join(directory, name))
			if err != nil || string(after) != before {
				fail(fmt.Errorf("config %s: %s changed after rejection: %v\n%s", test.name, name, err, after))
			}
		}
	}

	// Whitespace after a complete value is valid, including for included arrays.
	directory := filepath.Join(h.home, "config-validation", "valid")
	if err := os.MkdirAll(directory, 0o700); err != nil {
		fail(err)
	}
	files := map[string]string{
		"appwrite.config.json": "{\"projectId\":\"before\",\"custom\":{\"keep\":9007199254740993},\"includes\":{\"tables\":\"tables.json\"}} \n\t",
		"tables.json":          "[{\"$id\":\"orders\",\"name\":\"Orders\",\"databaseId\":\"main\",\"columns\":[]}] \n\t",
	}
	for name, contents := range files {
		if err := os.WriteFile(filepath.Join(directory, name), []byte(contents), 0o600); err != nil {
			fail(err)
		}
	}
	command := h.command("client", "--project-id", "after")
	command.Path, command.Dir = cli, directory
	if output, err := command.CombinedOutput(); err != nil {
		fail(fmt.Errorf("valid config rewrite failed: %w\n%s", err, output))
	}
	for name, want := range map[string][]string{
		"appwrite.config.json": {`"projectId": "after"`, `"keep": 9007199254740993`, `"tables": "tables.json"`},
		"tables.json":          {`"$id": "orders"`, `"name": "Orders"`, `"databaseId": "main"`},
	} {
		contents, err := os.ReadFile(filepath.Join(directory, name))
		if err != nil {
			fail(err)
		}
		for _, expected := range want {
			if !strings.Contains(string(contents), expected) {
				fail(fmt.Errorf("valid config rewrite lost %s in %s: %s", expected, name, contents))
			}
		}
	}
}

// typesDependencies runs from a monorepo root, not the nested output directory.
// Only local configuration is used; no Appwrite project is read or written.
func (h *harness) typesDependencies() {
	cli, err := filepath.Abs(binary)
	if err != nil {
		fail(err)
	}

	cases := []struct {
		name       string
		manifest   string // Empty means package.json is absent.
		deno       bool
		dependency string
	}{
		{"no-package", "", false, "appwrite"},
		{"missing-sections", `{}`, false, "appwrite"},
		{"null-sections", `{"dependencies":null,"devDependencies":null}`, false, "appwrite"},
		{"empty-sections", `{"dependencies":{},"devDependencies":{}}`, false, "appwrite"},
		{"appwrite-dependencies", `{"dependencies":{"appwrite":"1"}}`, false, "appwrite"},
		{"node-dependencies", `{"dependencies":{"node-appwrite":"1"}}`, false, "node-appwrite"},
		{"appwrite-dev", `{"devDependencies":{"appwrite":"1"}}`, false, "appwrite"},
		{"node-dev", `{"devDependencies":{"node-appwrite":"1"}}`, false, "node-appwrite"},
		{"appwrite-dev-null-dependencies", `{"dependencies":null,"devDependencies":{"appwrite":"1"}}`, false, "appwrite"},
		{"node-dev-null-dependencies", `{"dependencies":null,"devDependencies":{"node-appwrite":"1"}}`, false, "node-appwrite"},
		{"node-null-dev", `{"dependencies":{"node-appwrite":"1"},"devDependencies":null}`, false, "node-appwrite"},
		{"node-dev-empty-dependencies", `{"dependencies":{},"devDependencies":{"node-appwrite":"1"}}`, false, "node-appwrite"},
		{"node-dev-unrelated-dependencies", `{"dependencies":{"other":"1"},"devDependencies":{"node-appwrite":"1"}}`, false, "node-appwrite"},
		{"runtime-node-before-dev-appwrite", `{"dependencies":{"node-appwrite":"1"},"devDependencies":{"appwrite":"1"}}`, false, "node-appwrite"},
		{"runtime-appwrite-before-dev-node", `{"dependencies":{"appwrite":"1"},"devDependencies":{"node-appwrite":"1"}}`, false, "appwrite"},
		{"runtime-package-preference", `{"dependencies":{"node-appwrite":"1","appwrite":"1"}}`, false, "appwrite"},
		{"dev-package-preference", `{"devDependencies":{"node-appwrite":"1","appwrite":"1"}}`, false, "appwrite"},
		{"console-preference", `{"devDependencies":{"@appwrite.io/console":"1","react-native-appwrite":"1","appwrite":"1","node-appwrite":"1"}}`, false, "@appwrite.io/console"},
		{"react-native-preference", `{"devDependencies":{"react-native-appwrite":"1","appwrite":"1","node-appwrite":"1"}}`, false, "react-native-appwrite"},
		{"empty-versions", `{"dependencies":{"appwrite":""},"devDependencies":{"appwrite":"","node-appwrite":"1"}}`, false, "node-appwrite"},
		{"deno-default", "", true, "npm:node-appwrite"},
		{"deno-null-sections", `{"dependencies":null,"devDependencies":null}`, true, "npm:node-appwrite"},
		{"dev-before-deno", `{"devDependencies":{"node-appwrite":"1"}}`, true, "node-appwrite"},
		{"invalid-package", `{`, false, "appwrite"},
		{"invalid-package-deno", `{`, true, "npm:node-appwrite"},
	}

	for _, test := range cases {
		directory := filepath.Join(h.home, "types", test.name)
		output := filepath.Join("packages", "appwrite-types", "src")
		if err := os.MkdirAll(filepath.Join(directory, output), 0o700); err != nil {
			fail(err)
		}
		files := map[string]string{
			"appwrite.config.json":                 `{"projectId":"offline","collections":[{"$id":"water","databaseId":"main","name":"Fixed Water Sources","attributes":[{"key":"name","type":"string","required":true}]}]}`,
			"packages/appwrite-types/package.json": `{"name":"appwrite-types","private":true}`,
		}
		if test.manifest != "" {
			files["package.json"] = test.manifest
		}
		if test.deno {
			files["deno.json"] = `{}`
		}
		for name, content := range files {
			if err := os.WriteFile(filepath.Join(directory, name), []byte(content), 0o600); err != nil {
				fail(err)
			}
		}

		command := h.command("types", output, "--language=ts", "--verbose")
		command.Path = cli
		command.Dir = directory
		if output, err := command.CombinedOutput(); err != nil {
			fail(fmt.Errorf("types %s: %w\n%s", test.name, err, output))
		}
		generated, err := os.ReadFile(filepath.Join(directory, output, "appwrite.d.ts"))
		if err != nil {
			fail(err)
		}
		want := "import type { Models } from '" + test.dependency + "';"
		if !strings.HasPrefix(string(generated), want+"\n") ||
			!strings.Contains(string(generated), "export type FixedWaterSources = Models.Row & {") ||
			!strings.Contains(string(generated), "name: string;") {
			fail(fmt.Errorf("types %s: expected %s and collection fields, got:\n%s", test.name, want, generated))
		}
	}

	// --config-file picks a config other than the nearest one, with its
	// includes resolved next to it.
	directory := filepath.Join(h.home, "types", "no-package")
	files := map[string]string{
		"envs/appwrite.config.prod.json": `{"projectId":"offline","includes":{"collections":"collections.json"}}`,
		"envs/collections.json":          `[{"$id":"wells","databaseId":"main","name":"Prod Wells","attributes":[{"key":"depth","type":"integer","required":true}]}]`,
	}
	if err := os.MkdirAll(filepath.Join(directory, "envs"), 0o700); err != nil {
		fail(err)
	}
	for name, content := range files {
		if err := os.WriteFile(filepath.Join(directory, name), []byte(content), 0o600); err != nil {
			fail(err)
		}
	}
	command := h.command("types", "prod-types", "--language=ts", "--config-file", "envs/appwrite.config.prod.json")
	command.Path = cli
	command.Dir = directory
	if output, err := command.CombinedOutput(); err != nil {
		fail(fmt.Errorf("types --config-file: %w\n%s", err, output))
	}
	generated, err := os.ReadFile(filepath.Join(directory, "prod-types", "appwrite.d.ts"))
	if err != nil {
		fail(err)
	}
	if !strings.Contains(string(generated), "export type ProdWells = Models.Row & {") ||
		strings.Contains(string(generated), "FixedWaterSources") {
		fail(fmt.Errorf("types --config-file: expected only the chosen config's collections, got:\n%s", generated))
	}

	// A missing --config-file points back at itself, or at a placeholder when
	// the path would need shell quoting.
	for path, want := range map[string]string{
		"envs/missing.json":      "init project --config-file envs/missing.json",
		"envs/missing prod.json": "init project --config-file <path>",
	} {
		command = h.command("types", "prod-types", "--language=ts", "--config-file", path)
		command.Path = cli
		command.Dir = directory
		output, err := command.CombinedOutput()
		if err == nil || !strings.Contains(string(output), want) {
			fail(fmt.Errorf("types with a missing --config-file %q: expected %q, got:\n%s", path, want, output))
		}
	}
}

// run executes the CLI and returns its stdout, requiring a zero exit.
func (h *harness) run(args ...string) string {
	output, err := h.command(args...).Output()
	if err != nil {
		stderr := ""
		var exit *exec.ExitError
		if errors.As(err, &exit) {
			stderr = string(exit.Stderr)
		}
		fail(fmt.Errorf("`%s` failed: %w\n%s%s", strings.Join(args, " "), err, output, stderr))
	}

	return strings.TrimSpace(string(output))
}

// runExpectingFailure executes the CLI, requires a non-zero exit, and returns
// everything it wrote.
func (h *harness) runExpectingFailure(args ...string) string {
	command := h.command(args...)
	var combined strings.Builder
	command.Stdout = &combined
	command.Stderr = &combined

	if err := command.Run(); err == nil {
		fail(fmt.Errorf("expected `%s` to fail", strings.Join(args, " ")))
	}

	return combined.String()
}

// value runs the CLI and returns the bare value out of its rendered output,
// which is what RuntimeTest.php compares against.
func (h *harness) value(args ...string) string {
	output := h.run(args...)

	for line := range strings.SplitSeq(output, "\n") {
		line = strings.TrimSpace(line)
		if line == "" {
			continue
		}

		// Only a leading `result` label is stripped. Splitting on the first
		// colon would eat into values that contain one, and most fixtures here
		// do: `GET:/v1/...` and `x-sdk-name: cli; ...`.
		return strings.TrimSpace(resultLabel.ReplaceAllString(line, ""))
	}

	fail(fmt.Errorf("no value in CLI output for `%s`", strings.Join(args, " ")))

	return ""
}

// initApns sets up APNs for a project folder against the mock API: both providers and the Apple
// platform on the first run, nothing new on a rerun, and nothing written for bad input. The App ID
// check needs an Apple sign-in, and without a terminal it is skipped.
func (h *harness) initApns() {
	project := h.apnsProject()
	defer os.RemoveAll(project.dir)

	key, err := ecdsa.GenerateKey(elliptic.P256(), rand.Reader)
	if err != nil {
		fail(err)
	}
	der, err := x509.MarshalPKCS8PrivateKey(key)
	if err != nil {
		fail(err)
	}
	project.write("AuthKey_ABCDE12345.p8", string(pem.EncodeToMemory(&pem.Block{Type: "PRIVATE KEY", Bytes: der})))

	first := project.run(true, "--key-path", "AuthKey_ABCDE12345.p8", "--team-id", "ABCDE12345", "--bundle-id", "io.appwrite.e2e")
	expectOutput(first,
		"Created provider APNs (io.appwrite.e2e)",
		"Created provider APNs sandbox (io.appwrite.e2e)",
		"APNs is set up for io.appwrite.e2e",
		"Added the Apple platform io.appwrite.e2e to the project",
		"Skipped the App ID check",
	)
	if strings.Contains(first, "AppwriteNotificationService") {
		fail(fmt.Errorf("APNs alone asked for a Notification Service Extension:\n%s", first))
	}

	again := project.run(true, "--bundle-id", "io.appwrite.e2e")
	expectOutput(again, "APNs is already set up for io.appwrite.e2e")
	if strings.Contains(again, "Added the Apple platform") || strings.Contains(again, "Created provider") {
		fail(fmt.Errorf("a rerun changed the project:\n%s", again))
	}

	expectOutput(project.run(false, "--bundle-id", "not a bundle id!", "--key-path", "AuthKey_ABCDE12345.p8", "--team-id", "ABCDE12345"), "is not a bundle ID")
	expectOutput(project.run(false, "--bundle-id", "io.appwrite.e2e", "--provider", "nope"), `unknown APNs key setup "nope"`)
	expectOutput(project.run(false, "--bundle-id", "io.appwrite.e2e", "--environment", "staging"), "--environment must be production, sandbox or all")
}

// initApnsApple signs in to the mock of Apple with an Apple ID and password, creates the key and
// registers the App ID, then reuses the saved sign-in: to check the App ID on a rerun, to turn on
// Push Notifications for an existing App ID, and to create keys until the team has no free slot.
func (h *harness) initApnsApple() {
	project := h.apnsProject()
	defer os.RemoveAll(project.dir)

	appleID := project.name + "@example.com"
	project.env = []string{"APPWRITE_APPLE_HOST=http://mockapi/apple"}
	first := project.runWith([]string{"APPWRITE_APPLE_ID=" + appleID, "APPWRITE_APPLE_PASSWORD=e2e-password"},
		true, "--create-key", "--register-app-id", "--bundle-id", "io.appwrite.e2e.apple")
	expectOutput(first,
		"Using the Apple ID from APPWRITE_APPLE_ID",
		"Using Apple team E2E Team (E2ETEAM123)",
		"Created provider APNs (io.appwrite.e2e.apple)",
		"Created provider APNs sandbox (io.appwrite.e2e.apple)",
		"Registered io.appwrite.e2e.apple as an App ID with Push Notifications on team E2ETEAM123",
	)
	keys, err := filepath.Glob(filepath.Join(project.dir, "AuthKey_*.p8"))
	if err != nil || len(keys) != 1 {
		fail(fmt.Errorf("want the created key saved in the project folder, found %v:\n%s", keys, first))
	}

	expectOutput(project.run(true, "--bundle-id", "io.appwrite.e2e.apple"),
		"APNs is already set up for io.appwrite.e2e.apple",
		"Using the saved Apple sign-in for "+appleID,
		"io.appwrite.e2e.apple is registered as an App ID with Push Notifications",
	)

	expectOutput(project.run(true, "--register-app-id", "--bundle-id", "io.appwrite.e2e.nopush", "--key-path", filepath.Base(keys[0]), "--team-id", "E2ETEAM123"),
		"Using the saved Apple sign-in for "+appleID,
		"Turned on Push Notifications for the App ID io.appwrite.e2e.nopush",
	)

	project.run(true, "--create-key", "--bundle-id", "io.appwrite.e2e.second")
	expectOutput(project.run(false, "--create-key", "--bundle-id", "io.appwrite.e2e.third"), "this team has no free slot")
}

type apnsProject struct {
	h    *harness
	dir  string
	name string
	env  []string
}

// apnsProject is a project folder with a project of its own: the mock keeps state across runs.
func (h *harness) apnsProject() *apnsProject {
	dir, err := os.MkdirTemp("", "appwrite-cli-apns-")
	if err != nil {
		fail(err)
	}
	project := &apnsProject{h: h, dir: dir, name: "apns-e2e-" + filepath.Base(dir)[len("appwrite-cli-apns-"):]}
	project.write("appwrite.config.json", `{"projectId":"`+project.name+`","endpoint":"http://mockapi/v1"}`)

	return project
}

func (p *apnsProject) write(name, contents string) {
	if err := os.WriteFile(filepath.Join(p.dir, name), []byte(contents), 0o600); err != nil {
		fail(err)
	}
}

func (p *apnsProject) run(wantSuccess bool, args ...string) string {
	return p.runWith(nil, wantSuccess, args...)
}

// runWith runs `init apns` in the project folder with extra environment variables.
func (p *apnsProject) runWith(env []string, wantSuccess bool, args ...string) string {
	absolute, err := filepath.Abs(binary)
	if err != nil {
		fail(err)
	}
	command := p.h.command(append([]string{"init", "apns"}, args...)...)
	command.Dir = p.dir
	command.Path = absolute
	command.Env = append(append(command.Env, p.env...), env...)
	var combined strings.Builder
	command.Stdout = &combined
	command.Stderr = &combined
	err = command.Run()
	if (err == nil) != wantSuccess {
		fail(fmt.Errorf("`init apns %s` exit = %v, want success %v:\n%s", strings.Join(args, " "), err, wantSuccess, combined.String()))
	}

	return combined.String()
}

func expectOutput(output string, want ...string) {
	for _, part := range want {
		if !strings.Contains(output, part) {
			fail(fmt.Errorf("init apns output is missing %q:\n%s", part, output))
		}
	}
}

func fail(err error) {
	fmt.Fprintln(os.Stderr, "conformance harness:", err)
	os.Exit(1)
}

// Inspect the multipart upload received by the API, so the same assertion
// covers pattern matching, directory traversal and deployment packaging.
func (h *harness) unicodeIgnoreDeployment() {
	cli, err := filepath.Abs(binary)
	if err != nil {
		fail(err)
	}

	type uploadedArchive struct {
		names []string
		err   error
	}
	uploaded := make(chan uploadedArchive, 1)
	function := `{"$id":"unicode","name":"Unicode","runtime":"node-22","entrypoint":"index.js"}`
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		switch {
		case r.Method == http.MethodGet && r.URL.Path == "/v1/health/version":
			fmt.Fprint(w, `{"version":"1.8.0"}`)
		case r.Method == http.MethodGet && r.URL.Path == "/v1/functions/unicode":
			fmt.Fprint(w, function)
		case r.Method == http.MethodPut && r.URL.Path == "/v1/functions/unicode":
			fmt.Fprint(w, function)
		case r.Method == http.MethodGet && r.URL.Path == "/v1/proxy/rules":
			fmt.Fprint(w, `{"total":1,"rules":[{"domain":"example.test"}]}`)
		case r.Method == http.MethodPost && r.URL.Path == "/v1/functions/unicode/deployments":
			file, _, err := r.FormFile("code")
			if err != nil {
				uploaded <- uploadedArchive{err: err}
				http.Error(w, err.Error(), http.StatusBadRequest)
				return
			}
			defer file.Close()
			compressed, err := gzip.NewReader(file)
			if err != nil {
				uploaded <- uploadedArchive{err: err}
				http.Error(w, err.Error(), http.StatusBadRequest)
				return
			}
			defer compressed.Close()
			archive := tar.NewReader(compressed)
			var names []string
			for {
				header, err := archive.Next()
				if err == io.EOF {
					break
				}
				if err != nil {
					uploaded <- uploadedArchive{err: err}
					http.Error(w, err.Error(), http.StatusBadRequest)
					return
				}
				if header.Typeflag == tar.TypeReg {
					names = append(names, header.Name)
				}
			}
			uploaded <- uploadedArchive{names: names}
			fmt.Fprint(w, `{"$id":"deployment","status":"ready"}`)
		default:
			http.Error(w, r.Method+" "+r.URL.Path, http.StatusNotFound)
		}
	}))
	defer server.Close()
	h.run("client", "--endpoint", server.URL+"/v1", "--key", "dummy")

	patterns := []string{"données/", "secret-é.env", "*.café", "日本/**/*.key", `\é.env`, "secret-e.env", "[é].txt", "*.log", "!résumé.log", "src/*.tmp"}
	for _, source := range []string{"gitignore", "config"} {
		directory := filepath.Join(h.home, "unicode-ignore", source)
		files := []string{"index.js", "données/prod.env", "secret-é.env", "x.café", "日本/a/b.key", "é.env", "secret-e.env", "é.txt", "résumé.log", "src/visible-é.js", "src/temp-é.tmp"}
		for _, name := range files {
			path := filepath.Join(directory, "fn", filepath.FromSlash(name))
			if err := os.MkdirAll(filepath.Dir(path), 0o700); err != nil {
				fail(err)
			}
			if err := os.WriteFile(path, []byte("dummy verification content"), 0o600); err != nil {
				fail(err)
			}
		}

		entry := map[string]any{"$id": "unicode", "name": "Unicode", "runtime": "node-22", "entrypoint": "index.js", "path": "fn"}
		expected := []string{"index.js", "résumé.log", "src/visible-é.js"}
		if source == "gitignore" {
			if err := os.WriteFile(filepath.Join(directory, "fn", ".gitignore"), []byte(strings.Join(patterns, "\n")+"\n"), 0o600); err != nil {
				fail(err)
			}
			expected = append(expected, ".gitignore")
		} else {
			entry["ignore"] = patterns
		}
		config, err := json.Marshal(map[string]any{"projectId": "unicode", "endpoint": server.URL + "/v1", "functions": []any{entry}})
		if err != nil {
			fail(err)
		}
		if err := os.WriteFile(filepath.Join(directory, "appwrite.config.json"), config, 0o600); err != nil {
			fail(err)
		}

		command := h.command("push", "function", "--function-id", "unicode", "--activate=false", "--async", "--force")
		command.Path = cli
		command.Dir = directory
		if output, err := command.CombinedOutput(); err != nil {
			fail(fmt.Errorf("push with Unicode %s rules: %w\n%s", source, err, output))
		}
		select {
		case archive := <-uploaded:
			if archive.err != nil {
				fail(archive.err)
			}
			sort.Strings(archive.names)
			sort.Strings(expected)
			if !slices.Equal(archive.names, expected) {
				fail(fmt.Errorf("uploaded files with Unicode %s rules = %q, want %q", source, archive.names, expected))
			}
		default:
			fail(fmt.Errorf("push with Unicode %s rules did not upload code", source))
		}
	}
}

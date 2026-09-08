// Conformance harness for the CLI.
//
// Drives the built binary against the mock API and prints the values
// tests/e2e/Base.php compares positionally. It asserts observable CLI behaviour
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
	"errors"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
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

	// Base.php discards every line up to and including `Test Started`, then
	// compares the rest positionally. Order matters, and nothing may be printed
	// before this.
	fmt.Println("Test Started")

	// Index 0 is Base::getExpectedSdkHeaders() -- the header string without the
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

	harness.typesDependencies()
	fmt.Println("CLI_TYPES_DEPENDENCIES:passed")
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
	)

	return command
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
// which is what Base.php compares against.
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

func fail(err error) {
	fmt.Fprintln(os.Stderr, "conformance harness:", err)
	os.Exit(1)
}

package cmd

import (
	"sort"
	"strings"
	"testing"

	"github.com/spf13/pflag"
)

// `types` and `generate` are hand-written rather than spec-derived, so
// surface_test.go's contract does not cover them -- they are on its handWritten
// allowlist alongside login, client and update. Their flags are still public
// surface (docs/go-cli/PLAN.md invariant 1), so they are pinned here instead.
//
// The expectations below were read off `appwrite types --help` and
// `appwrite generate --help` from the built TypeScript CLI, not from its
// source. Recapture with:
//
//	php example.php cli && cd examples/cli && bun dist/cli.cjs types --help

// flagSurface is the sorted "--name/-shorthand" list a command exposes,
// excluding help and anything inherited.
func flagSurface(t *testing.T, path ...string) []string {
	t.Helper()

	root := NewRootCommand()
	command, _, err := root.Find(path)
	if err != nil {
		t.Fatalf("finding %v: %v", path, err)
	}
	if command == root {
		t.Fatalf("%v did not resolve to its own command", path)
	}

	var flags []string
	command.Flags().VisitAll(func(flag *pflag.Flag) {
		if flag.Name == "help" || root.PersistentFlags().Lookup(flag.Name) != nil {
			return
		}

		entry := "--" + flag.Name
		if flag.Shorthand != "" {
			entry += "/-" + flag.Shorthand
		}
		flags = append(flags, entry)
	})
	sort.Strings(flags)

	return flags
}

func assertFlags(t *testing.T, got, want []string) {
	t.Helper()

	if strings.Join(got, " ") != strings.Join(want, " ") {
		t.Errorf("flags:\n  want %v\n  got  %v", want, got)
	}
}

func TestTypesCommandFlagSurface(t *testing.T) {
	assertFlags(t, flagSurface(t, "types"), []string{
		"--language/-l",
		"--strict/-s",
	})
}

func TestGenerateCommandFlagSurface(t *testing.T) {
	assertFlags(t, flagSurface(t, "generate"), []string{
		"--appwrite-import-source",
		"--import-extension",
		"--language/-l",
		"--output/-o",
		"--server",
	})
}

// TestTypesRequiresAnOutputDirectory pins the positional argument: the
// TypeScript declares it with Argument("<output-directory>"), which commander
// treats as required.
func TestTypesRequiresAnOutputDirectory(t *testing.T) {
	root := NewRootCommand()
	command, _, err := root.Find([]string{"types"})
	if err != nil {
		t.Fatal(err)
	}

	if err := command.Args(command, nil); err == nil {
		t.Error("types accepted no arguments; the output directory is required")
	}
	if err := command.Args(command, []string{"a", "b"}); err == nil {
		t.Error("types accepted two arguments; it takes exactly one")
	}
}

// TestGenerateDefaults pins the two flags that carry a default the user sees in
// help output.
func TestGenerateDefaults(t *testing.T) {
	root := NewRootCommand()
	command, _, err := root.Find([]string{"generate"})
	if err != nil {
		t.Fatal(err)
	}

	for name, want := range map[string]string{"output": "generated", "server": "auto"} {
		flag := command.Flags().Lookup(name)
		if flag == nil {
			t.Fatalf("--%s is missing", name)
		}
		if flag.DefValue != want {
			t.Errorf("--%s default = %q, want %q", name, flag.DefValue, want)
		}
	}
}

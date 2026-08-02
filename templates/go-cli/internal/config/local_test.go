package config

import (
	"os"
	"path/filepath"
	"strings"
	"testing"
)

// realConfig is a project config in the exact shape and key order the
// TypeScript CLI writes: CONFIG_KEY_ORDER at the top level, four-space
// indentation, no trailing newline.
//
// It deliberately includes a URL with an ampersand and a `>` in a description.
// Go's JSON encoder escapes both by default, which would rewrite a user's file
// on the next save.
const realConfig = `{
    "projectId": "68f2a1b4c5d6e7f80912",
    "projectName": "Acme Storefront",
    "endpoint": "https://cloud.staging.appwrite.io/v1",
    "settings": {
        "services": {
            "account": true
        }
    },
    "functions": [
        {
            "$id": "checkout",
            "name": "Checkout handler",
            "runtime": "node-22",
            "execute": [
                "any"
            ],
            "events": [],
            "schedule": "",
            "timeout": 15,
            "path": "functions/checkout",
            "entrypoint": "src/main.js",
            "commands": "npm install",
            "specification": "s-0.5vcpu-512mb"
        }
    ],
    "databases": [
        {
            "$id": "main",
            "name": "Main",
            "enabled": true
        }
    ],
    "collections": [
        {
            "$id": "orders",
            "databaseId": "main",
            "name": "Orders",
            "enabled": true,
            "documentSecurity": false,
            "$permissions": [
                "read(\"any\")"
            ],
            "attributes": [
                {
                    "key": "callbackUrl",
                    "type": "string",
                    "required": false,
                    "array": false,
                    "size": 2048,
                    "default": "https://acme.test/cb?a=1&b=2"
                },
                {
                    "key": "note",
                    "type": "string",
                    "required": false,
                    "array": false,
                    "size": 255,
                    "default": "qty > 1"
                }
            ],
            "indexes": []
        }
    ],
    "buckets": [
        {
            "$id": "invoices",
            "name": "Invoices",
            "enabled": true,
            "maximumFileSize": 30000000
        }
    ],
    "teams": [
        {
            "$id": "staff",
            "name": "Staff"
        }
    ]
}`

func writeConfig(t *testing.T, contents string) string {
	t.Helper()

	path := filepath.Join(t.TempDir(), LocalFileName)
	if err := os.WriteFile(path, []byte(contents), 0o600); err != nil {
		t.Fatal(err)
	}

	return path
}

// Invariant 2. This file is code-reviewed in user repositories, so a read
// followed by a write must not change a byte.
func TestLocalRoundTripIsByteIdentical(t *testing.T) {
	path := writeConfig(t, realConfig)

	local, err := LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if err := local.Write(); err != nil {
		t.Fatal(err)
	}

	written, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if string(written) != realConfig {
		t.Errorf("round-trip changed the file.\n--- want ---\n%s\n--- got ---\n%s", realConfig, written)
	}
}

// The characters Go's encoder escapes by default. A regression here silently
// rewrites URLs and descriptions in a user's committed config.
func TestLocalRoundTripPreservesHTMLCharacters(t *testing.T) {
	path := writeConfig(t, realConfig)

	local, err := LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if err := local.Write(); err != nil {
		t.Fatal(err)
	}

	written, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	for _, literal := range []string{"https://acme.test/cb?a=1&b=2", "qty > 1"} {
		if !strings.Contains(string(written), literal) {
			t.Errorf("literal %q was rewritten:\n%s", literal, written)
		}
	}
	// Go's encoder writes these by default; JSON.stringify never does.
	for _, escape := range []string{`\u0026`, `\u003e`, `\u003c`} {
		if strings.Contains(string(written), escape) {
			t.Errorf("output contains the escape %s, which the TypeScript CLI never writes:\n%s",
				escape, written)
		}
	}
}

// Keys written out of order must come back in canonical order, and unknown keys
// must survive -- a newer CLI may have written a field this build has no name
// for, and dropping it on the next write is data loss.
func TestOrderConfigKeys(t *testing.T) {
	const scrambled = `{
    "buckets": [
        {
            "$id": "b"
        }
    ],
    "futureField": "keep me",
    "projectName": "Acme",
    "projectId": "abc",
    "organizationId": "org"
}`

	path := writeConfig(t, scrambled)
	local, err := LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if err := local.Write(); err != nil {
		t.Fatal(err)
	}

	written, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}

	want := []string{`"organizationId"`, `"projectId"`, `"projectName"`, `"buckets"`, `"futureField"`}
	previous := -1
	for _, key := range want {
		index := strings.Index(string(written), key)
		if index < 0 {
			t.Fatalf("%s missing from output:\n%s", key, written)
		}
		if index < previous {
			t.Errorf("%s is out of canonical order:\n%s", key, written)
		}
		previous = index
	}
}

// An empty resource array and an absent key mean the same thing, and writing
// `"teams": []` into a config the user never gave teams to is diff noise.
func TestEmptyResourceArraysArePruned(t *testing.T) {
	path := writeConfig(t, `{
    "projectId": "abc",
    "teams": [],
    "functions": []
}`)

	local, err := LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if err := local.Write(); err != nil {
		t.Fatal(err)
	}

	written, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if strings.Contains(string(written), "teams") || strings.Contains(string(written), "functions") {
		t.Errorf("empty resource arrays were not pruned:\n%s", written)
	}
}

// A split config must stay split: inlining the resources back into the root
// would silently reorganise the user's repository layout.
func TestIncludesRoundTripStaySplit(t *testing.T) {
	directory := t.TempDir()
	path := filepath.Join(directory, LocalFileName)

	const root = `{
    "projectId": "abc",
    "includes": {
        "functions": "config/functions.json"
    }
}`
	const functions = `[
    {
        "$id": "checkout",
        "name": "Checkout"
    }
]`

	if err := os.WriteFile(path, []byte(root), 0o600); err != nil {
		t.Fatal(err)
	}
	if err := os.MkdirAll(filepath.Join(directory, "config"), 0o700); err != nil {
		t.Fatal(err)
	}
	functionsPath := filepath.Join(directory, "config", "functions.json")
	if err := os.WriteFile(functionsPath, []byte(functions), 0o600); err != nil {
		t.Fatal(err)
	}

	local, err := LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}

	// Callers see one merged document regardless of the on-disk split.
	if _, ok := local.Data.Get("functions"); !ok {
		t.Fatal("included functions were not merged into the config")
	}

	if err := local.Write(); err != nil {
		t.Fatal(err)
	}

	writtenRoot, err := os.ReadFile(path)
	if err != nil {
		t.Fatal(err)
	}
	if strings.Contains(string(writtenRoot), "checkout") {
		t.Errorf("included resource was inlined into the root config:\n%s", writtenRoot)
	}
	if !strings.Contains(string(writtenRoot), `"includes"`) {
		t.Errorf("includes map was dropped:\n%s", writtenRoot)
	}

	writtenFunctions, err := os.ReadFile(functionsPath)
	if err != nil {
		t.Fatal(err)
	}
	if string(writtenFunctions) != functions {
		t.Errorf("included file changed.\n--- want ---\n%s\n--- got ---\n%s", functions, writtenFunctions)
	}
}

// An include pointing outside the project would let a config overwrite an
// arbitrary file on the next write.
func TestIncludesCannotEscapeTheProjectDirectory(t *testing.T) {
	path := writeConfig(t, `{
    "projectId": "abc",
    "includes": {
        "functions": "../../escape.json"
    }
}`)

	if _, err := LoadLocal(path); err == nil {
		t.Fatal("expected an error for an include outside the project directory")
	}
}

// A malformed project config is an error, not an empty document: treating a
// typo'd config as absent would let `push` deploy nothing and report success.
func TestLoadLocalRejectsMalformedConfig(t *testing.T) {
	path := writeConfig(t, "{not json")

	if _, err := LoadLocal(path); err == nil {
		t.Fatal("expected an error for a malformed config")
	}
}

func TestLocalWriteIsPrivate(t *testing.T) {
	path := writeConfig(t, `{"projectId":"abc"}`)

	local, err := LoadLocal(path)
	if err != nil {
		t.Fatal(err)
	}
	if err := local.Write(); err != nil {
		t.Fatal(err)
	}

	info, err := os.Stat(path)
	if err != nil {
		t.Fatal(err)
	}
	if perm := info.Mode().Perm(); perm != 0o600 {
		t.Errorf("config mode = %o, want 600", perm)
	}
}

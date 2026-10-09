package apple

import (
	"context"
	"errors"
	"os"
	"path/filepath"
	"runtime"
	"strings"
	"testing"
)

func TestSupportedNode(t *testing.T) {
	for version, supported := range map[string]bool{
		"v18.0.0":  true,
		"v22.11.0": true,
		"v16.20.2": false,
		"v8.9.0":   false,
		"garbage":  false,
	} {
		err := supportedNode(version)
		if (err == nil) != supported {
			t.Errorf("supportedNode(%q) = %v", version, err)
		}
		if err != nil && !errors.Is(err, ErrUnavailable) {
			t.Errorf("supportedNode(%q) is not ErrUnavailable: %v", version, err)
		}
	}
}

func TestDecodeResult(t *testing.T) {
	key, err := decodeResult([]byte(`{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"-----BEGIN PRIVATE KEY-----"}`))
	if err != nil || key.KeyID != "KEY1234567" || key.TeamID != "ABCDE12345" {
		t.Errorf("key = %+v, %v", key, err)
	}

	_, err = decodeResult([]byte(`{"error":"max-keys","keys":[{"id":"OLD1234567","name":"Old","canRevoke":true}]}`))
	var maxKeys *MaxKeysError
	if !errors.As(err, &maxKeys) || len(maxKeys.Keys) != 1 || maxKeys.Keys[0].ID != "OLD1234567" {
		t.Errorf("max keys = %v", err)
	}

	if _, err := decodeResult([]byte(`{"error":"failed","message":"Invalid username and password combination"}`)); err == nil ||
		err.Error() != "Invalid username and password combination" {
		t.Errorf("failure = %v", err)
	}
	if _, err := decodeResult([]byte(`{}`)); err == nil {
		t.Error("an empty result was accepted")
	}
}

// fakeTools writes node and npm stand-ins that log their arguments. The node
// stand-in answers --version and writes result to the --out file.
func fakeTools(t *testing.T, version, result string) (string, string, string) {
	t.Helper()
	if runtime.GOOS == "windows" {
		t.Skip("shell stand-ins")
	}
	bin := t.TempDir()
	log := filepath.Join(bin, "log")
	node := `#!/bin/sh
if [ "$1" = "--version" ]; then echo "` + version + `"; exit 0; fi
echo "node $* EXPO_NO_KEYCHAIN=$EXPO_NO_KEYCHAIN" >> "` + log + `"
while [ $# -gt 0 ]; do
  if [ "$1" = "--out" ]; then printf '%s' '` + result + `' > "$2"; fi
  shift
done
`
	npm := `#!/bin/sh
echo "npm $*" >> "` + log + `"
mkdir -p node_modules/@expo/apple-utils && echo '{}' > node_modules/@expo/apple-utils/package.json
`
	for name, script := range map[string]string{"node": node, "npm": npm} {
		if err := os.WriteFile(filepath.Join(bin, name), []byte(script), 0o700); err != nil {
			t.Fatal(err)
		}
	}

	return filepath.Join(bin, "node"), filepath.Join(bin, "npm"), log
}

func TestCreateKeyInstallsOnceAndRunsTheHelper(t *testing.T) {
	node, npm, log := fakeTools(t, "v20.1.0", `{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}`)
	helper := Helper{Dir: filepath.Join(t.TempDir(), "apple"), Node: node, Npm: npm}

	for range 2 {
		key, err := helper.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push")
		if err != nil || key.KeyID != "KEY1234567" || key.P8 != "pem" {
			t.Fatalf("key = %+v, %v", key, err)
		}
	}

	calls, _ := os.ReadFile(log)
	lines := strings.Split(strings.TrimSpace(string(calls)), "\n")
	if len(lines) != 3 || !strings.HasPrefix(lines[0], "npm ci --ignore-scripts") {
		t.Fatalf("calls:\n%s", calls)
	}
	for _, line := range lines[1:] {
		if !strings.Contains(line, "helper.js --team-id ABCDE12345 --name Appwrite Push --out ") ||
			!strings.HasSuffix(line, "EXPO_NO_KEYCHAIN=1") {
			t.Errorf("helper call = %s", line)
		}
	}
	for _, name := range []string{"helper.js", "package.json", "package-lock.json"} {
		if _, err := os.Stat(filepath.Join(helper.Dir, name)); err != nil {
			t.Errorf("%s was not installed: %v", name, err)
		}
	}
}

func TestCreateKeyNeedsARecentNode(t *testing.T) {
	node, npm, log := fakeTools(t, "v16.20.2", `{}`)
	helper := Helper{Dir: t.TempDir(), Node: node, Npm: npm}

	if _, err := helper.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push"); !errors.Is(err, ErrUnavailable) {
		t.Fatalf("err = %v", err)
	}
	if _, err := os.Stat(log); err == nil {
		t.Error("ran npm or the helper with an old Node.js")
	}

	missing := Helper{Dir: t.TempDir(), Node: filepath.Join(t.TempDir(), "node")}
	if _, err := missing.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push"); !errors.Is(err, ErrUnavailable) {
		t.Fatalf("missing node err = %v", err)
	}
}

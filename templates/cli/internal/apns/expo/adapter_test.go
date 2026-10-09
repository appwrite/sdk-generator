package expo

import (
	"context"
	"errors"
	"os"
	"path/filepath"
	"runtime"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
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
		if err != nil && !errors.Is(err, apns.ErrUnavailable) {
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
	var maxKeys *apns.MaxKeysError
	if !errors.As(err, &maxKeys) || len(maxKeys.Keys) != 1 || maxKeys.Keys[0].ID != "OLD1234567" || maxKeys.Keys[0].Name != "Old" {
		t.Errorf("max keys = %v", err)
	}

	_, err = decodeResult([]byte(`{"error":"invalid-credentials","message":"Invalid username and password combination"}`))
	if !errors.Is(err, apns.ErrInvalidCredentials) {
		t.Errorf("credentials = %v", err)
	}

	if _, err := decodeResult([]byte(`{"error":"failed","message":"Apple is down"}`)); err == nil || err.Error() != "Apple is down" {
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
echo "node $* EXPO_NO_KEYCHAIN=$EXPO_NO_KEYCHAIN EXPO_APPLE_ID=$EXPO_APPLE_ID" >> "` + log + `"
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
	adapter := &Adapter{Node: node, Npm: npm, Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	request := apns.Request{
		TeamID:      "ABCDE12345",
		Name:        "Appwrite Push",
		Environment: apns.EnvironmentAll,
		AppleID:     "dev@example.com",
		SessionDir:  filepath.Join(t.TempDir(), "expo"),
	}

	for range 2 {
		key, err := adapter.CreateKey(context.Background(), request)
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
		if !strings.Contains(line, "helper.js --name Appwrite Push --out ") ||
			!strings.HasSuffix(line, "--team-id ABCDE12345 EXPO_NO_KEYCHAIN=1 EXPO_APPLE_ID=dev@example.com") {
			t.Errorf("helper call = %s", line)
		}
	}
	for _, name := range []string{"helper.js", "package.json", "package-lock.json"} {
		if _, err := os.Stat(filepath.Join(request.SessionDir, name)); err != nil {
			t.Errorf("%s was not installed: %v", name, err)
		}
	}
}

// Without a team, the helper gets no --team-id and apple-utils picks the
// account's only team or asks, the flow the CLI uses when no team is known.
func TestCreateKeyWithoutATeamNotesTheEnvironment(t *testing.T) {
	node, npm, log := fakeTools(t, "v20.1.0", `{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}`)
	adapter := &Adapter{Node: node, Npm: npm, Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	var logged []string
	request := apns.Request{
		Name:        "Appwrite Push",
		Environment: apns.EnvironmentSandbox,
		SessionDir:  t.TempDir(),
		Log:         func(format string, args ...any) { logged = append(logged, format) },
	}

	if _, err := adapter.CreateKey(context.Background(), request); err != nil {
		t.Fatal(err)
	}
	if len(logged) != 1 || !strings.Contains(logged[0], "both APNs environments") {
		t.Errorf("logged %v", logged)
	}
	calls, _ := os.ReadFile(log)
	lines := strings.Split(strings.TrimSpace(string(calls)), "\n")
	helper := lines[len(lines)-1]
	if !strings.Contains(helper, "helper.js --name Appwrite Push --out ") || strings.Contains(helper, "--team-id") {
		t.Errorf("helper call = %s", helper)
	}
}

func TestCreateKeyNeedsARecentNode(t *testing.T) {
	node, npm, log := fakeTools(t, "v16.20.2", `{}`)
	adapter := &Adapter{Node: node, Npm: npm}
	request := apns.Request{Name: "Appwrite Push", Environment: apns.EnvironmentAll, SessionDir: t.TempDir()}

	if _, err := adapter.CreateKey(context.Background(), request); !errors.Is(err, apns.ErrUnavailable) {
		t.Fatalf("err = %v", err)
	}
	if _, err := os.Stat(log); err == nil {
		t.Error("ran npm or the helper with an old Node.js")
	}

	missing := &Adapter{Node: filepath.Join(t.TempDir(), "node")}
	if _, err := missing.CreateKey(context.Background(), request); !errors.Is(err, apns.ErrUnavailable) {
		t.Fatalf("missing node err = %v", err)
	}
}

func TestCreateKeyPassesResetToTheHelper(t *testing.T) {
	node, npm, log := fakeTools(t, "v20.1.0", `{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}`)
	adapter := &Adapter{Node: node, Npm: npm, Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	request := apns.Request{Name: "Appwrite Push", Environment: apns.EnvironmentAll, SessionDir: t.TempDir(), Reset: true}

	if _, err := adapter.CreateKey(context.Background(), request); err != nil {
		t.Fatal(err)
	}
	calls, _ := os.ReadFile(log)
	lines := strings.Split(strings.TrimSpace(string(calls)), "\n")
	if helper := lines[len(lines)-1]; !strings.Contains(helper, " --reset true ") {
		t.Errorf("helper call = %s", helper)
	}
}

type oneAnswer struct {
	answer string
	asked  []string
}

func (o *oneAnswer) Ask(question string, secret bool) (string, error) {
	o.asked = append(o.asked, question)

	return o.answer, nil
}

func (o *oneAnswer) Choose(string, []string) (int, error) {
	return 0, errors.New("not expected")
}

// A reset asks for the Apple ID once and hands it to apple-utils, whose sign
// out would otherwise ask, forget it, and leave the sign-in to ask again.
func TestResetAsksForTheAppleIDOnce(t *testing.T) {
	node, npm, log := fakeTools(t, "v20.1.0", `{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}`)
	adapter := &Adapter{Node: node, Npm: npm, Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	asker := &oneAnswer{answer: "dev@example.com"}
	request := apns.Request{Name: "Appwrite Push", Environment: apns.EnvironmentAll, SessionDir: t.TempDir(), Reset: true, Asker: asker}

	if _, err := adapter.CreateKey(context.Background(), request); err != nil {
		t.Fatal(err)
	}
	if strings.Join(asker.asked, "|") != "Apple ID (email)" {
		t.Errorf("asked %v", asker.asked)
	}
	calls, _ := os.ReadFile(log)
	lines := strings.Split(strings.TrimSpace(string(calls)), "\n")
	if helper := lines[len(lines)-1]; !strings.HasSuffix(helper, "EXPO_APPLE_ID=dev@example.com") || !strings.Contains(helper, " --reset true") {
		t.Errorf("helper call = %s", helper)
	}

	// With the Apple ID known, nothing is asked.
	known := &oneAnswer{}
	request.Asker, request.AppleID = known, "dev@example.com"
	if _, err := adapter.CreateKey(context.Background(), request); err != nil || len(known.asked) != 0 {
		t.Errorf("asked %v, %v", known.asked, err)
	}
}

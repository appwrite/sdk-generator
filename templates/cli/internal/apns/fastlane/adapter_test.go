package fastlane

import (
	"context"
	"errors"
	"fmt"
	"os"
	"path/filepath"
	"runtime"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)

func TestSupportedVersion(t *testing.T) {
	for output, supported := range map[string]bool{
		"fastlane installation at path:\n/opt/homebrew/bin/fastlane\n-----------------------------\nfastlane 2.228.0\n": true,
		"fastlane 2.225.0": true,
		"fastlane 3.0.0":   true,
		"fastlane 2.224.9": false,
		"fastlane 1.300.0": false,
		"something else":   false,
	} {
		err := supportedVersion(output)
		if (err == nil) != supported {
			t.Errorf("supportedVersion(%q) = %v", output, err)
		}
		if err != nil && !errors.Is(err, apns.ErrUnavailable) {
			t.Errorf("supportedVersion(%q) is not ErrUnavailable: %v", output, err)
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
	if !errors.As(err, &maxKeys) || len(maxKeys.Keys) != 1 || maxKeys.Keys[0].ID != "OLD1234567" || !maxKeys.Keys[0].CanRevoke {
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

// fakeFastlane writes a fastlane stand-in that answers --version with version
// and, for the lane, logs its arguments and environment, checks the Fastfile
// is in place and writes result to the out: file.
func fakeFastlane(t *testing.T, version, result string) (string, string) {
	t.Helper()
	if runtime.GOOS == "windows" {
		t.Skip("shell stand-in")
	}
	bin := t.TempDir()
	log := filepath.Join(bin, "log")
	script := `#!/bin/sh
if [ "$1" = "--version" ]; then printf 'fastlane installation at path:\n/usr/local/bin/fastlane\nfastlane ` + version + `\n'; exit 0; fi
test -f fastlane/Fastfile || { echo "no Fastfile" >> "` + log + `"; exit 1; }
password=unset
[ -n "$FASTLANE_PASSWORD" ] && password=set
echo "fastlane $* FASTLANE_USER=$FASTLANE_USER password=$password" >> "` + log + `"
for argument in "$@"; do
  case "$argument" in
    out:*) printf '%s' '` + result + `' > "${argument#out:}" ;;
  esac
done
`
	path := filepath.Join(bin, "fastlane")
	if err := os.WriteFile(path, []byte(script), 0o700); err != nil {
		t.Fatal(err)
	}

	return path, log
}

type scriptedAsker struct {
	answers map[string]string
	asked   []string
}

func (s *scriptedAsker) Ask(question string, secret bool) (string, error) {
	s.asked = append(s.asked, question)
	for prefix, answer := range s.answers {
		if strings.HasPrefix(question, prefix) {
			return answer, nil
		}
	}

	return "", errors.New("no answer for " + question)
}

func (s *scriptedAsker) Choose(string, []string) (int, error) {
	return 0, errors.New("not expected")
}

func TestCreateKeyRunsTheLane(t *testing.T) {
	fastlane, log := fakeFastlane(t, "2.228.0", `{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}`)
	adapter := &Adapter{Fastlane: fastlane, Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	asker := &scriptedAsker{answers: map[string]string{"Apple ID": "dev@example.com", "Password for": "secret"}}

	key, err := adapter.CreateKey(context.Background(), apns.Request{
		TeamID:      "ABCDE12345",
		Name:        "Appwrite Push",
		Environment: apns.EnvironmentSandbox,
		Asker:       asker,
	})
	if err != nil || key.KeyID != "KEY1234567" || key.P8 != "pem" {
		t.Fatalf("key = %+v, %v", key, err)
	}
	if strings.Join(asker.asked, "|") != "Apple ID (email)|Password for dev@example.com" {
		t.Errorf("asked %v", asker.asked)
	}
	calls, _ := os.ReadFile(log)
	call := strings.TrimSpace(string(calls))
	if !strings.HasPrefix(call, "fastlane create_apns_key out:") ||
		!strings.HasSuffix(call, "name:Appwrite Push environment:sandbox team_id:ABCDE12345 FASTLANE_USER=dev@example.com password=set") {
		t.Errorf("call = %s", call)
	}
}

func TestCreateKeyUsesTheCredentialsInTheRequest(t *testing.T) {
	fastlane, _ := fakeFastlane(t, "2.228.0", `{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}`)
	adapter := &Adapter{Fastlane: fastlane, Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	asker := &scriptedAsker{}

	_, err := adapter.CreateKey(context.Background(), apns.Request{
		Name: "Appwrite Push", Environment: apns.EnvironmentAll, AppleID: "dev@example.com", Password: "secret", Asker: asker,
	})
	if err != nil {
		t.Fatal(err)
	}
	if len(asker.asked) != 0 {
		t.Errorf("asked %v", asker.asked)
	}
}

func TestCreateKeyNeedsARecentFastlane(t *testing.T) {
	old, log := fakeFastlane(t, "2.220.0", `{}`)
	request := apns.Request{Name: "Appwrite Push", Environment: apns.EnvironmentAll, Asker: &scriptedAsker{}}

	if _, err := (&Adapter{Fastlane: old}).CreateKey(context.Background(), request); !errors.Is(err, apns.ErrUnavailable) ||
		!strings.Contains(err.Error(), "found 2.220.0") {
		t.Fatalf("err = %v", err)
	}
	if _, err := os.Stat(log); err == nil {
		t.Error("ran the lane with an old fastlane")
	}

	missing := &Adapter{Fastlane: filepath.Join(t.TempDir(), "fastlane")}
	if _, err := missing.CreateKey(context.Background(), request); !errors.Is(err, apns.ErrUnavailable) {
		t.Fatalf("missing fastlane err = %v", err)
	}
}

func TestProjectGemfile(t *testing.T) {
	root := t.TempDir()
	if gemfile := projectGemfile(root); gemfile != "" {
		t.Errorf("found %s in an empty folder", gemfile)
	}

	if err := os.WriteFile(filepath.Join(root, "Gemfile"), []byte("source \"https://rubygems.org\"\ngem \"cocoapods\"\n"), 0o600); err != nil {
		t.Fatal(err)
	}
	if gemfile := projectGemfile(root); gemfile != "" {
		t.Errorf("used a Gemfile without fastlane: %s", gemfile)
	}

	if err := os.MkdirAll(filepath.Join(root, "ios"), 0o700); err != nil {
		t.Fatal(err)
	}
	ios := filepath.Join(root, "ios", "Gemfile")
	if err := os.WriteFile(ios, []byte("source 'https://rubygems.org'\n\n  gem 'fastlane', '~> 2.228'\n"), 0o600); err != nil {
		t.Fatal(err)
	}
	if gemfile := projectGemfile(root); gemfile != ios {
		t.Errorf("projectGemfile = %q, want %q", gemfile, ios)
	}
}

func TestCreateKeyRunsTheProjectsBundlerFastlane(t *testing.T) {
	if runtime.GOOS == "windows" {
		t.Skip("shell stand-in")
	}
	project := t.TempDir()
	gemfile := filepath.Join(project, "Gemfile")
	if err := os.WriteFile(gemfile, []byte("gem \"fastlane\"\n"), 0o600); err != nil {
		t.Fatal(err)
	}
	bin := t.TempDir()
	log := filepath.Join(bin, "log")
	bundle := filepath.Join(bin, "bundle")
	script := `#!/bin/sh
echo "bundle $* BUNDLE_GEMFILE=$BUNDLE_GEMFILE" >> "` + log + `"
[ "$1 $2" = "exec fastlane" ] || exit 1
if [ "$3" = "--version" ]; then echo "fastlane 2.228.0"; exit 0; fi
test -f fastlane/Fastfile || exit 1
for argument in "$@"; do
  case "$argument" in
    out:*) printf '%s' '{"keyId":"KEY1234567","teamId":"ABCDE12345","p8":"pem"}' > "${argument#out:}" ;;
  esac
done
`
	if err := os.WriteFile(bundle, []byte(script), 0o700); err != nil {
		t.Fatal(err)
	}
	adapter := &Adapter{Bundle: bundle, WorkDir: project, Stdout: &strings.Builder{}, Stderr: &strings.Builder{}}
	var logged []string
	request := apns.Request{
		Name: "Appwrite Push", Environment: apns.EnvironmentAll, AppleID: "dev@example.com", Password: "secret",
		Log: func(format string, args ...any) { logged = append(logged, fmt.Sprintf(format, args...)) },
	}

	if _, err := adapter.CreateKey(context.Background(), request); err != nil {
		t.Fatal(err)
	}
	calls, _ := os.ReadFile(log)
	lines := strings.Split(strings.TrimSpace(string(calls)), "\n")
	if len(lines) != 2 || lines[0] != "bundle exec fastlane --version BUNDLE_GEMFILE="+gemfile ||
		!strings.HasPrefix(lines[1], "bundle exec fastlane create_apns_key out:") || !strings.HasSuffix(lines[1], "BUNDLE_GEMFILE="+gemfile) {
		t.Errorf("calls:\n%s", calls)
	}
	if len(logged) != 1 || logged[0] != "Using fastlane from "+gemfile+" with bundle exec." {
		t.Errorf("logged %v", logged)
	}
}

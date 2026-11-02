package cmd

import (
	"bytes"
	"context"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/rand"
	"crypto/rsa"
	"crypto/x509"
	"encoding/json"
	"encoding/pem"
	"errors"
	"io"
	"net/http"
	"net/http/httptest"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns/apnstest"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
)

const testPbxproj = `// !$*UTF8*$!
{
	objects = {

/* Begin PBXNativeTarget section */
		A00000000000000000000001 /* MyApp */ = {
			isa = PBXNativeTarget;
			buildConfigurationList = B00000000000000000000001 /* Build configuration list for PBXNativeTarget "MyApp" */;
			name = MyApp;
			productType = "com.apple.product-type.application";
		};
		A00000000000000000000002 /* MyAppTests */ = {
			isa = PBXNativeTarget;
			buildConfigurationList = B00000000000000000000002 /* Build configuration list for PBXNativeTarget "MyAppTests" */;
			name = MyAppTests;
			productType = "com.apple.product-type.bundle.unit-test";
		};
		A00000000000000000000003 /* Widget */ = {
			isa = PBXNativeTarget;
			buildConfigurationList = B00000000000000000000003 /* Build configuration list for PBXNativeTarget "Widget" */;
			name = Widget;
			productType = "com.apple.product-type.app-extension";
		};
		A00000000000000000000004 /* Admin */ = {
			isa = PBXNativeTarget;
			buildConfigurationList = B00000000000000000000004 /* Build configuration list for PBXNativeTarget "Admin" */;
			name = Admin;
			productType = "com.apple.product-type.application";
		};
/* End PBXNativeTarget section */

/* Begin XCBuildConfiguration section */
		C00000000000000000000001 /* Debug */ = {
			isa = XCBuildConfiguration;
			buildSettings = {
				CODE_SIGN_ENTITLEMENTS = MyApp/MyApp.entitlements;
				DEVELOPMENT_TEAM = ABCDE12345;
				INFOPLIST_FILE = MyApp/Info.plist;
				PRODUCT_BUNDLE_IDENTIFIER = "org.reactjs.native.example.$(PRODUCT_NAME:rfc1034identifier)";
				PRODUCT_NAME = "My App";
			};
			name = Debug;
		};
		C00000000000000000000002 /* Debug */ = {
			isa = XCBuildConfiguration;
			buildSettings = {
				DEVELOPMENT_TEAM = ABCDE12345;
				PRODUCT_BUNDLE_IDENTIFIER = com.example.MyAppTests;
			};
			name = Debug;
		};
		C00000000000000000000003 /* Debug */ = {
			isa = XCBuildConfiguration;
			buildSettings = {
				CODE_SIGN_ENTITLEMENTS = Widget/Widget.entitlements;
				DEVELOPMENT_TEAM = ABCDE12345;
				INFOPLIST_FILE = Widget/Info.plist;
				PRODUCT_BUNDLE_IDENTIFIER = com.example.widget;
			};
			name = Debug;
		};
		C00000000000000000000004 /* Debug */ = {
			isa = XCBuildConfiguration;
			buildSettings = {
				CODE_SIGN_ENTITLEMENTS = Admin/Admin.entitlements;
				DEVELOPMENT_TEAM = FGHIJ67890;
				INFOPLIST_FILE = Admin/Info.plist;
				PRODUCT_BUNDLE_IDENTIFIER = com.example.admin;
			};
			name = Debug;
		};
/* End XCBuildConfiguration section */

/* Begin XCConfigurationList section */
		B00000000000000000000001 /* Build configuration list for PBXNativeTarget "MyApp" */ = {
			isa = XCConfigurationList;
			buildConfigurations = (
				C00000000000000000000001 /* Debug */,
			);
		};
		B00000000000000000000002 /* Build configuration list for PBXNativeTarget "MyAppTests" */ = {
			isa = XCConfigurationList;
			buildConfigurations = (
				C00000000000000000000002 /* Debug */,
			);
		};
		B00000000000000000000003 /* Build configuration list for PBXNativeTarget "Widget" */ = {
			isa = XCConfigurationList;
			buildConfigurations = (
				C00000000000000000000003 /* Debug */,
			);
		};
		B00000000000000000000004 /* Build configuration list for PBXNativeTarget "Admin" */ = {
			isa = XCConfigurationList;
			buildConfigurations = (
				C00000000000000000000004 /* Debug */,
			);
		};
/* End XCConfigurationList section */
	};
}
`

const myAppBundle = "org.reactjs.native.example.My-App"

func writeFile(t *testing.T, path, contents string) {
	t.Helper()
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		t.Fatal(err)
	}
	if err := os.WriteFile(path, []byte(contents), 0o644); err != nil {
		t.Fatal(err)
	}
}

func TestDetectAppleReadsTheAppTarget(t *testing.T) {
	root := t.TempDir()
	writeFile(t, filepath.Join(root, "ios", "MyApp.xcodeproj", "project.pbxproj"), testPbxproj)
	writeFile(t, filepath.Join(root, "node_modules", "dep", "Dep.xcodeproj", "project.pbxproj"),
		strings.ReplaceAll(testPbxproj, "ABCDE12345", "ZZZZZ99999"))

	app, err := detectApple(root)
	if err != nil {
		t.Fatal(err)
	}

	if strings.Join(app.BundleIDs, ",") != "com.example.admin,"+myAppBundle {
		t.Errorf("bundle IDs = %v", app.BundleIDs)
	}
	if strings.Join(app.teams(myAppBundle), ",") != "ABCDE12345" || strings.Join(app.teams("com.example.admin"), ",") != "FGHIJ67890" {
		t.Errorf("teams = %v / %v", app.teams(myAppBundle), app.teams("com.example.admin"))
	}
	targets := app.targets(myAppBundle)
	if len(targets) != 1 {
		t.Fatalf("targets = %+v", app.Targets)
	}
	if strings.Join(targets[0].Entitlements, ",") != filepath.Join(root, "ios", "MyApp", "MyApp.entitlements") {
		t.Errorf("entitlements = %v", targets[0].Entitlements)
	}
	if strings.Join(targets[0].InfoPlists, ",") != filepath.Join(root, "ios", "MyApp", "Info.plist") {
		t.Errorf("Info.plist = %v", targets[0].InfoPlists)
	}
}

func TestDetectReadsExpoConfig(t *testing.T) {
	root := t.TempDir()
	writeFile(t, filepath.Join(root, "app.json"), `{"expo": {
  "ios": {"bundleIdentifier": "com.example.expo", "appleTeamId": "ABCDE12345"}
}}`)

	apple, _ := detectApple(root)

	if apple.ExpoConfig == "" || len(apple.BundleIDs) != 1 || apple.BundleIDs[0] != "com.example.expo" || apple.TeamIDs[0] != "ABCDE12345" {
		t.Errorf("apple = %+v", apple)
	}
}

func TestEnsurePlistEdits(t *testing.T) {
	empty := "<?xml version=\"1.0\"?>\n<plist version=\"1.0\">\n<dict/>\n</plist>\n"
	got := ensurePlistString(empty, "aps-environment", "development")
	want := "<?xml version=\"1.0\"?>\n<plist version=\"1.0\">\n<dict>\n\t<key>aps-environment</key>\n\t<string>development</string>\n</dict>\n</plist>\n"
	if got != want {
		t.Errorf("empty dict:\n%s", got)
	}
	if ensurePlistString(got, "aps-environment", "production") != got {
		t.Error("an existing aps-environment was changed")
	}

	info := "<plist version=\"1.0\">\n<dict>\n\t<key>CFBundleName</key>\n\t<string>App</string>\n\t<key>UIBackgroundModes</key>\n\t<array>\n\t\t<string>fetch</string>\n\t</array>\n</dict>\n</plist>\n"
	got = ensurePlistArrayValue(info, "UIBackgroundModes", "remote-notification")
	if !strings.Contains(got, "\t<array>\n\t\t<string>fetch</string>\n\t\t<string>remote-notification</string>\n\t</array>") {
		t.Errorf("existing array:\n%s", got)
	}
	if ensurePlistArrayValue(got, "UIBackgroundModes", "remote-notification") != got {
		t.Error("remote-notification was added twice")
	}

	expo := "<plist version=\"1.0\">\n  <dict>\n    <key>CFBundleName</key>\n    <string>App</string>\n  </dict>\n</plist>\n"
	got = ensurePlistArrayValue(expo, "UIBackgroundModes", "remote-notification")
	if !strings.Contains(got, "    <string>App</string>\n    <key>UIBackgroundModes</key>\n    <array>\n      <string>remote-notification</string>\n    </array>\n  </dict>\n") {
		t.Errorf("two-space plist:\n%s", got)
	}
	got = ensurePlistArrayValue(strings.Replace(got, "<string>remote-notification</string>", "<string>fetch</string>", 1), "UIBackgroundModes", "remote-notification")
	if !strings.Contains(got, "      <string>fetch</string>\n      <string>remote-notification</string>\n    </array>") {
		t.Errorf("two-space array:\n%s", got)
	}

	selfClosing := "<plist version=\"1.0\">\n<dict>\n\t<key>UIBackgroundModes</key>\n\t<array/>\n</dict>\n</plist>\n"
	got = ensurePlistArrayValue(selfClosing, "UIBackgroundModes", "remote-notification")
	if got != "<plist version=\"1.0\">\n<dict>\n\t<key>UIBackgroundModes</key>\n\t<array>\n\t\t<string>remote-notification</string>\n\t</array>\n</dict>\n</plist>\n" {
		t.Errorf("self-closing array:\n%s", got)
	}

	bare := "<plist version=\"1.0\">\n<dict>\n\t<key>Nested</key>\n\t<dict>\n\t</dict>\n</dict>\n</plist>\n"
	got = ensurePlistArrayValue(bare, "UIBackgroundModes", "remote-notification")
	if !strings.HasSuffix(got, "\t</dict>\n\t<key>UIBackgroundModes</key>\n\t<array>\n\t\t<string>remote-notification</string>\n\t</array>\n</dict>\n</plist>\n") {
		t.Errorf("new array:\n%s", got)
	}
}

func writeKey(t *testing.T, path string, key any) {
	t.Helper()
	encoded, err := x509.MarshalPKCS8PrivateKey(key)
	if err != nil {
		t.Fatal(err)
	}
	writeFile(t, path, string(pem.EncodeToMemory(&pem.Block{Type: "PRIVATE KEY", Bytes: encoded})))
}

func testApnsKey(t *testing.T, directory string) string {
	t.Helper()
	key, err := ecdsa.GenerateKey(elliptic.P256(), rand.Reader)
	if err != nil {
		t.Fatal(err)
	}
	path := filepath.Join(directory, "AuthKey_KEY1234567.p8")
	writeKey(t, path, key)

	return path
}

func TestReadApnsKeyRejectsOtherKeys(t *testing.T) {
	directory := t.TempDir()
	if _, err := readApnsKey(testApnsKey(t, directory)); err != nil {
		t.Errorf("P-256 key rejected: %s", err)
	}

	rsaKey, _ := rsa.GenerateKey(rand.Reader, 2048)
	rsaPath := filepath.Join(directory, "rsa.p8")
	writeKey(t, rsaPath, rsaKey)
	if _, err := readApnsKey(rsaPath); err == nil || !strings.Contains(err.Error(), "P-256") {
		t.Errorf("RSA key accepted: %v", err)
	}
}

func TestWaitForKeyFileIgnoresEarlierFiles(t *testing.T) {
	directory := t.TempDir()
	old := testApnsKey(t, directory)
	past := time.Now().Add(-time.Hour)
	if err := os.Chtimes(old, past, past); err != nil {
		t.Fatal(err)
	}

	started := time.Now()
	fresh := filepath.Join(directory, "AuthKey_NEW1234567.p8")
	go func() {
		time.Sleep(50 * time.Millisecond)
		writeFile(t, filepath.Join(directory, "notes.txt"), "not a key")
		data, _ := os.ReadFile(old)
		writeFile(t, fresh, string(data))
	}()

	path, err := waitForKeyFile([]string{t.TempDir(), directory}, started, func(path string) bool {
		return apnsKeyFileName.MatchString(filepath.Base(path))
	}, 5*time.Second, 10*time.Millisecond)
	if err != nil {
		t.Fatal(err)
	}
	if path != fresh {
		t.Errorf("picked %s", path)
	}
}

type fakeMessaging struct {
	platforms  []map[string]any
	providers  []map[string]any
	requests   []string
	identities []map[string]any
	failCreate bool
	failList   int
}

func (f *fakeMessaging) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	f.requests = append(f.requests, r.Method+" "+r.URL.Path)
	body, _ := io.ReadAll(r.Body)
	var params map[string]any
	_ = json.Unmarshal(body, &params)

	switch {
	case r.Method == "GET" && r.URL.Path == "/project/platforms":
		_ = json.NewEncoder(w).Encode(map[string]any{"total": len(f.platforms), "platforms": f.platforms})
	case r.Method == "POST" && r.URL.Path == "/project/platforms/apple":
		platform := map[string]any{"$id": "platform" + string(rune('0'+len(f.platforms))), "type": "apple", "name": params["name"], "bundleIdentifier": params["bundleIdentifier"]}
		f.platforms = append(f.platforms, platform)
		w.WriteHeader(http.StatusCreated)
		_ = json.NewEncoder(w).Encode(platform)
	case r.Method == "GET" && r.URL.Path == "/account/identities":
		_ = json.NewEncoder(w).Encode(map[string]any{"total": len(f.identities), "identities": f.identities})
	case r.Method == "GET" && r.URL.Path == "/account":
		_ = json.NewEncoder(w).Encode(map[string]any{"email": "dev@example.com"})
	case r.Method == "GET" && r.URL.Path == "/messaging/providers" && f.failList > 0:
		f.failList--
		w.WriteHeader(http.StatusServiceUnavailable)
		_ = json.NewEncoder(w).Encode(map[string]any{"message": "unavailable", "code": 503})
	case r.Method == "POST" && f.failCreate:
		w.WriteHeader(http.StatusBadRequest)
		_ = json.NewEncoder(w).Encode(map[string]any{"message": "provider rejected", "code": 400})
	case r.Method == "GET" && r.URL.Path == "/messaging/providers":
		kind := ""
		for _, query := range r.URL.Query()["queries[]"] {
			if strings.Contains(query, `"provider"`) {
				kind = strings.Split(strings.Split(query, `"values":["`)[1], `"`)[0]
			}
		}
		matching := []map[string]any{}
		for _, provider := range f.providers {
			if provider["provider"] == kind {
				matching = append(matching, provider)
			}
		}
		_ = json.NewEncoder(w).Encode(map[string]any{"total": len(matching), "providers": matching})
	case r.Method == "POST":
		kind := strings.TrimPrefix(r.URL.Path, "/messaging/providers/")
		provider := providerDocument(kind, "id"+string(rune('0'+len(f.providers))), params)
		f.providers = append(f.providers, provider)
		w.WriteHeader(http.StatusCreated)
		_ = json.NewEncoder(w).Encode(provider)
	case r.Method == "PATCH":
		parts := strings.Split(r.URL.Path, "/")
		id := parts[len(parts)-1]
		for index, provider := range f.providers {
			if provider["$id"] == id {
				f.providers[index] = providerDocument(provider["provider"].(string), id, params)
				_ = json.NewEncoder(w).Encode(f.providers[index])

				return
			}
		}
		w.WriteHeader(http.StatusNotFound)
	default:
		w.WriteHeader(http.StatusNotFound)
	}
}

func providerDocument(kind, id string, params map[string]any) map[string]any {
	credentials := map[string]any{}
	options := map[string]any{}
	for _, name := range []string{"authKey", "authKeyId", "teamId", "bundleId"} {
		if value, ok := params[name]; ok {
			credentials[name] = value
		}
	}
	if sandbox, ok := params["sandbox"]; ok {
		options["sandbox"] = sandbox
	}

	return map[string]any{
		"$id": id, "name": params["name"], "provider": kind, "type": "push",
		"enabled": params["enabled"], "credentials": credentials, "options": options,
	}
}

func newTestPushSetup(t *testing.T, server *httptest.Server, prompter prompt.Prompter, root string) (*pushSetup, *bytes.Buffer) {
	t.Helper()
	out := &bytes.Buffer{}

	return &pushSetup{
		api:       client.New(server.URL, "test").SetProject("project"),
		prompter:  prompter,
		out:       out,
		root:      root,
		keyDirs:   []string{root},
		downloads: t.TempDir(),
		open:      func(string) {},
	}, out
}

func TestInitApnsCreatesBothEnvironmentsAndEditsXcode(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	writeFile(t, filepath.Join(root, "ios", "MyApp.xcodeproj", "project.pbxproj"), testPbxproj)
	writeFile(t, filepath.Join(root, "ios", "MyApp", "MyApp.entitlements"), "<plist version=\"1.0\">\n<dict/>\n</plist>\n")
	writeFile(t, filepath.Join(root, "ios", "MyApp", "Info.plist"), "<plist version=\"1.0\">\n<dict>\n</dict>\n</plist>\n")
	untouched := map[string]string{}
	for _, path := range []string{"Admin/Admin.entitlements", "Admin/Info.plist", "Widget/Widget.entitlements", "Widget/Info.plist"} {
		untouched[path] = "<plist version=\"1.0\">\n<dict>\n</dict>\n</plist>\n"
		writeFile(t, filepath.Join(root, "ios", path), untouched[path])
	}
	keyPath := testApnsKey(t, t.TempDir())

	scripted := &prompt.Scripted{}
	setup, _ := newTestPushSetup(t, server, scripted, root)
	detected, _ := detectApple(root)
	if err := setup.apns(apnsOptions{keyPath: keyPath, bundleID: myAppBundle}, detected); err != nil {
		t.Fatal(err)
	}

	if len(scripted.Asked) != 0 {
		t.Errorf("asked %v", scripted.Asked)
	}
	if len(messaging.providers) != 2 {
		t.Fatalf("providers = %v", messaging.providers)
	}
	for index, sandbox := range []bool{false, true} {
		provider := messaging.providers[index]
		credentials := provider["credentials"].(map[string]any)
		if credentials["bundleId"] != myAppBundle || credentials["teamId"] != "ABCDE12345" ||
			credentials["authKeyId"] != "KEY1234567" || provider["options"].(map[string]any)["sandbox"] != sandbox ||
			provider["enabled"] != true {
			t.Errorf("provider %d = %v", index, provider)
		}
	}

	entitlements, _ := os.ReadFile(filepath.Join(root, "ios", "MyApp", "MyApp.entitlements"))
	if !strings.Contains(string(entitlements), "<key>aps-environment</key>\n\t<string>development</string>") {
		t.Errorf("entitlements:\n%s", entitlements)
	}
	info, _ := os.ReadFile(filepath.Join(root, "ios", "MyApp", "Info.plist"))
	if !strings.Contains(string(info), "<string>remote-notification</string>") {
		t.Errorf("Info.plist:\n%s", info)
	}
	for path, original := range untouched {
		if contents, _ := os.ReadFile(filepath.Join(root, "ios", path)); string(contents) != original {
			t.Errorf("%s was edited:\n%s", path, contents)
		}
	}

	previous := map[string]any{}
	for _, provider := range messaging.providers {
		previous[provider["$id"].(string)] = provider["credentials"].(map[string]any)["authKey"]
	}
	rotated, err := ecdsa.GenerateKey(elliptic.P256(), rand.Reader)
	if err != nil {
		t.Fatal(err)
	}
	rotatedPath := filepath.Join(t.TempDir(), "AuthKey_ROTATE1234.p8")
	writeKey(t, rotatedPath, rotated)
	if err := setup.apns(apnsOptions{keyPath: rotatedPath, bundleID: myAppBundle}, detected); err != nil {
		t.Fatal(err)
	}
	if len(messaging.providers) != 2 {
		t.Fatalf("providers after rotation = %v", messaging.providers)
	}
	for _, provider := range messaging.providers {
		id := provider["$id"].(string)
		credentials := provider["credentials"].(map[string]any)
		old, kept := previous[id]
		if !kept || credentials["authKeyId"] != "ROTATE1234" || credentials["authKey"] == old {
			t.Errorf("provider %s after rotation = %v", id, credentials)
		}
	}

	messaging.requests = nil
	if err := setup.apns(apnsOptions{bundleID: myAppBundle}, detected); err != nil {
		t.Fatal(err)
	}
	// A rerun only reads: the providers, the platform the first run added, and the Appwrite push provider.
	if strings.Join(messaging.requests, ",") != "GET /messaging/providers,GET /project/platforms,GET /messaging/providers" {
		t.Errorf("already set up requests = %v", messaging.requests)
	}
	if len(messaging.platforms) != 1 || messaging.platforms[0]["bundleIdentifier"] != myAppBundle {
		t.Errorf("platforms = %v", messaging.platforms)
	}
}

func TestInitApnsAddsTheApplePlatform(t *testing.T) {
	web := map[string]any{"$id": "web", "type": "web", "hostname": "example.com"}
	messaging := &fakeMessaging{platforms: []map[string]any{web}}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	setup, out := newTestPushSetup(t, server, &prompt.Scripted{}, root)
	err := setup.apns(apnsOptions{keyPath: testApnsKey(t, root), keyID: "ABCDE12345", teamID: "ABCDE12345", bundleID: "com.example.app", environment: "sandbox"}, appleApp{})
	if err != nil {
		t.Fatal(err)
	}
	if len(messaging.platforms) != 2 || messaging.platforms[1]["bundleIdentifier"] != "com.example.app" {
		t.Fatalf("platforms = %v", messaging.platforms)
	}
	text := out.String()
	if !strings.Contains(text, "Added the Apple platform com.example.app") {
		t.Errorf("output is missing the added platform:\n%s", text)
	}
	if strings.Contains(text, "AppwriteNotificationService") {
		t.Errorf("APNs alone asks for a Notification Service Extension:\n%s", text)
	}
}

func TestInitApnsAsksForTheExtensionWithAnAppwritePushProvider(t *testing.T) {
	push := map[string]any{"$id": "push", "provider": "appwrite", "enabled": true}
	messaging := &fakeMessaging{providers: []map[string]any{push}}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	setup, out := newTestPushSetup(t, server, &prompt.Scripted{}, root)
	err := setup.apns(apnsOptions{keyPath: testApnsKey(t, root), keyID: "ABCDE12345", teamID: "ABCDE12345", bundleID: "com.example.app", environment: "sandbox"}, appleApp{})
	if err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(out.String(), "AppwriteNotificationService") {
		t.Errorf("output is missing the extension hint:\n%s", out.String())
	}
}

func TestInitApnsUsesAKeyInTheProjectFolder(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	testApnsKey(t, root)
	writeFile(t, filepath.Join(root, "notes.p8"), "not a key")

	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, root)
	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345"}, appleApp{}); err != nil {
		t.Fatal(err)
	}

	if len(scripted.Asked) != 0 {
		t.Errorf("asked %v", scripted.Asked)
	}
	if !strings.Contains(out.String(), "Using AuthKey_KEY1234567.p8 from the project folder.") {
		t.Errorf("output:\n%s", out.String())
	}
	if len(messaging.providers) != 2 || messaging.providers[0]["credentials"].(map[string]any)["authKeyId"] != "KEY1234567" {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestBrowserFlowFindsAKeySavedIntoTheProjectFolder(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	setup, _ := newTestPushSetup(t, server, &prompt.Scripted{}, root)
	go func() {
		time.Sleep(100 * time.Millisecond)
		testApnsKey(t, root)
	}()

	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(messaging.providers) != 2 {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestKeysInAGitRepositoryMustBeIgnored(t *testing.T) {
	if _, err := exec.LookPath("git"); err != nil {
		t.Skip("git is not installed")
	}
	root := t.TempDir()
	if err := exec.Command("git", "-C", root, "init", "-q").Run(); err != nil {
		t.Skip("git init failed")
	}
	key := testApnsKey(t, root)

	out := &bytes.Buffer{}
	setup := &pushSetup{out: out, keyDirs: []string{root}}
	setup.checkKeyLocation(key)
	if !strings.Contains(out.String(), "AuthKey_KEY1234567.p8 is inside a git repository and not ignored") {
		t.Errorf("output:\n%s", out.String())
	}

	writeFile(t, filepath.Join(root, ".gitignore"), "*.p8\n")
	out.Reset()
	setup.checkKeyLocation(key)
	if out.Len() != 0 {
		t.Errorf("warned for an ignored key:\n%s", out.String())
	}
}

// useAdapter makes adapter the only APNs key setup, under the default name,
// as an account with one team (APPLE12345) would see it.
func useAdapter(setup *pushSetup, adapter *apnstest.Adapter) *apnstest.Adapter {
	adapter.AdapterName = defaultApnsSetup
	if adapter.TeamID == "" {
		adapter.TeamID = "APPLE12345"
	}
	setup.apnsAdapters = apns.NewRegistry(adapter)

	return adapter
}
func TestInitApnsCreatesTheKeyWithTheAppleID(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, root)
	appleKey := useAdapter(setup, &apnstest.Adapter{KeyID: "NEWKEY1234"})

	options := apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true}
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}

	if len(scripted.Asked) != 0 {
		t.Errorf("asked %v", scripted.Asked)
	}
	if len(appleKey.Requests) != 1 || appleKey.Requests[0].TeamID != "ABCDE12345" || !strings.HasPrefix(appleKey.Requests[0].Name, "Appwrite Push ") {
		t.Errorf("helper calls = %v", appleKey.Requests)
	}
	saved := filepath.Join(root, "AuthKey_NEWKEY1234.p8")
	info, err := os.Stat(saved)
	if err != nil {
		t.Fatalf("key was not saved: %v", err)
	}
	if info.Mode().Perm() != 0o600 {
		t.Errorf("key mode = %v", info.Mode().Perm())
	}
	if len(messaging.providers) != 2 {
		t.Fatalf("providers = %v", messaging.providers)
	}
	for _, provider := range messaging.providers {
		credentials := provider["credentials"].(map[string]any)
		if credentials["authKeyId"] != "NEWKEY1234" || credentials["teamId"] != "ABCDE12345" {
			t.Errorf("provider = %v", provider)
		}
	}
	if !strings.Contains(out.String(), "Created APNs key NEWKEY1234 on team ABCDE12345 and saved it to AuthKey_NEWKEY1234.p8.") {
		t.Errorf("output:\n%s", out.String())
	}

	// A second run finds both providers and does not spend another key slot.
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(appleKey.Requests) != 1 {
		t.Errorf("created another key on rerun: %v", appleKey.Requests)
	}
}

func TestInitApnsOffersTheAppleIDWhenNoKeyIsFound(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{Choices: map[string]string{
		"How would you like to provide the APNs auth key (.p8)?": "automatic",
	}}
	setup, _ := newTestPushSetup(t, server, scripted, t.TempDir())
	appleKey := useAdapter(setup, &apnstest.Adapter{KeyID: "NEWKEY1234"})

	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(appleKey.Requests) != 1 || len(messaging.providers) != 2 {
		t.Errorf("calls = %v, providers = %v", appleKey.Requests, messaging.providers)
	}
}

func TestInitApnsStopsWhenCreateKeyFails(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	// A key already in the project folder must not stand in for the one
	// --create-key was asked to create.
	testApnsKey(t, root)
	scripted := &prompt.Scripted{}
	setup, _ := newTestPushSetup(t, server, scripted, root)
	useAdapter(setup, &apnstest.Adapter{Err: errors.New("could not reach Apple")})

	err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true}, appleApp{})
	if err == nil || err.Error() != "could not create the APNs key with your Apple ID: could not reach Apple" {
		t.Fatalf("err = %v", err)
	}
	if len(scripted.Asked) != 0 || len(messaging.providers) != 0 {
		t.Errorf("asked %v, providers %v", scripted.Asked, messaging.providers)
	}
}

// choiceSequence answers the same choice differently each time it is asked.
type choiceSequence struct {
	*prompt.Scripted
	answers []string
}

func (c *choiceSequence) Choice(question prompt.Choice) (string, error) {
	c.Scripted.Asked = append(c.Scripted.Asked, question.Message)
	answer := c.answers[0]
	c.answers = c.answers[1:]

	return answer, nil
}

func TestInitApnsFallsBackWhenTheChosenAppleSignInFails(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	existing := testApnsKey(t, t.TempDir())
	scripted := &choiceSequence{
		Scripted: &prompt.Scripted{Texts: map[string]string{"Path to the APNs auth key (.p8)": existing}},
		answers:  []string{"automatic", "file"},
	}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	useAdapter(setup, &apnstest.Adapter{Err: errors.New("could not reach Apple")})

	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(out.String(), "Could not create the APNs key with your Apple ID: could not reach Apple.") {
		t.Errorf("output:\n%s", out.String())
	}
	if len(messaging.providers) != 2 || messaging.providers[0]["credentials"].(map[string]any)["authKeyId"] != "KEY1234567" {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestInitApnsStopsWhenTheTeamHasNoFreeKeySlot(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	setup, out := newTestPushSetup(t, server, &prompt.Scripted{}, root)
	appleKey := &apnstest.Adapter{Err: &apns.MaxKeysError{Keys: []apns.ExistingKey{
		{ID: "OLDKEY1234", Name: "Firebase", CanRevoke: true},
		{ID: "OLDKEY5678", Name: "Expo", CanRevoke: true},
	}}}
	useAdapter(setup, appleKey)

	err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true}, appleApp{})
	if err == nil || !strings.Contains(err.Error(), "--key-path") {
		t.Fatalf("err = %v", err)
	}
	for _, line := range []string{"Firebase (OLDKEY1234)", "Expo (OLDKEY5678)", apnsKeysListPage} {
		if !strings.Contains(out.String(), line) {
			t.Errorf("output is missing %q:\n%s", line, out.String())
		}
	}
	if len(messaging.providers) != 0 {
		t.Errorf("providers = %v", messaging.providers)
	}
	if entries, _ := os.ReadDir(root); len(entries) != 0 {
		t.Errorf("wrote %v", entries)
	}
}

func TestInitApnsTakesTheTeamFromTheAppleSignIn(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{}
	setup, _ := newTestPushSetup(t, server, scripted, t.TempDir())
	appleKey := useAdapter(setup, &apnstest.Adapter{KeyID: "NEWKEY1234"})

	if err := setup.apns(apnsOptions{bundleID: "com.example.app", createKey: true}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(scripted.Asked) != 0 {
		t.Errorf("asked %v", scripted.Asked)
	}
	if len(appleKey.Requests) != 1 || appleKey.Requests[0].TeamID != "" {
		t.Errorf("helper calls = %v", appleKey.Requests)
	}
	for _, provider := range messaging.providers {
		if provider["credentials"].(map[string]any)["teamId"] != "APPLE12345" {
			t.Errorf("provider = %v", provider)
		}
	}
}

func TestInitApnsAsksForTheTeamAfterAKeyFile(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	existing := testApnsKey(t, t.TempDir())
	scripted := &prompt.Scripted{
		Choices: map[string]string{"How would you like to provide the APNs auth key (.p8)?": "file"},
		Texts: map[string]string{
			"Path to the APNs auth key (.p8)":       existing,
			"What is your Apple Developer team ID?": "ABCDE12345",
		},
	}
	setup, _ := newTestPushSetup(t, server, scripted, t.TempDir())
	useAdapter(setup, &apnstest.Adapter{KeyID: "NEWKEY1234"})

	if err := setup.apns(apnsOptions{bundleID: "com.example.app"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	want := []string{"How would you like to provide the APNs auth key (.p8)?", "Path to the APNs auth key (.p8)", "What is your Apple Developer team ID?"}
	if strings.Join(scripted.Asked, "|") != strings.Join(want, "|") {
		t.Errorf("asked %v", scripted.Asked)
	}
	if messaging.providers[0]["credentials"].(map[string]any)["teamId"] != "ABCDE12345" {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestInitApnsSetsUpOneEnvironment(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	setup, out := newTestPushSetup(t, server, &prompt.Scripted{}, root)
	appleKey := useAdapter(setup, &apnstest.Adapter{KeyID: "NEWKEY1234"})

	options := apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true, environment: "sandbox"}
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(appleKey.Requests) != 1 || appleKey.Requests[0].Environment != apns.EnvironmentSandbox {
		t.Errorf("helper calls = %v", appleKey.Requests)
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["options"].(map[string]any)["sandbox"] != true {
		t.Fatalf("providers = %v", messaging.providers)
	}
	if !strings.Contains(out.String(), "APNs (sandbox) is set up for com.example.app.") {
		t.Errorf("output:\n%s", out.String())
	}

	// The sandbox provider is all a sandbox run needs.
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(appleKey.Requests) != 1 || !strings.Contains(out.String(), "APNs (sandbox) is already set up for com.example.app.") {
		t.Errorf("calls = %v, output:\n%s", appleKey.Requests, out.String())
	}

	// Production still needs its own provider; the key already in the folder
	// serves it.
	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", environment: "production"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(messaging.providers) != 2 || messaging.providers[1]["options"].(map[string]any)["sandbox"] != false {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestInitApnsRejectsAnUnknownEnvironment(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	setup, _ := newTestPushSetup(t, server, &prompt.Scripted{}, t.TempDir())
	err := setup.apns(apnsOptions{bundleID: "com.example.app", environment: "staging"}, appleApp{})
	if err == nil || err.Error() != `--environment must be production, sandbox or all, not "staging"` {
		t.Fatalf("err = %v", err)
	}
	if len(messaging.requests) != 0 {
		t.Errorf("requests = %v", messaging.requests)
	}
}

func TestInitApnsPicksTheSetupFromTheFlagThenTheEnvironment(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	setup, out := newTestPushSetup(t, server, &prompt.Scripted{}, t.TempDir())
	appwrite := &apnstest.Adapter{AdapterName: "appwrite", KeyID: "APPWRITE01", TeamID: "ABCDE12345"}
	expo := &apnstest.Adapter{AdapterName: "expo", KeyID: "EXPOKEY001", TeamID: "ABCDE12345"}
	setup.apnsAdapters = apns.NewRegistry(appwrite, expo)
	env := map[string]string{
		"APPWRITE_APNS_SETUP":     "expo",
		"APPWRITE_APPLE_ID":       "dev@example.com",
		"APPWRITE_APPLE_PASSWORD": "secret",
	}
	setup.getenv = func(name string) string { return env[name] }
	options := apnsOptions{bundleID: "com.example.app", createKey: true}

	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(expo.Requests) != 1 || len(appwrite.Requests) != 0 {
		t.Fatalf("expo = %d, appwrite = %d requests", len(expo.Requests), len(appwrite.Requests))
	}
	request := expo.Requests[0]
	if request.AppleID != "dev@example.com" || request.Password != "secret" || request.Asker == nil {
		t.Errorf("request = %+v", request)
	}
	for _, line := range []string{"Signing in to the Apple Developer portal (expo).", "Using the Apple ID from APPWRITE_APPLE_ID.", "Using the Apple ID password from APPWRITE_APPLE_PASSWORD."} {
		if !strings.Contains(out.String(), line) {
			t.Errorf("output is missing %q:\n%s", line, out.String())
		}
	}
	if strings.Contains(out.String(), "secret") {
		t.Errorf("printed the password:\n%s", out.String())
	}

	// Each run starts from no providers and a fresh folder: a key file is
	// never overwritten.
	fresh := func() {
		messaging.providers = nil
		setup.keyDirs = []string{t.TempDir()}
	}
	fresh()
	options.setup = "appwrite"
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(appwrite.Requests) != 1 || len(expo.Requests) != 1 {
		t.Errorf("--provider did not win over %s", apnsSetupVariable)
	}

	delete(env, "APPWRITE_APNS_SETUP")
	options.setup = ""
	fresh()
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(appwrite.Requests) != 2 {
		t.Errorf("the default is not %s", defaultApnsSetup)
	}
}

func TestInitApnsRejectsAMalformedBundleID(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	setup, _ := newTestPushSetup(t, server, &prompt.Scripted{}, t.TempDir())
	err := setup.apns(apnsOptions{bundleID: "not a bundle id!", environment: "sandbox"}, appleApp{})
	if err == nil || !strings.Contains(err.Error(), "is not a bundle ID") {
		t.Fatalf("err = %v", err)
	}
	if len(messaging.requests) != 0 {
		t.Errorf("requests = %v", messaging.requests)
	}
}

func TestInitApnsRejectsAnUnknownSetupBeforeAnyRequest(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	setup, _ := newTestPushSetup(t, server, &prompt.Scripted{}, t.TempDir())
	setup.apnsAdapters = apns.NewRegistry(&apnstest.Adapter{AdapterName: "appwrite"})
	err := setup.apns(apnsOptions{bundleID: "com.example.app", environment: "all", setup: "nope"}, appleApp{})
	if err == nil || !strings.Contains(err.Error(), `unknown APNs key setup "nope"`) {
		t.Fatalf("err = %v", err)
	}
	if len(messaging.requests) != 0 {
		t.Errorf("requests = %v", messaging.requests)
	}
}

func TestInitApnsReportsAnUnknownSetup(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	setup, _ := newTestPushSetup(t, server, &prompt.Scripted{}, t.TempDir())
	setup.apnsAdapters = apns.NewRegistry(&apnstest.Adapter{AdapterName: "appwrite"})
	err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true, setup: "fastlane"}, appleApp{})
	if err == nil || !strings.Contains(err.Error(), `unknown APNs key setup "fastlane": use one of appwrite`) {
		t.Fatalf("err = %v", err)
	}
	if len(messaging.providers) != 0 {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestInitApnsWithoutSetupsOnlyOffersTheOtherWays(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	var offered []prompt.Option
	scripted := &prompt.Scripted{Choices: map[string]string{"How would you like to provide the APNs auth key (.p8)?": "file"}, Texts: map[string]string{"Path to the APNs auth key (.p8)": testApnsKey(t, t.TempDir())}}
	setup, _ := newTestPushSetup(t, server, optionsRecorder{scripted, &offered}, t.TempDir())
	setup.apnsAdapters = apns.NewRegistry()

	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	for _, option := range offered {
		if option.Value == "automatic" {
			t.Errorf("offered the Apple sign-in with no setup built in: %v", offered)
		}
	}

	messaging.providers = nil
	err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true}, appleApp{})
	if err == nil || !strings.Contains(err.Error(), "this build has none") {
		t.Errorf("err = %v", err)
	}
}

// optionsRecorder records the options of the key question.
type optionsRecorder struct {
	*prompt.Scripted
	options *[]prompt.Option
}

func (r optionsRecorder) Choice(question prompt.Choice) (string, error) {
	if question.Message == "How would you like to provide the APNs auth key (.p8)?" {
		*r.options = question.Options
	}

	return r.Scripted.Choice(question)
}

func TestInitApnsUsesTheCreatedKeysID(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{Choices: map[string]string{"How would you like to provide the APNs auth key (.p8)?": "automatic"}}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	useAdapter(setup, &apnstest.Adapter{KeyID: "NEWKEY1234"})

	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", keyID: "WRONGKEY12"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	for _, provider := range messaging.providers {
		if provider["credentials"].(map[string]any)["authKeyId"] != "NEWKEY1234" {
			t.Errorf("provider = %v", provider)
		}
	}
	if !strings.Contains(out.String(), "Using the created key's ID NEWKEY1234 instead of --key-id WRONGKEY12.") {
		t.Errorf("output:\n%s", out.String())
	}

	command := newInitApnsCommand()
	if err := command.ParseFlags([]string{"--create-key", "--key-id", "WRONGKEY12"}); err != nil {
		t.Fatal(err)
	}
	if err := command.ValidateFlagGroups(); err == nil || !strings.Contains(err.Error(), "key-id") {
		t.Errorf("--create-key with --key-id: %v", err)
	}
}

// askingAdapter asks one question before creating a key, as a sign-in does.
type askingAdapter struct{}

func (askingAdapter) Name() string { return defaultApnsSetup }

func (askingAdapter) PrepareAppID(_ context.Context, request apns.Request, _ apns.AppIDRequest) (apns.AppIDResult, error) {
	return apns.AppIDResult{TeamID: request.TeamID, Registered: true, Push: true}, nil
}

func (askingAdapter) CreateKey(_ context.Context, request apns.Request) (apns.Key, error) {
	if _, err := request.Asker.Ask("Enter the 6-digit code sent to +1 •••12", false); err != nil {
		return apns.Key{}, err
	}

	return apns.Key{KeyID: "NEWKEY1234", TeamID: request.TeamID, P8: apnstest.P8()}, nil
}

func TestInitApnsExplainsASignInWithoutATerminal(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	setup, _ := newTestPushSetup(t, server, prompt.NonInteractive{}, t.TempDir())
	setup.apnsAdapters = apns.NewRegistry(askingAdapter{})

	err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true}, appleApp{})
	if err == nil {
		t.Fatal("no error without a terminal")
	}
	message := err.Error()
	for _, part := range []string{`asked "Enter the 6-digit code sent to +1 •••12"`, "APPWRITE_APPLE_ID", "the two-factor code always needs one", "instead of --create-key"} {
		if !strings.Contains(message, part) {
			t.Errorf("message is missing %q: %s", part, message)
		}
	}
	if strings.Contains(message, "Pass --key-path instead") {
		t.Errorf("suggests a flag --create-key rejects: %s", message)
	}
}

func TestInitApnsResetSignsInAgainAndCreatesAKey(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	setup, out := newTestPushSetup(t, server, &prompt.Scripted{}, t.TempDir())
	adapter := useAdapter(setup, &apnstest.Adapter{KeyID: "FIRSTKEY12"})
	options := apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true}
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}

	// Already set up, yet --reset signs in again and creates a key, without
	// --create-key or --force.
	setup.keyDirs = []string{t.TempDir()}
	adapter.KeyID = "SECONDKEY1"
	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", reset: true}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(adapter.Requests) != 2 || adapter.Requests[0].Reset || !adapter.Requests[1].Reset {
		t.Fatalf("requests = %+v", adapter.Requests)
	}
	for _, provider := range messaging.providers {
		if provider["credentials"].(map[string]any)["authKeyId"] != "SECONDKEY1" {
			t.Errorf("provider = %v", provider)
		}
	}
	if !strings.Contains(out.String(), "Signing in again: the saved Apple sign-in is not used (--reset).") {
		t.Errorf("output:\n%s", out.String())
	}

	command := newInitApnsCommand()
	if err := command.ParseFlags([]string{"--reset", "--key-path", "AuthKey_KEY1234567.p8"}); err != nil {
		t.Fatal(err)
	}
	if err := command.ValidateFlagGroups(); err == nil || !strings.Contains(err.Error(), "key-path") {
		t.Errorf("--reset with --key-path: %v", err)
	}
}

const appIDSignInQuestion = "Sign in with your Apple ID to check the App ID for com.example.app?"

func TestInitApnsChecksTheAppIDAfterCreatingAKey(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	adapter := useAdapter(setup, &apnstest.Adapter{
		KeyID:       "NEWKEY1234",
		AppIDResult: apns.AppIDResult{TeamID: "ABCDE12345", Registered: true, Push: true, Created: true, PushTurnedOn: true},
	})

	options := apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", reset: true}
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(adapter.AppIDRequests) != 1 || adapter.AppIDRequests[0] != (apns.AppIDRequest{BundleID: "com.example.app", Ask: true}) {
		t.Fatalf("App ID calls = %v", adapter.AppIDRequests)
	}
	// The key's saved sign-in is reused for its Apple ID: no question, and
	// --reset does not sign in a second time.
	if signIn := adapter.AppIDSignIns[0]; signIn.TeamID != "ABCDE12345" || signIn.Reset || !signIn.SavedSignInOnly || signIn.AppleID != "dev@example.com" {
		t.Errorf("App ID sign-in = %+v", signIn)
	}
	if len(scripted.Asked) != 0 {
		t.Errorf("asked %v", scripted.Asked)
	}
	if !strings.Contains(out.String(), "Registered com.example.app as an App ID with Push Notifications on team ABCDE12345.") {
		t.Errorf("output:\n%s", out.String())
	}
}

func TestInitApnsChecksTheAppIDWithTheSavedSignIn(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	adapter := useAdapter(setup, &apnstest.Adapter{
		AppIDResult: apns.AppIDResult{TeamID: "ABCDE12345", Registered: true, Push: true},
	})

	keyPath := testApnsKey(t, t.TempDir())
	if err := setup.apns(apnsOptions{keyPath: keyPath, bundleID: "com.example.app", teamID: "ABCDE12345"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(adapter.AppIDRequests) != 1 || !adapter.AppIDRequests[0].Ask || !adapter.AppIDSignIns[0].SavedSignInOnly {
		t.Fatalf("App ID calls = %v, sign-ins = %v", adapter.AppIDRequests, adapter.AppIDSignIns)
	}
	if len(scripted.Asked) != 0 || !strings.Contains(out.String(), "com.example.app is registered as an App ID with Push Notifications.") {
		t.Errorf("asked %v, output:\n%s", scripted.Asked, out.String())
	}

	// An already set-up project is checked on every run.
	if err := setup.apns(apnsOptions{bundleID: "com.example.app"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(adapter.AppIDRequests) != 2 || adapter.AppIDSignIns[1].TeamID != "ABCDE12345" {
		t.Errorf("App ID calls = %v, sign-ins = %v", adapter.AppIDRequests, adapter.AppIDSignIns)
	}
}

func TestInitApnsAsksBeforeSigningInToCheckTheAppID(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	adapter := useAdapter(setup, &apnstest.Adapter{
		SignInNeeded: true,
		AppIDResult:  apns.AppIDResult{TeamID: "ABCDE12345", Registered: true, Push: true, PushTurnedOn: true},
	})
	keyPath := testApnsKey(t, t.TempDir())

	// No saved sign-in and no answer: skipped, and the setup still succeeds.
	if err := setup.apns(apnsOptions{keyPath: keyPath, bundleID: "com.example.app", teamID: "ABCDE12345"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(messaging.providers) != 2 || len(adapter.AppIDRequests) != 1 || strings.Join(scripted.Asked, "|") != appIDSignInQuestion {
		t.Fatalf("providers = %v, App ID calls = %v, asked %v", messaging.providers, adapter.AppIDRequests, scripted.Asked)
	}
	if !strings.Contains(out.String(), "Skipped the App ID check. Run `appwrite init apns --register-app-id`") {
		t.Errorf("output:\n%s", out.String())
	}

	// Agreeing signs in and checks.
	scripted.Choices = map[string]string{appIDSignInQuestion: "check"}
	if err := setup.apns(apnsOptions{bundleID: "com.example.app"}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(adapter.AppIDRequests) != 3 || adapter.AppIDSignIns[2].SavedSignInOnly || !adapter.AppIDRequests[2].Ask {
		t.Fatalf("App ID calls = %v, sign-ins = %v", adapter.AppIDRequests, adapter.AppIDSignIns)
	}
	if !strings.Contains(out.String(), "Turned on Push Notifications for the App ID com.example.app.") {
		t.Errorf("output:\n%s", out.String())
	}
}

func TestInitApnsRegisterAppIDSignsInAndDoesNotAsk(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	adapter := useAdapter(setup, &apnstest.Adapter{
		SignInNeeded: true,
		AppIDResult:  apns.AppIDResult{TeamID: "ABCDE12345", Registered: true, Push: true, Created: true, PushTurnedOn: true},
	})
	keyPath := testApnsKey(t, t.TempDir())
	options := apnsOptions{keyPath: keyPath, bundleID: "com.example.app", teamID: "ABCDE12345", registerAppID: true}
	if err := setup.apns(options, appleApp{}); err != nil {
		t.Fatal(err)
	}
	if len(adapter.AppIDRequests) != 1 || adapter.AppIDRequests[0].Ask || adapter.AppIDSignIns[0].SavedSignInOnly || len(scripted.Asked) != 0 {
		t.Fatalf("App ID calls = %v, sign-ins = %v, asked %v", adapter.AppIDRequests, adapter.AppIDSignIns, scripted.Asked)
	}
	if !strings.Contains(out.String(), "Registered com.example.app as an App ID with Push Notifications on team ABCDE12345.") {
		t.Errorf("output:\n%s", out.String())
	}

	// Asked for explicitly, a failure fails the command.
	adapter.AppIDErr = errors.New("could not reach Apple")
	err := setup.apns(apnsOptions{bundleID: "com.example.app", registerAppID: true}, appleApp{})
	if err == nil || !strings.Contains(err.Error(), "Could not check the App ID for com.example.app: could not reach Apple") {
		t.Errorf("err = %v", err)
	}
}

func TestInitApnsSignsInAgainWhenTheNewKeysSignInIsNotSaved(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{}
	setup, _ := newTestPushSetup(t, server, scripted, t.TempDir())
	adapter := useAdapter(setup, &apnstest.Adapter{KeyID: "NEWKEY1234", SignInNeeded: true})

	if err := setup.apns(apnsOptions{bundleID: "com.example.app", teamID: "ABCDE12345", createKey: true}, appleApp{}); err != nil {
		t.Fatal(err)
	}
	// The person just signed in for the key: no "sign in to check?" question.
	if len(adapter.AppIDSignIns) != 2 || !adapter.AppIDSignIns[0].SavedSignInOnly || adapter.AppIDSignIns[1].SavedSignInOnly || len(scripted.Asked) != 0 {
		t.Errorf("sign-ins = %+v, asked %v", adapter.AppIDSignIns, scripted.Asked)
	}
}

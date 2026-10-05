package cmd

import (
	"bytes"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/rand"
	"crypto/rsa"
	"crypto/x509"
	"encoding/base64"
	"encoding/json"
	"encoding/pem"
	"errors"
	"io"
	"net/http"
	"net/http/httptest"
	"net/url"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"testing"
	"time"

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

func TestDetectAndroidReadsGradleAndGoogleServices(t *testing.T) {
	root := t.TempDir()
	writeFile(t, filepath.Join(root, "android", "app", "build.gradle.kts"), `plugins {
    id("com.android.application")
    id("com.google.gms.google-services")
}

android {
    namespace = "com.example.namespace"
    defaultConfig {
        applicationId = "com.example.app"
    }
}
`)
	writeFile(t, filepath.Join(root, "android", "app", "google-services.json"), `{
  "project_info": {"project_id": "demo-project"},
  "client": [{"client_info": {"android_client_info": {"package_name": "com.example.app"}}}]
}`)
	writeFile(t, filepath.Join(root, "android", "lib", "build.gradle"), `plugins { id 'com.android.library' }`)

	app, err := detectAndroid(root)
	if err != nil {
		t.Fatal(err)
	}

	if strings.Join(app.ApplicationIDs, ",") != "com.example.app" {
		t.Errorf("application IDs = %v", app.ApplicationIDs)
	}
	if len(app.Firebase) != 1 || app.Firebase[0].ProjectID != "demo-project" || strings.Join(app.Firebase[0].Packages, ",") != "com.example.app" {
		t.Errorf("Firebase = %+v", app.Firebase)
	}
	if !app.HasServicesPlugin || app.HasMessaging {
		t.Errorf("plugin = %v, messaging = %v", app.HasServicesPlugin, app.HasMessaging)
	}
}

func TestDetectReadsExpoConfig(t *testing.T) {
	root := t.TempDir()
	writeFile(t, filepath.Join(root, "app.json"), `{"expo": {
  "ios": {"bundleIdentifier": "com.example.expo", "appleTeamId": "ABCDE12345"},
  "android": {"package": "com.example.expo", "googleServicesFile": "./google-services.json"}
}}`)
	writeFile(t, filepath.Join(root, "google-services.json"), `{"project_info": {"project_id": "expo-project"}, "client": []}`)

	apple, _ := detectApple(root)
	android, _ := detectAndroid(root)

	if !apple.found() || apple.BundleIDs[0] != "com.example.expo" || apple.TeamIDs[0] != "ABCDE12345" {
		t.Errorf("apple = %+v", apple)
	}
	if !android.found() || android.ApplicationIDs[0] != "com.example.expo" || strings.Join(android.projectIDs(), ",") != "expo-project" {
		t.Errorf("android = %+v", android)
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

func testServiceAccount(t *testing.T, directory, projectID string) string {
	t.Helper()
	key, err := rsa.GenerateKey(rand.Reader, 2048)
	if err != nil {
		t.Fatal(err)
	}
	encoded, _ := x509.MarshalPKCS8PrivateKey(key)
	account, _ := json.Marshal(map[string]string{
		"type":         "service_account",
		"project_id":   projectID,
		"client_email": "push@" + projectID + ".iam.gserviceaccount.com",
		"private_key":  string(pem.EncodeToMemory(&pem.Block{Type: "PRIVATE KEY", Bytes: encoded})),
		"token_uri":    "https://oauth2.googleapis.com/token",
	})
	path := filepath.Join(directory, projectID+"-firebase-adminsdk-abc.json")
	writeFile(t, path, string(account))

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

func TestReadServiceAccountChecksTheProject(t *testing.T) {
	path := testServiceAccount(t, t.TempDir(), "demo-project")
	if _, err := readServiceAccount(path, "demo-project"); err != nil {
		t.Errorf("valid key rejected: %s", err)
	}
	if _, err := readServiceAccount(path, "other-project"); err == nil || !strings.Contains(err.Error(), "not other-project") {
		t.Errorf("wrong project accepted: %v", err)
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
	for _, name := range []string{"authKey", "authKeyId", "teamId", "bundleId", "serviceAccountJSON"} {
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
		firebase:  func() ([]firebaseProject, error) { return nil, errors.New("firebase is not installed") },
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
	if strings.Join(messaging.requests, ",") != "GET /messaging/providers" {
		t.Errorf("already set up requests = %v", messaging.requests)
	}
}

func TestInitFcmRefusesAMismatchedApp(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	writeFile(t, filepath.Join(root, "app", "build.gradle"), "plugins { id 'com.android.application' }\nandroid { defaultConfig { applicationId 'com.example.other' } }\n")
	writeFile(t, filepath.Join(root, "app", "google-services.json"), `{"project_info": {"project_id": "demo-project"},
  "client": [{"client_info": {"android_client_info": {"package_name": "com.example.app"}}}]}`)

	setup, _ := newTestPushSetup(t, server, &prompt.Scripted{}, root)
	detected, _ := detectAndroid(root)
	err := setup.fcm(fcmOptions{keyPath: testServiceAccount(t, t.TempDir(), "demo-project")}, detected)
	if err == nil || !strings.Contains(err.Error(), "com.example.other") {
		t.Fatalf("err = %v", err)
	}
	if len(messaging.requests) != 0 {
		t.Errorf("requests = %v", messaging.requests)
	}
}

func TestInitFcmPicksUpTheDownloadedKey(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	writeFile(t, filepath.Join(root, "app", "build.gradle"), "plugins { id 'com.android.application' }\nandroid { defaultConfig { applicationId 'com.example.app' } }\n")
	writeFile(t, filepath.Join(root, "app", "google-services.json"), `{"project_info": {"project_id": "demo-project"},
  "client": [{"client_info": {"android_client_info": {"package_name": "com.example.app"}}}]}`)

	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, root)
	go func() {
		time.Sleep(100 * time.Millisecond)
		testServiceAccount(t, setup.downloads, "other-project")
		testServiceAccount(t, setup.downloads, "demo-project")
	}()

	detected, _ := detectAndroid(root)
	if err := setup.fcm(fcmOptions{}, detected); err != nil {
		t.Fatal(err)
	}

	if len(messaging.providers) != 1 {
		t.Fatalf("providers = %v", messaging.providers)
	}
	account := messaging.providers[0]["credentials"].(map[string]any)["serviceAccountJSON"].(map[string]any)
	if account["project_id"] != "demo-project" || messaging.providers[0]["name"] != "FCM (demo-project)" {
		t.Errorf("provider = %v", messaging.providers[0])
	}
	if !strings.Contains(out.String(), "firebase-messaging") {
		t.Errorf("missing Firebase Messaging hint:\n%s", out.String())
	}
}

func TestInitFcmSelectsTheConfigurationForTheChosenApp(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	writeFile(t, filepath.Join(root, "app", "build.gradle"), "plugins { id 'com.android.application' }\nandroid { defaultConfig { applicationId 'com.example.app' } }\n")
	writeFile(t, filepath.Join(root, "app", "src", "staging", "google-services.json"), `{"project_info": {"project_id": "staging-project"},
  "client": [{"client_info": {"android_client_info": {"package_name": "com.example.app.staging"}}}]}`)
	writeFile(t, filepath.Join(root, "app", "src", "release", "google-services.json"), `{"project_info": {"project_id": "release-project"},
  "client": [{"client_info": {"android_client_info": {"package_name": "com.example.app"}}}]}`)

	detected, _ := detectAndroid(root)
	if strings.Join(detected.projectIDs(), ",") != "release-project,staging-project" && strings.Join(detected.projectIDs(), ",") != "staging-project,release-project" {
		t.Fatalf("projects = %v", detected.projectIDs())
	}

	setup, _ := newTestPushSetup(t, server, &prompt.Scripted{}, root)
	if err := setup.fcm(fcmOptions{keyPath: testServiceAccount(t, t.TempDir(), "release-project")}, detected); err != nil {
		t.Fatal(err)
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["name"] != "FCM (release-project)" {
		t.Fatalf("providers = %v", messaging.providers)
	}

	fresh := httptest.NewServer(&fakeMessaging{})
	defer fresh.Close()
	warned, out := newTestPushSetup(t, fresh, &prompt.Scripted{Choices: map[string]string{
		"How would you like to provide the Firebase service account key (.json)?": "file",
	}}, root)
	testServiceAccount(t, root, "staging-project")
	_ = warned.fcm(fcmOptions{applicationID: "com.example.app"}, detected)
	if !strings.Contains(out.String(), "Skipping staging-project-firebase-adminsdk-abc.json: it belongs to Firebase project staging-project, not release-project.") {
		t.Errorf("output:\n%s", out.String())
	}

	err := setup.fcm(fcmOptions{keyPath: testServiceAccount(t, t.TempDir(), "staging-project")}, detected)
	if err == nil || !strings.Contains(err.Error(), "not release-project") {
		t.Errorf("err = %v", err)
	}

	err = setup.fcm(fcmOptions{applicationID: "com.example.app.staging", projectID: "release-project"}, detected)
	if err == nil || !strings.Contains(err.Error(), "belongs to staging-project") {
		t.Errorf("err = %v", err)
	}
}

func TestInitFcmTakesTheProjectFromTheDownloadedKey(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	var opened []string
	setup.open = func(page string) { opened = append(opened, page) }
	go func() {
		time.Sleep(100 * time.Millisecond)
		testServiceAccount(t, setup.downloads, "picked-project")
	}()

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatal(err)
	}

	if strings.Join(scripted.Asked, "|") != "How would you like to provide the Firebase service account key (.json)?" {
		t.Errorf("asked %v", scripted.Asked)
	}
	if strings.Join(opened, ",") != "https://console.firebase.google.com/project/_/settings/serviceaccounts/adminsdk" {
		t.Errorf("opened %v", opened)
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["name"] != "FCM (picked-project)" {
		t.Errorf("providers = %v", messaging.providers)
	}
	if !strings.Contains(out.String(), "Using Firebase project picked-project from the key.") {
		t.Errorf("output:\n%s", out.String())
	}
}

func TestInitFcmOffersTheFirebaseCLIProjects(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	scripted := &prompt.Scripted{Choices: map[string]string{
		"Which Firebase project should send push notifications?": "tools-4821",
	}}
	setup, _ := newTestPushSetup(t, server, scripted, t.TempDir())
	var opened []string
	setup.open = func(page string) { opened = append(opened, page) }
	var offered []prompt.Option
	setup.prompter = recordingPrompter{Scripted: scripted, choices: &offered}
	setup.firebase = func() ([]firebaseProject, error) {
		return []firebaseProject{
			{ProjectID: "sticker-smash-fb", DisplayName: "Sticker Smash"},
			{ProjectID: "tools-4821", DisplayName: "tools-4821"},
		}, nil
	}
	go func() {
		time.Sleep(100 * time.Millisecond)
		testServiceAccount(t, setup.downloads, "sticker-smash-fb")
		testServiceAccount(t, setup.downloads, "tools-4821")
	}()

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatal(err)
	}

	labels := []string{}
	for _, option := range offered {
		labels = append(labels, option.Label)
	}
	if strings.Join(labels, "|") != "Sticker Smash (sticker-smash-fb)|tools-4821|Another project (choose it in the Firebase console)" {
		t.Errorf("offered %v", labels)
	}
	if len(opened) != 1 || !strings.Contains(opened[0], "/project/tools-4821/settings/serviceaccounts") {
		t.Errorf("opened %v", opened)
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["name"] != "FCM (tools-4821)" {
		t.Errorf("providers = %v", messaging.providers)
	}
}

type recordingPrompter struct {
	*prompt.Scripted
	choices *[]prompt.Option
}

func (r recordingPrompter) Choice(question prompt.Choice) (string, error) {
	if question.Message == "Which Firebase project should send push notifications?" {
		*r.choices = question.Options
	}

	return r.Scripted.Choice(question)
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

func TestInitFcmUsesAKeyInTheProjectFolderBeforeAskingForAProject(t *testing.T) {
	messaging := &fakeMessaging{}
	server := httptest.NewServer(messaging)
	defer server.Close()

	root := t.TempDir()
	testServiceAccount(t, root, "root-project")
	writeFile(t, filepath.Join(root, "package.json"), `{"name": "app"}`)

	scripted := &prompt.Scripted{}
	setup, _ := newTestPushSetup(t, server, scripted, root)
	setup.firebase = func() ([]firebaseProject, error) {
		t.Error("the Firebase project list was requested")

		return nil, nil
	}
	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatal(err)
	}

	if len(scripted.Asked) != 0 {
		t.Errorf("asked %v", scripted.Asked)
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["name"] != "FCM (root-project)" {
		t.Errorf("providers = %v", messaging.providers)
	}

	messaging.requests = nil
	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatal(err)
	}
	if strings.Join(messaging.requests, ",") != "GET /messaging/providers" {
		t.Errorf("rerun requests = %v", messaging.requests)
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

type fakeGoogle struct {
	t           *testing.T
	projects    []map[string]any
	keyBlocked  bool
	missing     []string
	requests    []string
	policy      map[string]any
	deletedKeys []string
	token       string
	onKey       func()
}

func (f *fakeGoogle) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	f.requests = append(f.requests, r.Method+" "+r.URL.Path)
	if r.Header.Get("Authorization") != "Bearer "+f.token {
		w.WriteHeader(http.StatusUnauthorized)
		_ = json.NewEncoder(w).Encode(map[string]any{"error": map[string]any{"message": "invalid token", "status": "UNAUTHENTICATED"}})

		return
	}
	body, _ := io.ReadAll(r.Body)
	var params map[string]any
	_ = json.Unmarshal(body, &params)
	path := r.URL.Path

	switch {
	case path == "/firebase/v1beta1/projects":
		_ = json.NewEncoder(w).Encode(map[string]any{"results": f.projects})
	case strings.HasSuffix(path, ":testIamPermissions"):
		granted := []string{}
		for _, permission := range fcmProvisionPermissions {
			if !contains(f.missing, permission) {
				granted = append(granted, permission)
			}
		}
		_ = json.NewEncoder(w).Encode(map[string]any{"permissions": granted})
	case strings.HasSuffix(path, "/services:batchEnable"):
		_ = json.NewEncoder(w).Encode(map[string]any{"name": "operations/1", "done": true})
	case strings.HasSuffix(path, ":getIamPolicy"):
		_ = json.NewEncoder(w).Encode(map[string]any{"etag": "e1", "bindings": []any{map[string]any{"role": "roles/owner", "members": []any{"user:dev@example.com"}}}})
	case strings.HasSuffix(path, ":setIamPolicy"):
		f.policy, _ = params["policy"].(map[string]any)
		_ = json.NewEncoder(w).Encode(f.policy)
	case r.Method == "POST" && strings.HasSuffix(path, "/serviceAccounts"):
		project := strings.Split(path, "/")[4]
		_ = json.NewEncoder(w).Encode(map[string]any{"email": params["accountId"].(string) + "@" + project + ".iam.gserviceaccount.com"})
	case r.Method == "POST" && strings.HasSuffix(path, "/keys"):
		if f.keyBlocked {
			w.WriteHeader(http.StatusPreconditionFailed)
			_ = json.NewEncoder(w).Encode(map[string]any{"error": map[string]any{"message": "Key creation is not allowed on this service account.", "status": "FAILED_PRECONDITION"}})

			return
		}
		if f.onKey != nil {
			f.onKey()
		}
		project := strings.Split(path, "/")[4]
		contents, _ := os.ReadFile(testServiceAccount(f.t, f.t.TempDir(), project))
		_ = json.NewEncoder(w).Encode(map[string]any{"name": "projects/" + project + "/serviceAccounts/sa/keys/k1", "privateKeyData": base64.StdEncoding.EncodeToString(contents)})
	case r.Method == "DELETE":
		f.deletedKeys = append(f.deletedKeys, strings.TrimPrefix(path, "/iam/v1/"))
	default:
		w.WriteHeader(http.StatusNotFound)
	}
}

func newGoogleTestSetup(t *testing.T, messaging *fakeMessaging, google *fakeGoogle, answers map[string]string) (*pushSetup, *bytes.Buffer, *prompt.Scripted) {
	t.Helper()
	server := httptest.NewServer(messaging)
	t.Cleanup(server.Close)
	googleServer := httptest.NewServer(http.StripPrefix("", google))
	t.Cleanup(googleServer.Close)

	scripted := &prompt.Scripted{Choices: answers}
	setup, out := newTestPushSetup(t, server, scripted, t.TempDir())
	setup.console = client.New(server.URL, "test").SetProject("console")
	setup.googleHosts = map[string]string{
		"firebase":     googleServer.URL + "/firebase",
		"crm":          googleServer.URL + "/crm",
		"iam":          googleServer.URL + "/iam",
		"serviceusage": googleServer.URL + "/serviceusage",
	}
	setup.googlePoll = 10 * time.Millisecond
	setup.googleTimeout = 2 * time.Second

	return setup, out, scripted
}

func linkedGoogle(token string) map[string]any {
	return map[string]any{
		"provider":                  "google",
		"providerEmail":             "dev@example.com",
		"providerAccessToken":       token,
		"providerAccessTokenExpiry": time.Now().Add(time.Hour).UTC().Format(time.RFC3339Nano),
	}
}

func TestInitFcmCreatesTheKeyWithGoogle(t *testing.T) {
	messaging := &fakeMessaging{identities: []map[string]any{linkedGoogle("live-token")}}
	google := &fakeGoogle{t: t, token: "live-token", projects: []map[string]any{
		{"projectId": "sticker-smash-fb", "displayName": "Sticker Smash", "state": "ACTIVE"},
		{"projectId": "tools-4821", "displayName": "Tools", "state": "ACTIVE"},
		{"projectId": "old-project", "state": "DELETED"},
	}}
	setup, out, scripted := newGoogleTestSetup(t, messaging, google, map[string]string{
		"Which Firebase project should send push notifications?": "tools-4821",
	})
	var opened []string
	setup.open = func(page string) { opened = append(opened, page) }

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatalf("%s\n%s", err, out.String())
	}

	if len(opened) != 0 {
		t.Errorf("opened %v although a Google token was already linked", opened)
	}
	if strings.Join(scripted.Asked, "|") != "How would you like to provide the Firebase service account key (.json)?|Which Firebase project should send push notifications?" {
		t.Errorf("asked %v", scripted.Asked)
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["name"] != "FCM (tools-4821)" {
		t.Fatalf("providers = %v", messaging.providers)
	}
	account := messaging.providers[0]["credentials"].(map[string]any)["serviceAccountJSON"].(map[string]any)
	if account["project_id"] != "tools-4821" {
		t.Errorf("account = %v", account)
	}
	policy, _ := json.Marshal(google.policy)
	if !strings.Contains(string(policy), `"role":"roles/firebasecloudmessaging.admin"`) || !strings.Contains(string(policy), "@tools-4821.iam.gserviceaccount.com") {
		t.Errorf("policy = %s", policy)
	}
	if len(google.deletedKeys) != 0 {
		t.Errorf("deleted %v", google.deletedKeys)
	}
}

func TestInitFcmSignsInWithGoogleThroughTheConsole(t *testing.T) {
	messaging := &fakeMessaging{}
	google := &fakeGoogle{t: t, token: "fresh-token", projects: []map[string]any{activeProject("only-project")}}
	setup, out, scripted := newGoogleTestSetup(t, messaging, google, nil)
	var opened []string
	setup.open = func(page string) {
		opened = append(opened, page)
		messaging.identities = []map[string]any{linkedGoogle("fresh-token")}
	}

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatalf("%s\n%s", err, out.String())
	}

	if len(opened) != 1 {
		t.Fatalf("opened %v", opened)
	}
	signIn, err := url.Parse(opened[0])
	if err != nil {
		t.Fatal(err)
	}
	query := signIn.Query()
	if signIn.Path != "/account/tokens/oauth2/google" || query.Get("project") != "console" || query.Get("scopes[]") != googleCloudScope {
		t.Errorf("sign-in URL = %s", opened[0])
	}
	if !contains(scripted.Asked, "Which Firebase project should send push notifications?") {
		t.Errorf("the only Firebase project was used without asking: %v", scripted.Asked)
	}
	if strings.Contains(out.String(), "from the key") {
		t.Errorf("output:\n%s", out.String())
	}
	if !strings.Contains(out.String(), "signed in to the Appwrite console as dev@example.com") {
		t.Errorf("output:\n%s", out.String())
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["name"] != "FCM (only-project)" {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestInitFcmFallsBackWhenGoogleCannotCreateTheKey(t *testing.T) {
	messaging := &fakeMessaging{identities: []map[string]any{linkedGoogle("live-token")}}
	google := &fakeGoogle{t: t, token: "live-token", keyBlocked: true, projects: []map[string]any{activeProject("locked-project")}}
	setup, out, _ := newGoogleTestSetup(t, messaging, google, nil)
	go func() {
		time.Sleep(100 * time.Millisecond)
		testServiceAccount(t, setup.downloads, "locked-project")
	}()

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatalf("%s\n%s", err, out.String())
	}

	if !strings.Contains(out.String(), "an organization policy blocks service account key creation") {
		t.Errorf("output:\n%s", out.String())
	}
	if len(messaging.providers) != 1 || messaging.providers[0]["name"] != "FCM (locked-project)" {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestInitFcmReportsMissingGooglePermissions(t *testing.T) {
	messaging := &fakeMessaging{identities: []map[string]any{linkedGoogle("live-token")}}
	google := &fakeGoogle{t: t, token: "live-token", missing: []string{"resourcemanager.projects.setIamPolicy"},
		projects: []map[string]any{activeProject("shared-project")}}
	setup, out, _ := newGoogleTestSetup(t, messaging, google, nil)
	go func() {
		time.Sleep(100 * time.Millisecond)
		testServiceAccount(t, setup.downloads, "shared-project")
	}()

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(out.String(), "missing resourcemanager.projects.setIamPolicy on shared-project") {
		t.Errorf("output:\n%s", out.String())
	}
	for _, request := range google.requests {
		if strings.HasSuffix(request, "/serviceAccounts") {
			t.Errorf("created a service account without the permissions: %v", google.requests)
		}
	}
}

func TestInitFcmRevokesTheCreatedKeyWhenTheProviderIsRejected(t *testing.T) {
	messaging := &fakeMessaging{identities: []map[string]any{linkedGoogle("live-token")}, failCreate: true}
	google := &fakeGoogle{t: t, token: "live-token", projects: []map[string]any{activeProject("only-project")}}
	setup, _, _ := newGoogleTestSetup(t, messaging, google, nil)

	if err := setup.fcm(fcmOptions{}, androidApp{}); err == nil {
		t.Fatal("expected the provider creation to fail")
	}
	if strings.Join(google.deletedKeys, ",") != "projects/only-project/serviceAccounts/sa/keys/k1" {
		t.Errorf("deleted %v", google.deletedKeys)
	}
}

func TestInitFcmWithGoogleKeepsAnExistingProvider(t *testing.T) {
	messaging := &fakeMessaging{identities: []map[string]any{linkedGoogle("live-token")}}
	messaging.providers = append(messaging.providers, providerDocument("fcm", "id0", map[string]any{
		"name": "FCM (only-project)", "enabled": true,
		"serviceAccountJSON": map[string]any{"project_id": "only-project"},
	}))
	google := &fakeGoogle{t: t, token: "live-token", projects: []map[string]any{activeProject("only-project")}}
	setup, out, _ := newGoogleTestSetup(t, messaging, google, nil)

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatal(err)
	}
	if !strings.Contains(out.String(), "FCM is already set up for Firebase project only-project.") {
		t.Errorf("output:\n%s", out.String())
	}
	for _, request := range google.requests {
		if strings.HasSuffix(request, "/serviceAccounts") {
			t.Errorf("created a service account for a project that is already set up: %v", google.requests)
		}
	}
}

func activeProject(id string) map[string]any {
	return map[string]any{"projectId": id, "state": "ACTIVE"}
}

func TestInitFcmRevokesTheCreatedKeyWhenTheProviderLookupFails(t *testing.T) {
	messaging := &fakeMessaging{identities: []map[string]any{linkedGoogle("live-token")}}
	google := &fakeGoogle{t: t, token: "live-token", projects: []map[string]any{activeProject("only-project")}}
	setup, _, _ := newGoogleTestSetup(t, messaging, google, nil)
	setup.firebase = nil
	google.onKey = func() { messaging.failList = 1 }

	if err := setup.fcm(fcmOptions{}, androidApp{}); err == nil {
		t.Fatal("expected the provider lookup to fail")
	}
	if strings.Join(google.deletedKeys, ",") != "projects/only-project/serviceAccounts/sa/keys/k1" {
		t.Errorf("deleted %v", google.deletedKeys)
	}
	if len(messaging.providers) != 0 {
		t.Errorf("providers = %v", messaging.providers)
	}
}

func TestInitFcmKeepsTheCreatedKeyOnceTheProviderIsSaved(t *testing.T) {
	messaging := &fakeMessaging{identities: []map[string]any{linkedGoogle("live-token")}}
	google := &fakeGoogle{t: t, token: "live-token", projects: []map[string]any{activeProject("only-project")}}
	setup, _, _ := newGoogleTestSetup(t, messaging, google, nil)

	if err := setup.fcm(fcmOptions{}, androidApp{}); err != nil {
		t.Fatal(err)
	}
	if len(google.deletedKeys) != 0 || len(messaging.providers) != 1 {
		t.Errorf("deleted %v, providers %v", google.deletedKeys, messaging.providers)
	}
}

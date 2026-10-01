package cmd

import (
	"bytes"
	"crypto/ecdsa"
	"crypto/elliptic"
	"crypto/rand"
	"crypto/rsa"
	"crypto/x509"
	"encoding/json"
	"encoding/pem"
	"io"
	"net/http"
	"net/http/httptest"
	"os"
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
		1 /* Debug */ = {
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
		2 /* Debug */ = {
			isa = XCBuildConfiguration;
			buildSettings = {
				BUNDLE_LOADER = "$(TEST_HOST)";
				DEVELOPMENT_TEAM = ABCDE12345;
				PRODUCT_BUNDLE_IDENTIFIER = com.example.MyAppTests;
				TEST_HOST = "$(BUILT_PRODUCTS_DIR)/MyApp.app/MyApp";
			};
			name = Debug;
		};
	};
}
`

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

	if strings.Join(app.BundleIDs, ",") != "org.reactjs.native.example.My-App" {
		t.Errorf("bundle IDs = %v", app.BundleIDs)
	}
	if strings.Join(app.TeamIDs, ",") != "ABCDE12345" {
		t.Errorf("team IDs = %v", app.TeamIDs)
	}
	if len(app.Entitlements) != 1 || app.Entitlements[0] != filepath.Join(root, "ios", "MyApp", "MyApp.entitlements") {
		t.Errorf("entitlements = %v", app.Entitlements)
	}
	if len(app.InfoPlists) != 1 || app.InfoPlists[0] != filepath.Join(root, "ios", "MyApp", "Info.plist") {
		t.Errorf("Info.plist = %v", app.InfoPlists)
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
	if app.ProjectID != "demo-project" || strings.Join(app.FirebasePackages, ",") != "com.example.app" {
		t.Errorf("Firebase = %q %v", app.ProjectID, app.FirebasePackages)
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
	if !android.found() || android.ApplicationIDs[0] != "com.example.expo" || android.ProjectID != "expo-project" {
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

func TestWaitForDownloadIgnoresEarlierFiles(t *testing.T) {
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

	path, err := waitForDownload(directory, started, func(path string) bool {
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
	providers []map[string]any
	requests  []string
}

func (f *fakeMessaging) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	f.requests = append(f.requests, r.Method+" "+r.URL.Path)
	body, _ := io.ReadAll(r.Body)
	var params map[string]any
	_ = json.Unmarshal(body, &params)

	switch {
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
	keyPath := testApnsKey(t, t.TempDir())

	scripted := &prompt.Scripted{}
	setup, _ := newTestPushSetup(t, server, scripted, root)
	detected, _ := detectApple(root)
	if err := setup.apns(apnsOptions{keyPath: keyPath}, detected); err != nil {
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
		if credentials["bundleId"] != "org.reactjs.native.example.My-App" || credentials["teamId"] != "ABCDE12345" ||
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

	messaging.requests = nil
	if err := setup.apns(apnsOptions{keyPath: keyPath}, detected); err != nil {
		t.Fatal(err)
	}
	if len(messaging.providers) != 2 || strings.Join(messaging.requests, ",") != "GET /messaging/providers,PATCH /messaging/providers/apns/id0,PATCH /messaging/providers/apns/id1" {
		t.Errorf("rerun requests = %v", messaging.requests)
	}

	messaging.requests = nil
	if err := setup.apns(apnsOptions{}, detected); err != nil {
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

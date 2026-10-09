package appwrite

import (
	"bytes"
	"context"
	"crypto/sha1"
	"encoding/base64"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"math/big"
	"net/http"
	"net/http/httptest"
	"net/url"
	"os"
	"path/filepath"
	"strings"
	"sync"
	"testing"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)
{% verbatim %}
const (
	testAppleID  = "dev@example.com"
	testPassword = "correct horse battery staple"
	testCode     = "123456"
	testP8       = "-----BEGIN PRIVATE KEY-----\nMIGT\n-----END PRIVATE KEY-----"
)

// fakeApple plays idmsa.apple.com, App Store Connect and the Developer
// portal: it runs the server side of SRP, so a wrong password or a wrong
// proof fails the way it would against Apple.
type fakeApple struct {
	mu       sync.Mutex
	t        *testing.T
	requests []string

	widgetKeyGone    bool
	noTwoFactor      bool
	noTrustedDevices bool
	olympusRefuses   bool
	// generation names the current signed-in cookie; bumping it expires
	// every session handed out before.
	generation int
	textsSent  int
	// hashcashBits is the difficulty asked for; empty means 8.
	hashcashBits string
	teams        []Team
	keys         []apns.ExistingKey
	maxKeys      bool

	salt     []byte
	b        *big.Int
	serverB  []byte
	clientA  []byte
	verifier *big.Int
	created  map[string]map[string]any
}

func newFakeApple(t *testing.T) *fakeApple {
	return &fakeApple{
		t:       t,
		teams:   []Team{{ID: "ABCDE12345", Name: "Example Inc", Type: "Company/Organization"}},
		salt:    []byte("0123456789abcdef"),
		created: map[string]map[string]any{},
	}
}

func (f *fakeApple) record(r *http.Request) {
	f.mu.Lock()
	defer f.mu.Unlock()
	f.requests = append(f.requests, r.Method+" "+r.URL.Path)
}

func (f *fakeApple) signedIn(r *http.Request) bool {
	cookie, err := r.Cookie("myacinfo")

	return err == nil && cookie.Value == f.sessionCookie().Value
}

func (f *fakeApple) sessionCookie() *http.Cookie {
	return &http.Cookie{Name: "myacinfo", Value: fmt.Sprintf("signed-in-%d", f.generation), Path: "/"}
}

// trusted reports whether the request carries the cookie 2sv/trust handed
// out, quoted the way Apple quotes it.
func (f *fakeApple) trusted(r *http.Request) bool {
	cookie, err := r.Cookie("DES123")

	return err == nil && cookie.Value == "trusted==SRVT" && cookie.Quoted
}

func writeJSON(w http.ResponseWriter, status int, value any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(value)
}

func (f *fakeApple) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	f.record(r)
	body, _ := io.ReadAll(r.Body)
	var params map[string]any
	if strings.HasPrefix(r.Header.Get("Content-Type"), "application/x-www-form-urlencoded") {
		form, _ := url.ParseQuery(string(body))
		params = map[string]any{}
		for name := range form {
			params[name] = form.Get(name)
		}
	} else {
		_ = json.Unmarshal(body, &params)
	}
	idmsa := r.Header.Get("X-Apple-Widget-Key") == "widget-key"
	twoFactorSession := r.Header.Get("X-Apple-Id-Session-Id") == "session-1" && r.Header.Get("scnt") == "scnt-1"

	switch {
	case r.Method == http.MethodHead && r.URL.Path == "/logout":
		if len(r.Cookies()) > 0 {
			f.t.Error("the widget key request sent cookies, which would sign the session out")
		}
		if !f.widgetKeyGone {
			w.Header().Set("Location", "https://idmsa.apple.com/IDMSWebAuth/signout?widgetKey=widget-key&path=/")
		}
		w.WriteHeader(http.StatusFound)

	case r.URL.Path == "/olympus/v1/app/config":
		w.WriteHeader(http.StatusNotFound)

	case r.URL.Path == "/appleauth/auth/signin/init" && idmsa:
		f.clientA, _ = base64.StdEncoding.DecodeString(params["a"].(string))
		key, _ := passwordKey(testPassword, f.salt, 1000, "s2k")
		x := new(big.Int).SetBytes(sha256Of(f.salt, sha256Of([]byte(":"), key)))
		f.verifier = new(big.Int).Exp(srpG, x, srpN)
		f.b = big.NewInt(987654321)
		B := new(big.Int).Mul(hashPadded(srpN.Bytes(), srpG.Bytes()), f.verifier)
		B.Add(B, new(big.Int).Exp(srpG, f.b, srpN))
		B.Mod(B, srpN)
		f.serverB = B.Bytes()
		writeJSON(w, http.StatusOK, map[string]any{
			"iteration": 1000, "salt": base64.StdEncoding.EncodeToString(f.salt), "protocol": "s2k",
			"b": base64.StdEncoding.EncodeToString(f.serverB), "c": "challenge-c",
		})

	case r.Method == http.MethodGet && r.URL.Path == "/appleauth/auth/signin":
		bits := f.hashcashBits
		if bits == "" {
			bits = "8"
		}
		w.Header().Set("X-Apple-HC-Bits", bits)
		w.Header().Set("X-Apple-HC-Challenge", "hc-challenge")
		w.WriteHeader(http.StatusOK)

	case r.URL.Path == "/appleauth/auth/signin/complete" && idmsa:
		token := r.Header.Get("X-Apple-HC")
		if f.hashcashBits != "" && token != "" {
			f.t.Errorf("answered a %s-bit hashcash: %q", f.hashcashBits, token)
		} else if f.hashcashBits == "" && (!strings.Contains(token, ":hc-challenge::") || leadingZeroBits(sha1.Sum([]byte(token))) < 8) {
			f.t.Errorf("hashcash = %q", token)
		}
		if params["c"] != "challenge-c" || params["accountName"] != testAppleID {
			f.t.Errorf("complete params = %v", params)
		}
		// The server's side of SRP-6a: S = (A * v^u) ^ b mod N.
		A := new(big.Int).SetBytes(f.clientA)
		u := hashPadded(f.clientA, f.serverB)
		S := new(big.Int).Exp(f.verifier, u, srpN)
		S.Mul(S, A)
		S.Exp(S, f.b, srpN)
		K := sha256Of(S.Bytes())
		xor := new(big.Int).Xor(hashPadded(srpN.Bytes()), hashPadded(srpG.Bytes()))
		expected := sha256Of(xor.Bytes(), sha256Of([]byte(testAppleID)), f.salt, f.clientA, f.serverB, K)
		m1, _ := base64.StdEncoding.DecodeString(params["m1"].(string))
		if !bytes.Equal(m1, expected) {
			writeJSON(w, http.StatusUnauthorized, map[string]any{"serviceErrors": []map[string]string{{"code": "-20101", "message": "Your Apple ID or password was incorrect."}}})

			return
		}
		if f.noTwoFactor || f.trusted(r) {
			http.SetCookie(w, f.sessionCookie())
			w.WriteHeader(http.StatusOK)

			return
		}
		w.Header().Set("X-Apple-Id-Session-Id", "session-1")
		w.Header().Set("scnt", "scnt-1")
		writeJSON(w, http.StatusConflict, map[string]any{"authType": "hsa2"})

	case r.Method == http.MethodGet && r.URL.Path == "/appleauth/auth" && twoFactorSession:
		writeJSON(w, http.StatusOK, map[string]any{
			"trustedPhoneNumbers": []map[string]any{{"id": 1, "numberWithDialCode": "+1 (•••) •••-••12", "pushMode": "sms"}},
			"securityCode":        map[string]any{"length": 6},
			"noTrustedDevices":    f.noTrustedDevices,
		})

	case r.URL.Path == "/appleauth/auth/verify/trusteddevice/securitycode" && twoFactorSession:
		code := params["securityCode"].(map[string]any)["code"]
		if code != testCode {
			writeJSON(w, http.StatusBadRequest, map[string]any{"serviceErrors": []map[string]string{{"code": "-21669", "message": "Incorrect verification code."}}})

			return
		}
		http.SetCookie(w, f.sessionCookie())
		w.WriteHeader(http.StatusNoContent)

	case r.Method == http.MethodPut && r.URL.Path == "/appleauth/auth/verify/phone" && twoFactorSession:
		f.textsSent++
		writeJSON(w, http.StatusOK, map[string]any{})

	case r.URL.Path == "/appleauth/auth/verify/phone/securitycode" && twoFactorSession:
		phone, _ := params["phoneNumber"].(map[string]any)
		code := params["securityCode"].(map[string]any)["code"]
		if phone["id"] != float64(1) || params["mode"] != "sms" || code != testCode {
			writeJSON(w, http.StatusBadRequest, map[string]any{"serviceErrors": []map[string]string{{"code": "-21669", "message": "Incorrect verification code."}}})

			return
		}
		http.SetCookie(w, f.sessionCookie())
		w.WriteHeader(http.StatusOK)

	case r.URL.Path == "/appleauth/auth/2sv/trust" && twoFactorSession:
		http.SetCookie(w, &http.Cookie{Name: "DES123", Value: "trusted==SRVT", Quoted: true, Path: "/"})
		w.WriteHeader(http.StatusNoContent)

	case r.URL.Path == "/olympus/v1/session":
		if !f.signedIn(r) || f.olympusRefuses {
			w.WriteHeader(http.StatusUnauthorized)

			return
		}
		writeJSON(w, http.StatusOK, map[string]any{
			"user": map[string]any{"emailAddress": testAppleID}, "provider": map[string]any{"providerId": 1, "name": "Example Inc"},
		})

	case strings.HasPrefix(r.URL.Path, "/services-account/QH65B2/"):
		if !f.signedIn(r) {
			w.WriteHeader(http.StatusUnauthorized)

			return
		}
		f.portal(w, r, strings.TrimPrefix(r.URL.Path, "/services-account/QH65B2/"), params)

	default:
		f.t.Errorf("unexpected request %s %s", r.Method, r.URL.Path)
		w.WriteHeader(http.StatusNotFound)
	}
}

func (f *fakeApple) portal(w http.ResponseWriter, r *http.Request, path string, params map[string]any) {
	switch path {
	case "account/listTeams.action":
		// The real endpoint refuses a JSON body with 415.
		if r.Header.Get("Content-Type") != "" || r.ContentLength > 0 {
			w.WriteHeader(http.StatusUnsupportedMediaType)

			return
		}
		writeJSON(w, http.StatusOK, map[string]any{"resultCode": 0, "teams": f.teams})
	case "account/auth/key/list":
		// Like Apple, the key list takes a form and refuses JSON with 415.
		if !strings.HasPrefix(r.Header.Get("Content-Type"), "application/x-www-form-urlencoded") {
			w.WriteHeader(http.StatusUnsupportedMediaType)

			return
		}
		if params["teamId"] == nil || params["pageSize"] != "500" || params["sort"] != "name=asc" {
			f.t.Errorf("list params = %v", params)
		}
		w.Header().Set("csrf", "csrf-token")
		w.Header().Set("csrf_ts", "csrf-ts")
		keys := []map[string]any{}
		for _, key := range f.keys {
			keys = append(keys, map[string]any{"keyId": key.ID, "keyName": key.Name, "canRevoke": key.CanRevoke})
		}
		writeJSON(w, http.StatusOK, map[string]any{"resultCode": 0, "keys": keys})
	case "account/auth/key/v2/create":
		if r.Header.Get("Content-Type") != "application/json" {
			w.WriteHeader(http.StatusUnsupportedMediaType)

			return
		}
		if r.Header.Get("csrf") != "csrf-token" || r.Header.Get("csrf_ts") != "csrf-ts" {
			f.t.Errorf("create sent csrf %q / %q", r.Header.Get("csrf"), r.Header.Get("csrf_ts"))
		}
		if f.maxKeys {
			writeJSON(w, http.StatusOK, map[string]any{
				"resultCode":   9401,
				"resultString": "You%20have%20already%20reached%20the%20maximum%20allowed%20number%20of%20team%20scoped%20Keys%20for%20this%20service%20in%20production%20and%20sandbox%20environment.",
			})

			return
		}
		f.created["NEWKEY1234"] = params
		writeJSON(w, http.StatusOK, map[string]any{"resultCode": 0, "keys": []map[string]any{{"keyId": "NEWKEY1234", "keyName": params["name"]}}})
	case "account/auth/key/download":
		if r.URL.Query().Get("keyId") != "NEWKEY1234" || r.URL.Query().Get("teamId") == "" {
			f.t.Errorf("download query = %v", r.URL.Query())
		}
		w.Header().Set("Content-Type", "text/plain")
		_, _ = w.Write([]byte(testP8))
	default:
		f.t.Errorf("unexpected portal call %s", path)
		w.WriteHeader(http.StatusNotFound)
	}
}

type scriptedAsker struct {
	answers map[string][]string
	choices map[string]int
	asked   []string
}

func (s *scriptedAsker) Ask(question string, secret bool) (string, error) {
	s.asked = append(s.asked, question)
	for prefix, answers := range s.answers {
		if strings.HasPrefix(question, prefix) && len(answers) > 0 {
			s.answers[prefix] = answers[1:]

			return answers[0], nil
		}
	}

	return "", errors.New("no answer for " + question)
}

func (s *scriptedAsker) Choose(question string, options []string) (int, error) {
	s.asked = append(s.asked, question)
	if index, ok := s.choices[question]; ok {
		return index, nil
	}

	return 0, errors.New("no choice for " + question)
}

// testClient runs the adapter against the fake with a fixed request, the way
// the CLI does.
type testClient struct {
	adapter *Adapter
	request apns.Request
}

func (c testClient) CreateKey(ctx context.Context, teamID, name string, environment apns.Environment) (apns.Key, error) {
	request := c.request
	request.TeamID, request.Name, request.Environment = teamID, name, environment

	return c.adapter.CreateKey(ctx, request)
}

// newTestClient points the adapter at fake. credentials holds the Apple ID
// and password the CLI would pass from APPWRITE_APPLE_ID and
// APPWRITE_APPLE_PASSWORD.
func newTestClient(t *testing.T, fake *fakeApple, asker *scriptedAsker, dir string, credentials map[string]string) (testClient, *[]string) {
	server := httptest.NewServer(fake)
	t.Cleanup(server.Close)
	logged := &[]string{}

	return testClient{
		adapter: &Adapter{
			HTTP:  server.Client(),
			Hosts: Hosts{Idmsa: server.URL, AppStoreConnect: server.URL, Portal: server.URL},
			Now:   func() time.Time { return time.Date(2026, 10, 9, 12, 0, 0, 0, time.UTC) },
		},
		request: apns.Request{
			AppleID:    credentials["APPWRITE_APPLE_ID"],
			Password:   credentials["APPWRITE_APPLE_PASSWORD"],
			SessionDir: dir,
			Asker:      asker,
			Log: func(format string, args ...any) {
				*logged = append(*logged, strings.TrimSpace(fmt.Sprintf(format, args...)))
			},
		},
	}, logged
}

func TestCreateKeySignsInWithTwoFactorAndReusesTheSession(t *testing.T) {
	fake := newFakeApple(t)
	dir := t.TempDir()
	asker := &scriptedAsker{answers: map[string][]string{
		"Apple ID":       {testAppleID},
		"Password for":   {testPassword},
		"Enter the 6-di": {"000000", testCode},
	}}
	client, logged := newTestClient(t, fake, asker, dir, nil)

	key, err := client.CreateKey(context.Background(), "", "Appwrite Push", apns.EnvironmentAll)
	if err != nil {
		t.Fatal(err)
	}
	if key.KeyID != "NEWKEY1234" || key.TeamID != "ABCDE12345" || key.P8 != testP8 {
		t.Errorf("key = %+v", key)
	}
	created := fake.created["NEWKEY1234"]
	configurations, _ := json.Marshal(created["serviceConfigurations"])
	requests, _ := json.Marshal(created["serviceConfigurationsRequests"])
	if created["teamId"] != "ABCDE12345" || created["name"] != "Appwrite Push" || created["scope"] != "team" ||
		string(configurations) != `{"U27F4V844T":[]}` ||
		string(requests) != `[{"environment":"all","identifiers":{},"isNew":true,"scope":"team","serviceId":"U27F4V844T"}]` {
		t.Errorf("create params = %v", created)
	}
	if strings.Join(asker.asked, "|") != "Apple ID (email)|Password for "+testAppleID+"|Enter the 6-digit code shown on your Apple device (or type sms to get a text message)|Enter the 6-digit code shown on your Apple device (or type sms to get a text message)" {
		t.Errorf("asked %v", asker.asked)
	}
	if !contains(*logged, "That code is not right. Try again.") || !contains(*logged, "Using Apple team Example Inc (ABCDE12345).") {
		t.Errorf("logged %v", *logged)
	}
	info, err := os.Stat(filepath.Join(dir, "session.json"))
	if err != nil || info.Mode().Perm() != 0o600 {
		t.Fatalf("session file = %v, %v", info, err)
	}

	// A second run reuses the trusted session: no questions, no sign-in.
	again := &scriptedAsker{}
	second, logged := newTestClient(t, fake, again, dir, nil)
	fake.requests = nil
	if _, err := second.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push 2", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}
	if len(again.asked) != 0 {
		t.Errorf("asked %v", again.asked)
	}
	for _, request := range fake.requests {
		if strings.Contains(request, "/appleauth/") {
			t.Errorf("signed in again: %v", fake.requests)

			break
		}
	}
	if !contains(*logged, "Using the saved Apple sign-in for "+testAppleID+".") {
		t.Errorf("logged %v", *logged)
	}
}

func TestCreateKeyUsesTheCredentialsInTheRequest(t *testing.T) {
	fake := newFakeApple(t)
	fake.noTwoFactor = true
	asker := &scriptedAsker{}
	client, logged := newTestClient(t, fake, asker, t.TempDir(), map[string]string{
		"APPWRITE_APPLE_ID":       testAppleID,
		"APPWRITE_APPLE_PASSWORD": testPassword,
	})

	if _, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}
	if len(asker.asked) != 0 {
		t.Errorf("asked %v", asker.asked)
	}
	for _, line := range *logged {
		if strings.Contains(line, testPassword) {
			t.Errorf("logged the password: %v", *logged)
		}
	}
}

func TestCreateKeyRejectsAWrongPassword(t *testing.T) {
	fake := newFakeApple(t)
	dir := t.TempDir()
	asker := &scriptedAsker{answers: map[string][]string{"Apple ID": {testAppleID}, "Password for": {"wrong"}}}
	client, _ := newTestClient(t, fake, asker, dir, nil)

	if _, err := client.CreateKey(context.Background(), "", "Appwrite Push", apns.EnvironmentAll); !errors.Is(err, apns.ErrInvalidCredentials) {
		t.Fatalf("err = %v", err)
	}
	if _, err := os.Stat(filepath.Join(dir, "session.json")); err == nil {
		t.Error("saved a session after a failed sign-in")
	}
}

func TestCreateKeyReportsAFullTeam(t *testing.T) {
	fake := newFakeApple(t)
	fake.noTwoFactor = true
	fake.maxKeys = true
	fake.keys = []apns.ExistingKey{{ID: "OLDKEY1234", Name: "Firebase", CanRevoke: true}, {ID: "OLDKEY5678", Name: "Expo", CanRevoke: true}}
	client, _ := newTestClient(t, fake, &scriptedAsker{}, t.TempDir(), map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword})

	_, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll)
	var maxKeys *apns.MaxKeysError
	if !errors.As(err, &maxKeys) || len(maxKeys.Keys) != 2 || maxKeys.Keys[0].ID != "OLDKEY1234" || maxKeys.Keys[1].Name != "Expo" {
		t.Fatalf("err = %v", err)
	}
	if len(fake.created) != 0 {
		t.Errorf("created %v", fake.created)
	}
}

func TestCreateKeyChoosesAmongTeams(t *testing.T) {
	fake := newFakeApple(t)
	fake.noTwoFactor = true
	fake.teams = []Team{{ID: "AAAAA11111", Name: "Personal"}, {ID: "ABCDE12345", Name: "Example Inc"}}
	env := map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword}
	asker := &scriptedAsker{choices: map[string]int{"Which Apple Developer team should own the key?": 1}}
	client, _ := newTestClient(t, fake, asker, t.TempDir(), env)

	key, err := client.CreateKey(context.Background(), "", "Appwrite Push", apns.EnvironmentAll)
	if err != nil || key.TeamID != "ABCDE12345" {
		t.Fatalf("key = %+v, %v", key, err)
	}

	other, _ := newTestClient(t, fake, &scriptedAsker{}, t.TempDir(), env)
	if _, err := other.CreateKey(context.Background(), "ZZZZZ99999", "Appwrite Push", apns.EnvironmentAll); err == nil ||
		!strings.Contains(err.Error(), "AAAAA11111, ABCDE12345") {
		t.Errorf("err = %v", err)
	}
}

func TestWidgetKeyFallsBackToTheSavedCopy(t *testing.T) {
	fake := newFakeApple(t)
	fake.noTwoFactor = true
	fake.widgetKeyGone = true
	dir := t.TempDir()
	if err := os.WriteFile(filepath.Join(dir, "widget-key"), []byte("widget-key\n"), 0o600); err != nil {
		t.Fatal(err)
	}
	client, _ := newTestClient(t, fake, &scriptedAsker{}, dir, map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword})
	if _, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}

	empty, _ := newTestClient(t, fake, &scriptedAsker{}, t.TempDir(), map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword})
	if _, err := empty.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err == nil ||
		!strings.Contains(err.Error(), "sign-in key") {
		t.Errorf("err = %v", err)
	}
}

func contains(lines []string, line string) bool {
	for _, candidate := range lines {
		if candidate == line {
			return true
		}
	}

	return false
}

func TestCreateKeyWithATextMessageCode(t *testing.T) {
	fake := newFakeApple(t)
	fake.noTrustedDevices = true
	asker := &scriptedAsker{answers: map[string][]string{"Enter the 6-digit code sent to": {testCode}}}
	client, _ := newTestClient(t, fake, asker, t.TempDir(), map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword})

	if _, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}
	if strings.Join(asker.asked, "|") != "Enter the 6-digit code sent to +1 (•••) •••-••12" {
		t.Errorf("asked %v", asker.asked)
	}
	requests := strings.Join(fake.requests, ",")
	// With no trusted devices and one phone, Apple has already texted the code.
	if !strings.Contains(requests, "POST /appleauth/auth/verify/phone/securitycode") || strings.Contains(requests, "PUT /appleauth/auth/verify/phone,") {
		t.Errorf("requests = %v", fake.requests)
	}
}

func TestTheSignInIsSavedAsSoonAsTheCodeIsAccepted(t *testing.T) {
	fake := newFakeApple(t)
	fake.olympusRefuses = true
	dir := t.TempDir()
	env := map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword}
	client, _ := newTestClient(t, fake, &scriptedAsker{answers: map[string][]string{"Enter the 6-di": {testCode}}}, dir, env)

	if _, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err == nil ||
		!strings.Contains(err.Error(), "refused the session") {
		t.Fatalf("err = %v", err)
	}
	if _, err := os.Stat(filepath.Join(dir, "session.json")); err != nil {
		t.Fatalf("the accepted sign-in was not saved: %v", err)
	}

	fake.olympusRefuses = false
	again := &scriptedAsker{}
	second, _ := newTestClient(t, fake, again, dir, env)
	if _, err := second.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}
	if len(again.asked) != 0 {
		t.Errorf("asked %v", again.asked)
	}
}

func TestAnExpiredSessionSignsInAgainWithoutACode(t *testing.T) {
	fake := newFakeApple(t)
	dir := t.TempDir()
	env := map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword}
	first, _ := newTestClient(t, fake, &scriptedAsker{answers: map[string][]string{"Enter the 6-di": {testCode}}}, dir, env)
	if _, err := first.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}

	fake.generation++
	fake.requests = nil
	again := &scriptedAsker{}
	second, _ := newTestClient(t, fake, again, dir, env)
	if _, err := second.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}
	if len(again.asked) != 0 {
		t.Errorf("asked %v", again.asked)
	}
	requests := strings.Join(fake.requests, ",")
	if !strings.Contains(requests, "/appleauth/auth/signin/complete") || strings.Contains(requests, "/securitycode") {
		t.Errorf("requests = %v", fake.requests)
	}
}

func TestCreateKeyForOneEnvironment(t *testing.T) {
	fake := newFakeApple(t)
	fake.noTwoFactor = true
	env := map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword}
	client, _ := newTestClient(t, fake, &scriptedAsker{}, t.TempDir(), env)

	if _, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentSandbox); err != nil {
		t.Fatal(err)
	}
	requests := fake.created["NEWKEY1234"]["serviceConfigurationsRequests"].([]any)
	if environment := requests[0].(map[string]any)["environment"]; environment != "sandbox" {
		t.Errorf("environment = %v", environment)
	}

}

func TestChoosingATextMessageIsNotACodeAttempt(t *testing.T) {
	fake := newFakeApple(t)
	asker := &scriptedAsker{answers: map[string][]string{
		"Enter the 6-digit code shown":   {"sms"},
		"Enter the 6-digit code sent to": {"000000", "111111", testCode},
	}}
	client, _ := newTestClient(t, fake, asker, t.TempDir(), map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword})

	if _, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatalf("the third code was not accepted: %v", err)
	}
	if fake.textsSent != 1 || len(asker.asked) != 4 {
		t.Errorf("texts = %d, asked %v", fake.textsSent, asker.asked)
	}
}

func TestAnImpracticalHashcashIsNotAnswered(t *testing.T) {
	fake := newFakeApple(t)
	fake.noTwoFactor = true
	fake.hashcashBits = "40"
	client, _ := newTestClient(t, fake, &scriptedAsker{}, t.TempDir(), map[string]string{"APPWRITE_APPLE_ID": testAppleID, "APPWRITE_APPLE_PASSWORD": testPassword})

	if _, err := client.CreateKey(context.Background(), "ABCDE12345", "Appwrite Push", apns.EnvironmentAll); err != nil {
		t.Fatal(err)
	}
}

func TestMaxKeysMessages(t *testing.T) {
	for message, full := range map[string]bool{
		"You have already reached the maximum allowed number of Keys for this service.":                                                   true,
		"You have already reached the maximum allowed number of team scoped Keys for this service in production and sandbox environment.": true,
		"An unexpected error occurred.": false,
	} {
		if got := maxKeysMessage.MatchString(message); got != full {
			t.Errorf("maxKeysMessage(%q) = %v", message, got)
		}
	}
}
{% endverbatim %}
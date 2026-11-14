package client

import (
	"os"
	"path/filepath"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/config"
)

// global writes a prefs.json as the cli does, and loads it.
func global(t *testing.T, body string) *config.Global {
	t.Helper()
	path := filepath.Join(t.TempDir(), "prefs.json")
	if err := os.WriteFile(path, []byte(body), 0o600); err != nil {
		t.Fatal(err)
	}
	return config.LoadGlobal(path)
}

// TestResolve checks which credential the options resolve to, in the cli's order: the login's
// token or cookie over the key `appwrite client --key` stored, and the console project without a
// project; and what stops them.
func TestResolve(t *testing.T) {
	cookie := global(t, `{"current":"a1","a1":{"endpoint":"https://appwrite.example.com/v1","email":"ada@example.com","cookie":"a_session_console=abc","selfSigned":"true"}}`)
	token := global(t, `{"current":"b2","b2":{"endpoint":"https://cloud.appwrite.io/v1","email":"grace@example.com","accessToken":"tok","tokenExpiry":4102444800000}}`)
	stored := global(t, `{"current":"c3","c3":{"endpoint":"https://appwrite.example.com/v1","key":"stored"}}`)
	both := global(t, `{"current":"d4","d4":{"endpoint":"https://appwrite.example.com/v1","email":"ada@example.com","cookie":"a_session_console=abc","key":"stored"}}`)
	staging := global(t, `{"current":"e5","e5":{"endpoint":"https://cloud.staging.appwrite.io/v1","email":"grace@example.com","cookie":"a_session_console=abc"}}`)
	empty := global(t, `{"current":"a1","a1":{"endpoint":"https://cloud.appwrite.io/v1"}}`)
	missing := config.LoadGlobal(filepath.Join(t.TempDir(), "prefs.json"))
	tests := []struct {
		name         string
		opts         Options
		wantEndpoint string
		wantProject  string
		wantKey      string
		wantCookie   string
		wantToken    string
		wantEmail    string
		wantSelf     bool
		wantCloud    bool
		wantErr      string
	}{
		{name: "stored key", opts: Options{Project: "shop", Global: stored},
			wantEndpoint: "https://appwrite.example.com/v1", wantProject: "shop", wantKey: "stored"},
		{name: "stored key without project", opts: Options{Global: stored}, wantErr: "missing a project"},
		{name: "stored key of another instance", opts: Options{Project: "shop", Endpoint: "https://other.example.com/v1", Global: stored},
			wantErr: "signed in to https://appwrite.example.com/v1"},
		{name: "login over a stored key", opts: Options{Project: "shop", Global: both},
			wantEndpoint: "https://appwrite.example.com/v1", wantProject: "shop", wantCookie: "a_session_console=abc", wantEmail: "ada@example.com"},
		{name: "cookie login", opts: Options{Global: cookie},
			wantEndpoint: "https://appwrite.example.com/v1", wantProject: "console", wantCookie: "a_session_console=abc", wantEmail: "ada@example.com", wantSelf: true},
		{name: "login with project", opts: Options{Project: "shop", Endpoint: "https://appwrite.example.com/v1/", Global: cookie},
			wantEndpoint: "https://appwrite.example.com/v1", wantProject: "shop", wantCookie: "a_session_console=abc", wantEmail: "ada@example.com", wantSelf: true},
		{name: "token login on a region", opts: Options{Project: "shop", Endpoint: "https://fra.cloud.appwrite.io/v1", Global: token},
			wantEndpoint: "https://cloud.appwrite.io/v1", wantProject: "shop", wantToken: "tok", wantEmail: "grace@example.com", wantCloud: true},
		{name: "login on staging", opts: Options{Global: staging},
			wantEndpoint: "https://cloud.staging.appwrite.io/v1", wantProject: "console", wantCookie: "a_session_console=abc", wantEmail: "grace@example.com", wantCloud: true},
		{name: "login of another instance", opts: Options{Endpoint: "https://other.example.com/v1", Global: cookie}, wantErr: "signed in to https://appwrite.example.com/v1"},
		{name: "no credential in the login", opts: Options{Global: empty}, wantErr: "not signed in"},
		{name: "no prefs", opts: Options{Global: missing}, wantErr: "not signed in"},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			got, email, err := tt.opts.resolve()
			if tt.wantErr != "" {
				if err == nil || !strings.Contains(err.Error(), tt.wantErr) {
					t.Fatalf("error %v, want %q", err, tt.wantErr)
				}
				return
			}
			if err != nil {
				t.Fatal(err)
			}
			if got.Endpoint != tt.wantEndpoint || got.Project != tt.wantProject || got.Key != tt.wantKey ||
				got.Cookie != tt.wantCookie || got.SelfSigned != tt.wantSelf || got.Cloud != tt.wantCloud || email != tt.wantEmail {
				t.Errorf("resolved %+v as %q", got, email)
			}
			if (got.Token != nil) != (tt.wantToken != "") {
				t.Fatalf("token func set %t, want %t", got.Token != nil, tt.wantToken != "")
			}
			if got.Token == nil {
				return
			}
			if tok, err := got.Token(); err != nil || tok != tt.wantToken {
				t.Errorf("token %q, %v, want %q", tok, err, tt.wantToken)
			}
		})
	}
}

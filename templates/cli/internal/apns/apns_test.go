package apns_test

import (
	"context"
	"errors"
	"strings"
	"testing"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns/apnstest"
)

func TestParseEnvironment(t *testing.T) {
	for value, want := range map[string]apns.Environment{
		"":           apns.EnvironmentAll,
		"all":        apns.EnvironmentAll,
		"Production": apns.EnvironmentProduction,
		" sandbox ":  apns.EnvironmentSandbox,
	} {
		if got, err := apns.ParseEnvironment(value); err != nil || got != want {
			t.Errorf("ParseEnvironment(%q) = %q, %v", value, got, err)
		}
	}
	if _, err := apns.ParseEnvironment("staging"); err == nil {
		t.Error("accepted staging")
	}
}

func TestCreateKeyPassesTheRequestThrough(t *testing.T) {
	adapter := &apnstest.Adapter{KeyID: "NEWKEY1234", TeamID: "TEAM123456"}
	key, err := apns.CreateKey(context.Background(), adapter, apns.Request{Name: "Appwrite Push", Environment: "Sandbox"})
	if err != nil {
		t.Fatal(err)
	}
	if key.KeyID != "NEWKEY1234" || key.TeamID != "TEAM123456" {
		t.Errorf("key = %+v", key)
	}
	if len(adapter.Requests) != 1 || adapter.Requests[0].Environment != apns.EnvironmentSandbox || adapter.Requests[0].Log == nil {
		t.Errorf("requests = %+v", adapter.Requests)
	}
}

func TestCreateKeyChecksTheRequest(t *testing.T) {
	adapter := &apnstest.Adapter{KeyID: "NEWKEY1234"}
	for _, request := range []apns.Request{
		{Name: "Appwrite Push", Environment: "staging"},
		{Name: " "},
		{Name: "Appwrite Push", TeamID: "team"},
	} {
		if _, err := apns.CreateKey(context.Background(), adapter, request); err == nil {
			t.Errorf("accepted %+v", request)
		}
	}
	if len(adapter.Requests) != 0 {
		t.Errorf("ran the adapter for a bad request: %+v", adapter.Requests)
	}
}

func TestCreateKeyHoldsAdaptersToTheContract(t *testing.T) {
	for _, test := range []struct {
		name    string
		adapter apns.Adapter
		want    string
	}{
		{"key ID", &apnstest.Adapter{KeyID: "short", TeamID: "TEAM123456"}, `invalid key ID "short"`},
		{"team ID", &apnstest.Adapter{KeyID: "NEWKEY1234"}, `invalid team ID ""`},
		{"other team", &otherTeam{}, "on team OTHER12345, not TEAM123456"},
	} {
		t.Run(test.name, func(t *testing.T) {
			_, err := apns.CreateKey(context.Background(), test.adapter, apns.Request{Name: "Appwrite Push", TeamID: teamFor(test.name)})
			if err == nil || !strings.Contains(err.Error(), test.want) {
				t.Errorf("err = %v", err)
			}
		})
	}

	failing := &apnstest.Adapter{Err: apns.ErrInvalidCredentials}
	if _, err := apns.CreateKey(context.Background(), failing, apns.Request{Name: "Appwrite Push"}); !errors.Is(err, apns.ErrInvalidCredentials) {
		t.Errorf("err = %v", err)
	}
}

func teamFor(test string) string {
	if test == "other team" {
		return "TEAM123456"
	}

	return ""
}

type otherTeam struct{}

func (otherTeam) Name() string { return "other" }

func (otherTeam) CreateKey(context.Context, apns.Request) (apns.Key, error) {
	return apns.Key{KeyID: "NEWKEY1234", TeamID: "OTHER12345", P8: apnstest.P8()}, nil
}

func TestRegistry(t *testing.T) {
	empty := apns.NewRegistry()
	if _, err := empty.Get("appwrite"); err == nil || !strings.Contains(err.Error(), "this build has none") {
		t.Errorf("empty registry err = %v", err)
	}

	registry := apns.NewRegistry(&apnstest.Adapter{AdapterName: "expo"}, &apnstest.Adapter{AdapterName: "appwrite"})
	if names := strings.Join(registry.Names(), ","); names != "appwrite,expo" {
		t.Errorf("names = %s", names)
	}
	if adapter, err := registry.Get(" Expo "); err != nil || adapter.Name() != "expo" {
		t.Errorf("Get(expo) = %v, %v", adapter, err)
	}
	if _, err := registry.Get("fastlane"); err == nil || err.Error() != `unknown APNs key setup "fastlane": use one of appwrite, expo` {
		t.Errorf("err = %v", err)
	}
}

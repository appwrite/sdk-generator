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

func (otherTeam) PrepareAppID(context.Context, apns.Request, apns.AppIDRequest) (apns.AppIDResult, error) {
	return apns.AppIDResult{TeamID: "OTHER12345", Registered: true, Push: true}, nil
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

func TestPrepareAppIDChecksTheRequest(t *testing.T) {
	adapter := &apnstest.Adapter{TeamID: "TEAM123456"}
	for _, test := range []struct {
		bundleID, teamID, want string
	}{
		{"", "", "is not a bundle ID"},
		{"com.example app", "", "is not a bundle ID"},
		{"com.example.app", "team", "is not an Apple team ID"},
	} {
		if _, err := apns.PrepareAppID(context.Background(), adapter, apns.Request{TeamID: test.teamID}, apns.AppIDRequest{BundleID: test.bundleID}); err == nil || !strings.Contains(err.Error(), test.want) {
			t.Errorf("%q, %q: err = %v", test.bundleID, test.teamID, err)
		}
	}
	if len(adapter.AppIDRequests) != 0 {
		t.Errorf("ran the adapter for a bad request: %+v", adapter.AppIDRequests)
	}

	result, err := apns.PrepareAppID(context.Background(), adapter, apns.Request{}, apns.AppIDRequest{BundleID: "com.example.app", Ask: true})
	if err != nil || result != (apns.AppIDResult{TeamID: "TEAM123456", Registered: true, Push: true}) || !adapter.AppIDRequests[0].Ask {
		t.Errorf("result = %+v, %v, calls %v", result, err, adapter.AppIDRequests)
	}
}

func TestPrepareAppIDHoldsAdaptersToTheContract(t *testing.T) {
	for _, test := range []struct {
		name    string
		adapter apns.Adapter
		teamID  string
		want    string
	}{
		{"team ID", &apnstest.Adapter{AppIDResult: apns.AppIDResult{TeamID: "bad", Registered: true}}, "", `invalid team ID "bad"`},
		{"other team", &otherTeam{}, "TEAM123456", "on team OTHER12345, not TEAM123456"},
		{"created but not registered", &apnstest.Adapter{AppIDResult: apns.AppIDResult{TeamID: "TEAM123456", Created: true}}, "", "inconsistent"},
		{"push turned on but off", &apnstest.Adapter{AppIDResult: apns.AppIDResult{TeamID: "TEAM123456", Registered: true, PushTurnedOn: true}}, "", "inconsistent"},
	} {
		t.Run(test.name, func(t *testing.T) {
			_, err := apns.PrepareAppID(context.Background(), test.adapter, apns.Request{TeamID: test.teamID}, apns.AppIDRequest{BundleID: "com.example.app"})
			if err == nil || !strings.Contains(err.Error(), test.want) {
				t.Errorf("err = %v", err)
			}
		})
	}

	needed := &apnstest.Adapter{SignInNeeded: true}
	if _, err := apns.PrepareAppID(context.Background(), needed, apns.Request{SavedSignInOnly: true}, apns.AppIDRequest{BundleID: "com.example.app"}); !errors.Is(err, apns.ErrSignInNeeded) {
		t.Errorf("err = %v", err)
	}
}

func TestAppIDNameKeepsLettersAndDigits(t *testing.T) {
	if name := apns.AppIDName("io.appwrite.push-test_2"); name != "io appwrite push test 2" {
		t.Errorf("name = %q", name)
	}
}

type choosing struct {
	asked  []string
	choice int
}

func (c *choosing) Ask(string, bool) (string, error) { return "", errors.New("no questions") }

func (c *choosing) Choose(question string, options []string) (int, error) {
	c.asked = append(c.asked, question+" "+strings.Join(options, "/"))

	return c.choice, nil
}

func TestConfirmAppIDChange(t *testing.T) {
	asker := &choosing{}
	if yes, err := apns.ConfirmAppIDChange(asker, "com.example.app", "TEAM123456", false); !yes || err != nil {
		t.Errorf("register = %v, %v", yes, err)
	}
	asker.choice = 1
	if yes, err := apns.ConfirmAppIDChange(asker, "com.example.app", "TEAM123456", true); yes || err != nil {
		t.Errorf("turn on = %v, %v", yes, err)
	}
	if strings.Join(asker.asked, "|") != "com.example.app is not registered as an App ID on team TEAM123456, so APNs refuses pushes for it. Register it with Push Notifications? Yes/No|Push Notifications is off for com.example.app, so APNs refuses pushes for it. Turn it on? Yes/No" {
		t.Errorf("asked %v", asker.asked)
	}
}

package client

import (
	"net/http"
	"net/http/httptest"
	"strings"
	"testing"
)

// The API's own wording for an unauthenticated request describes a permission
// model the user did not ask about and names no way out of it.
func TestUnauthenticatedRequestSaysHowToFixIt(t *testing.T) {
	apiError := &APIError{
		Status:  401,
		Code:    401,
		Type:    unauthorizedScopeType,
		Message: `User (role: guests) missing scopes (["account"])`,
	}

	message := apiError.Error()
	if !strings.Contains(message, "not authenticated") ||
		!strings.Contains(message, ExecutableName+" login") {
		t.Errorf("the guests 401 was repeated verbatim: %q", message)
	}
}

// Message keeps what the API said, so --verbose and a bug report still carry it.
func TestUnauthenticatedRequestKeepsTheAPIWording(t *testing.T) {
	raw := `User (role: guests) missing scopes (["account"])`
	apiError := &APIError{Status: 401, Code: 401, Type: unauthorizedScopeType, Message: raw}

	if apiError.Message != raw {
		t.Errorf("the API's own message was overwritten: %q", apiError.Message)
	}
}

// Only this one error is rewritten. A 401 of another type, or a scope error
// against a real account, is a different problem -- and a message about signing
// in would hide it.
func TestOtherFailuresAreNotRewritten(t *testing.T) {
	cases := []*APIError{
		// A real account that lacks one scope: the role is not `guests`.
		{Status: 401, Code: 401, Type: unauthorizedScopeType,
			Message: `User (role: member) missing scopes (["teams.write"])`},
		// 401, guests, but a different type -- MFA, for one.
		{Status: 401, Code: 401, Type: "user_more_factors_required",
			Message: `More factors are required (role: guests)`},
		// The type and role match but it is not a 401.
		{Status: 403, Code: 403, Type: unauthorizedScopeType,
			Message: `User (role: guests) missing scopes (["account"])`},
	}

	for _, apiError := range cases {
		if got := apiError.Error(); got != apiError.Message {
			t.Errorf("rewrote %s/%s: %q", apiError.Type, apiError.Message, got)
		}
	}
}

// The role is matched the way the TypeScript matches it: `/role:\s*guests/i`,
// case-insensitive and tolerant of the space.
//
// Deliberately no word boundary after `guests`, because the TypeScript has none
// either and this is a port. A stricter match here would mean the CLI builds
// disagreed about which failure gets which message, which is a worse outcome
// than sharing a loose pattern -- there is no role whose name merely begins with
// `guests` for it to catch.
func TestGuestRoleMatching(t *testing.T) {
	for _, message := range []string{
		"User (role: guests) missing scopes",
		"User (role:guests) missing scopes",
		"User (Role: Guests) missing scopes",
	} {
		apiError := &APIError{Code: 401, Type: unauthorizedScopeType, Message: message}
		if apiError.Error() == message {
			t.Errorf("did not recognise %q as unauthenticated", message)
		}
	}
}

// A body that is not the API's JSON at all -- a proxy error page -- still has to
// produce something better than an empty message.
func TestNonJSONFailureFallsBackToTheStatus(t *testing.T) {
	server := httptest.NewServer(http.HandlerFunc(func(w http.ResponseWriter, _ *http.Request) {
		w.WriteHeader(http.StatusBadGateway)
		_, _ = w.Write([]byte("<html>502 Bad Gateway</html>"))
	}))
	defer server.Close()

	err := New(server.URL, "0.0.1").Call("GET", "/account", nil, nil)
	if err == nil {
		t.Fatal("a 502 was not reported as an error")
	}
	if !strings.Contains(err.Error(), "502") {
		t.Errorf("the status is missing from %q", err)
	}
}

package appwrite

import (
	"strings"
)

// Account service
type Account struct {
	client Client
}

func NewAccount(clt Client) Account {  
    service := Account{
		client: clt,
	}

    return service
}

// Get get currently logged in user data as JSON object.
func (srv *Account) Get() (map[string]interface{}, error) {
	path := "/account"

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// Delete delete a currently logged in user account. Behind the scene, the
// user record is not deleted but permanently blocked from any access. This is
// done to avoid deleted accounts being overtaken by new users with the same
// email address. Any user-related resources like documents or storage files
// should be deleted separately.
func (srv *Account) Delete() (map[string]interface{}, error) {
	path := "/account"

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}

// UpdateEmail update currently logged in user account email address. After
// changing user address, user confirmation status is being reset and a new
// confirmation mail is sent. For security measures, user password is required
// to complete this request.
// This endpoint can also be used to convert an anonymous account to a normal
// one, by passing an email address and a new password.
func (srv *Account) UpdateEmail(Email string, Password string) (map[string]interface{}, error) {
	path := "/account/email"

	params := map[string]interface{}{
		"email": Email,
		"password": Password,
	}

	return srv.client.Call("PATCH", path, nil, params)
}

// GetLogs get currently logged in user list of latest security activity logs.
// Each log returns user IP address, location and date and time of log.
func (srv *Account) GetLogs() (map[string]interface{}, error) {
	path := "/account/logs"

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// UpdateName update currently logged in user account name.
func (srv *Account) UpdateName(Name string) (map[string]interface{}, error) {
	path := "/account/name"

	params := map[string]interface{}{
		"name": Name,
	}

	return srv.client.Call("PATCH", path, nil, params)
}

// UpdatePassword update currently logged in user password. For validation,
// user is required to pass in the new password, and the old password. For
// users created with OAuth and Team Invites, oldPassword is optional.
func (srv *Account) UpdatePassword(Password string, OldPassword string) (map[string]interface{}, error) {
	path := "/account/password"

	params := map[string]interface{}{
		"password": Password,
		"oldPassword": OldPassword,
	}

	return srv.client.Call("PATCH", path, nil, params)
}

// GetPrefs get currently logged in user preferences as a key-value object.
func (srv *Account) GetPrefs() (map[string]interface{}, error) {
	path := "/account/prefs"

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// UpdatePrefs update currently logged in user account preferences. You can
// pass only the specific settings you wish to update.
func (srv *Account) UpdatePrefs(Prefs object) (map[string]interface{}, error) {
	path := "/account/prefs"

	params := map[string]interface{}{
		"prefs": Prefs,
	}

	return srv.client.Call("PATCH", path, nil, params)
}

// CreateRecovery sends the user an email with a temporary secret key for
// password reset. When the user clicks the confirmation link he is redirected
// back to your app password reset URL with the secret key and email address
// values attached to the URL query string. Use the query string params to
// submit a request to the [PUT
// /account/recovery](/docs/client/account#accountUpdateRecovery) endpoint to
// complete the process. The verification link sent to the user's email
// address is valid for 1 hour.
func (srv *Account) CreateRecovery(Email string, Url string) (map[string]interface{}, error) {
	path := "/account/recovery"

	params := map[string]interface{}{
		"email": Email,
		"url": Url,
	}

	return srv.client.Call("POST", path, nil, params)
}

// UpdateRecovery use this endpoint to complete the user account password
// reset. Both the **userId** and **secret** arguments will be passed as query
// parameters to the redirect URL you have provided when sending your request
// to the [POST /account/recovery](/docs/client/account#accountCreateRecovery)
// endpoint.
// 
// Please note that in order to avoid a [Redirect
// Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
// the only valid redirect URLs are the ones from domains you have set when
// adding your platforms in the console interface.
func (srv *Account) UpdateRecovery(UserId string, Secret string, Password string, PasswordAgain string) (map[string]interface{}, error) {
	path := "/account/recovery"

	params := map[string]interface{}{
		"userId": UserId,
		"secret": Secret,
		"password": Password,
		"passwordAgain": PasswordAgain,
	}

	return srv.client.Call("PUT", path, nil, params)
}

// GetSessions get currently logged in user list of active sessions across
// different devices.
func (srv *Account) GetSessions() (map[string]interface{}, error) {
	path := "/account/sessions"

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// DeleteSessions delete all sessions from the user account and remove any
// sessions cookies from the end client.
func (srv *Account) DeleteSessions() (map[string]interface{}, error) {
	path := "/account/sessions"

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}

// GetSession use this endpoint to get a logged in user's session using a
// Session ID. Inputting 'current' will return the current session being used.
func (srv *Account) GetSession(SessionId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{sessionId}", SessionId)
	path := r.Replace("/account/sessions/{sessionId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// DeleteSession use this endpoint to log out the currently logged in user
// from all their account sessions across all of their different devices. When
// using the option id argument, only the session unique ID provider will be
// deleted.
func (srv *Account) DeleteSession(SessionId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{sessionId}", SessionId)
	path := r.Replace("/account/sessions/{sessionId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}

// CreateVerification use this endpoint to send a verification message to your
// user email address to confirm they are the valid owners of that address.
// Both the **userId** and **secret** arguments will be passed as query
// parameters to the URL you have provided to be attached to the verification
// email. The provided URL should redirect the user back to your app and allow
// you to complete the verification process by verifying both the **userId**
// and **secret** parameters. Learn more about how to [complete the
// verification process](/docs/client/account#accountUpdateVerification). The
// verification link sent to the user's email address is valid for 7 days.
// 
// Please note that in order to avoid a [Redirect
// Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md),
// the only valid redirect URLs are the ones from domains you have set when
// adding your platforms in the console interface.
// 
func (srv *Account) CreateVerification(Url string) (map[string]interface{}, error) {
	path := "/account/verification"

	params := map[string]interface{}{
		"url": Url,
	}

	return srv.client.Call("POST", path, nil, params)
}

// UpdateVerification use this endpoint to complete the user email
// verification process. Use both the **userId** and **secret** parameters
// that were attached to your app URL to verify the user email ownership. If
// confirmed this route will return a 200 status code.
func (srv *Account) UpdateVerification(UserId string, Secret string) (map[string]interface{}, error) {
	path := "/account/verification"

	params := map[string]interface{}{
		"userId": UserId,
		"secret": Secret,
	}

	return srv.client.Call("PUT", path, nil, params)
}

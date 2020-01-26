

class Auth: Service
{
    /**
     * Login
     *
     * Allow the user to login into his account by providing a valid email and
     * password combination. Use the success and failure arguments to provide a
     * redirect URL\'s back to your app when login is completed. 
     * 
     * Please notice that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     * 
     * When accessing this route using Javascript from the browser, success and
     * failure parameter URLs are required. Appwrite server will respond with a
     * 301 redirect status code and will set the user session cookie. This
     * behavior is enforced because modern browsers are limiting 3rd party cookies
     * in XHR of fetch requests to protect user privacy.
     *
     * @param String email
     * @param String password
     * @param String success
     * @param String failure
     * @throws Exception
     * @return array
     */

    func login(email: String, password: String, success: String = null, failure: String = null)-> Array<Any> {
        let methodPath = "/auth/login"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["email"] = email
        params["password"] = password
        params["success"] = success
        params["failure"] = failure

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Login with OAuth
     *
     * Allow the user to login to his account using the OAuth provider of his
     * choice. Each OAuth provider should be enabled from the Appwrite console
     * first. Use the success and failure arguments to provide a redirect URL's
     * back to your app when login is completed.
     *
     * @param String provider
     * @param String success
     * @param String failure
     * @throws Exception
     * @return array
     */

    func oauth(provider: String, success: String, failure: String)-> Array<Any> {
        let methodPath = "/auth/login/oauth/{provider}"
        let path = methodPath.replacingOccurrences(
          of: "['//{provider}']",
          with: "$provider",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["success"] = success
        params["failure"] = failure

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Logout Current Session
     *
     * Use this endpoint to log out the currently logged in user from his account.
     * When successful this endpoint will delete the user session and remove the
     * session secret cookie from the user client.
     *
     * @throws Exception
     * @return array
     */

    func logout()-> Array<Any> {
        let methodPath = "/auth/logout"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Logout Specific Session
     *
     * Use this endpoint to log out the currently logged in user from all his
     * account sessions across all his different devices. When using the option id
     * argument, only the session unique ID provider will be deleted.
     *
     * @param String id
     * @throws Exception
     * @return array
     */

    func logoutBySession(id: String)-> Array<Any> {
        let methodPath = "/auth/logout/{id}"
        let path = methodPath.replacingOccurrences(
          of: "['//{id}']",
          with: "$id",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Password Recovery
     *
     * Sends the user an email with a temporary secret token for password reset.
     * When the user clicks the confirmation link he is redirected back to your
     * app password reset redirect URL with a secret token and email address
     * values attached to the URL query string. Use the query string params to
     * submit a request to the /auth/password/reset endpoint to complete the
     * process.
     *
     * @param String email
     * @param String reset
     * @throws Exception
     * @return array
     */

    func recovery(email: String, reset: String)-> Array<Any> {
        let methodPath = "/auth/recovery"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["email"] = email
        params["reset"] = reset

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Password Reset
     *
     * Use this endpoint to complete the user account password reset. Both the
     * **userId** and **token** arguments will be passed as query parameters to
     * the redirect URL you have provided when sending your request to the
     * /auth/recovery endpoint.
     * 
     * Please notice that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param String userId
     * @param String token
     * @param String passwordA
     * @param String passwordB
     * @throws Exception
     * @return array
     */

    func recoveryReset(userId: String, token: String, passwordA: String, passwordB: String)-> Array<Any> {
        let methodPath = "/auth/recovery/reset"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["userId"] = userId
        params["token"] = token
        params["password-a"] = passwordA
        params["password-b"] = passwordB

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Register
     *
     * Use this endpoint to allow a new user to register an account in your
     * project. Use the success and failure URLs to redirect users back to your
     * application after signup completes.
     * 
     * If registration completes successfully user will be sent with a
     * confirmation email in order to confirm he is the owner of the account email
     * address. Use the confirmation parameter to redirect the user from the
     * confirmation email back to your app. When the user is redirected, use the
     * /auth/confirm endpoint to complete the account confirmation.
     * 
     * Please notice that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     * 
     * When accessing this route using Javascript from the browser, success and
     * failure parameter URLs are required. Appwrite server will respond with a
     * 301 redirect status code and will set the user session cookie. This
     * behavior is enforced because modern browsers are limiting 3rd party cookies
     * in XHR of fetch requests to protect user privacy.
     *
     * @param String email
     * @param String password
     * @param String confirm
     * @param String success
     * @param String failure
     * @param String name
     * @throws Exception
     * @return array
     */

    func register(email: String, password: String, confirm: String, success: String = null, failure: String = null, name: String = null)-> Array<Any> {
        let methodPath = "/auth/register"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["email"] = email
        params["password"] = password
        params["confirm"] = confirm
        params["success"] = success
        params["failure"] = failure
        params["name"] = name

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Confirmation
     *
     * Use this endpoint to complete the confirmation of the user account email
     * address. Both the **userId** and **token** arguments will be passed as
     * query parameters to the redirect URL you have provided when sending your
     * request to the /auth/register endpoint.
     *
     * @param String userId
     * @param String token
     * @throws Exception
     * @return array
     */

    func confirm(userId: String, token: String)-> Array<Any> {
        let methodPath = "/auth/register/confirm"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["userId"] = userId
        params["token"] = token

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Resend Confirmation
     *
     * This endpoint allows the user to request your app to resend him his email
     * confirmation message. The redirect arguments act the same way as in
     * /auth/register endpoint.
     * 
     * Please notice that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param String confirm
     * @throws Exception
     * @return array
     */

    func confirmResend(confirm: String)-> Array<Any> {
        let methodPath = "/auth/register/confirm/resend"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["confirm"] = confirm

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

}

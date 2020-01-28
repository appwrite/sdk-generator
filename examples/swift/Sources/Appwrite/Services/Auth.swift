

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
     * @param String _email
     * @param String _password
     * @param String _success
     * @param String _failure
     * @throws Exception
     * @return array
     */

    func login(_email: String, _password: String, _success: String = "", _failure: String = "") -> Array<Any> {
        let path: String = "/auth/login"


                var params: [String: Any] = [:]
        
        params["email"] = _email
        params["password"] = _password
        params["success"] = _success
        params["failure"] = _failure

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Login with OAuth
     *
     * Allow the user to login to his account using the OAuth provider of his
     * choice. Each OAuth provider should be enabled from the Appwrite console
     * first. Use the success and failure arguments to provide a redirect URL's
     * back to your app when login is completed.
     *
     * @param String _provider
     * @param String _success
     * @param String _failure
     * @throws Exception
     * @return array
     */

    func oauth(_provider: String, _success: String, _failure: String) -> Array<Any> {
        var path: String = "/auth/login/oauth/{provider}"

        path = path.replacingOccurrences(
          of: "{provider}",
          with: _provider
        )

                var params: [String: Any] = [:]
        
        params["success"] = _success
        params["failure"] = _failure

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
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

    func logout() -> Array<Any> {
        let path: String = "/auth/logout"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Logout Specific Session
     *
     * Use this endpoint to log out the currently logged in user from all his
     * account sessions across all his different devices. When using the option id
     * argument, only the session unique ID provider will be deleted.
     *
     * @param String _id
     * @throws Exception
     * @return array
     */

    func logoutBySession(_id: String) -> Array<Any> {
        var path: String = "/auth/logout/{id}"

        path = path.replacingOccurrences(
          of: "{id}",
          with: _id
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
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
     * @param String _email
     * @param String _reset
     * @throws Exception
     * @return array
     */

    func recovery(_email: String, _reset: String) -> Array<Any> {
        let path: String = "/auth/recovery"


                var params: [String: Any] = [:]
        
        params["email"] = _email
        params["reset"] = _reset

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
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
     * @param String _userId
     * @param String _token
     * @param String _passwordA
     * @param String _passwordB
     * @throws Exception
     * @return array
     */

    func recoveryReset(_userId: String, _token: String, _passwordA: String, _passwordB: String) -> Array<Any> {
        let path: String = "/auth/recovery/reset"


                var params: [String: Any] = [:]
        
        params["userId"] = _userId
        params["token"] = _token
        params["password-a"] = _passwordA
        params["password-b"] = _passwordB

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
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
     * @param String _email
     * @param String _password
     * @param String _confirm
     * @param String _success
     * @param String _failure
     * @param String _name
     * @throws Exception
     * @return array
     */

    func register(_email: String, _password: String, _confirm: String, _success: String = "", _failure: String = "", _name: String = "") -> Array<Any> {
        let path: String = "/auth/register"


                var params: [String: Any] = [:]
        
        params["email"] = _email
        params["password"] = _password
        params["confirm"] = _confirm
        params["success"] = _success
        params["failure"] = _failure
        params["name"] = _name

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Confirmation
     *
     * Use this endpoint to complete the confirmation of the user account email
     * address. Both the **userId** and **token** arguments will be passed as
     * query parameters to the redirect URL you have provided when sending your
     * request to the /auth/register endpoint.
     *
     * @param String _userId
     * @param String _token
     * @throws Exception
     * @return array
     */

    func confirm(_userId: String, _token: String) -> Array<Any> {
        let path: String = "/auth/register/confirm"


                var params: [String: Any] = [:]
        
        params["userId"] = _userId
        params["token"] = _token

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
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
     * @param String _confirm
     * @throws Exception
     * @return array
     */

    func confirmResend(_confirm: String) -> Array<Any> {
        let path: String = "/auth/register/confirm/resend"


                var params: [String: Any] = [:]
        
        params["confirm"] = _confirm

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}

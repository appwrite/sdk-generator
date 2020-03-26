import { Service } from "../service";

export class Account extends Service {

    /**
     * Get Account
     *
     * Get currently logged in user data as JSON object.
     *
     * @throws Exception
     * @return {}
     */
    async get() {
        let path = '/account';
        
        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Create Account
     *
     * Use this endpoint to allow a new user to register a new account in your
     * project. After the user registration completes successfully, you can use
     * the [/account/verfication](/docs/account#createVerification) route to start
     * verifying the user email address. To allow your new user to login to his
     * new account, you need to create a new [account
     * session](/docs/account#createSession).
     *
     * @param string email
     * @param string password
     * @param string name
     * @throws Exception
     * @return {}
     */
    async create(email, password, name = '') {
        let path = '/account';
        
        return await this.client.call('post', path, {
                    'content-type': 'application/json',
               },
               {
                'email': email,
                'password': password,
                'name': name
            });
    }

    /**
     * Delete Account
     *
     * Delete a currently logged in user account. Behind the scene, the user
     * record is not deleted but permanently blocked from any access. This is done
     * to avoid deleted accounts being overtaken by new users with the same email
     * address. Any user-related resources like documents or storage files should
     * be deleted separately.
     *
     * @throws Exception
     * @return {}
     */
    async delete() {
        let path = '/account';
        
        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Update Account Email
     *
     * Update currently logged in user account email address. After changing user
     * address, user confirmation status is being reset and a new confirmation
     * mail is sent. For security measures, user password is required to complete
     * this request.
     *
     * @param string email
     * @param string password
     * @throws Exception
     * @return {}
     */
    async updateEmail(email, password) {
        let path = '/account/email';
        
        return await this.client.call('patch', path, {
                    'content-type': 'application/json',
               },
               {
                'email': email,
                'password': password
            });
    }

    /**
     * Get Account Logs
     *
     * Get currently logged in user list of latest security activity logs. Each
     * log returns user IP address, location and date and time of log.
     *
     * @throws Exception
     * @return {}
     */
    async getLogs() {
        let path = '/account/logs';
        
        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Update Account Name
     *
     * Update currently logged in user account name.
     *
     * @param string name
     * @throws Exception
     * @return {}
     */
    async updateName(name) {
        let path = '/account/name';
        
        return await this.client.call('patch', path, {
                    'content-type': 'application/json',
               },
               {
                'name': name
            });
    }

    /**
     * Update Account Password
     *
     * Update currently logged in user password. For validation, user is required
     * to pass the password twice.
     *
     * @param string password
     * @param string oldPassword
     * @throws Exception
     * @return {}
     */
    async updatePassword(password, oldPassword) {
        let path = '/account/password';
        
        return await this.client.call('patch', path, {
                    'content-type': 'application/json',
               },
               {
                'password': password,
                'old-password': oldPassword
            });
    }

    /**
     * Get Account Preferences
     *
     * Get currently logged in user preferences as a key-value object.
     *
     * @throws Exception
     * @return {}
     */
    async getPrefs() {
        let path = '/account/prefs';
        
        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Update Account Preferences
     *
     * Update currently logged in user account preferences. You can pass only the
     * specific settings you wish to update.
     *
     * @param object prefs
     * @throws Exception
     * @return {}
     */
    async updatePrefs(prefs) {
        let path = '/account/prefs';
        
        return await this.client.call('patch', path, {
                    'content-type': 'application/json',
               },
               {
                'prefs': prefs
            });
    }

    /**
     * Create Password Recovery
     *
     * Sends the user an email with a temporary secret key for password reset.
     * When the user clicks the confirmation link he is redirected back to your
     * app password reset URL with the secret key and email address values
     * attached to the URL query string. Use the query string params to submit a
     * request to the [PUT /account/recovery](/docs/account#updateRecovery)
     * endpoint to complete the process.
     *
     * @param string email
     * @param string url
     * @throws Exception
     * @return {}
     */
    async createRecovery(email, url) {
        let path = '/account/recovery';
        
        return await this.client.call('post', path, {
                    'content-type': 'application/json',
               },
               {
                'email': email,
                'url': url
            });
    }

    /**
     * Complete Password Recovery
     *
     * Use this endpoint to complete the user account password reset. Both the
     * **userId** and **secret** arguments will be passed as query parameters to
     * the redirect URL you have provided when sending your request to the [POST
     * /account/recovery](/docs/account#createRecovery) endpoint.
     * 
     * Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param string userId
     * @param string secret
     * @param string passwordA
     * @param string passwordB
     * @throws Exception
     * @return {}
     */
    async updateRecovery(userId, secret, passwordA, passwordB) {
        let path = '/account/recovery';
        
        return await this.client.call('put', path, {
                    'content-type': 'application/json',
               },
               {
                'userId': userId,
                'secret': secret,
                'password-a': passwordA,
                'password-b': passwordB
            });
    }

    /**
     * Get Account Sessions
     *
     * Get currently logged in user list of active sessions across different
     * devices.
     *
     * @throws Exception
     * @return {}
     */
    async getSessions() {
        let path = '/account/sessions';
        
        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Create Account Session
     *
     * Allow the user to login into his account by providing a valid email and
     * password combination. This route will create a new session for the user.
     *
     * @param string email
     * @param string password
     * @throws Exception
     * @return {}
     */
    async createSession(email, password) {
        let path = '/account/sessions';
        
        return await this.client.call('post', path, {
                    'content-type': 'application/json',
               },
               {
                'email': email,
                'password': password
            });
    }

    /**
     * Delete All Account Sessions
     *
     * Delete all sessions from the user account and remove any sessions cookies
     * from the end client.
     *
     * @throws Exception
     * @return {}
     */
    async deleteSessions() {
        let path = '/account/sessions';
        
        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Create Account Session with OAuth2
     *
     * Allow the user to login to his account using the OAuth2 provider of his
     * choice. Each OAuth2 provider should be enabled from the Appwrite console
     * first. Use the success and failure arguments to provide a redirect URL's
     * back to your app when login is completed.
     *
     * @param string provider
     * @param string success
     * @param string failure
     * @throws Exception
     * @return {}
     */
    async createOAuth2Session(provider, success, failure) {
        let path = '/account/sessions/oauth2/{provider}'.replace(new RegExp('{provider}', 'g'), provider);
        
        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               },
               {
                'success': success,
                'failure': failure
            });
    }

    /**
     * Delete Account Session
     *
     * Use this endpoint to log out the currently logged in user from all his
     * account sessions across all his different devices. When using the option id
     * argument, only the session unique ID provider will be deleted.
     *
     * @param string sessionId
     * @throws Exception
     * @return {}
     */
    async deleteSession(sessionId) {
        let path = '/account/sessions/{sessionId}'.replace(new RegExp('{sessionId}', 'g'), sessionId);
        
        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               },
               {
            });
    }

    /**
     * Create Email Verification
     *
     * Use this endpoint to send a verification message to your user email address
     * to confirm they are the valid owners of that address. Both the **userId**
     * and **secret** arguments will be passed as query parameters to the URL you
     * have provider to be attached to the verification email. The provided URL
     * should redirect the user back for your app and allow you to complete the
     * verification process by verifying both the **userId** and **secret**
     * parameters. Learn more about how to [complete the verification
     * process](/docs/account#updateAccountVerification). 
     * 
     * Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param string url
     * @throws Exception
     * @return {}
     */
    async createVerification(url) {
        let path = '/account/verification';
        
        return await this.client.call('post', path, {
                    'content-type': 'application/json',
               },
               {
                'url': url
            });
    }

    /**
     * Complete Email Verification
     *
     * Use this endpoint to complete the user email verification process. Use both
     * the **userId** and **secret** parameters that were attached to your app URL
     * to verify the user email ownership. If confirmed this route will return a
     * 200 status code.
     *
     * @param string userId
     * @param string secret
     * @throws Exception
     * @return {}
     */
    async updateVerification(userId, secret) {
        let path = '/account/verification';
        
        return await this.client.call('put', path, {
                    'content-type': 'application/json',
               },
               {
                'userId': userId,
                'secret': secret
            });
    }
}
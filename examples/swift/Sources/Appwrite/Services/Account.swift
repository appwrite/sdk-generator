

class Account: Service
{
    /**
     * Get Account
     *
     * Get currently logged in user data as JSON object.
     *
     * @throws Exception
     * @return array
     */

    func get()-> Array<Any> {
        let path: String = "/account"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
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
     * @return array
     */

    func delete()-> Array<Any> {
        let path: String = "/account"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Account Email
     *
     * Update currently logged in user account email address. After changing user
     * address, user confirmation status is being reset and a new confirmation
     * mail is sent. For security measures, user password is required to complete
     * this request.
     *
     * @param String email
     * @param String password
     * @throws Exception
     * @return array
     */

    func updateEmail(email: String, password: String)-> Array<Any> {
        let path: String = "/account/email"


                var params: [String: Any] = [:]
        
        params["email"] = email
        params["password"] = password

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Account Name
     *
     * Update currently logged in user account name.
     *
     * @param String name
     * @throws Exception
     * @return array
     */

    func updateName(name: String)-> Array<Any> {
        let path: String = "/account/name"


                var params: [String: Any] = [:]
        
        params["name"] = name

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Account Password
     *
     * Update currently logged in user password. For validation, user is required
     * to pass the password twice.
     *
     * @param String password
     * @param String oldPassword
     * @throws Exception
     * @return array
     */

    func updatePassword(password: String, oldPassword: String)-> Array<Any> {
        let path: String = "/account/password"


                var params: [String: Any] = [:]
        
        params["password"] = password
        params["old-password"] = oldPassword

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Account Preferences
     *
     * Get currently logged in user preferences key-value object.
     *
     * @throws Exception
     * @return array
     */

    func getPrefs()-> Array<Any> {
        let path: String = "/account/prefs"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Account Prefs
     *
     * Update currently logged in user account preferences. You can pass only the
     * specific settings you wish to update.
     *
     * @param String prefs
     * @throws Exception
     * @return array
     */

    func updatePrefs(prefs: String)-> Array<Any> {
        let path: String = "/account/prefs"


                var params: [String: Any] = [:]
        
        params["prefs"] = prefs

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Account Security Log
     *
     * Get currently logged in user list of latest security activity logs. Each
     * log returns user IP address, location and date and time of log.
     *
     * @throws Exception
     * @return array
     */

    func getSecurity()-> Array<Any> {
        let path: String = "/account/security"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Account Active Sessions
     *
     * Get currently logged in user list of active sessions across different
     * devices.
     *
     * @throws Exception
     * @return array
     */

    func getSessions()-> Array<Any> {
        let path: String = "/account/sessions"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}



class Users: Service
{
    /**
     * List Users
     *
     * Get a list of all the project users. You can use the query params to filter
     * your results.
     *
     * @param String _search
     * @param Int _limit
     * @param Int _offset
     * @param String _orderType
     * @throws Exception
     * @return array
     */

    func listUsers(_search: String = "", _limit: Int = 25, _offset: Int = 0, _orderType: String = "ASC") -> Array<Any> {
        let path: String = "/users"


                var params: [String: Any] = [:]
        
        params["search"] = _search
        params["limit"] = _limit
        params["offset"] = _offset
        params["orderType"] = _orderType

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create User
     *
     * Create a new user.
     *
     * @param String _email
     * @param String _password
     * @param String _name
     * @throws Exception
     * @return array
     */

    func createUser(_email: String, _password: String, _name: String = "") -> Array<Any> {
        let path: String = "/users"


                var params: [String: Any] = [:]
        
        params["email"] = _email
        params["password"] = _password
        params["name"] = _name

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get User
     *
     * Get user by its unique ID.
     *
     * @param String _userId
     * @throws Exception
     * @return array
     */

    func getUser(_userId: String) -> Array<Any> {
        var path: String = "/users/{userId}"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get User Logs
     *
     * Get user activity logs list by its unique ID.
     *
     * @param String _userId
     * @throws Exception
     * @return array
     */

    func getUserLogs(_userId: String) -> Array<Any> {
        var path: String = "/users/{userId}/logs"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get User Prefs
     *
     * Get user preferences by its unique ID.
     *
     * @param String _userId
     * @throws Exception
     * @return array
     */

    func getUserPrefs(_userId: String) -> Array<Any> {
        var path: String = "/users/{userId}/prefs"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update User Prefs
     *
     * Update user preferences by its unique ID. You can pass only the specific
     * settings you wish to update.
     *
     * @param String _userId
     * @param String _prefs
     * @throws Exception
     * @return array
     */

    func updateUserPrefs(_userId: String, _prefs: String) -> Array<Any> {
        var path: String = "/users/{userId}/prefs"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                var params: [String: Any] = [:]
        
        params["prefs"] = _prefs

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get User Sessions
     *
     * Get user sessions list by its unique ID.
     *
     * @param String _userId
     * @throws Exception
     * @return array
     */

    func getUserSessions(_userId: String) -> Array<Any> {
        var path: String = "/users/{userId}/sessions"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete User Sessions
     *
     * Delete all user sessions by its unique ID.
     *
     * @param String _userId
     * @throws Exception
     * @return array
     */

    func deleteUserSessions(_userId: String) -> Array<Any> {
        var path: String = "/users/{userId}/sessions"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete User Session
     *
     * Delete user sessions by its unique ID.
     *
     * @param String _userId
     * @param String _sessionId
     * @throws Exception
     * @return array
     */

    func deleteUserSession(_userId: String, _sessionId: String) -> Array<Any> {
        var path: String = "/users/{userId}/sessions/:session"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                var params: [String: Any] = [:]
        
        params["sessionId"] = _sessionId

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update User Status
     *
     * Update user status by its unique ID.
     *
     * @param String _userId
     * @param String _status
     * @throws Exception
     * @return array
     */

    func updateUserStatus(_userId: String, _status: String) -> Array<Any> {
        var path: String = "/users/{userId}/status"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: _userId
        )

                var params: [String: Any] = [:]
        
        params["status"] = _status

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}

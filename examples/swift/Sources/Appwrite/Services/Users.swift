

class Users: Service
{
    /**
     * List Users
     *
     * Get a list of all the project users. You can use the query params to filter
     * your results.
     *
     * @param String search
     * @param Int limit
     * @param Int offset
     * @param String orderType
     * @throws Exception
     * @return array
     */

    func listUsers(search: String = "", limit: Int = 25, offset: Int = 0, orderType: String = "ASC")-> Array<Any> {
        let path: String = "/users"


                var params: [String: Any] = [:]
        
        params["search"] = search
        params["limit"] = limit
        params["offset"] = offset
        params["orderType"] = orderType

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create User
     *
     * Create a new user.
     *
     * @param String email
     * @param String password
     * @param String name
     * @throws Exception
     * @return array
     */

    func createUser(email: String, password: String, name: String = "")-> Array<Any> {
        let path: String = "/users"


                var params: [String: Any] = [:]
        
        params["email"] = email
        params["password"] = password
        params["name"] = name

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get User
     *
     * Get user by its unique ID.
     *
     * @param String userId
     * @throws Exception
     * @return array
     */

    func getUser(userId: String)-> Array<Any> {
        var path: String = "/users/{userId}"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
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
     * @param String userId
     * @throws Exception
     * @return array
     */

    func getUserLogs(userId: String)-> Array<Any> {
        var path: String = "/users/{userId}/logs"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
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
     * @param String userId
     * @throws Exception
     * @return array
     */

    func getUserPrefs(userId: String)-> Array<Any> {
        var path: String = "/users/{userId}/prefs"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
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
     * @param String userId
     * @param String prefs
     * @throws Exception
     * @return array
     */

    func updateUserPrefs(userId: String, prefs: String)-> Array<Any> {
        var path: String = "/users/{userId}/prefs"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
        )

                var params: [String: Any] = [:]
        
        params["prefs"] = prefs

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get User Sessions
     *
     * Get user sessions list by its unique ID.
     *
     * @param String userId
     * @throws Exception
     * @return array
     */

    func getUserSessions(userId: String)-> Array<Any> {
        var path: String = "/users/{userId}/sessions"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
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
     * @param String userId
     * @throws Exception
     * @return array
     */

    func deleteUserSessions(userId: String)-> Array<Any> {
        var path: String = "/users/{userId}/sessions"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
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
     * @param String userId
     * @param String sessionId
     * @throws Exception
     * @return array
     */

    func deleteUserSession(userId: String, sessionId: String)-> Array<Any> {
        var path: String = "/users/{userId}/sessions/:session"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
        )

                var params: [String: Any] = [:]
        
        params["sessionId"] = sessionId

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update User Status
     *
     * Update user status by its unique ID.
     *
     * @param String userId
     * @param String status
     * @throws Exception
     * @return array
     */

    func updateUserStatus(userId: String, status: String)-> Array<Any> {
        var path: String = "/users/{userId}/status"

        path = path.replacingOccurrences(
          of: "{userId}",
          with: userId
        )

                var params: [String: Any] = [:]
        
        params["status"] = status

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}

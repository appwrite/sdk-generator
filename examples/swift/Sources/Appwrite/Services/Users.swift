

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

    func listUsers(search: String = null, limit: Int = 25, offset: Int = 0, orderType: String = 'ASC')-> Array<Any> {
        let methodPath = "/users"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["search"] = search
        params["limit"] = limit
        params["offset"] = offset
        params["orderType"] = orderType

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func createUser(email: String, password: String, name: String = null)-> Array<Any> {
        let methodPath = "/users"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["email"] = email
        params["password"] = password
        params["name"] = name

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}/logs"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}/prefs"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}/prefs"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["prefs"] = prefs

        return self.client.call(HTTPMethod.patch, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}/sessions"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}/sessions"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}/sessions/:session"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["sessionId"] = sessionId

        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/users/{userId}/status"
        let path = methodPath.replacingOccurrences(
          of: "['//{userId}']",
          with: "$userId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["status"] = status

        return self.client.call(HTTPMethod.patch, path, [
            "content-type": "application/json",
        ], params);
    }

}

part of dart_appwrite;

class Users extends Service {
    Users(Client client): super(client);

     /// List Users
     ///
     /// Get a list of all the project's users. You can use the query params to
     /// filter your results.
     ///
    Future<Response> list({String? search, int? limit, int? offset, String? orderType}) {
        final String path = '/users';

        final Map<String, dynamic> params = {
            'search': search,
            'limit': limit,
            'offset': offset,
            'orderType': orderType,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Create User
     ///
     /// Create a new user.
     ///
    Future<Response> create({required String email, required String password, String? name}) {
        final String path = '/users';

        final Map<String, dynamic> params = {
            'email': email,
            'password': password,
            'name': name,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.post, path: path, params: params, headers: headers);
    }

     /// Get User
     ///
     /// Get a user by its unique ID.
     ///
    Future<Response> get({required String userId}) {
        final String path = '/users/{userId}'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Delete User
     ///
     /// Delete a user by its unique ID.
     ///
    Future<Response> delete({required String userId}) {
        final String path = '/users/{userId}'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.delete, path: path, params: params, headers: headers);
    }

     /// Update Email
     ///
     /// Update the user email by its unique ID.
     ///
    Future<Response> updateEmail({required String userId, required String email}) {
        final String path = '/users/{userId}/email'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
            'email': email,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.patch, path: path, params: params, headers: headers);
    }

     /// Get User Logs
     ///
     /// Get a user activity logs list by its unique ID.
     ///
    Future<Response> getLogs({required String userId}) {
        final String path = '/users/{userId}/logs'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Update Name
     ///
     /// Update the user name by its unique ID.
     ///
    Future<Response> updateName({required String userId, required String name}) {
        final String path = '/users/{userId}/name'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
            'name': name,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.patch, path: path, params: params, headers: headers);
    }

     /// Update Password
     ///
     /// Update the user password by its unique ID.
     ///
    Future<Response> updatePassword({required String userId, required String password}) {
        final String path = '/users/{userId}/password'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
            'password': password,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.patch, path: path, params: params, headers: headers);
    }

     /// Get User Preferences
     ///
     /// Get the user preferences by its unique ID.
     ///
    Future<Response> getPrefs({required String userId}) {
        final String path = '/users/{userId}/prefs'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Update User Preferences
     ///
     /// Update the user preferences by its unique ID. You can pass only the
     /// specific settings you wish to update.
     ///
    Future<Response> updatePrefs({required String userId, required Map prefs}) {
        final String path = '/users/{userId}/prefs'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
            'prefs': prefs,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.patch, path: path, params: params, headers: headers);
    }

     /// Get User Sessions
     ///
     /// Get the user sessions list by its unique ID.
     ///
    Future<Response> getSessions({required String userId}) {
        final String path = '/users/{userId}/sessions'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Delete User Sessions
     ///
     /// Delete all user's sessions by using the user's unique ID.
     ///
    Future<Response> deleteSessions({required String userId}) {
        final String path = '/users/{userId}/sessions'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.delete, path: path, params: params, headers: headers);
    }

     /// Delete User Session
     ///
     /// Delete a user sessions by its unique ID.
     ///
    Future<Response> deleteSession({required String userId, required String sessionId}) {
        final String path = '/users/{userId}/sessions/{sessionId}'.replaceAll(RegExp('{userId}'), userId).replaceAll(RegExp('{sessionId}'), sessionId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.delete, path: path, params: params, headers: headers);
    }

     /// Update User Status
     ///
     /// Update the user status by its unique ID.
     ///
    Future<Response> updateStatus({required String userId, required int status}) {
        final String path = '/users/{userId}/status'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
            'status': status,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.patch, path: path, params: params, headers: headers);
    }

     /// Update Email Verification
     ///
     /// Update the user email verification status by its unique ID.
     ///
    Future<Response> updateVerification({required String userId, required bool emailVerification}) {
        final String path = '/users/{userId}/verification'.replaceAll(RegExp('{userId}'), userId);

        final Map<String, dynamic> params = {
            'emailVerification': emailVerification,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.patch, path: path, params: params, headers: headers);
    }
}
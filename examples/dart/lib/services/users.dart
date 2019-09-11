import "package:dart_appwrite/service.dart";
import "package:dart_appwrite/client.dart";

class Users extends Service {
     
     Users(Client client): super(client);

     /// Get a list of all the project users. You can use the query params to filter
     /// your results.
    listUsers({search = null, limit = 25, offset = 0, orderType = 'ASC'}) async {
       String path = '/users';

       var params = {
         'search': search,
         'limit': limit,
         'offset': offset,
         'orderType': orderType,
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Create a new user.
    createUser({email, password, name = null}) async {
       String path = '/users';

       var params = {
         'email': email,
         'password': password,
         'name': name,
       };

       return await this.client.call('post', path: path, params: params);
    }
     /// Get user by its unique ID.
    getUser({userId}) async {
       String path = '/users/{userId}'.replaceAll(RegExp('{userId}'), userId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Get user activity logs list by its unique ID.
    getUserLogs({userId}) async {
       String path = '/users/{userId}/logs'.replaceAll(RegExp('{userId}'), userId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Get user preferences by its unique ID.
    getUserPrefs({userId}) async {
       String path = '/users/{userId}/prefs'.replaceAll(RegExp('{userId}'), userId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Get user sessions list by its unique ID.
    getUserSessions({userId}) async {
       String path = '/users/{userId}/sessions'.replaceAll(RegExp('{userId}'), userId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Delete all user sessions by its unique ID.
    deleteUserSessions({userId}) async {
       String path = '/users/{userId}/sessions'.replaceAll(RegExp('{userId}'), userId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
     /// Delete user sessions by its unique ID.
    deleteUsersSession({userId, sessionId}) async {
       String path = '/users/{userId}/sessions/:session'.replaceAll(RegExp('{userId}'), userId);

       var params = {
         'sessionId': sessionId,
       };

       return await this.client.call('delete', path: path, params: params);
    }
     /// Update user status by its unique ID.
    updateUserStatus({userId, status}) async {
       String path = '/users/{userId}/status'.replaceAll(RegExp('{userId}'), userId);

       var params = {
         'status': status,
       };

       return await this.client.call('patch', path: path, params: params);
    }
}
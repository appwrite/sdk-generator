import "package:dart_appwrite/service.dart";
import "package:dart_appwrite/client.dart";

class Account extends Service {
     
     Account(Client client): super(client);

     /// Get currently logged in user data as JSON object.
    get() async {
       String path = '/account';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Delete currently logged in user account.
    delete() async {
       String path = '/account';

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
     /// Update currently logged in user account email address. After changing user
     /// address, user confirmation status is being reset and a new confirmation
     /// mail is sent. For security measures, user password is required to complete
     /// this request.
    updateEmail({email, password}) async {
       String path = '/account/email';

       var params = {
         'email': email,
         'password': password,
       };

       return await this.client.call('patch', path: path, params: params);
    }
     /// Update currently logged in user account name.
    updateName({name}) async {
       String path = '/account/name';

       var params = {
         'name': name,
       };

       return await this.client.call('patch', path: path, params: params);
    }
     /// Update currently logged in user password. For validation, user is required
     /// to pass the password twice.
    updatePassword({password, oldPassword}) async {
       String path = '/account/password';

       var params = {
         'password': password,
         'old-password': oldPassword,
       };

       return await this.client.call('patch', path: path, params: params);
    }
     /// Get currently logged in user preferences key-value object.
    getPrefs() async {
       String path = '/account/prefs';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Update currently logged in user account preferences. You can pass only the
     /// specific settings you wish to update.
    updatePrefs({prefs}) async {
       String path = '/account/prefs';

       var params = {
         'prefs': prefs,
       };

       return await this.client.call('patch', path: path, params: params);
    }
     /// Get currently logged in user list of latest security activity logs. Each
     /// log returns user IP address, location and date and time of log.
    getSecurity() async {
       String path = '/account/security';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Get currently logged in user list of active sessions across different
     /// devices.
    getSessions() async {
       String path = '/account/sessions';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
}
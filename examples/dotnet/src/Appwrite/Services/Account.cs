
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Threading.Tasks;

namespace Appwrite
{
    public class Account : Service
    {
        public Account(Client client) : base(client) { }

        /// <summary>
        /// Get Account
        /// <para>
        /// Get currently logged in user data as JSON object.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Get() 
        {
            string path = "/account";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Delete Account
        /// <para>
        /// Delete a currently logged in user account. Behind the scene, the user
        /// record is not deleted but permanently blocked from any access. This is done
        /// to avoid deleted accounts being overtaken by new users with the same email
        /// address. Any user-related resources like documents or storage files should
        /// be deleted separately.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Delete() 
        {
            string path = "/account";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("DELETE", path, headers, parameters);
        }

        /// <summary>
        /// Update Account Email
        /// <para>
        /// Update currently logged in user account email address. After changing user
        /// address, user confirmation status is being reset and a new confirmation
        /// mail is sent. For security measures, user password is required to complete
        /// this request.
        /// This endpoint can also be used to convert an anonymous account to a normal
        /// one, by passing an email address and a new password.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateEmail(string email, string password) 
        {
            string path = "/account/email";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "email", email },
                { "password", password }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Get Account Logs
        /// <para>
        /// Get currently logged in user list of latest security activity logs. Each
        /// log returns user IP address, location and date and time of log.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetLogs() 
        {
            string path = "/account/logs";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Update Account Name
        /// <para>
        /// Update currently logged in user account name.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateName(string name) 
        {
            string path = "/account/name";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "name", name }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Update Account Password
        /// <para>
        /// Update currently logged in user password. For validation, user is required
        /// to pass in the new password, and the old password. For users created with
        /// OAuth and Team Invites, oldPassword is optional.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdatePassword(string password, string oldPassword = "") 
        {
            string path = "/account/password";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "password", password },
                { "oldPassword", oldPassword }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Get Account Preferences
        /// <para>
        /// Get currently logged in user preferences as a key-value object.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetPrefs() 
        {
            string path = "/account/prefs";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Update Account Preferences
        /// <para>
        /// Update currently logged in user account preferences. You can pass only the
        /// specific settings you wish to update.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdatePrefs(object prefs) 
        {
            string path = "/account/prefs";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "prefs", prefs }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Create Password Recovery
        /// <para>
        /// Sends the user an email with a temporary secret key for password reset.
        /// When the user clicks the confirmation link he is redirected back to your
        /// app password reset URL with the secret key and email address values
        /// attached to the URL query string. Use the query string params to submit a
        /// request to the [PUT
        /// /account/recovery](/docs/client/account#accountUpdateRecovery) endpoint to
        /// complete the process. The verification link sent to the user's email
        /// address is valid for 1 hour.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> CreateRecovery(string email, string url) 
        {
            string path = "/account/recovery";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "email", email },
                { "url", url }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("POST", path, headers, parameters);
        }

        /// <summary>
        /// Create Password Recovery (confirmation)
        /// <para>
        /// Use this endpoint to complete the user account password reset. Both the
        /// **userId** and **secret** arguments will be passed as query parameters to
        /// the redirect URL you have provided when sending your request to the [POST
        /// /account/recovery](/docs/client/account#accountCreateRecovery) endpoint.
        /// 
        /// Please note that in order to avoid a [Redirect
        /// Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
        /// the only valid redirect URLs are the ones from domains you have set when
        /// adding your platforms in the console interface.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateRecovery(string userId, string secret, string password, string passwordAgain) 
        {
            string path = "/account/recovery";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "userId", userId },
                { "secret", secret },
                { "password", password },
                { "passwordAgain", passwordAgain }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PUT", path, headers, parameters);
        }

        /// <summary>
        /// Get Account Sessions
        /// <para>
        /// Get currently logged in user list of active sessions across different
        /// devices.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetSessions() 
        {
            string path = "/account/sessions";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Delete All Account Sessions
        /// <para>
        /// Delete all sessions from the user account and remove any sessions cookies
        /// from the end client.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> DeleteSessions() 
        {
            string path = "/account/sessions";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("DELETE", path, headers, parameters);
        }

        /// <summary>
        /// Get Session By ID
        /// <para>
        /// Use this endpoint to get a logged in user's session using a Session ID.
        /// Inputting 'current' will return the current session being used.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetSession(string sessionId) 
        {
            string path = "/account/sessions/{sessionId}".Replace("{sessionId}", sessionId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Delete Account Session
        /// <para>
        /// Use this endpoint to log out the currently logged in user from all their
        /// account sessions across all of their different devices. When using the
        /// option id argument, only the session unique ID provider will be deleted.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> DeleteSession(string sessionId) 
        {
            string path = "/account/sessions/{sessionId}".Replace("{sessionId}", sessionId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("DELETE", path, headers, parameters);
        }

        /// <summary>
        /// Create Email Verification
        /// <para>
        /// Use this endpoint to send a verification message to your user email address
        /// to confirm they are the valid owners of that address. Both the **userId**
        /// and **secret** arguments will be passed as query parameters to the URL you
        /// have provided to be attached to the verification email. The provided URL
        /// should redirect the user back to your app and allow you to complete the
        /// verification process by verifying both the **userId** and **secret**
        /// parameters. Learn more about how to [complete the verification
        /// process](/docs/client/account#accountUpdateVerification). The verification
        /// link sent to the user's email address is valid for 7 days.
        /// 
        /// Please note that in order to avoid a [Redirect
        /// Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md),
        /// the only valid redirect URLs are the ones from domains you have set when
        /// adding your platforms in the console interface.
        /// 
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> CreateVerification(string url) 
        {
            string path = "/account/verification";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "url", url }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("POST", path, headers, parameters);
        }

        /// <summary>
        /// Create Email Verification (confirmation)
        /// <para>
        /// Use this endpoint to complete the user email verification process. Use both
        /// the **userId** and **secret** parameters that were attached to your app URL
        /// to verify the user email ownership. If confirmed this route will return a
        /// 200 status code.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateVerification(string userId, string secret) 
        {
            string path = "/account/verification";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "userId", userId },
                { "secret", secret }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PUT", path, headers, parameters);
        }
    };
}
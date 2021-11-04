
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Threading.Tasks;

namespace Appwrite
{
    public class Users : Service
    {
        public Users(Client client) : base(client) { }

        /// <summary>
        /// List Users
        /// <para>
        /// Get a list of all the project's users. You can use the query params to
        /// filter your results.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> List(string search = "", int? limit = 25, int? offset = 0, OrderType orderType = OrderType.ASC) 
        {
            string path = "/users";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "search", search },
                { "limit", limit },
                { "offset", offset },
                { "orderType", orderType.ToString() }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Create User
        /// <para>
        /// Create a new user.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Create(string email, string password, string name = "") 
        {
            string path = "/users";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "email", email },
                { "password", password },
                { "name", name }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("POST", path, headers, parameters);
        }

        /// <summary>
        /// Get User
        /// <para>
        /// Get a user by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Get(string userId) 
        {
            string path = "/users/{userId}".Replace("{userId}", userId);

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
        /// Delete User
        /// <para>
        /// Delete a user by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Delete(string userId) 
        {
            string path = "/users/{userId}".Replace("{userId}", userId);

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
        /// Update Email
        /// <para>
        /// Update the user email by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateEmail(string userId, string email) 
        {
            string path = "/users/{userId}/email".Replace("{userId}", userId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "email", email }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Get User Logs
        /// <para>
        /// Get a user activity logs list by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetLogs(string userId) 
        {
            string path = "/users/{userId}/logs".Replace("{userId}", userId);

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
        /// Update Name
        /// <para>
        /// Update the user name by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateName(string userId, string name) 
        {
            string path = "/users/{userId}/name".Replace("{userId}", userId);

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
        /// Update Password
        /// <para>
        /// Update the user password by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdatePassword(string userId, string password) 
        {
            string path = "/users/{userId}/password".Replace("{userId}", userId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "password", password }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Get User Preferences
        /// <para>
        /// Get the user preferences by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetPrefs(string userId) 
        {
            string path = "/users/{userId}/prefs".Replace("{userId}", userId);

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
        /// Update User Preferences
        /// <para>
        /// Update the user preferences by its unique ID. You can pass only the
        /// specific settings you wish to update.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdatePrefs(string userId, object prefs) 
        {
            string path = "/users/{userId}/prefs".Replace("{userId}", userId);

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
        /// Get User Sessions
        /// <para>
        /// Get the user sessions list by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetSessions(string userId) 
        {
            string path = "/users/{userId}/sessions".Replace("{userId}", userId);

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
        /// Delete User Sessions
        /// <para>
        /// Delete all user's sessions by using the user's unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> DeleteSessions(string userId) 
        {
            string path = "/users/{userId}/sessions".Replace("{userId}", userId);

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
        /// Delete User Session
        /// <para>
        /// Delete a user sessions by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> DeleteSession(string userId, string sessionId) 
        {
            string path = "/users/{userId}/sessions/{sessionId}".Replace("{userId}", userId).Replace("{sessionId}", sessionId);

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
        /// Update User Status
        /// <para>
        /// Update the user status by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateStatus(string userId, int status) 
        {
            string path = "/users/{userId}/status".Replace("{userId}", userId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "status", status }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Update Email Verification
        /// <para>
        /// Update the user email verification status by its unique ID.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateVerification(string userId, bool emailVerification) 
        {
            string path = "/users/{userId}/verification".Replace("{userId}", userId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "emailVerification", emailVerification }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }
    };
}
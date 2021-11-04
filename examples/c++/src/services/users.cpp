#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "users.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {

class Users : public Service {
 public:
    /**
     * List Users
     *
     * Get a list of all the project's users. You can use the query params to
     * filter your results.
     *
     * @param string search
     * @param int limit
     * @param int offset
     * @param string orderType
     * @throws AppwriteException
     * @return array
     */
    json list(string* search = NULL, int* limit = NULL, int* offset = NULL, string* orderType = NULL) {
        map<string, string> params;
        string path = "/users";
        size_t pos;
        string searchString;

        if (search != NULL) {
            params["search"] = *search;
        }

        if (limit != NULL) {
            params["limit"] = *limit;
        }

        if (offset != NULL) {
            params["offset"] = *offset;
        }

        if (orderType != NULL) {
            params["orderType"] = *orderType;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Create User
     *
     * Create a new user.
     *
     * @param string email
     * @param string password
     * @param string name
     * @throws AppwriteException
     * @return array
     */
    json create(string* email, string* password, string* name = NULL) {
        if (!isset(email)) {
            throw new AppwriteException("Missing required parameter: 'email'");
        }

        if (!isset(password)) {
            throw new AppwriteException("Missing required parameter: 'password'");
        }

        map<string, string> params;
        string path = "/users";
        size_t pos;
        string searchString;

        if (email != NULL) {
            params["email"] = *email;
        }

        if (password != NULL) {
            params["password"] = *password;
        }

        if (name != NULL) {
            params["name"] = *name;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get User
     *
     * Get a user by its unique ID.
     *
     * @param string userId
     * @throws AppwriteException
     * @return array
     */
    json get(string* userId) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        map<string, string> params;
        string path = "/users/{userId}";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Delete User
     *
     * Delete a user by its unique ID.
     *
     * @param string userId
     * @throws AppwriteException
     * @return array
     */
    json delete(string* userId) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        map<string, string> params;
        string path = "/users/{userId}";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * Update Email
     *
     * Update the user email by its unique ID.
     *
     * @param string userId
     * @param string email
     * @throws AppwriteException
     * @return array
     */
    json updateEmail(string* userId, string* email) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(email)) {
            throw new AppwriteException("Missing required parameter: 'email'");
        }

        map<string, string> params;
        string path = "/users/{userId}/email";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        if (email != NULL) {
            params["email"] = *email;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * Get User Logs
     *
     * Get a user activity logs list by its unique ID.
     *
     * @param string userId
     * @throws AppwriteException
     * @return array
     */
    json getLogs(string* userId) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        map<string, string> params;
        string path = "/users/{userId}/logs";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update Name
     *
     * Update the user name by its unique ID.
     *
     * @param string userId
     * @param string name
     * @throws AppwriteException
     * @return array
     */
    json updateName(string* userId, string* name) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(name)) {
            throw new AppwriteException("Missing required parameter: 'name'");
        }

        map<string, string> params;
        string path = "/users/{userId}/name";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        if (name != NULL) {
            params["name"] = *name;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * Update Password
     *
     * Update the user password by its unique ID.
     *
     * @param string userId
     * @param string password
     * @throws AppwriteException
     * @return array
     */
    json updatePassword(string* userId, string* password) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(password)) {
            throw new AppwriteException("Missing required parameter: 'password'");
        }

        map<string, string> params;
        string path = "/users/{userId}/password";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        if (password != NULL) {
            params["password"] = *password;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * Get User Preferences
     *
     * Get the user preferences by its unique ID.
     *
     * @param string userId
     * @throws AppwriteException
     * @return array
     */
    json getPrefs(string* userId) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        map<string, string> params;
        string path = "/users/{userId}/prefs";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update User Preferences
     *
     * Update the user preferences by its unique ID. You can pass only the
     * specific settings you wish to update.
     *
     * @param string userId
     * @param json prefs
     * @throws AppwriteException
     * @return array
     */
    json updatePrefs(string* userId, json* prefs) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(prefs)) {
            throw new AppwriteException("Missing required parameter: 'prefs'");
        }

        map<string, string> params;
        string path = "/users/{userId}/prefs";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        if (prefs != NULL) {
            params["prefs"] = *prefs;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * Get User Sessions
     *
     * Get the user sessions list by its unique ID.
     *
     * @param string userId
     * @throws AppwriteException
     * @return array
     */
    json getSessions(string* userId) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        map<string, string> params;
        string path = "/users/{userId}/sessions";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Delete User Sessions
     *
     * Delete all user's sessions by using the user's unique ID.
     *
     * @param string userId
     * @throws AppwriteException
     * @return array
     */
    json deleteSessions(string* userId) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        map<string, string> params;
        string path = "/users/{userId}/sessions";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * Delete User Session
     *
     * Delete a user sessions by its unique ID.
     *
     * @param string userId
     * @param string sessionId
     * @throws AppwriteException
     * @return array
     */
    json deleteSession(string* userId, string* sessionId) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(sessionId)) {
            throw new AppwriteException("Missing required parameter: 'sessionId'");
        }

        map<string, string> params;
        string path = "/users/{userId}/sessions/{sessionId}";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");
        searchString = "{sessionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "sessionId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * Update User Status
     *
     * Update the user status by its unique ID.
     *
     * @param string userId
     * @param int status
     * @throws AppwriteException
     * @return array
     */
    json updateStatus(string* userId, int* status) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(status)) {
            throw new AppwriteException("Missing required parameter: 'status'");
        }

        map<string, string> params;
        string path = "/users/{userId}/status";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        if (status != NULL) {
            params["status"] = *status;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * Update Email Verification
     *
     * Update the user email verification status by its unique ID.
     *
     * @param string userId
     * @param bool emailVerification
     * @throws AppwriteException
     * @return array
     */
    json updateVerification(string* userId, bool* emailVerification) {
        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(emailVerification)) {
            throw new AppwriteException("Missing required parameter: 'emailVerification'");
        }

        map<string, string> params;
        string path = "/users/{userId}/verification";
        size_t pos;
        string searchString;
        searchString = "{userId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "userId");

        if (emailVerification != NULL) {
            params["emailVerification"] = *emailVerification;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }
};
}

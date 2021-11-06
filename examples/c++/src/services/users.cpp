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
/*
 * List Users
 *
     * Get a list of all the project's users. You can use the query params to
     * filter your results.
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Users::list(string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Create User
 *
     * Create a new user.
 * @param string email
 * @param string password
 * @param string name
 * @throws AppwriteException
 * @return array
 */
json Users::create(string email, string password, string name) {
    json params = {
        {"email", email},
        {"password", password},
        {"name", name},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get User
 *
     * Get a user by its unique ID.
 * @param string userId
 * @throws AppwriteException
 * @return array
 */
json Users::get(string userId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Delete User
 *
     * Delete a user by its unique ID.
 * @param string userId
 * @throws AppwriteException
 * @return array
 */
json Users::fake_delete(string userId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * Update Email
 *
     * Update the user email by its unique ID.
 * @param string userId
 * @param string email
 * @throws AppwriteException
 * @return array
 */
json Users::updateEmail(string userId, string email) {
    json params = {
        {"email", email},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/email";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * Get User Logs
 *
     * Get a user activity logs list by its unique ID.
 * @param string userId
 * @throws AppwriteException
 * @return array
 */
json Users::getLogs(string userId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/logs";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update Name
 *
     * Update the user name by its unique ID.
 * @param string userId
 * @param string name
 * @throws AppwriteException
 * @return array
 */
json Users::updateName(string userId, string name) {
    json params = {
        {"name", name},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/name";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * Update Password
 *
     * Update the user password by its unique ID.
 * @param string userId
 * @param string password
 * @throws AppwriteException
 * @return array
 */
json Users::updatePassword(string userId, string password) {
    json params = {
        {"password", password},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/password";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * Get User Preferences
 *
     * Get the user preferences by its unique ID.
 * @param string userId
 * @throws AppwriteException
 * @return array
 */
json Users::getPrefs(string userId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/prefs";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update User Preferences
 *
     * Update the user preferences by its unique ID. You can pass only the
     * specific settings you wish to update.
 * @param string userId
 * @param json prefs
 * @throws AppwriteException
 * @return array
 */
json Users::updatePrefs(string userId, json prefs) {
    json params = {
        {"prefs", prefs},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/prefs";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * Get User Sessions
 *
     * Get the user sessions list by its unique ID.
 * @param string userId
 * @throws AppwriteException
 * @return array
 */
json Users::getSessions(string userId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/sessions";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Delete User Sessions
 *
     * Delete all user's sessions by using the user's unique ID.
 * @param string userId
 * @throws AppwriteException
 * @return array
 */
json Users::deleteSessions(string userId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/sessions";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * Delete User Session
 *
     * Delete a user sessions by its unique ID.
 * @param string userId
 * @param string sessionId
 * @throws AppwriteException
 * @return array
 */
json Users::deleteSession(string userId, string sessionId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
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

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * Update User Status
 *
     * Update the user status by its unique ID.
 * @param string userId
 * @param int status
 * @throws AppwriteException
 * @return array
 */
json Users::updateStatus(string userId, int status) {
    json params = {
        {"status", status},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/status";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * Update Email Verification
 *
     * Update the user email verification status by its unique ID.
 * @param string userId
 * @param bool emailVerification
 * @throws AppwriteException
 * @return array
 */
json Users::updateVerification(string userId, bool emailVerification) {
    json params = {
        {"emailVerification", emailVerification},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/users/{userId}/verification";
    size_t pos;
    string searchString;
    searchString = "{userId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "userId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}
} // namespace Appwrite

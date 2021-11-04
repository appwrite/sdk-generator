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

class Teams : public Service {
 public:
    /**
     * List Teams
     *
     * Get a list of all the current user teams. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's teams. [Learn more about different API
     * modes](/docs/admin).
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
        string path = "/teams";
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
     * Create Team
     *
     * Create a new team. The user who creates the team will automatically be
     * assigned as the owner of the team. The team owner can invite new members,
     * who will be able add new owners and update or delete the team from your
     * project.
     *
     * @param string name
     * @param array roles
     * @throws AppwriteException
     * @return array
     */
    json create(string* name, array* roles = NULL) {
        if (!isset(name)) {
            throw new AppwriteException("Missing required parameter: 'name'");
        }

        map<string, string> params;
        string path = "/teams";
        size_t pos;
        string searchString;

        if (name != NULL) {
            params["name"] = *name;
        }

        if (roles != NULL) {
            params["roles"] = *roles;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get Team
     *
     * Get a team by its unique ID. All team members have read access for this
     * resource.
     *
     * @param string teamId
     * @throws AppwriteException
     * @return array
     */
    json get(string* teamId) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update Team
     *
     * Update a team by its unique ID. Only team owners have write access for this
     * resource.
     *
     * @param string teamId
     * @param string name
     * @throws AppwriteException
     * @return array
     */
    json update(string* teamId, string* name) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        if (!isset(name)) {
            throw new AppwriteException("Missing required parameter: 'name'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");

        if (name != NULL) {
            params["name"] = *name;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PUT, path, headers, params);
    }

    /**
     * Delete Team
     *
     * Delete a team by its unique ID. Only team owners have write access for this
     * resource.
     *
     * @param string teamId
     * @throws AppwriteException
     * @return array
     */
    json delete(string* teamId) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * Get Team Memberships
     *
     * Get a team members by the team unique ID. All team members have read access
     * for this list of resources.
     *
     * @param string teamId
     * @param string search
     * @param int limit
     * @param int offset
     * @param string orderType
     * @throws AppwriteException
     * @return array
     */
    json getMemberships(string* teamId, string* search = NULL, int* limit = NULL, int* offset = NULL, string* orderType = NULL) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}/memberships";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");

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
     * Create Team Membership
     *
     * Use this endpoint to invite a new member to join your team. If initiated
     * from Client SDK, an email with a link to join the team will be sent to the
     * new member's email address if the member doesn't exist in the project it
     * will be created automatically. If initiated from server side SDKs, new
     * member will automatically be added to the team.
     * 
     * Use the 'URL' parameter to redirect the user from the invitation email back
     * to your app. When the user is redirected, use the [Update Team Membership
     * Status](/docs/client/teams#teamsUpdateMembershipStatus) endpoint to allow
     * the user to accept the invitation to the team.  While calling from side
     * SDKs the redirect url can be empty string.
     * 
     * Please note that in order to avoid a [Redirect
     * Attacks](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URL's are the once from domains you have set when
     * added your platforms in the console interface.
     *
     * @param string teamId
     * @param string email
     * @param array roles
     * @param string url
     * @param string name
     * @throws AppwriteException
     * @return array
     */
    json createMembership(string* teamId, string* email, array* roles, string* url, string* name = NULL) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        if (!isset(email)) {
            throw new AppwriteException("Missing required parameter: 'email'");
        }

        if (!isset(roles)) {
            throw new AppwriteException("Missing required parameter: 'roles'");
        }

        if (!isset(url)) {
            throw new AppwriteException("Missing required parameter: 'url'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}/memberships";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");

        if (email != NULL) {
            params["email"] = *email;
        }

        if (roles != NULL) {
            params["roles"] = *roles;
        }

        if (url != NULL) {
            params["url"] = *url;
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
     * Update Membership Roles
     *
     * @param string teamId
     * @param string membershipId
     * @param array roles
     * @throws AppwriteException
     * @return array
     */
    json updateMembershipRoles(string* teamId, string* membershipId, array* roles) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        if (!isset(membershipId)) {
            throw new AppwriteException("Missing required parameter: 'membershipId'");
        }

        if (!isset(roles)) {
            throw new AppwriteException("Missing required parameter: 'roles'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}/memberships/{membershipId}";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");
        searchString = "{membershipId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "membershipId");

        if (roles != NULL) {
            params["roles"] = *roles;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * Delete Team Membership
     *
     * This endpoint allows a user to leave a team or for a team owner to delete
     * the membership of any other team member. You can also use this endpoint to
     * delete a user membership even if it is not accepted.
     *
     * @param string teamId
     * @param string membershipId
     * @throws AppwriteException
     * @return array
     */
    json deleteMembership(string* teamId, string* membershipId) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        if (!isset(membershipId)) {
            throw new AppwriteException("Missing required parameter: 'membershipId'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}/memberships/{membershipId}";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");
        searchString = "{membershipId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "membershipId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * Update Team Membership Status
     *
     * Use this endpoint to allow a user to accept an invitation to join a team
     * after being redirected back to your app from the invitation email recieved
     * by the user.
     *
     * @param string teamId
     * @param string membershipId
     * @param string userId
     * @param string secret
     * @throws AppwriteException
     * @return array
     */
    json updateMembershipStatus(string* teamId, string* membershipId, string* userId, string* secret) {
        if (!isset(teamId)) {
            throw new AppwriteException("Missing required parameter: 'teamId'");
        }

        if (!isset(membershipId)) {
            throw new AppwriteException("Missing required parameter: 'membershipId'");
        }

        if (!isset(userId)) {
            throw new AppwriteException("Missing required parameter: 'userId'");
        }

        if (!isset(secret)) {
            throw new AppwriteException("Missing required parameter: 'secret'");
        }

        map<string, string> params;
        string path = "/teams/{teamId}/memberships/{membershipId}/status";
        size_t pos;
        string searchString;
        searchString = "{teamId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "teamId");
        searchString = "{membershipId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "membershipId");

        if (userId != NULL) {
            params["userId"] = *userId;
        }

        if (secret != NULL) {
            params["secret"] = *secret;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }
};
}

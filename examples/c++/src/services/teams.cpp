#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "teams.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {
/*
 * List Teams
 *
     * Get a list of all the current user teams. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's teams. [Learn more about different API
     * modes](/docs/admin).
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Teams::list(string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/teams";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Create Team
 *
     * Create a new team. The user who creates the team will automatically be
     * assigned as the owner of the team. The team owner can invite new members,
     * who will be able add new owners and update or delete the team from your
     * project.
 * @param string name
 * @param array roles
 * @throws AppwriteException
 * @return array
 */
json Teams::create(string name, array roles) {
    json params = {
        {"name", name},
        {"roles", roles},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/teams";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get Team
 *
     * Get a team by its unique ID. All team members have read access for this
     * resource.
 * @param string teamId
 * @throws AppwriteException
 * @return array
 */
json Teams::get(string teamId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/teams/{teamId}";
    size_t pos;
    string searchString;
    searchString = "{teamId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "teamId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update Team
 *
     * Update a team by its unique ID. Only team owners have write access for this
     * resource.
 * @param string teamId
 * @param string name
 * @throws AppwriteException
 * @return array
 */
json Teams::update(string teamId, string name) {
    json params = {
        {"name", name},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/teams/{teamId}";
    size_t pos;
    string searchString;
    searchString = "{teamId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "teamId");

    return this->client->call(Client::METHOD_PUT, path, headers, params);
}

/*
 * Delete Team
 *
     * Delete a team by its unique ID. Only team owners have write access for this
     * resource.
 * @param string teamId
 * @throws AppwriteException
 * @return array
 */
json Teams::delete(string teamId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/teams/{teamId}";
    size_t pos;
    string searchString;
    searchString = "{teamId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "teamId");

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * Get Team Memberships
 *
     * Get a team members by the team unique ID. All team members have read access
     * for this list of resources.
 * @param string teamId
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Teams::getMemberships(string teamId, string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/teams/{teamId}/memberships";
    size_t pos;
    string searchString;
    searchString = "{teamId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "teamId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
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
 * @param string teamId
 * @param string email
 * @param array roles
 * @param string url
 * @param string name
 * @throws AppwriteException
 * @return array
 */
json Teams::createMembership(string teamId, string email, array roles, string url, string name) {
    json params = {
        {"email", email},
        {"roles", roles},
        {"url", url},
        {"name", name},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/teams/{teamId}/memberships";
    size_t pos;
    string searchString;
    searchString = "{teamId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "teamId");

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Update Membership Roles
 *
 * @param string teamId
 * @param string membershipId
 * @param array roles
 * @throws AppwriteException
 * @return array
 */
json Teams::updateMembershipRoles(string teamId, string membershipId, array roles) {
    json params = {
        {"roles", roles},
    };
    json headers = {
        {"content-type", "application/json"},
    };
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

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * Delete Team Membership
 *
     * This endpoint allows a user to leave a team or for a team owner to delete
     * the membership of any other team member. You can also use this endpoint to
     * delete a user membership even if it is not accepted.
 * @param string teamId
 * @param string membershipId
 * @throws AppwriteException
 * @return array
 */
json Teams::deleteMembership(string teamId, string membershipId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
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

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * Update Team Membership Status
 *
     * Use this endpoint to allow a user to accept an invitation to join a team
     * after being redirected back to your app from the invitation email recieved
     * by the user.
 * @param string teamId
 * @param string membershipId
 * @param string userId
 * @param string secret
 * @throws AppwriteException
 * @return array
 */
json Teams::updateMembershipStatus(string teamId, string membershipId, string userId, string secret) {
    json params = {
        {"userId", userId},
        {"secret", secret},
    };
    json headers = {
        {"content-type", "application/json"},
    };
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

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}
} // namespace Appwrite

#ifndef APPWRITE_TEAMS_H
#define APPWRITE_TEAMS_H

#include <cstring>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"
using namespace std;
using json = nlohmann::json;

namespace Appwrite {

class Teams : public Service {
public:
    json list(string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json create(string name, array roles = );
    json get(string teamId);
    json update(string teamId, string name);
    json delete(string teamId);
    json getMemberships(string teamId, string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json createMembership(string teamId, string email, array roles, string url, string name = "");
    json updateMembershipRoles(string teamId, string membershipId, array roles);
    json deleteMembership(string teamId, string membershipId);
    json updateMembershipStatus(string teamId, string membershipId, string userId, string secret);
};
}
#endif

#ifndef APPWRITE_USERS_H
#define APPWRITE_USERS_H

#include <cstring>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"
using namespace std;
using json = nlohmann::json;

namespace Appwrite {

class Users : public Service {
public:
    json list(string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json create(string email, string password, string name = "");
    json get(string userId);
    json delete(string userId);
    json updateEmail(string userId, string email);
    json getLogs(string userId);
    json updateName(string userId, string name);
    json updatePassword(string userId, string password);
    json getPrefs(string userId);
    json updatePrefs(string userId, json prefs);
    json getSessions(string userId);
    json deleteSessions(string userId);
    json deleteSession(string userId, string sessionId);
    json updateStatus(string userId, int status);
    json updateVerification(string userId, bool emailVerification);
};
}
#endif

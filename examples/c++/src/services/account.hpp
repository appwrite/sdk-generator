#ifndef APPWRITE_ACCOUNT_H
#define APPWRITE_ACCOUNT_H

#include <cstring>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"
using namespace std;
using json = nlohmann::json;

namespace Appwrite {

class Account : public Service {
public:
    json get();
    json delete();
    json updateEmail(string email, string password);
    json getLogs();
    json updateName(string name);
    json updatePassword(string password, string oldPassword = "");
    json getPrefs();
    json updatePrefs(json prefs);
    json createRecovery(string email, string url);
    json updateRecovery(string userId, string secret, string password, string passwordAgain);
    json getSessions();
    json deleteSessions();
    json getSession(string sessionId);
    json deleteSession(string sessionId);
    json createVerification(string url);
    json updateVerification(string userId, string secret);
};
}
#endif

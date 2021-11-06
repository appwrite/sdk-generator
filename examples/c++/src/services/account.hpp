#pragma once

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Account : public Service {
 public:
    using Service::Service;
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
} // namespace Appwrite

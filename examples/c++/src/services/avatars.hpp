#pragma once

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Avatars : public Service {
 public:
    using Service::Service;
    string getBrowser(string code, int width = 100, int height = 100, int quality = 100);
    string getCreditCard(string code, int width = 100, int height = 100, int quality = 100);
    string getFavicon(string url);
    string getFlag(string code, int width = 100, int height = 100, int quality = 100);
    string getImage(string url, int width = 400, int height = 400);
    string getInitials(string name = "", int width = 500, int height = 500, string color = "", string background = "");
    string getQR(string text, int size = 400, int margin = 1, bool download = false);
};
} // namespace Appwrite

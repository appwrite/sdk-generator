#pragma once

#include <map>
#include <string>
#include "temp_libs/json.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Client {
 protected:
    inline static const string METHOD_GET;
    inline static const string METHOD_POST;
    inline static const string METHOD_PUT;
    inline static const string METHOD_PATCH;
    inline static const string METHOD_DELETE;
    inline static const string METHOD_HEAD;
    inline static const string METHOD_OPTIONS;
    inline static const string METHOD_CONNECT;
    inline static const string METHOD_TRACE;

    bool selfSigned = false;
    string endpoint = "https://appwrite.io/v1";
    std::map<string, string> headers = {
        { "content-type", "" },
        { "x-sdk-version", "appwrite:c++:0.0.0-SNAPSHOT" },
        { "x-appwrite-response-format", "0.11.0"},
    };

 public:
    Client();
    ~Client();
    Client& setProject(string value);
    Client& setKey(string value);
    Client& setJWT(string value);
    Client& setLocale(string value);
    Client& setSelfSigned(bool status = true);
    Client& setEndpoint(string endpoint);
    Client& addHeader(string key, string value);
    json call(string method, string path = "", std::map<string, string> headers = std::map<string, string>(), std::map<string, string> params = std::map<string, string>());
};
} // namespace Appwrite

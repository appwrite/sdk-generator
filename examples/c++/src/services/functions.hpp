#pragma once

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Functions : public Service {
 public:
    using Service::Service;
    json list(string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json create(string name, array execute, string runtime, json vars = , array events = , string schedule = "", int timeout = 15);
    json get(string functionId);
    json update(string functionId, string name, array execute, json vars = , array events = , string schedule = "", int timeout = 15);
    json delete(string functionId);
    json listExecutions(string functionId, string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json createExecution(string functionId, string data = "");
    json getExecution(string functionId, string executionId);
    json updateTag(string functionId, string tag);
    json listTags(string functionId, string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json createTag(string functionId, string command, file code);
    json getTag(string functionId, string tagId);
    json deleteTag(string functionId, string tagId);
};
} // namespace Appwrite

#pragma once

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Health : public Service {
 public:
    using Service::Service;
    json get();
    json getAntiVirus();
    json getCache();
    json getDB();
    json getQueueCertificates();
    json getQueueFunctions();
    json getQueueLogs();
    json getQueueTasks();
    json getQueueUsage();
    json getQueueWebhooks();
    json getStorageLocal();
    json getTime();
};
} // namespace Appwrite

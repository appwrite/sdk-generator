#ifndef APPWRITE_HEALTH_H
#define APPWRITE_HEALTH_H

#include <cstring>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"
using namespace std;
using json = nlohmann::json;

namespace Appwrite {

class Health : public Service {
public:
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
}
#endif

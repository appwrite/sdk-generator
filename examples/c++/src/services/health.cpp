#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "users.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {

class Health : public Service {
 public:
    /**
     * Get HTTP
     *
     * Check the Appwrite HTTP server is up and responsive.
     *
     * @throws AppwriteException
     * @return array
     */
    json get() {
        map<string, string> params;
        string path = "/health";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Anti virus
     *
     * Check the Appwrite Anti Virus server is up and connection is successful.
     *
     * @throws AppwriteException
     * @return array
     */
    json getAntiVirus() {
        map<string, string> params;
        string path = "/health/anti-virus";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Cache
     *
     * Check the Appwrite in-memory cache server is up and connection is
     * successful.
     *
     * @throws AppwriteException
     * @return array
     */
    json getCache() {
        map<string, string> params;
        string path = "/health/cache";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get DB
     *
     * Check the Appwrite database server is up and connection is successful.
     *
     * @throws AppwriteException
     * @return array
     */
    json getDB() {
        map<string, string> params;
        string path = "/health/db";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Certificates Queue
     *
     * Get the number of certificates that are waiting to be issued against
     * [Letsencrypt](https://letsencrypt.org/) in the Appwrite internal queue
     * server.
     *
     * @throws AppwriteException
     * @return array
     */
    json getQueueCertificates() {
        map<string, string> params;
        string path = "/health/queue/certificates";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Functions Queue
     *
     * @throws AppwriteException
     * @return array
     */
    json getQueueFunctions() {
        map<string, string> params;
        string path = "/health/queue/functions";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Logs Queue
     *
     * Get the number of logs that are waiting to be processed in the Appwrite
     * internal queue server.
     *
     * @throws AppwriteException
     * @return array
     */
    json getQueueLogs() {
        map<string, string> params;
        string path = "/health/queue/logs";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Tasks Queue
     *
     * Get the number of tasks that are waiting to be processed in the Appwrite
     * internal queue server.
     *
     * @throws AppwriteException
     * @return array
     */
    json getQueueTasks() {
        map<string, string> params;
        string path = "/health/queue/tasks";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Usage Queue
     *
     * Get the number of usage stats that are waiting to be processed in the
     * Appwrite internal queue server.
     *
     * @throws AppwriteException
     * @return array
     */
    json getQueueUsage() {
        map<string, string> params;
        string path = "/health/queue/usage";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Webhooks Queue
     *
     * Get the number of webhooks that are waiting to be processed in the Appwrite
     * internal queue server.
     *
     * @throws AppwriteException
     * @return array
     */
    json getQueueWebhooks() {
        map<string, string> params;
        string path = "/health/queue/webhooks";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Local Storage
     *
     * Check the Appwrite local storage device is up and connection is successful.
     *
     * @throws AppwriteException
     * @return array
     */
    json getStorageLocal() {
        map<string, string> params;
        string path = "/health/storage/local";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Time
     *
     * Check the Appwrite server time is synced with Google remote NTP server. We
     * use this technology to smoothly handle leap seconds with no disruptive
     * events. The [Network Time
     * Protocol](https://en.wikipedia.org/wiki/Network_Time_Protocol) (NTP) is
     * used by hundreds of millions of computers and devices to synchronize their
     * clocks over the Internet. If your computer sets its own clock, it likely
     * uses NTP.
     *
     * @throws AppwriteException
     * @return array
     */
    json getTime() {
        map<string, string> params;
        string path = "/health/time";
        size_t pos;
        string searchString;

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }
};
}

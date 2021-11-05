#include "client.hpp"
#include "service.hpp"

namespace Appwrite {

/**
 * @param Client client
 */
Service::Service(Client client) {
    this->client = &client;
}

Service::~Service() {}
} // namespace Appwrite

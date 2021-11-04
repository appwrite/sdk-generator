#include "client.hpp"
#include "service.hpp"

namespace Appwrite {

/**
 * @param Client client
 */
explicit Service::Service(Client client) {
    this->client = &client;
}
} // namespace Appwrite

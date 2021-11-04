#pragma once
#include "client.hpp"

namespace Appwrite {

class Service {
 protected:
    Client* client;
 public:
    explicit Service(Client client);
    ~Service();
};
} // namespace Appwrite

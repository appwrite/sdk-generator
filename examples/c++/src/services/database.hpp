#pragma once

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Database : public Service {
 public:
    using Service::Service;
    json listCollections(string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json createCollection(string name, array read, array write, array rules);
    json getCollection(string collectionId);
    json updateCollection(string collectionId, string name, array read = , array write = , array rules = );
    json deleteCollection(string collectionId);
    json listDocuments(string collectionId, array filters = , int limit = 25, int offset = 0, string orderField = "", string orderType = "ASC", string orderCast = "string", string search = "");
    json createDocument(string collectionId, json data, array read = , array write = , string parentDocument = "", string parentProperty = "", string parentPropertyType = "assign");
    json getDocument(string collectionId, string documentId);
    json updateDocument(string collectionId, string documentId, json data, array read = , array write = );
    json deleteDocument(string collectionId, string documentId);
};
} // namespace Appwrite

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "database.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {
/*
 * List Collections
 *
     * Get a list of all the user collections. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's collections. [Learn more about different API
     * modes](/docs/admin).
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Database::listCollections(string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Create Collection
 *
     * Create a new Collection.
 * @param string name
 * @param array read
 * @param array write
 * @param array rules
 * @throws AppwriteException
 * @return array
 */
json Database::createCollection(string name, array read, array write, array rules) {
    json params = {
        {"name", name},
        {"read", read},
        {"write", write},
        {"rules", rules},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get Collection
 *
     * Get a collection by its unique ID. This endpoint response returns a JSON
     * object with the collection metadata.
 * @param string collectionId
 * @throws AppwriteException
 * @return array
 */
json Database::getCollection(string collectionId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update Collection
 *
     * Update a collection by its unique ID.
 * @param string collectionId
 * @param string name
 * @param array read
 * @param array write
 * @param array rules
 * @throws AppwriteException
 * @return array
 */
json Database::updateCollection(string collectionId, string name, array read, array write, array rules) {
    json params = {
        {"name", name},
        {"read", read},
        {"write", write},
        {"rules", rules},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");

    return this->client->call(Client::METHOD_PUT, path, headers, params);
}

/*
 * Delete Collection
 *
     * Delete a collection by its unique ID. Only users with write permissions
     * have access to delete this resource.
 * @param string collectionId
 * @throws AppwriteException
 * @return array
 */
json Database::deleteCollection(string collectionId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * List Documents
 *
     * Get a list of all the user documents. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's documents. [Learn more about different API
     * modes](/docs/admin).
 * @param string collectionId
 * @param array filters
 * @param int limit
 * @param int offset
 * @param string orderField
 * @param string orderType
 * @param string orderCast
 * @param string search
 * @throws AppwriteException
 * @return array
 */
json Database::listDocuments(string collectionId, array filters, int limit, int offset, string orderField, string orderType, string orderCast, string search) {
    json params = {
        {"filters", filters},
        {"limit", limit},
        {"offset", offset},
        {"orderField", orderField},
        {"orderType", orderType},
        {"orderCast", orderCast},
        {"search", search},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}/documents";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Create Document
 *
     * Create a new Document. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](/docs/server/database#databaseCreateCollection) API or
     * directly from your database console.
 * @param string collectionId
 * @param json data
 * @param array read
 * @param array write
 * @param string parentDocument
 * @param string parentProperty
 * @param string parentPropertyType
 * @throws AppwriteException
 * @return array
 */
json Database::createDocument(string collectionId, json data, array read, array write, string parentDocument, string parentProperty, string parentPropertyType) {
    json params = {
        {"data", data},
        {"read", read},
        {"write", write},
        {"parentDocument", parentDocument},
        {"parentProperty", parentProperty},
        {"parentPropertyType", parentPropertyType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}/documents";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get Document
 *
     * Get a document by its unique ID. This endpoint response returns a JSON
     * object with the document data.
 * @param string collectionId
 * @param string documentId
 * @throws AppwriteException
 * @return array
 */
json Database::getDocument(string collectionId, string documentId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}/documents/{documentId}";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");
    searchString = "{documentId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "documentId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update Document
 *
     * Update a document by its unique ID. Using the patch method you can pass
     * only specific fields that will get updated.
 * @param string collectionId
 * @param string documentId
 * @param json data
 * @param array read
 * @param array write
 * @throws AppwriteException
 * @return array
 */
json Database::updateDocument(string collectionId, string documentId, json data, array read, array write) {
    json params = {
        {"data", data},
        {"read", read},
        {"write", write},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}/documents/{documentId}";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");
    searchString = "{documentId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "documentId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * Delete Document
 *
     * Delete a document by its unique ID. This endpoint deletes only the parent
     * documents, its attributes and relations to other documents. Child documents
     * **will not** be deleted.
 * @param string collectionId
 * @param string documentId
 * @throws AppwriteException
 * @return array
 */
json Database::deleteDocument(string collectionId, string documentId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/database/collections/{collectionId}/documents/{documentId}";
    size_t pos;
    string searchString;
    searchString = "{collectionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "collectionId");
    searchString = "{documentId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "documentId");

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}
} // namespace Appwrite

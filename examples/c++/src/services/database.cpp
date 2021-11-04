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

class Database : public Service {
 public:
    /**
     * List Collections
     *
     * Get a list of all the user collections. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's collections. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param string search
     * @param int limit
     * @param int offset
     * @param string orderType
     * @throws AppwriteException
     * @return array
     */
    json listCollections(string* search = NULL, int* limit = NULL, int* offset = NULL, string* orderType = NULL) {
        map<string, string> params;
        string path = "/database/collections";
        size_t pos;
        string searchString;

        if (search != NULL) {
            params["search"] = *search;
        }

        if (limit != NULL) {
            params["limit"] = *limit;
        }

        if (offset != NULL) {
            params["offset"] = *offset;
        }

        if (orderType != NULL) {
            params["orderType"] = *orderType;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Create Collection
     *
     * Create a new Collection.
     *
     * @param string name
     * @param array read
     * @param array write
     * @param array rules
     * @throws AppwriteException
     * @return array
     */
    json createCollection(string* name, array* read, array* write, array* rules) {
        if (!isset(name)) {
            throw new AppwriteException("Missing required parameter: 'name'");
        }

        if (!isset(read)) {
            throw new AppwriteException("Missing required parameter: 'read'");
        }

        if (!isset(write)) {
            throw new AppwriteException("Missing required parameter: 'write'");
        }

        if (!isset(rules)) {
            throw new AppwriteException("Missing required parameter: 'rules'");
        }

        map<string, string> params;
        string path = "/database/collections";
        size_t pos;
        string searchString;

        if (name != NULL) {
            params["name"] = *name;
        }

        if (read != NULL) {
            params["read"] = *read;
        }

        if (write != NULL) {
            params["write"] = *write;
        }

        if (rules != NULL) {
            params["rules"] = *rules;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get Collection
     *
     * Get a collection by its unique ID. This endpoint response returns a JSON
     * object with the collection metadata.
     *
     * @param string collectionId
     * @throws AppwriteException
     * @return array
     */
    json getCollection(string* collectionId) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        map<string, string> params;
        string path = "/database/collections/{collectionId}";
        size_t pos;
        string searchString;
        searchString = "{collectionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "collectionId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update Collection
     *
     * Update a collection by its unique ID.
     *
     * @param string collectionId
     * @param string name
     * @param array read
     * @param array write
     * @param array rules
     * @throws AppwriteException
     * @return array
     */
    json updateCollection(string* collectionId, string* name, array* read = NULL, array* write = NULL, array* rules = NULL) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        if (!isset(name)) {
            throw new AppwriteException("Missing required parameter: 'name'");
        }

        map<string, string> params;
        string path = "/database/collections/{collectionId}";
        size_t pos;
        string searchString;
        searchString = "{collectionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "collectionId");

        if (name != NULL) {
            params["name"] = *name;
        }

        if (read != NULL) {
            params["read"] = *read;
        }

        if (write != NULL) {
            params["write"] = *write;
        }

        if (rules != NULL) {
            params["rules"] = *rules;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PUT, path, headers, params);
    }

    /**
     * Delete Collection
     *
     * Delete a collection by its unique ID. Only users with write permissions
     * have access to delete this resource.
     *
     * @param string collectionId
     * @throws AppwriteException
     * @return array
     */
    json deleteCollection(string* collectionId) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        map<string, string> params;
        string path = "/database/collections/{collectionId}";
        size_t pos;
        string searchString;
        searchString = "{collectionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "collectionId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * List Documents
     *
     * Get a list of all the user documents. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's documents. [Learn more about different API
     * modes](/docs/admin).
     *
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
    json listDocuments(string* collectionId, array* filters = NULL, int* limit = NULL, int* offset = NULL, string* orderField = NULL, string* orderType = NULL, string* orderCast = NULL, string* search = NULL) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        map<string, string> params;
        string path = "/database/collections/{collectionId}/documents";
        size_t pos;
        string searchString;
        searchString = "{collectionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "collectionId");

        if (filters != NULL) {
            params["filters"] = *filters;
        }

        if (limit != NULL) {
            params["limit"] = *limit;
        }

        if (offset != NULL) {
            params["offset"] = *offset;
        }

        if (orderField != NULL) {
            params["orderField"] = *orderField;
        }

        if (orderType != NULL) {
            params["orderType"] = *orderType;
        }

        if (orderCast != NULL) {
            params["orderCast"] = *orderCast;
        }

        if (search != NULL) {
            params["search"] = *search;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Create Document
     *
     * Create a new Document. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](/docs/server/database#databaseCreateCollection) API or
     * directly from your database console.
     *
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
    json createDocument(string* collectionId, json* data, array* read = NULL, array* write = NULL, string* parentDocument = NULL, string* parentProperty = NULL, string* parentPropertyType = NULL) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        if (!isset(data)) {
            throw new AppwriteException("Missing required parameter: 'data'");
        }

        map<string, string> params;
        string path = "/database/collections/{collectionId}/documents";
        size_t pos;
        string searchString;
        searchString = "{collectionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "collectionId");

        if (data != NULL) {
            params["data"] = *data;
        }

        if (read != NULL) {
            params["read"] = *read;
        }

        if (write != NULL) {
            params["write"] = *write;
        }

        if (parentDocument != NULL) {
            params["parentDocument"] = *parentDocument;
        }

        if (parentProperty != NULL) {
            params["parentProperty"] = *parentProperty;
        }

        if (parentPropertyType != NULL) {
            params["parentPropertyType"] = *parentPropertyType;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get Document
     *
     * Get a document by its unique ID. This endpoint response returns a JSON
     * object with the document data.
     *
     * @param string collectionId
     * @param string documentId
     * @throws AppwriteException
     * @return array
     */
    json getDocument(string* collectionId, string* documentId) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        if (!isset(documentId)) {
            throw new AppwriteException("Missing required parameter: 'documentId'");
        }

        map<string, string> params;
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

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update Document
     *
     * Update a document by its unique ID. Using the patch method you can pass
     * only specific fields that will get updated.
     *
     * @param string collectionId
     * @param string documentId
     * @param json data
     * @param array read
     * @param array write
     * @throws AppwriteException
     * @return array
     */
    json updateDocument(string* collectionId, string* documentId, json* data, array* read = NULL, array* write = NULL) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        if (!isset(documentId)) {
            throw new AppwriteException("Missing required parameter: 'documentId'");
        }

        if (!isset(data)) {
            throw new AppwriteException("Missing required parameter: 'data'");
        }

        map<string, string> params;
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

        if (data != NULL) {
            params["data"] = *data;
        }

        if (read != NULL) {
            params["read"] = *read;
        }

        if (write != NULL) {
            params["write"] = *write;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * Delete Document
     *
     * Delete a document by its unique ID. This endpoint deletes only the parent
     * documents, its attributes and relations to other documents. Child documents
     * **will not** be deleted.
     *
     * @param string collectionId
     * @param string documentId
     * @throws AppwriteException
     * @return array
     */
    json deleteDocument(string* collectionId, string* documentId) {
        if (!isset(collectionId)) {
            throw new AppwriteException("Missing required parameter: 'collectionId'");
        }

        if (!isset(documentId)) {
            throw new AppwriteException("Missing required parameter: 'documentId'");
        }

        map<string, string> params;
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

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }
};
}

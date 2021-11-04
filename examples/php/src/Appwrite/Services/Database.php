<?php

namespace Appwrite\Services;

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\Service;

class Database extends Service
{
    /**
     * List Collections
     *
     * Get a list of all the user collections. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's collections. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param string $orderType
     * @throws AppwriteException
     * @return array
     */
    public function listCollections(string $search = null, int $limit = null, int $offset = null, string $orderType = null): array
    {
        $path   = str_replace([], [], '/database/collections');
        $params = [];

        if (!is_null($search)) {
            $params['search'] = $search;
        }

        if (!is_null($limit)) {
            $params['limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['offset'] = $offset;
        }

        if (!is_null($orderType)) {
            $params['orderType'] = $orderType;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Create Collection
     *
     * Create a new Collection.
     *
     * @param string $name
     * @param array $read
     * @param array $write
     * @param array $rules
     * @throws AppwriteException
     * @return array
     */
    public function createCollection(string $name, array $read, array $write, array $rules): array
    {
        if (!isset($name)) {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        if (!isset($read)) {
            throw new AppwriteException('Missing required parameter: "read"');
        }

        if (!isset($write)) {
            throw new AppwriteException('Missing required parameter: "write"');
        }

        if (!isset($rules)) {
            throw new AppwriteException('Missing required parameter: "rules"');
        }

        $path   = str_replace([], [], '/database/collections');
        $params = [];

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        if (!is_null($read)) {
            $params['read'] = $read;
        }

        if (!is_null($write)) {
            $params['write'] = $write;
        }

        if (!is_null($rules)) {
            $params['rules'] = $rules;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Collection
     *
     * Get a collection by its unique ID. This endpoint response returns a JSON
     * object with the collection metadata.
     *
     * @param string $collectionId
     * @throws AppwriteException
     * @return array
     */
    public function getCollection(string $collectionId): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update Collection
     *
     * Update a collection by its unique ID.
     *
     * @param string $collectionId
     * @param string $name
     * @param array $read
     * @param array $write
     * @param array $rules
     * @throws AppwriteException
     * @return array
     */
    public function updateCollection(string $collectionId, string $name, array $read = null, array $write = null, array $rules = null): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        if (!isset($name)) {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}');
        $params = [];

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        if (!is_null($read)) {
            $params['read'] = $read;
        }

        if (!is_null($write)) {
            $params['write'] = $write;
        }

        if (!is_null($rules)) {
            $params['rules'] = $rules;
        }

        return $this->client->call(Client::METHOD_PUT, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Delete Collection
     *
     * Delete a collection by its unique ID. Only users with write permissions
     * have access to delete this resource.
     *
     * @param string $collectionId
     * @throws AppwriteException
     * @return array
     */
    public function deleteCollection(string $collectionId): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}');
        $params = [];

        return $this->client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * List Documents
     *
     * Get a list of all the user documents. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's documents. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param string $collectionId
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @param string $orderField
     * @param string $orderType
     * @param string $orderCast
     * @param string $search
     * @throws AppwriteException
     * @return array
     */
    public function listDocuments(string $collectionId, array $filters = null, int $limit = null, int $offset = null, string $orderField = null, string $orderType = null, string $orderCast = null, string $search = null): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}/documents');
        $params = [];

        if (!is_null($filters)) {
            $params['filters'] = $filters;
        }

        if (!is_null($limit)) {
            $params['limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['offset'] = $offset;
        }

        if (!is_null($orderField)) {
            $params['orderField'] = $orderField;
        }

        if (!is_null($orderType)) {
            $params['orderType'] = $orderType;
        }

        if (!is_null($orderCast)) {
            $params['orderCast'] = $orderCast;
        }

        if (!is_null($search)) {
            $params['search'] = $search;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Create Document
     *
     * Create a new Document. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](/docs/server/database#databaseCreateCollection) API or
     * directly from your database console.
     *
     * @param string $collectionId
     * @param array $data
     * @param array $read
     * @param array $write
     * @param string $parentDocument
     * @param string $parentProperty
     * @param string $parentPropertyType
     * @throws AppwriteException
     * @return array
     */
    public function createDocument(string $collectionId, array $data, array $read = null, array $write = null, string $parentDocument = null, string $parentProperty = null, string $parentPropertyType = null): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        if (!isset($data)) {
            throw new AppwriteException('Missing required parameter: "data"');
        }

        $path   = str_replace(['{collectionId}'], [$collectionId], '/database/collections/{collectionId}/documents');
        $params = [];

        if (!is_null($data)) {
            $params['data'] = $data;
        }

        if (!is_null($read)) {
            $params['read'] = $read;
        }

        if (!is_null($write)) {
            $params['write'] = $write;
        }

        if (!is_null($parentDocument)) {
            $params['parentDocument'] = $parentDocument;
        }

        if (!is_null($parentProperty)) {
            $params['parentProperty'] = $parentProperty;
        }

        if (!is_null($parentPropertyType)) {
            $params['parentPropertyType'] = $parentPropertyType;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Document
     *
     * Get a document by its unique ID. This endpoint response returns a JSON
     * object with the document data.
     *
     * @param string $collectionId
     * @param string $documentId
     * @throws AppwriteException
     * @return array
     */
    public function getDocument(string $collectionId, string $documentId): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        if (!isset($documentId)) {
            throw new AppwriteException('Missing required parameter: "documentId"');
        }

        $path   = str_replace(['{collectionId}', '{documentId}'], [$collectionId, $documentId], '/database/collections/{collectionId}/documents/{documentId}');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update Document
     *
     * Update a document by its unique ID. Using the patch method you can pass
     * only specific fields that will get updated.
     *
     * @param string $collectionId
     * @param string $documentId
     * @param array $data
     * @param array $read
     * @param array $write
     * @throws AppwriteException
     * @return array
     */
    public function updateDocument(string $collectionId, string $documentId, array $data, array $read = null, array $write = null): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        if (!isset($documentId)) {
            throw new AppwriteException('Missing required parameter: "documentId"');
        }

        if (!isset($data)) {
            throw new AppwriteException('Missing required parameter: "data"');
        }

        $path   = str_replace(['{collectionId}', '{documentId}'], [$collectionId, $documentId], '/database/collections/{collectionId}/documents/{documentId}');
        $params = [];

        if (!is_null($data)) {
            $params['data'] = $data;
        }

        if (!is_null($read)) {
            $params['read'] = $read;
        }

        if (!is_null($write)) {
            $params['write'] = $write;
        }

        return $this->client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Delete Document
     *
     * Delete a document by its unique ID. This endpoint deletes only the parent
     * documents, its attributes and relations to other documents. Child documents
     * **will not** be deleted.
     *
     * @param string $collectionId
     * @param string $documentId
     * @throws AppwriteException
     * @return array
     */
    public function deleteDocument(string $collectionId, string $documentId): array
    {
        if (!isset($collectionId)) {
            throw new AppwriteException('Missing required parameter: "collectionId"');
        }

        if (!isset($documentId)) {
            throw new AppwriteException('Missing required parameter: "documentId"');
        }

        $path   = str_replace(['{collectionId}', '{documentId}'], [$collectionId, $documentId], '/database/collections/{collectionId}/documents/{documentId}');
        $params = [];

        return $this->client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
    }
}

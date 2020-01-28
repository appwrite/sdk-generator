

class Database: Service
{
    /**
     * List Collections
     *
     * Get a list of all the user collections. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project collections. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param String _search
     * @param Int _limit
     * @param Int _offset
     * @param String _orderType
     * @throws Exception
     * @return array
     */

    func listCollections(_search: String = "", _limit: Int = 25, _offset: Int = 0, _orderType: String = "ASC") -> Array<Any> {
        let path: String = "/database"


                var params: [String: Any] = [:]
        
        params["search"] = _search
        params["limit"] = _limit
        params["offset"] = _offset
        params["orderType"] = _orderType

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Collection
     *
     * Create a new Collection.
     *
     * @param String _name
     * @param Array<Any> _read
     * @param Array<Any> _write
     * @param Array<Any> _rules
     * @throws Exception
     * @return array
     */

    func createCollection(_name: String, _read: Array<Any>, _write: Array<Any>, _rules: Array<Any>) -> Array<Any> {
        let path: String = "/database"


                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["read"] = _read
        params["write"] = _write
        params["rules"] = _rules

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Collection
     *
     * Get collection by its unique ID. This endpoint response returns a JSON
     * object with the collection metadata.
     *
     * @param String _collectionId
     * @throws Exception
     * @return array
     */

    func getCollection(_collectionId: String) -> Array<Any> {
        var path: String = "/database/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Collection
     *
     * Update collection by its unique ID.
     *
     * @param String _collectionId
     * @param String _name
     * @param Array<Any> _read
     * @param Array<Any> _write
     * @param Array<Any> _rules
     * @throws Exception
     * @return array
     */

    func updateCollection(_collectionId: String, _name: String, _read: Array<Any>, _write: Array<Any>, _rules: Array<Any> = []) -> Array<Any> {
        var path: String = "/database/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["read"] = _read
        params["write"] = _write
        params["rules"] = _rules

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Collection
     *
     * Delete a collection by its unique ID. Only users with write permissions
     * have access to delete this resource.
     *
     * @param String _collectionId
     * @throws Exception
     * @return array
     */

    func deleteCollection(_collectionId: String) -> Array<Any> {
        var path: String = "/database/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Documents
     *
     * Get a list of all the user documents. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project documents. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param String _collectionId
     * @param Array<Any> _filters
     * @param Int _offset
     * @param Int _limit
     * @param String _orderField
     * @param String _orderType
     * @param String _orderCast
     * @param String _search
     * @param Int _first
     * @param Int _last
     * @throws Exception
     * @return array
     */

    func listDocuments(_collectionId: String, _filters: Array<Any> = [], _offset: Int = 0, _limit: Int = 50, _orderField: String = "$uid", _orderType: String = "ASC", _orderCast: String = "string", _search: String = "", _first: Int = 0, _last: Int = 0) -> Array<Any> {
        var path: String = "/database/{collectionId}/documents"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )

                var params: [String: Any] = [:]
        
        params["filters"] = _filters
        params["offset"] = _offset
        params["limit"] = _limit
        params["order-field"] = _orderField
        params["order-type"] = _orderType
        params["order-cast"] = _orderCast
        params["search"] = _search
        params["first"] = _first
        params["last"] = _last

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Document
     *
     * Create a new Document.
     *
     * @param String _collectionId
     * @param String _data
     * @param Array<Any> _read
     * @param Array<Any> _write
     * @param String _parentDocument
     * @param String _parentProperty
     * @param String _parentPropertyType
     * @throws Exception
     * @return array
     */

    func createDocument(_collectionId: String, _data: String, _read: Array<Any>, _write: Array<Any>, _parentDocument: String = "", _parentProperty: String = "", _parentPropertyType: String = "assign") -> Array<Any> {
        var path: String = "/database/{collectionId}/documents"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )

                var params: [String: Any] = [:]
        
        params["data"] = _data
        params["read"] = _read
        params["write"] = _write
        params["parentDocument"] = _parentDocument
        params["parentProperty"] = _parentProperty
        params["parentPropertyType"] = _parentPropertyType

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Document
     *
     * Get document by its unique ID. This endpoint response returns a JSON object
     * with the document data.
     *
     * @param String _collectionId
     * @param String _documentId
     * @throws Exception
     * @return array
     */

    func getDocument(_collectionId: String, _documentId: String) -> Array<Any> {
        var path: String = "/database/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )
        path = path.replacingOccurrences(
          of: "{documentId}",
          with: _documentId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Document
     *
     * @param String _collectionId
     * @param String _documentId
     * @param String _data
     * @param Array<Any> _read
     * @param Array<Any> _write
     * @throws Exception
     * @return array
     */

    func updateDocument(_collectionId: String, _documentId: String, _data: String, _read: Array<Any>, _write: Array<Any>) -> Array<Any> {
        var path: String = "/database/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )
        path = path.replacingOccurrences(
          of: "{documentId}",
          with: _documentId
        )

                var params: [String: Any] = [:]
        
        params["data"] = _data
        params["read"] = _read
        params["write"] = _write

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Document
     *
     * Delete document by its unique ID. This endpoint deletes only the parent
     * documents, his attributes and relations to other documents. Child documents
     * **will not** be deleted.
     *
     * @param String _collectionId
     * @param String _documentId
     * @throws Exception
     * @return array
     */

    func deleteDocument(_collectionId: String, _documentId: String) -> Array<Any> {
        var path: String = "/database/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: _collectionId
        )
        path = path.replacingOccurrences(
          of: "{documentId}",
          with: _documentId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}

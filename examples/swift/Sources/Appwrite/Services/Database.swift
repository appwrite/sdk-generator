

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
     * @param String search
     * @param Int limit
     * @param Int offset
     * @param String orderType
     * @throws Exception
     * @return array
     */

    func listCollections(search: String = "", limit: Int = 25, offset: Int = 0, orderType: String = "ASC")-> Array<Any> {
        let path: String = "/database"


                var params: [String: Any] = [:]
        
        params["search"] = search
        params["limit"] = limit
        params["offset"] = offset
        params["orderType"] = orderType

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Collection
     *
     * Create a new Collection.
     *
     * @param String name
     * @param Array<Any> read
     * @param Array<Any> write
     * @param Array<Any> rules
     * @throws Exception
     * @return array
     */

    func createCollection(name: String, read: Array<Any>, write: Array<Any>, rules: Array<Any>)-> Array<Any> {
        let path: String = "/database"


                var params: [String: Any] = [:]
        
        params["name"] = name
        params["read"] = read
        params["write"] = write
        params["rules"] = rules

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
     * @param String collectionId
     * @throws Exception
     * @return array
     */

    func getCollection(collectionId: String)-> Array<Any> {
        var path: String = "/database/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
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
     * @param String collectionId
     * @param String name
     * @param Array<Any> read
     * @param Array<Any> write
     * @param Array<Any> rules
     * @throws Exception
     * @return array
     */

    func updateCollection(collectionId: String, name: String, read: Array<Any>, write: Array<Any>, rules: Array<Any> = [])-> Array<Any> {
        var path: String = "/database/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["read"] = read
        params["write"] = write
        params["rules"] = rules

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
     * @param String collectionId
     * @throws Exception
     * @return array
     */

    func deleteCollection(collectionId: String)-> Array<Any> {
        var path: String = "/database/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
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
     * @param String collectionId
     * @param Array<Any> filters
     * @param Int offset
     * @param Int limit
     * @param String orderField
     * @param String orderType
     * @param String orderCast
     * @param String search
     * @param Int first
     * @param Int last
     * @throws Exception
     * @return array
     */

    func listDocuments(collectionId: String, filters: Array<Any> = [], offset: Int = 0, limit: Int = 50, orderField: String = "$uid", orderType: String = "ASC", orderCast: String = "string", search: String = "", first: Int = 0, last: Int = 0)-> Array<Any> {
        var path: String = "/database/{collectionId}/documents"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
        )

                var params: [String: Any] = [:]
        
        params["filters"] = filters
        params["offset"] = offset
        params["limit"] = limit
        params["order-field"] = orderField
        params["order-type"] = orderType
        params["order-cast"] = orderCast
        params["search"] = search
        params["first"] = first
        params["last"] = last

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Document
     *
     * Create a new Document.
     *
     * @param String collectionId
     * @param String data
     * @param Array<Any> read
     * @param Array<Any> write
     * @param String parentDocument
     * @param String parentProperty
     * @param String parentPropertyType
     * @throws Exception
     * @return array
     */

    func createDocument(collectionId: String, data: String, read: Array<Any>, write: Array<Any>, parentDocument: String = "", parentProperty: String = "", parentPropertyType: String = "assign")-> Array<Any> {
        var path: String = "/database/{collectionId}/documents"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
        )

                var params: [String: Any] = [:]
        
        params["data"] = data
        params["read"] = read
        params["write"] = write
        params["parentDocument"] = parentDocument
        params["parentProperty"] = parentProperty
        params["parentPropertyType"] = parentPropertyType

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
     * @param String collectionId
     * @param String documentId
     * @throws Exception
     * @return array
     */

    func getDocument(collectionId: String, documentId: String)-> Array<Any> {
        var path: String = "/database/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
        )
        path = path.replacingOccurrences(
          of: "{documentId}",
          with: documentId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Document
     *
     * @param String collectionId
     * @param String documentId
     * @param String data
     * @param Array<Any> read
     * @param Array<Any> write
     * @throws Exception
     * @return array
     */

    func updateDocument(collectionId: String, documentId: String, data: String, read: Array<Any>, write: Array<Any>)-> Array<Any> {
        var path: String = "/database/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
        )
        path = path.replacingOccurrences(
          of: "{documentId}",
          with: documentId
        )

                var params: [String: Any] = [:]
        
        params["data"] = data
        params["read"] = read
        params["write"] = write

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
     * @param String collectionId
     * @param String documentId
     * @throws Exception
     * @return array
     */

    func deleteDocument(collectionId: String, documentId: String)-> Array<Any> {
        var path: String = "/database/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId
        )
        path = path.replacingOccurrences(
          of: "{documentId}",
          with: documentId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}

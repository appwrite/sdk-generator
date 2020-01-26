

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

    func listCollections(search: String = null, limit: Int = 25, offset: Int = 0, orderType: String = 'ASC')-> Array<Any> {
        let methodPath = "/database"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["search"] = search
        params["limit"] = limit
        params["offset"] = offset
        params["orderType"] = orderType

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create Collection
     *
     * Create a new Collection.
     *
     * @param String name
     * @param Array read
     * @param Array write
     * @param Array rules
     * @throws Exception
     * @return array
     */

    func createCollection(name: String, read: Array, write: Array, rules: Array)-> Array<Any> {
        let methodPath = "/database"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["read"] = read
        params["write"] = write
        params["rules"] = rules

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/database/{collectionId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}']",
          with: "$collectionId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Update Collection
     *
     * Update collection by its unique ID.
     *
     * @param String collectionId
     * @param String name
     * @param Array read
     * @param Array write
     * @param Array rules
     * @throws Exception
     * @return array
     */

    func updateCollection(collectionId: String, name: String, read: Array, write: Array, rules: Array = const [])-> Array<Any> {
        let methodPath = "/database/{collectionId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}']",
          with: "$collectionId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["read"] = read
        params["write"] = write
        params["rules"] = rules

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/database/{collectionId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}']",
          with: "$collectionId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
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
     * @param Array filters
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

    func listDocuments(collectionId: String, filters: Array = const [], offset: Int = 0, limit: Int = 50, orderField: String = '$uid', orderType: String = 'ASC', orderCast: String = 'string', search: String = null, first: Int = 0, last: Int = 0)-> Array<Any> {
        let methodPath = "/database/{collectionId}/documents"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}']",
          with: "$collectionId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["filters"] = filters
        params["offset"] = offset
        params["limit"] = limit
        params["order-field"] = orderField
        params["order-type"] = orderType
        params["order-cast"] = orderCast
        params["search"] = search
        params["first"] = first
        params["last"] = last

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create Document
     *
     * Create a new Document.
     *
     * @param String collectionId
     * @param String data
     * @param Array read
     * @param Array write
     * @param String parentDocument
     * @param String parentProperty
     * @param String parentPropertyType
     * @throws Exception
     * @return array
     */

    func createDocument(collectionId: String, data: String, read: Array, write: Array, parentDocument: String = null, parentProperty: String = null, parentPropertyType: String = 'assign')-> Array<Any> {
        let methodPath = "/database/{collectionId}/documents"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}']",
          with: "$collectionId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["data"] = data
        params["read"] = read
        params["write"] = write
        params["parentDocument"] = parentDocument
        params["parentProperty"] = parentProperty
        params["parentPropertyType"] = parentPropertyType

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/database/{collectionId}/documents/{documentId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}', '//{documentId}']",
          with: "$collectionId, $documentId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Update Document
     *
     * @param String collectionId
     * @param String documentId
     * @param String data
     * @param Array read
     * @param Array write
     * @throws Exception
     * @return array
     */

    func updateDocument(collectionId: String, documentId: String, data: String, read: Array, write: Array)-> Array<Any> {
        let methodPath = "/database/{collectionId}/documents/{documentId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}', '//{documentId}']",
          with: "$collectionId, $documentId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["data"] = data
        params["read"] = read
        params["write"] = write

        return self.client.call(HTTPMethod.patch, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/database/{collectionId}/documents/{documentId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{collectionId}', '//{documentId}']",
          with: "$collectionId, $documentId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

}

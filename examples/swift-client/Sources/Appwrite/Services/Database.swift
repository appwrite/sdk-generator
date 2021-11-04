import AsyncHTTPClient
import Foundation
import NIO
import AppwriteModels

open class Database: Service {
    ///
    /// List Collections
    ///
    /// Get a list of all the user collections. You can use the query params to
    /// filter your results. On admin mode, this endpoint will return a list of all
    /// of the project's collections. [Learn more about different API
    /// modes](/docs/admin).
    ///
    /// @param String search
    /// @param Int limit
    /// @param Int offset
    /// @param String orderType
    /// @throws Exception
    /// @return array
    ///
    open func listCollections(
        search: String? = nil,
        limit: Int? = nil,
        offset: Int? = nil,
        orderType: String? = nil,
        completion: ((Result<AppwriteModels.CollectionList, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/database/collections"

        let params: [String: Any?] = [
            "search": search,
            "limit": limit,
            "offset": offset,
            "orderType": orderType
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.CollectionList = { dict in
            return AppwriteModels.CollectionList.from(map: dict)
        }

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Create Collection
    ///
    /// Create a new Collection.
    ///
    /// @param String name
    /// @param [Any] read
    /// @param [Any] write
    /// @param [Any] rules
    /// @throws Exception
    /// @return array
    ///
    open func createCollection(
        name: String,
        read: [Any],
        write: [Any],
        rules: [Any],
        completion: ((Result<AppwriteModels.Collection, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/database/collections"

        let params: [String: Any?] = [
            "name": name,
            "read": read,
            "write": write,
            "rules": rules
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Collection = { dict in
            return AppwriteModels.Collection.from(map: dict)
        }

        client.call(
            method: "POST",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Get Collection
    ///
    /// Get a collection by its unique ID. This endpoint response returns a JSON
    /// object with the collection metadata.
    ///
    /// @param String collectionId
    /// @throws Exception
    /// @return array
    ///
    open func getCollection(
        collectionId: String,
        completion: ((Result<AppwriteModels.Collection, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Collection = { dict in
            return AppwriteModels.Collection.from(map: dict)
        }

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Update Collection
    ///
    /// Update a collection by its unique ID.
    ///
    /// @param String collectionId
    /// @param String name
    /// @param [Any] read
    /// @param [Any] write
    /// @param [Any] rules
    /// @throws Exception
    /// @return array
    ///
    open func updateCollection(
        collectionId: String,
        name: String,
        read: [Any]? = nil,
        write: [Any]? = nil,
        rules: [Any]? = nil,
        completion: ((Result<AppwriteModels.Collection, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        let params: [String: Any?] = [
            "name": name,
            "read": read,
            "write": write,
            "rules": rules
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Collection = { dict in
            return AppwriteModels.Collection.from(map: dict)
        }

        client.call(
            method: "PUT",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Delete Collection
    ///
    /// Delete a collection by its unique ID. Only users with write permissions
    /// have access to delete this resource.
    ///
    /// @param String collectionId
    /// @throws Exception
    /// @return array
    ///
    open func deleteCollection(
        collectionId: String,
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "DELETE",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// List Documents
    ///
    /// Get a list of all the user documents. You can use the query params to
    /// filter your results. On admin mode, this endpoint will return a list of all
    /// of the project's documents. [Learn more about different API
    /// modes](/docs/admin).
    ///
    /// @param String collectionId
    /// @param [Any] filters
    /// @param Int limit
    /// @param Int offset
    /// @param String orderField
    /// @param String orderType
    /// @param String orderCast
    /// @param String search
    /// @throws Exception
    /// @return array
    ///
    open func listDocuments(
        collectionId: String,
        filters: [Any]? = nil,
        limit: Int? = nil,
        offset: Int? = nil,
        orderField: String? = nil,
        orderType: String? = nil,
        orderCast: String? = nil,
        search: String? = nil,
        completion: ((Result<AppwriteModels.DocumentList, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}/documents"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        let params: [String: Any?] = [
            "filters": filters,
            "limit": limit,
            "offset": offset,
            "orderField": orderField,
            "orderType": orderType,
            "orderCast": orderCast,
            "search": search
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.DocumentList = { dict in
            return AppwriteModels.DocumentList.from(map: dict)
        }

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Create Document
    ///
    /// Create a new Document. Before using this route, you should create a new
    /// collection resource using either a [server
    /// integration](/docs/server/database#databaseCreateCollection) API or
    /// directly from your database console.
    ///
    /// @param String collectionId
    /// @param Any data
    /// @param [Any] read
    /// @param [Any] write
    /// @param String parentDocument
    /// @param String parentProperty
    /// @param String parentPropertyType
    /// @throws Exception
    /// @return array
    ///
    open func createDocument(
        collectionId: String,
        data: Any,
        read: [Any]? = nil,
        write: [Any]? = nil,
        parentDocument: String? = nil,
        parentProperty: String? = nil,
        parentPropertyType: String? = nil,
        completion: ((Result<AppwriteModels.Document, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}/documents"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        let params: [String: Any?] = [
            "data": data,
            "read": read,
            "write": write,
            "parentDocument": parentDocument,
            "parentProperty": parentProperty,
            "parentPropertyType": parentPropertyType
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Document = { dict in
            return AppwriteModels.Document.from(map: dict)
        }

        client.call(
            method: "POST",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Get Document
    ///
    /// Get a document by its unique ID. This endpoint response returns a JSON
    /// object with the document data.
    ///
    /// @param String collectionId
    /// @param String documentId
    /// @throws Exception
    /// @return array
    ///
    open func getDocument(
        collectionId: String,
        documentId: String,
        completion: ((Result<AppwriteModels.Document, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        path = path.replacingOccurrences(
          of: "{documentId}",
          with: documentId        )

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Document = { dict in
            return AppwriteModels.Document.from(map: dict)
        }

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Update Document
    ///
    /// Update a document by its unique ID. Using the patch method you can pass
    /// only specific fields that will get updated.
    ///
    /// @param String collectionId
    /// @param String documentId
    /// @param Any data
    /// @param [Any] read
    /// @param [Any] write
    /// @throws Exception
    /// @return array
    ///
    open func updateDocument(
        collectionId: String,
        documentId: String,
        data: Any,
        read: [Any]? = nil,
        write: [Any]? = nil,
        completion: ((Result<AppwriteModels.Document, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        path = path.replacingOccurrences(
          of: "{documentId}",
          with: documentId        )

        let params: [String: Any?] = [
            "data": data,
            "read": read,
            "write": write
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Document = { dict in
            return AppwriteModels.Document.from(map: dict)
        }

        client.call(
            method: "PATCH",
            path: path,
            headers: headers,
            params: params,
            convert: convert,
            completion: completion
        )
    }

    ///
    /// Delete Document
    ///
    /// Delete a document by its unique ID. This endpoint deletes only the parent
    /// documents, its attributes and relations to other documents. Child documents
    /// **will not** be deleted.
    ///
    /// @param String collectionId
    /// @param String documentId
    /// @throws Exception
    /// @return array
    ///
    open func deleteDocument(
        collectionId: String,
        documentId: String,
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/database/collections/{collectionId}/documents/{documentId}"

        path = path.replacingOccurrences(
          of: "{collectionId}",
          with: collectionId        )

        path = path.replacingOccurrences(
          of: "{documentId}",
          with: documentId        )

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "DELETE",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

}

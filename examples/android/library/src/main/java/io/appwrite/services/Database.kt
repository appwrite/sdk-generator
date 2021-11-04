package io.appwrite.services

import android.net.Uri
import io.appwrite.Client
import io.appwrite.exceptions.AppwriteException
import okhttp3.Cookie
import okhttp3.Response
import java.io.File

class Database(client: Client) : Service(client) {

    /**
     * List Collections
     *
     * Get a list of all the user collections. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's collections. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param search
     * @param limit
     * @param offset
     * @param orderType
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun listCollections(
		search: String? = null,
		limit: Int? = null,
		offset: Int? = null,
		orderType: String? = null
	): Response {
        val path = "/database/collections"
        val params = mapOf<String, Any?>(
            "search" to search,
            "limit" to limit,
            "offset" to offset,
            "orderType" to orderType
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Create Collection
     *
     * Create a new Collection.
     *
     * @param name
     * @param read
     * @param write
     * @param rules
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun createCollection(
		name: String,
		read: List<Any>,
		write: List<Any>,
		rules: List<Any>
	): Response {
        val path = "/database/collections"
        val params = mapOf<String, Any?>(
            "name" to name,
            "read" to read,
            "write" to write,
            "rules" to rules
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("POST", path, headers, params)
    }
    
    /**
     * Get Collection
     *
     * Get a collection by its unique ID. This endpoint response returns a JSON
     * object with the collection metadata.
     *
     * @param collectionId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getCollection(
		collectionId: String
	): Response {
        val path = "/database/collections/{collectionId}".replace("{collectionId}", collectionId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Update Collection
     *
     * Update a collection by its unique ID.
     *
     * @param collectionId
     * @param name
     * @param read
     * @param write
     * @param rules
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updateCollection(
		collectionId: String,
		name: String,
		read: List<Any>? = null,
		write: List<Any>? = null,
		rules: List<Any>? = null
	): Response {
        val path = "/database/collections/{collectionId}".replace("{collectionId}", collectionId)
        val params = mapOf<String, Any?>(
            "name" to name,
            "read" to read,
            "write" to write,
            "rules" to rules
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PUT", path, headers, params)
    }
    
    /**
     * Delete Collection
     *
     * Delete a collection by its unique ID. Only users with write permissions
     * have access to delete this resource.
     *
     * @param collectionId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun deleteCollection(
		collectionId: String
	): Response {
        val path = "/database/collections/{collectionId}".replace("{collectionId}", collectionId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("DELETE", path, headers, params)
    }
    
    /**
     * List Documents
     *
     * Get a list of all the user documents. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's documents. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param collectionId
     * @param filters
     * @param limit
     * @param offset
     * @param orderField
     * @param orderType
     * @param orderCast
     * @param search
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun listDocuments(
		collectionId: String,
		filters: List<Any>? = null,
		limit: Int? = null,
		offset: Int? = null,
		orderField: String? = null,
		orderType: String? = null,
		orderCast: String? = null,
		search: String? = null
	): Response {
        val path = "/database/collections/{collectionId}/documents".replace("{collectionId}", collectionId)
        val params = mapOf<String, Any?>(
            "filters" to filters,
            "limit" to limit,
            "offset" to offset,
            "orderField" to orderField,
            "orderType" to orderType,
            "orderCast" to orderCast,
            "search" to search
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Create Document
     *
     * Create a new Document. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](/docs/server/database#databaseCreateCollection) API or
     * directly from your database console.
     *
     * @param collectionId
     * @param data
     * @param read
     * @param write
     * @param parentDocument
     * @param parentProperty
     * @param parentPropertyType
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun createDocument(
		collectionId: String,
		data: Any,
		read: List<Any>? = null,
		write: List<Any>? = null,
		parentDocument: String? = null,
		parentProperty: String? = null,
		parentPropertyType: String? = null
	): Response {
        val path = "/database/collections/{collectionId}/documents".replace("{collectionId}", collectionId)
        val params = mapOf<String, Any?>(
            "data" to data,
            "read" to read,
            "write" to write,
            "parentDocument" to parentDocument,
            "parentProperty" to parentProperty,
            "parentPropertyType" to parentPropertyType
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("POST", path, headers, params)
    }
    
    /**
     * Get Document
     *
     * Get a document by its unique ID. This endpoint response returns a JSON
     * object with the document data.
     *
     * @param collectionId
     * @param documentId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getDocument(
		collectionId: String,
		documentId: String
	): Response {
        val path = "/database/collections/{collectionId}/documents/{documentId}".replace("{collectionId}", collectionId).replace("{documentId}", documentId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Update Document
     *
     * Update a document by its unique ID. Using the patch method you can pass
     * only specific fields that will get updated.
     *
     * @param collectionId
     * @param documentId
     * @param data
     * @param read
     * @param write
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updateDocument(
		collectionId: String,
		documentId: String,
		data: Any,
		read: List<Any>? = null,
		write: List<Any>? = null
	): Response {
        val path = "/database/collections/{collectionId}/documents/{documentId}".replace("{collectionId}", collectionId).replace("{documentId}", documentId)
        val params = mapOf<String, Any?>(
            "data" to data,
            "read" to read,
            "write" to write
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
    /**
     * Delete Document
     *
     * Delete a document by its unique ID. This endpoint deletes only the parent
     * documents, its attributes and relations to other documents. Child documents
     * **will not** be deleted.
     *
     * @param collectionId
     * @param documentId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun deleteDocument(
		collectionId: String,
		documentId: String
	): Response {
        val path = "/database/collections/{collectionId}/documents/{documentId}".replace("{collectionId}", collectionId).replace("{documentId}", documentId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("DELETE", path, headers, params)
    }
    
}
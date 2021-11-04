package io.appwrite.services

import io.appwrite.Client
import io.appwrite.exceptions.AppwriteException
import okhttp3.Cookie
import okhttp3.Response
import java.io.File

class Functions(private val client: Client) : BaseService(client) {

    /**
     * List Functions
     *
     * Get a list of all the project's functions. You can use the query params to
     * filter your results.
     *
     * @param search
     * @param limit
     * @param offset
     * @param orderType
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun list(
		search: String? = null,
		limit: Int? = null,
		offset: Int? = null,
		orderType: String? = null
	): Response {
        val path = "/functions"
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
     * Create Function
     *
     * Create a new function. You can pass a list of
     * [permissions](/docs/permissions) to allow different project users or team
     * with access to execute the function using the client API.
     *
     * @param name
     * @param execute
     * @param runtime
     * @param vars
     * @param events
     * @param schedule
     * @param timeout
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun create(
		name: String,
		execute: List<Any>,
		runtime: String,
		vars: Any? = null,
		events: List<Any>? = null,
		schedule: String? = null,
		timeout: Int? = null
	): Response {
        val path = "/functions"
        val params = mapOf<String, Any?>(
            "name" to name,
            "execute" to execute,
            "runtime" to runtime,
            "vars" to vars,
            "events" to events,
            "schedule" to schedule,
            "timeout" to timeout
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("POST", path, headers, params)
    }
    
    /**
     * Get Function
     *
     * Get a function by its unique ID.
     *
     * @param functionId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun get(
		functionId: String
	): Response {
        val path = "/functions/{functionId}".replace("{functionId}", functionId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Update Function
     *
     * Update function by its unique ID.
     *
     * @param functionId
     * @param name
     * @param execute
     * @param vars
     * @param events
     * @param schedule
     * @param timeout
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun update(
		functionId: String,
		name: String,
		execute: List<Any>,
		vars: Any? = null,
		events: List<Any>? = null,
		schedule: String? = null,
		timeout: Int? = null
	): Response {
        val path = "/functions/{functionId}".replace("{functionId}", functionId)
        val params = mapOf<String, Any?>(
            "name" to name,
            "execute" to execute,
            "vars" to vars,
            "events" to events,
            "schedule" to schedule,
            "timeout" to timeout
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PUT", path, headers, params)
    }
    
    /**
     * Delete Function
     *
     * Delete a function by its unique ID.
     *
     * @param functionId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun delete(
		functionId: String
	): Response {
        val path = "/functions/{functionId}".replace("{functionId}", functionId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("DELETE", path, headers, params)
    }
    
    /**
     * List Executions
     *
     * Get a list of all the current user function execution logs. You can use the
     * query params to filter your results. On admin mode, this endpoint will
     * return a list of all of the project's executions. [Learn more about
     * different API modes](/docs/admin).
     *
     * @param functionId
     * @param search
     * @param limit
     * @param offset
     * @param orderType
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun listExecutions(
		functionId: String,
		search: String? = null,
		limit: Int? = null,
		offset: Int? = null,
		orderType: String? = null
	): Response {
        val path = "/functions/{functionId}/executions".replace("{functionId}", functionId)
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
     * Create Execution
     *
     * Trigger a function execution. The returned object will return you the
     * current execution status. You can ping the `Get Execution` endpoint to get
     * updates on the current execution status. Once this endpoint is called, your
     * function execution process will start asynchronously.
     *
     * @param functionId
     * @param data
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun createExecution(
		functionId: String,
		data: String? = null
	): Response {
        val path = "/functions/{functionId}/executions".replace("{functionId}", functionId)
        val params = mapOf<String, Any?>(
            "data" to data
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("POST", path, headers, params)
    }
    
    /**
     * Get Execution
     *
     * Get a function execution log by its unique ID.
     *
     * @param functionId
     * @param executionId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getExecution(
		functionId: String,
		executionId: String
	): Response {
        val path = "/functions/{functionId}/executions/{executionId}".replace("{functionId}", functionId).replace("{executionId}", executionId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Update Function Tag
     *
     * Update the function code tag ID using the unique function ID. Use this
     * endpoint to switch the code tag that should be executed by the execution
     * endpoint.
     *
     * @param functionId
     * @param tag
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updateTag(
		functionId: String,
		tag: String
	): Response {
        val path = "/functions/{functionId}/tag".replace("{functionId}", functionId)
        val params = mapOf<String, Any?>(
            "tag" to tag
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
    /**
     * List Tags
     *
     * Get a list of all the project's code tags. You can use the query params to
     * filter your results.
     *
     * @param functionId
     * @param search
     * @param limit
     * @param offset
     * @param orderType
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun listTags(
		functionId: String,
		search: String? = null,
		limit: Int? = null,
		offset: Int? = null,
		orderType: String? = null
	): Response {
        val path = "/functions/{functionId}/tags".replace("{functionId}", functionId)
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
     * Create Tag
     *
     * Create a new function code tag. Use this endpoint to upload a new version
     * of your code function. To execute your newly uploaded code, you'll need to
     * update the function's tag to use your new tag UID.
     * 
     * This endpoint accepts a tar.gz file compressed with your code. Make sure to
     * include any dependencies your code has within the compressed file. You can
     * learn more about code packaging in the [Appwrite Cloud Functions
     * tutorial](/docs/functions).
     * 
     * Use the "command" param to set the entry point used to execute your code.
     *
     * @param functionId
     * @param command
     * @param code
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun createTag(
		functionId: String,
		command: String,
		code: File
	): Response {
        val path = "/functions/{functionId}/tags".replace("{functionId}", functionId)
        val params = mapOf<String, Any?>(
            "command" to command,
            "code" to code
        )

        val headers = mapOf(
            "content-type" to "multipart/form-data"
        )

        return client.call("POST", path, headers, params)
    }
    
    /**
     * Get Tag
     *
     * Get a code tag by its unique ID.
     *
     * @param functionId
     * @param tagId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getTag(
		functionId: String,
		tagId: String
	): Response {
        val path = "/functions/{functionId}/tags/{tagId}".replace("{functionId}", functionId).replace("{tagId}", tagId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Delete Tag
     *
     * Delete a code tag by its unique ID.
     *
     * @param functionId
     * @param tagId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun deleteTag(
		functionId: String,
		tagId: String
	): Response {
        val path = "/functions/{functionId}/tags/{tagId}".replace("{functionId}", functionId).replace("{tagId}", tagId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("DELETE", path, headers, params)
    }
    
}

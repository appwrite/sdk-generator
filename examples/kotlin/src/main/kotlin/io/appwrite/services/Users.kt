package io.appwrite.services

import io.appwrite.Client
import io.appwrite.exceptions.AppwriteException
import okhttp3.Cookie
import okhttp3.Response
import java.io.File

class Users(private val client: Client) : BaseService(client) {

    /**
     * List Users
     *
     * Get a list of all the project's users. You can use the query params to
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
        val path = "/users"
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
     * Create User
     *
     * Create a new user.
     *
     * @param email
     * @param password
     * @param name
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun create(
		email: String,
		password: String,
		name: String? = null
	): Response {
        val path = "/users"
        val params = mapOf<String, Any?>(
            "email" to email,
            "password" to password,
            "name" to name
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("POST", path, headers, params)
    }
    
    /**
     * Get User
     *
     * Get a user by its unique ID.
     *
     * @param userId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun get(
		userId: String
	): Response {
        val path = "/users/{userId}".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Delete User
     *
     * Delete a user by its unique ID.
     *
     * @param userId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun delete(
		userId: String
	): Response {
        val path = "/users/{userId}".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("DELETE", path, headers, params)
    }
    
    /**
     * Update Email
     *
     * Update the user email by its unique ID.
     *
     * @param userId
     * @param email
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updateEmail(
		userId: String,
		email: String
	): Response {
        val path = "/users/{userId}/email".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
            "email" to email
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
    /**
     * Get User Logs
     *
     * Get a user activity logs list by its unique ID.
     *
     * @param userId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getLogs(
		userId: String
	): Response {
        val path = "/users/{userId}/logs".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Update Name
     *
     * Update the user name by its unique ID.
     *
     * @param userId
     * @param name
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updateName(
		userId: String,
		name: String
	): Response {
        val path = "/users/{userId}/name".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
            "name" to name
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
    /**
     * Update Password
     *
     * Update the user password by its unique ID.
     *
     * @param userId
     * @param password
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updatePassword(
		userId: String,
		password: String
	): Response {
        val path = "/users/{userId}/password".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
            "password" to password
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
    /**
     * Get User Preferences
     *
     * Get the user preferences by its unique ID.
     *
     * @param userId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getPrefs(
		userId: String
	): Response {
        val path = "/users/{userId}/prefs".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Update User Preferences
     *
     * Update the user preferences by its unique ID. You can pass only the
     * specific settings you wish to update.
     *
     * @param userId
     * @param prefs
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updatePrefs(
		userId: String,
		prefs: Any
	): Response {
        val path = "/users/{userId}/prefs".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
            "prefs" to prefs
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
    /**
     * Get User Sessions
     *
     * Get the user sessions list by its unique ID.
     *
     * @param userId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getSessions(
		userId: String
	): Response {
        val path = "/users/{userId}/sessions".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Delete User Sessions
     *
     * Delete all user's sessions by using the user's unique ID.
     *
     * @param userId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun deleteSessions(
		userId: String
	): Response {
        val path = "/users/{userId}/sessions".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("DELETE", path, headers, params)
    }
    
    /**
     * Delete User Session
     *
     * Delete a user sessions by its unique ID.
     *
     * @param userId
     * @param sessionId
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun deleteSession(
		userId: String,
		sessionId: String
	): Response {
        val path = "/users/{userId}/sessions/{sessionId}".replace("{userId}", userId).replace("{sessionId}", sessionId)
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("DELETE", path, headers, params)
    }
    
    /**
     * Update User Status
     *
     * Update the user status by its unique ID.
     *
     * @param userId
     * @param status
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updateStatus(
		userId: String,
		status: Int
	): Response {
        val path = "/users/{userId}/status".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
            "status" to status
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
    /**
     * Update Email Verification
     *
     * Update the user email verification status by its unique ID.
     *
     * @param userId
     * @param emailVerification
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun updateVerification(
		userId: String,
		emailVerification: Boolean
	): Response {
        val path = "/users/{userId}/verification".replace("{userId}", userId)
        val params = mapOf<String, Any?>(
            "emailVerification" to emailVerification
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("PATCH", path, headers, params)
    }
    
}

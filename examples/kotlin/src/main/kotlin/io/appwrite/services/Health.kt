package io.appwrite.services

import io.appwrite.Client
import io.appwrite.exceptions.AppwriteException
import okhttp3.Cookie
import okhttp3.Response
import java.io.File

class Health(private val client: Client) : BaseService(client) {

    /**
     * Get HTTP
     *
     * Check the Appwrite HTTP server is up and responsive.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun get(): Response {
        val path = "/health"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Anti virus
     *
     * Check the Appwrite Anti Virus server is up and connection is successful.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getAntiVirus(): Response {
        val path = "/health/anti-virus"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Cache
     *
     * Check the Appwrite in-memory cache server is up and connection is
     * successful.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getCache(): Response {
        val path = "/health/cache"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get DB
     *
     * Check the Appwrite database server is up and connection is successful.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getDB(): Response {
        val path = "/health/db"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Certificates Queue
     *
     * Get the number of certificates that are waiting to be issued against
     * [Letsencrypt](https://letsencrypt.org/) in the Appwrite internal queue
     * server.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getQueueCertificates(): Response {
        val path = "/health/queue/certificates"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Functions Queue
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getQueueFunctions(): Response {
        val path = "/health/queue/functions"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Logs Queue
     *
     * Get the number of logs that are waiting to be processed in the Appwrite
     * internal queue server.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getQueueLogs(): Response {
        val path = "/health/queue/logs"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Tasks Queue
     *
     * Get the number of tasks that are waiting to be processed in the Appwrite
     * internal queue server.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getQueueTasks(): Response {
        val path = "/health/queue/tasks"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Usage Queue
     *
     * Get the number of usage stats that are waiting to be processed in the
     * Appwrite internal queue server.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getQueueUsage(): Response {
        val path = "/health/queue/usage"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Webhooks Queue
     *
     * Get the number of webhooks that are waiting to be processed in the Appwrite
     * internal queue server.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getQueueWebhooks(): Response {
        val path = "/health/queue/webhooks"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Local Storage
     *
     * Check the Appwrite local storage device is up and connection is successful.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getStorageLocal(): Response {
        val path = "/health/storage/local"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
    /**
     * Get Time
     *
     * Check the Appwrite server time is synced with Google remote NTP server. We
     * use this technology to smoothly handle leap seconds with no disruptive
     * events. The [Network Time
     * Protocol](https://en.wikipedia.org/wiki/Network_Time_Protocol) (NTP) is
     * used by hundreds of millions of computers and devices to synchronize their
     * clocks over the Internet. If your computer sets its own clock, it likely
     * uses NTP.
     *
     * @return [Response]     
     */
    @JvmOverloads
    @Throws(AppwriteException::class)
    suspend fun getTime(): Response {
        val path = "/health/time"
        val params = mapOf<String, Any?>(
        )

        val headers = mapOf(
            "content-type" to "application/json"
        )

        return client.call("GET", path, headers, params)
    }
    
}

import AsyncHTTPClient
import Foundation
import NIO
import AppwriteModels

open class Health: Service {
    ///
    /// Get HTTP
    ///
    /// Check the Appwrite HTTP server is up and responsive.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func get(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Anti virus
    ///
    /// Check the Appwrite Anti Virus server is up and connection is successful.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getAntiVirus(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/anti-virus"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Cache
    ///
    /// Check the Appwrite in-memory cache server is up and connection is
    /// successful.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getCache(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/cache"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get DB
    ///
    /// Check the Appwrite database server is up and connection is successful.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getDB(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/db"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Certificates Queue
    ///
    /// Get the number of certificates that are waiting to be issued against
    /// [Letsencrypt](https://letsencrypt.org/) in the Appwrite internal queue
    /// server.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getQueueCertificates(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/queue/certificates"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Functions Queue
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getQueueFunctions(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/queue/functions"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Logs Queue
    ///
    /// Get the number of logs that are waiting to be processed in the Appwrite
    /// internal queue server.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getQueueLogs(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/queue/logs"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Tasks Queue
    ///
    /// Get the number of tasks that are waiting to be processed in the Appwrite
    /// internal queue server.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getQueueTasks(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/queue/tasks"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Usage Queue
    ///
    /// Get the number of usage stats that are waiting to be processed in the
    /// Appwrite internal queue server.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getQueueUsage(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/queue/usage"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Webhooks Queue
    ///
    /// Get the number of webhooks that are waiting to be processed in the Appwrite
    /// internal queue server.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getQueueWebhooks(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/queue/webhooks"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Local Storage
    ///
    /// Check the Appwrite local storage device is up and connection is successful.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getStorageLocal(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/storage/local"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

    ///
    /// Get Time
    ///
    /// Check the Appwrite server time is synced with Google remote NTP server. We
    /// use this technology to smoothly handle leap seconds with no disruptive
    /// events. The [Network Time
    /// Protocol](https://en.wikipedia.org/wiki/Network_Time_Protocol) (NTP) is
    /// used by hundreds of millions of computers and devices to synchronize their
    /// clocks over the Internet. If your computer sets its own clock, it likely
    /// uses NTP.
    ///
    /// @throws Exception
    /// @return array
    ///
    open func getTime(
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/health/time"

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        client.call(
            method: "GET",
            path: path,
            headers: headers,
            params: params,
            completion: completion
        )
    }

}

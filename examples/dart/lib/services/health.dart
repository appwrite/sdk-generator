part of dart_appwrite;

class Health extends Service {
    Health(Client client): super(client);

     /// Get HTTP
     ///
     /// Check the Appwrite HTTP server is up and responsive.
     ///
    Future<Response> get() {
        final String path = '/health';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Anti virus
     ///
     /// Check the Appwrite Anti Virus server is up and connection is successful.
     ///
    Future<Response> getAntiVirus() {
        final String path = '/health/anti-virus';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Cache
     ///
     /// Check the Appwrite in-memory cache server is up and connection is
     /// successful.
     ///
    Future<Response> getCache() {
        final String path = '/health/cache';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get DB
     ///
     /// Check the Appwrite database server is up and connection is successful.
     ///
    Future<Response> getDB() {
        final String path = '/health/db';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Certificates Queue
     ///
     /// Get the number of certificates that are waiting to be issued against
     /// [Letsencrypt](https://letsencrypt.org/) in the Appwrite internal queue
     /// server.
     ///
    Future<Response> getQueueCertificates() {
        final String path = '/health/queue/certificates';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Functions Queue
    Future<Response> getQueueFunctions() {
        final String path = '/health/queue/functions';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Logs Queue
     ///
     /// Get the number of logs that are waiting to be processed in the Appwrite
     /// internal queue server.
     ///
    Future<Response> getQueueLogs() {
        final String path = '/health/queue/logs';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Tasks Queue
     ///
     /// Get the number of tasks that are waiting to be processed in the Appwrite
     /// internal queue server.
     ///
    Future<Response> getQueueTasks() {
        final String path = '/health/queue/tasks';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Usage Queue
     ///
     /// Get the number of usage stats that are waiting to be processed in the
     /// Appwrite internal queue server.
     ///
    Future<Response> getQueueUsage() {
        final String path = '/health/queue/usage';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Webhooks Queue
     ///
     /// Get the number of webhooks that are waiting to be processed in the Appwrite
     /// internal queue server.
     ///
    Future<Response> getQueueWebhooks() {
        final String path = '/health/queue/webhooks';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Get Local Storage
     ///
     /// Check the Appwrite local storage device is up and connection is successful.
     ///
    Future<Response> getStorageLocal() {
        final String path = '/health/storage/local';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

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
    Future<Response> getTime() {
        final String path = '/health/time';

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }
}
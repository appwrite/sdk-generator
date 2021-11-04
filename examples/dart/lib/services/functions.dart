part of dart_appwrite;

class Functions extends Service {
    Functions(Client client): super(client);

     /// List Functions
     ///
     /// Get a list of all the project's functions. You can use the query params to
     /// filter your results.
     ///
    Future<Response> list({String? search, int? limit, int? offset, String? orderType}) {
        final String path = '/functions';

        final Map<String, dynamic> params = {
            'search': search,
            'limit': limit,
            'offset': offset,
            'orderType': orderType,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Create Function
     ///
     /// Create a new function. You can pass a list of
     /// [permissions](/docs/permissions) to allow different project users or team
     /// with access to execute the function using the client API.
     ///
    Future<Response> create({required String name, required List execute, required String runtime, Map? vars, List? events, String? schedule, int? timeout}) {
        final String path = '/functions';

        final Map<String, dynamic> params = {
            'name': name,
            'execute': execute,
            'runtime': runtime,
            'vars': vars,
            'events': events,
            'schedule': schedule,
            'timeout': timeout,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.post, path: path, params: params, headers: headers);
    }

     /// Get Function
     ///
     /// Get a function by its unique ID.
     ///
    Future<Response> get({required String functionId}) {
        final String path = '/functions/{functionId}'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Update Function
     ///
     /// Update function by its unique ID.
     ///
    Future<Response> update({required String functionId, required String name, required List execute, Map? vars, List? events, String? schedule, int? timeout}) {
        final String path = '/functions/{functionId}'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
            'name': name,
            'execute': execute,
            'vars': vars,
            'events': events,
            'schedule': schedule,
            'timeout': timeout,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.put, path: path, params: params, headers: headers);
    }

     /// Delete Function
     ///
     /// Delete a function by its unique ID.
     ///
    Future<Response> delete({required String functionId}) {
        final String path = '/functions/{functionId}'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.delete, path: path, params: params, headers: headers);
    }

     /// List Executions
     ///
     /// Get a list of all the current user function execution logs. You can use the
     /// query params to filter your results. On admin mode, this endpoint will
     /// return a list of all of the project's executions. [Learn more about
     /// different API modes](/docs/admin).
     ///
    Future<Response> listExecutions({required String functionId, String? search, int? limit, int? offset, String? orderType}) {
        final String path = '/functions/{functionId}/executions'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
            'search': search,
            'limit': limit,
            'offset': offset,
            'orderType': orderType,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Create Execution
     ///
     /// Trigger a function execution. The returned object will return you the
     /// current execution status. You can ping the `Get Execution` endpoint to get
     /// updates on the current execution status. Once this endpoint is called, your
     /// function execution process will start asynchronously.
     ///
    Future<Response> createExecution({required String functionId, String? data}) {
        final String path = '/functions/{functionId}/executions'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
            'data': data,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.post, path: path, params: params, headers: headers);
    }

     /// Get Execution
     ///
     /// Get a function execution log by its unique ID.
     ///
    Future<Response> getExecution({required String functionId, required String executionId}) {
        final String path = '/functions/{functionId}/executions/{executionId}'.replaceAll(RegExp('{functionId}'), functionId).replaceAll(RegExp('{executionId}'), executionId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Update Function Tag
     ///
     /// Update the function code tag ID using the unique function ID. Use this
     /// endpoint to switch the code tag that should be executed by the execution
     /// endpoint.
     ///
    Future<Response> updateTag({required String functionId, required String tag}) {
        final String path = '/functions/{functionId}/tag'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
            'tag': tag,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.patch, path: path, params: params, headers: headers);
    }

     /// List Tags
     ///
     /// Get a list of all the project's code tags. You can use the query params to
     /// filter your results.
     ///
    Future<Response> listTags({required String functionId, String? search, int? limit, int? offset, String? orderType}) {
        final String path = '/functions/{functionId}/tags'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
            'search': search,
            'limit': limit,
            'offset': offset,
            'orderType': orderType,
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Create Tag
     ///
     /// Create a new function code tag. Use this endpoint to upload a new version
     /// of your code function. To execute your newly uploaded code, you'll need to
     /// update the function's tag to use your new tag UID.
     /// 
     /// This endpoint accepts a tar.gz file compressed with your code. Make sure to
     /// include any dependencies your code has within the compressed file. You can
     /// learn more about code packaging in the [Appwrite Cloud Functions
     /// tutorial](/docs/functions).
     /// 
     /// Use the "command" param to set the entry point used to execute your code.
     ///
    Future<Response> createTag({required String functionId, required String command, required http.MultipartFile code}) {
        final String path = '/functions/{functionId}/tags'.replaceAll(RegExp('{functionId}'), functionId);

        final Map<String, dynamic> params = {
            'command': command,
            'code': code,
        };

        final Map<String, String> headers = {
            'content-type': 'multipart/form-data',
        };

        return client.call(HttpMethod.post, path: path, params: params, headers: headers);
    }

     /// Get Tag
     ///
     /// Get a code tag by its unique ID.
     ///
    Future<Response> getTag({required String functionId, required String tagId}) {
        final String path = '/functions/{functionId}/tags/{tagId}'.replaceAll(RegExp('{functionId}'), functionId).replaceAll(RegExp('{tagId}'), tagId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.get, path: path, params: params, headers: headers);
    }

     /// Delete Tag
     ///
     /// Delete a code tag by its unique ID.
     ///
    Future<Response> deleteTag({required String functionId, required String tagId}) {
        final String path = '/functions/{functionId}/tags/{tagId}'.replaceAll(RegExp('{functionId}'), functionId).replaceAll(RegExp('{tagId}'), tagId);

        final Map<String, dynamic> params = {
        };

        final Map<String, String> headers = {
            'content-type': 'application/json',
        };

        return client.call(HttpMethod.delete, path: path, params: params, headers: headers);
    }
}
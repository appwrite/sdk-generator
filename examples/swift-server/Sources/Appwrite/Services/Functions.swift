import AsyncHTTPClient
import Foundation
import NIO
import AppwriteModels

open class Functions: Service {
    ///
    /// List Functions
    ///
    /// Get a list of all the project's functions. You can use the query params to
    /// filter your results.
    ///
    /// @param String search
    /// @param Int limit
    /// @param Int offset
    /// @param String orderType
    /// @throws Exception
    /// @return array
    ///
    open func list(
        search: String? = nil,
        limit: Int? = nil,
        offset: Int? = nil,
        orderType: String? = nil,
        completion: ((Result<AppwriteModels.FunctionList, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/functions"

        let params: [String: Any?] = [
            "search": search,
            "limit": limit,
            "offset": offset,
            "orderType": orderType
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.FunctionList = { dict in
            return AppwriteModels.FunctionList.from(map: dict)
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
    /// Create Function
    ///
    /// Create a new function. You can pass a list of
    /// [permissions](/docs/permissions) to allow different project users or team
    /// with access to execute the function using the client API.
    ///
    /// @param String name
    /// @param [Any] execute
    /// @param String runtime
    /// @param Any vars
    /// @param [Any] events
    /// @param String schedule
    /// @param Int timeout
    /// @throws Exception
    /// @return array
    ///
    open func create(
        name: String,
        execute: [Any],
        runtime: String,
        vars: Any? = nil,
        events: [Any]? = nil,
        schedule: String? = nil,
        timeout: Int? = nil,
        completion: ((Result<AppwriteModels.Function, AppwriteError>) -> Void)? = nil
    ) {
        let path: String = "/functions"

        let params: [String: Any?] = [
            "name": name,
            "execute": execute,
            "runtime": runtime,
            "vars": vars,
            "events": events,
            "schedule": schedule,
            "timeout": timeout
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Function = { dict in
            return AppwriteModels.Function.from(map: dict)
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
    /// Get Function
    ///
    /// Get a function by its unique ID.
    ///
    /// @param String functionId
    /// @throws Exception
    /// @return array
    ///
    open func get(
        functionId: String,
        completion: ((Result<AppwriteModels.Function, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Function = { dict in
            return AppwriteModels.Function.from(map: dict)
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
    /// Update Function
    ///
    /// Update function by its unique ID.
    ///
    /// @param String functionId
    /// @param String name
    /// @param [Any] execute
    /// @param Any vars
    /// @param [Any] events
    /// @param String schedule
    /// @param Int timeout
    /// @throws Exception
    /// @return array
    ///
    open func update(
        functionId: String,
        name: String,
        execute: [Any],
        vars: Any? = nil,
        events: [Any]? = nil,
        schedule: String? = nil,
        timeout: Int? = nil,
        completion: ((Result<AppwriteModels.Function, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        let params: [String: Any?] = [
            "name": name,
            "execute": execute,
            "vars": vars,
            "events": events,
            "schedule": schedule,
            "timeout": timeout
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Function = { dict in
            return AppwriteModels.Function.from(map: dict)
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
    /// Delete Function
    ///
    /// Delete a function by its unique ID.
    ///
    /// @param String functionId
    /// @throws Exception
    /// @return array
    ///
    open func delete(
        functionId: String,
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

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
    /// List Executions
    ///
    /// Get a list of all the current user function execution logs. You can use the
    /// query params to filter your results. On admin mode, this endpoint will
    /// return a list of all of the project's executions. [Learn more about
    /// different API modes](/docs/admin).
    ///
    /// @param String functionId
    /// @param String search
    /// @param Int limit
    /// @param Int offset
    /// @param String orderType
    /// @throws Exception
    /// @return array
    ///
    open func listExecutions(
        functionId: String,
        search: String? = nil,
        limit: Int? = nil,
        offset: Int? = nil,
        orderType: String? = nil,
        completion: ((Result<AppwriteModels.ExecutionList, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/executions"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        let params: [String: Any?] = [
            "search": search,
            "limit": limit,
            "offset": offset,
            "orderType": orderType
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.ExecutionList = { dict in
            return AppwriteModels.ExecutionList.from(map: dict)
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
    /// Create Execution
    ///
    /// Trigger a function execution. The returned object will return you the
    /// current execution status. You can ping the `Get Execution` endpoint to get
    /// updates on the current execution status. Once this endpoint is called, your
    /// function execution process will start asynchronously.
    ///
    /// @param String functionId
    /// @param String data
    /// @throws Exception
    /// @return array
    ///
    open func createExecution(
        functionId: String,
        data: String? = nil,
        completion: ((Result<AppwriteModels.Execution, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/executions"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        let params: [String: Any?] = [
            "data": data
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Execution = { dict in
            return AppwriteModels.Execution.from(map: dict)
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
    /// Get Execution
    ///
    /// Get a function execution log by its unique ID.
    ///
    /// @param String functionId
    /// @param String executionId
    /// @throws Exception
    /// @return array
    ///
    open func getExecution(
        functionId: String,
        executionId: String,
        completion: ((Result<AppwriteModels.Execution, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/executions/{executionId}"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        path = path.replacingOccurrences(
          of: "{executionId}",
          with: executionId        )

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Execution = { dict in
            return AppwriteModels.Execution.from(map: dict)
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
    /// Update Function Tag
    ///
    /// Update the function code tag ID using the unique function ID. Use this
    /// endpoint to switch the code tag that should be executed by the execution
    /// endpoint.
    ///
    /// @param String functionId
    /// @param String tag
    /// @throws Exception
    /// @return array
    ///
    open func updateTag(
        functionId: String,
        tag: String,
        completion: ((Result<AppwriteModels.Function, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/tag"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        let params: [String: Any?] = [
            "tag": tag
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Function = { dict in
            return AppwriteModels.Function.from(map: dict)
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
    /// List Tags
    ///
    /// Get a list of all the project's code tags. You can use the query params to
    /// filter your results.
    ///
    /// @param String functionId
    /// @param String search
    /// @param Int limit
    /// @param Int offset
    /// @param String orderType
    /// @throws Exception
    /// @return array
    ///
    open func listTags(
        functionId: String,
        search: String? = nil,
        limit: Int? = nil,
        offset: Int? = nil,
        orderType: String? = nil,
        completion: ((Result<AppwriteModels.TagList, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/tags"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        let params: [String: Any?] = [
            "search": search,
            "limit": limit,
            "offset": offset,
            "orderType": orderType
        ]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.TagList = { dict in
            return AppwriteModels.TagList.from(map: dict)
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
    /// @param String functionId
    /// @param String command
    /// @param File code
    /// @throws Exception
    /// @return array
    ///
    open func createTag(
        functionId: String,
        command: String,
        code: File,
        completion: ((Result<AppwriteModels.Tag, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/tags"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        let params: [String: Any?] = [
            "command": command,
            "code": code
        ]

        let headers: [String: String] = [
            "content-type": "multipart/form-data"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Tag = { dict in
            return AppwriteModels.Tag.from(map: dict)
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
    /// Get Tag
    ///
    /// Get a code tag by its unique ID.
    ///
    /// @param String functionId
    /// @param String tagId
    /// @throws Exception
    /// @return array
    ///
    open func getTag(
        functionId: String,
        tagId: String,
        completion: ((Result<AppwriteModels.Tag, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/tags/{tagId}"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        path = path.replacingOccurrences(
          of: "{tagId}",
          with: tagId        )

        let params: [String: Any?] = [:]

        let headers: [String: String] = [
            "content-type": "application/json"
        ]

        let convert: ([String: Any]) -> AppwriteModels.Tag = { dict in
            return AppwriteModels.Tag.from(map: dict)
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
    /// Delete Tag
    ///
    /// Delete a code tag by its unique ID.
    ///
    /// @param String functionId
    /// @param String tagId
    /// @throws Exception
    /// @return array
    ///
    open func deleteTag(
        functionId: String,
        tagId: String,
        completion: ((Result<Any, AppwriteError>) -> Void)? = nil
    ) {
        var path: String = "/functions/{functionId}/tags/{tagId}"

        path = path.replacingOccurrences(
          of: "{functionId}",
          with: functionId        )

        path = path.replacingOccurrences(
          of: "{tagId}",
          with: tagId        )

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

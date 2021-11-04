#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "users.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {

class Functions : public Service {
 public:
    /**
     * List Functions
     *
     * Get a list of all the project's functions. You can use the query params to
     * filter your results.
     *
     * @param string search
     * @param int limit
     * @param int offset
     * @param string orderType
     * @throws AppwriteException
     * @return array
     */
    json list(string* search = NULL, int* limit = NULL, int* offset = NULL, string* orderType = NULL) {
        map<string, string> params;
        string path = "/functions";
        size_t pos;
        string searchString;

        if (search != NULL) {
            params["search"] = *search;
        }

        if (limit != NULL) {
            params["limit"] = *limit;
        }

        if (offset != NULL) {
            params["offset"] = *offset;
        }

        if (orderType != NULL) {
            params["orderType"] = *orderType;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Create Function
     *
     * Create a new function. You can pass a list of
     * [permissions](/docs/permissions) to allow different project users or team
     * with access to execute the function using the client API.
     *
     * @param string name
     * @param array execute
     * @param string runtime
     * @param json vars
     * @param array events
     * @param string schedule
     * @param int timeout
     * @throws AppwriteException
     * @return array
     */
    json create(string* name, array* execute, string* runtime, json* vars = NULL, array* events = NULL, string* schedule = NULL, int* timeout = NULL) {
        if (!isset(name)) {
            throw new AppwriteException("Missing required parameter: 'name'");
        }

        if (!isset(execute)) {
            throw new AppwriteException("Missing required parameter: 'execute'");
        }

        if (!isset(runtime)) {
            throw new AppwriteException("Missing required parameter: 'runtime'");
        }

        map<string, string> params;
        string path = "/functions";
        size_t pos;
        string searchString;

        if (name != NULL) {
            params["name"] = *name;
        }

        if (execute != NULL) {
            params["execute"] = *execute;
        }

        if (runtime != NULL) {
            params["runtime"] = *runtime;
        }

        if (vars != NULL) {
            params["vars"] = *vars;
        }

        if (events != NULL) {
            params["events"] = *events;
        }

        if (schedule != NULL) {
            params["schedule"] = *schedule;
        }

        if (timeout != NULL) {
            params["timeout"] = *timeout;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get Function
     *
     * Get a function by its unique ID.
     *
     * @param string functionId
     * @throws AppwriteException
     * @return array
     */
    json get(string* functionId) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update Function
     *
     * Update function by its unique ID.
     *
     * @param string functionId
     * @param string name
     * @param array execute
     * @param json vars
     * @param array events
     * @param string schedule
     * @param int timeout
     * @throws AppwriteException
     * @return array
     */
    json update(string* functionId, string* name, array* execute, json* vars = NULL, array* events = NULL, string* schedule = NULL, int* timeout = NULL) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        if (!isset(name)) {
            throw new AppwriteException("Missing required parameter: 'name'");
        }

        if (!isset(execute)) {
            throw new AppwriteException("Missing required parameter: 'execute'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        if (name != NULL) {
            params["name"] = *name;
        }

        if (execute != NULL) {
            params["execute"] = *execute;
        }

        if (vars != NULL) {
            params["vars"] = *vars;
        }

        if (events != NULL) {
            params["events"] = *events;
        }

        if (schedule != NULL) {
            params["schedule"] = *schedule;
        }

        if (timeout != NULL) {
            params["timeout"] = *timeout;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PUT, path, headers, params);
    }

    /**
     * Delete Function
     *
     * Delete a function by its unique ID.
     *
     * @param string functionId
     * @throws AppwriteException
     * @return array
     */
    json delete(string* functionId) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * List Executions
     *
     * Get a list of all the current user function execution logs. You can use the
     * query params to filter your results. On admin mode, this endpoint will
     * return a list of all of the project's executions. [Learn more about
     * different API modes](/docs/admin).
     *
     * @param string functionId
     * @param string search
     * @param int limit
     * @param int offset
     * @param string orderType
     * @throws AppwriteException
     * @return array
     */
    json listExecutions(string* functionId, string* search = NULL, int* limit = NULL, int* offset = NULL, string* orderType = NULL) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/executions";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        if (search != NULL) {
            params["search"] = *search;
        }

        if (limit != NULL) {
            params["limit"] = *limit;
        }

        if (offset != NULL) {
            params["offset"] = *offset;
        }

        if (orderType != NULL) {
            params["orderType"] = *orderType;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Create Execution
     *
     * Trigger a function execution. The returned object will return you the
     * current execution status. You can ping the `Get Execution` endpoint to get
     * updates on the current execution status. Once this endpoint is called, your
     * function execution process will start asynchronously.
     *
     * @param string functionId
     * @param string data
     * @throws AppwriteException
     * @return array
     */
    json createExecution(string* functionId, string* data = NULL) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/executions";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        if (data != NULL) {
            params["data"] = *data;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get Execution
     *
     * Get a function execution log by its unique ID.
     *
     * @param string functionId
     * @param string executionId
     * @throws AppwriteException
     * @return array
     */
    json getExecution(string* functionId, string* executionId) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        if (!isset(executionId)) {
            throw new AppwriteException("Missing required parameter: 'executionId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/executions/{executionId}";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");
        searchString = "{executionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "executionId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update Function Tag
     *
     * Update the function code tag ID using the unique function ID. Use this
     * endpoint to switch the code tag that should be executed by the execution
     * endpoint.
     *
     * @param string functionId
     * @param string tag
     * @throws AppwriteException
     * @return array
     */
    json updateTag(string* functionId, string* tag) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        if (!isset(tag)) {
            throw new AppwriteException("Missing required parameter: 'tag'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/tag";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        if (tag != NULL) {
            params["tag"] = *tag;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PATCH, path, headers, params);
    }

    /**
     * List Tags
     *
     * Get a list of all the project's code tags. You can use the query params to
     * filter your results.
     *
     * @param string functionId
     * @param string search
     * @param int limit
     * @param int offset
     * @param string orderType
     * @throws AppwriteException
     * @return array
     */
    json listTags(string* functionId, string* search = NULL, int* limit = NULL, int* offset = NULL, string* orderType = NULL) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/tags";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        if (search != NULL) {
            params["search"] = *search;
        }

        if (limit != NULL) {
            params["limit"] = *limit;
        }

        if (offset != NULL) {
            params["offset"] = *offset;
        }

        if (orderType != NULL) {
            params["orderType"] = *orderType;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
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
     * @param string functionId
     * @param string command
     * @param file code
     * @throws AppwriteException
     * @return array
     */
    json createTag(string* functionId, string* command, file* code) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        if (!isset(command)) {
            throw new AppwriteException("Missing required parameter: 'command'");
        }

        if (!isset(code)) {
            throw new AppwriteException("Missing required parameter: 'code'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/tags";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");

        if (command != NULL) {
            params["command"] = *command;
        }

        if (code != NULL) {
            params["code"] = *code;
        }

        map<string, string> headers = {
            {"content-type", "multipart/form-data"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get Tag
     *
     * Get a code tag by its unique ID.
     *
     * @param string functionId
     * @param string tagId
     * @throws AppwriteException
     * @return array
     */
    json getTag(string* functionId, string* tagId) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        if (!isset(tagId)) {
            throw new AppwriteException("Missing required parameter: 'tagId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/tags/{tagId}";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");
        searchString = "{tagId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "tagId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Delete Tag
     *
     * Delete a code tag by its unique ID.
     *
     * @param string functionId
     * @param string tagId
     * @throws AppwriteException
     * @return array
     */
    json deleteTag(string* functionId, string* tagId) {
        if (!isset(functionId)) {
            throw new AppwriteException("Missing required parameter: 'functionId'");
        }

        if (!isset(tagId)) {
            throw new AppwriteException("Missing required parameter: 'tagId'");
        }

        map<string, string> params;
        string path = "/functions/{functionId}/tags/{tagId}";
        size_t pos;
        string searchString;
        searchString = "{functionId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "functionId");
        searchString = "{tagId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "tagId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }
};
}

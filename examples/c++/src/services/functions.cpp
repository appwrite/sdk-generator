#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "functions.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {
/*
 * List Functions
 *
     * Get a list of all the project's functions. You can use the query params to
     * filter your results.
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Functions::list(string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Create Function
 *
     * Create a new function. You can pass a list of
     * [permissions](/docs/permissions) to allow different project users or team
     * with access to execute the function using the client API.
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
json Functions::create(string name, array execute, string runtime, json vars, array events, string schedule, int timeout) {
    json params = {
        {"name", name},
        {"execute", execute},
        {"runtime", runtime},
        {"vars", vars},
        {"events", events},
        {"schedule", schedule},
        {"timeout", timeout},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get Function
 *
     * Get a function by its unique ID.
 * @param string functionId
 * @throws AppwriteException
 * @return array
 */
json Functions::get(string functionId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions/{functionId}";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update Function
 *
     * Update function by its unique ID.
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
json Functions::update(string functionId, string name, array execute, json vars, array events, string schedule, int timeout) {
    json params = {
        {"name", name},
        {"execute", execute},
        {"vars", vars},
        {"events", events},
        {"schedule", schedule},
        {"timeout", timeout},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions/{functionId}";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_PUT, path, headers, params);
}

/*
 * Delete Function
 *
     * Delete a function by its unique ID.
 * @param string functionId
 * @throws AppwriteException
 * @return array
 */
json Functions::delete(string functionId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions/{functionId}";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * List Executions
 *
     * Get a list of all the current user function execution logs. You can use the
     * query params to filter your results. On admin mode, this endpoint will
     * return a list of all of the project's executions. [Learn more about
     * different API modes](/docs/admin).
 * @param string functionId
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Functions::listExecutions(string functionId, string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions/{functionId}/executions";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Create Execution
 *
     * Trigger a function execution. The returned object will return you the
     * current execution status. You can ping the `Get Execution` endpoint to get
     * updates on the current execution status. Once this endpoint is called, your
     * function execution process will start asynchronously.
 * @param string functionId
 * @param string data
 * @throws AppwriteException
 * @return array
 */
json Functions::createExecution(string functionId, string data) {
    json params = {
        {"data", data},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions/{functionId}/executions";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get Execution
 *
     * Get a function execution log by its unique ID.
 * @param string functionId
 * @param string executionId
 * @throws AppwriteException
 * @return array
 */
json Functions::getExecution(string functionId, string executionId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
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

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update Function Tag
 *
     * Update the function code tag ID using the unique function ID. Use this
     * endpoint to switch the code tag that should be executed by the execution
     * endpoint.
 * @param string functionId
 * @param string tag
 * @throws AppwriteException
 * @return array
 */
json Functions::updateTag(string functionId, string tag) {
    json params = {
        {"tag", tag},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions/{functionId}/tag";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_PATCH, path, headers, params);
}

/*
 * List Tags
 *
     * Get a list of all the project's code tags. You can use the query params to
     * filter your results.
 * @param string functionId
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Functions::listTags(string functionId, string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/functions/{functionId}/tags";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
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
 * @param string functionId
 * @param string command
 * @param file code
 * @throws AppwriteException
 * @return array
 */
json Functions::createTag(string functionId, string command, file code) {
    json params = {
        {"command", command},
        {"code", code},
    };
    json headers = {
        {"content-type", "multipart/form-data"},
    };
    string path = "/functions/{functionId}/tags";
    size_t pos;
    string searchString;
    searchString = "{functionId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "functionId");

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get Tag
 *
     * Get a code tag by its unique ID.
 * @param string functionId
 * @param string tagId
 * @throws AppwriteException
 * @return array
 */
json Functions::getTag(string functionId, string tagId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
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

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Delete Tag
 *
     * Delete a code tag by its unique ID.
 * @param string functionId
 * @param string tagId
 * @throws AppwriteException
 * @return array
 */
json Functions::deleteTag(string functionId, string tagId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
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

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}
} // namespace Appwrite

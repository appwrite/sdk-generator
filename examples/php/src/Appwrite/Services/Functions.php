<?php

namespace Appwrite\Services;

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\Service;

class Functions extends Service
{
    /**
     * List Functions
     *
     * Get a list of all the project's functions. You can use the query params to
     * filter your results.
     *
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param string $orderType
     * @throws AppwriteException
     * @return array
     */
    public function list(string $search = null, int $limit = null, int $offset = null, string $orderType = null): array
    {
        $path   = str_replace([], [], '/functions');
        $params = [];

        if (!is_null($search)) {
            $params['search'] = $search;
        }

        if (!is_null($limit)) {
            $params['limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['offset'] = $offset;
        }

        if (!is_null($orderType)) {
            $params['orderType'] = $orderType;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Create Function
     *
     * Create a new function. You can pass a list of
     * [permissions](/docs/permissions) to allow different project users or team
     * with access to execute the function using the client API.
     *
     * @param string $name
     * @param array $execute
     * @param string $runtime
     * @param array $vars
     * @param array $events
     * @param string $schedule
     * @param int $timeout
     * @throws AppwriteException
     * @return array
     */
    public function create(string $name, array $execute, string $runtime, array $vars = null, array $events = null, string $schedule = null, int $timeout = null): array
    {
        if (!isset($name)) {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        if (!isset($execute)) {
            throw new AppwriteException('Missing required parameter: "execute"');
        }

        if (!isset($runtime)) {
            throw new AppwriteException('Missing required parameter: "runtime"');
        }

        $path   = str_replace([], [], '/functions');
        $params = [];

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        if (!is_null($execute)) {
            $params['execute'] = $execute;
        }

        if (!is_null($runtime)) {
            $params['runtime'] = $runtime;
        }

        if (!is_null($vars)) {
            $params['vars'] = $vars;
        }

        if (!is_null($events)) {
            $params['events'] = $events;
        }

        if (!is_null($schedule)) {
            $params['schedule'] = $schedule;
        }

        if (!is_null($timeout)) {
            $params['timeout'] = $timeout;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Function
     *
     * Get a function by its unique ID.
     *
     * @param string $functionId
     * @throws AppwriteException
     * @return array
     */
    public function get(string $functionId): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update Function
     *
     * Update function by its unique ID.
     *
     * @param string $functionId
     * @param string $name
     * @param array $execute
     * @param array $vars
     * @param array $events
     * @param string $schedule
     * @param int $timeout
     * @throws AppwriteException
     * @return array
     */
    public function update(string $functionId, string $name, array $execute, array $vars = null, array $events = null, string $schedule = null, int $timeout = null): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (!isset($name)) {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        if (!isset($execute)) {
            throw new AppwriteException('Missing required parameter: "execute"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}');
        $params = [];

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        if (!is_null($execute)) {
            $params['execute'] = $execute;
        }

        if (!is_null($vars)) {
            $params['vars'] = $vars;
        }

        if (!is_null($events)) {
            $params['events'] = $events;
        }

        if (!is_null($schedule)) {
            $params['schedule'] = $schedule;
        }

        if (!is_null($timeout)) {
            $params['timeout'] = $timeout;
        }

        return $this->client->call(Client::METHOD_PUT, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Delete Function
     *
     * Delete a function by its unique ID.
     *
     * @param string $functionId
     * @throws AppwriteException
     * @return array
     */
    public function delete(string $functionId): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}');
        $params = [];

        return $this->client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * List Executions
     *
     * Get a list of all the current user function execution logs. You can use the
     * query params to filter your results. On admin mode, this endpoint will
     * return a list of all of the project's executions. [Learn more about
     * different API modes](/docs/admin).
     *
     * @param string $functionId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param string $orderType
     * @throws AppwriteException
     * @return array
     */
    public function listExecutions(string $functionId, string $search = null, int $limit = null, int $offset = null, string $orderType = null): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}/executions');
        $params = [];

        if (!is_null($search)) {
            $params['search'] = $search;
        }

        if (!is_null($limit)) {
            $params['limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['offset'] = $offset;
        }

        if (!is_null($orderType)) {
            $params['orderType'] = $orderType;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Create Execution
     *
     * Trigger a function execution. The returned object will return you the
     * current execution status. You can ping the `Get Execution` endpoint to get
     * updates on the current execution status. Once this endpoint is called, your
     * function execution process will start asynchronously.
     *
     * @param string $functionId
     * @param string $data
     * @throws AppwriteException
     * @return array
     */
    public function createExecution(string $functionId, string $data = null): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}/executions');
        $params = [];

        if (!is_null($data)) {
            $params['data'] = $data;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Execution
     *
     * Get a function execution log by its unique ID.
     *
     * @param string $functionId
     * @param string $executionId
     * @throws AppwriteException
     * @return array
     */
    public function getExecution(string $functionId, string $executionId): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (!isset($executionId)) {
            throw new AppwriteException('Missing required parameter: "executionId"');
        }

        $path   = str_replace(['{functionId}', '{executionId}'], [$functionId, $executionId], '/functions/{functionId}/executions/{executionId}');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update Function Tag
     *
     * Update the function code tag ID using the unique function ID. Use this
     * endpoint to switch the code tag that should be executed by the execution
     * endpoint.
     *
     * @param string $functionId
     * @param string $tag
     * @throws AppwriteException
     * @return array
     */
    public function updateTag(string $functionId, string $tag): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (!isset($tag)) {
            throw new AppwriteException('Missing required parameter: "tag"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}/tag');
        $params = [];

        if (!is_null($tag)) {
            $params['tag'] = $tag;
        }

        return $this->client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * List Tags
     *
     * Get a list of all the project's code tags. You can use the query params to
     * filter your results.
     *
     * @param string $functionId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param string $orderType
     * @throws AppwriteException
     * @return array
     */
    public function listTags(string $functionId, string $search = null, int $limit = null, int $offset = null, string $orderType = null): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}/tags');
        $params = [];

        if (!is_null($search)) {
            $params['search'] = $search;
        }

        if (!is_null($limit)) {
            $params['limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['offset'] = $offset;
        }

        if (!is_null($orderType)) {
            $params['orderType'] = $orderType;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
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
     * @param string $functionId
     * @param string $command
     * @param \CurlFile $code
     * @throws AppwriteException
     * @return array
     */
    public function createTag(string $functionId, string $command, \CurlFile $code): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (!isset($command)) {
            throw new AppwriteException('Missing required parameter: "command"');
        }

        if (!isset($code)) {
            throw new AppwriteException('Missing required parameter: "code"');
        }

        $path   = str_replace(['{functionId}'], [$functionId], '/functions/{functionId}/tags');
        $params = [];

        if (!is_null($command)) {
            $params['command'] = $command;
        }

        if (!is_null($code)) {
            $params['code'] = $code;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'multipart/form-data',
        ], $params);
    }

    /**
     * Get Tag
     *
     * Get a code tag by its unique ID.
     *
     * @param string $functionId
     * @param string $tagId
     * @throws AppwriteException
     * @return array
     */
    public function getTag(string $functionId, string $tagId): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (!isset($tagId)) {
            throw new AppwriteException('Missing required parameter: "tagId"');
        }

        $path   = str_replace(['{functionId}', '{tagId}'], [$functionId, $tagId], '/functions/{functionId}/tags/{tagId}');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Delete Tag
     *
     * Delete a code tag by its unique ID.
     *
     * @param string $functionId
     * @param string $tagId
     * @throws AppwriteException
     * @return array
     */
    public function deleteTag(string $functionId, string $tagId): array
    {
        if (!isset($functionId)) {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (!isset($tagId)) {
            throw new AppwriteException('Missing required parameter: "tagId"');
        }

        $path   = str_replace(['{functionId}', '{tagId}'], [$functionId, $tagId], '/functions/{functionId}/tags/{tagId}');
        $params = [];

        return $this->client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
    }
}

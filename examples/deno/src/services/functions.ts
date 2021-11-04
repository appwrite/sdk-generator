import { Service } from '../service.ts';
import { Payload } from '../client.ts';
import { AppwriteException } from '../exception.ts';

export class Functions extends Service {

    /**
     * List Functions
     *
     * Get a list of all the project's functions. You can use the query params to
     * filter your results.
     *
     * @param {string} search
     * @param {number} limit
     * @param {number} offset
     * @param {string} orderType
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async list<T extends unknown>(search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> {
        let path = '/functions';
        let payload: Payload = {};

        if (typeof search !== 'undefined') {
            payload['search'] = search;
        }

        if (typeof limit !== 'undefined') {
            payload['limit'] = limit;
        }

        if (typeof offset !== 'undefined') {
            payload['offset'] = offset;
        }

        if (typeof orderType !== 'undefined') {
            payload['orderType'] = orderType;
        }

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Create Function
     *
     * Create a new function. You can pass a list of
     * [permissions](/docs/permissions) to allow different project users or team
     * with access to execute the function using the client API.
     *
     * @param {string} name
     * @param {string[]} execute
     * @param {string} runtime
     * @param {object} vars
     * @param {string[]} events
     * @param {string} schedule
     * @param {number} timeout
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async create<T extends unknown>(name: string, execute: string[], runtime: string, vars?: object, events?: string[], schedule?: string, timeout?: number): Promise<T> {
        if (typeof name === 'undefined') {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        if (typeof execute === 'undefined') {
            throw new AppwriteException('Missing required parameter: "execute"');
        }

        if (typeof runtime === 'undefined') {
            throw new AppwriteException('Missing required parameter: "runtime"');
        }

        let path = '/functions';
        let payload: Payload = {};

        if (typeof name !== 'undefined') {
            payload['name'] = name;
        }

        if (typeof execute !== 'undefined') {
            payload['execute'] = execute;
        }

        if (typeof runtime !== 'undefined') {
            payload['runtime'] = runtime;
        }

        if (typeof vars !== 'undefined') {
            payload['vars'] = vars;
        }

        if (typeof events !== 'undefined') {
            payload['events'] = events;
        }

        if (typeof schedule !== 'undefined') {
            payload['schedule'] = schedule;
        }

        if (typeof timeout !== 'undefined') {
            payload['timeout'] = timeout;
        }

        return await this.client.call('post', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Get Function
     *
     * Get a function by its unique ID.
     *
     * @param {string} functionId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async get<T extends unknown>(functionId: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        let path = '/functions/{functionId}'.replace('{functionId}', functionId);
        let payload: Payload = {};

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Update Function
     *
     * Update function by its unique ID.
     *
     * @param {string} functionId
     * @param {string} name
     * @param {string[]} execute
     * @param {object} vars
     * @param {string[]} events
     * @param {string} schedule
     * @param {number} timeout
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async update<T extends unknown>(functionId: string, name: string, execute: string[], vars?: object, events?: string[], schedule?: string, timeout?: number): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (typeof name === 'undefined') {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        if (typeof execute === 'undefined') {
            throw new AppwriteException('Missing required parameter: "execute"');
        }

        let path = '/functions/{functionId}'.replace('{functionId}', functionId);
        let payload: Payload = {};

        if (typeof name !== 'undefined') {
            payload['name'] = name;
        }

        if (typeof execute !== 'undefined') {
            payload['execute'] = execute;
        }

        if (typeof vars !== 'undefined') {
            payload['vars'] = vars;
        }

        if (typeof events !== 'undefined') {
            payload['events'] = events;
        }

        if (typeof schedule !== 'undefined') {
            payload['schedule'] = schedule;
        }

        if (typeof timeout !== 'undefined') {
            payload['timeout'] = timeout;
        }

        return await this.client.call('put', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Delete Function
     *
     * Delete a function by its unique ID.
     *
     * @param {string} functionId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async delete<T extends unknown>(functionId: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        let path = '/functions/{functionId}'.replace('{functionId}', functionId);
        let payload: Payload = {};

        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * List Executions
     *
     * Get a list of all the current user function execution logs. You can use the
     * query params to filter your results. On admin mode, this endpoint will
     * return a list of all of the project's executions. [Learn more about
     * different API modes](/docs/admin).
     *
     * @param {string} functionId
     * @param {string} search
     * @param {number} limit
     * @param {number} offset
     * @param {string} orderType
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async listExecutions<T extends unknown>(functionId: string, search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        let path = '/functions/{functionId}/executions'.replace('{functionId}', functionId);
        let payload: Payload = {};

        if (typeof search !== 'undefined') {
            payload['search'] = search;
        }

        if (typeof limit !== 'undefined') {
            payload['limit'] = limit;
        }

        if (typeof offset !== 'undefined') {
            payload['offset'] = offset;
        }

        if (typeof orderType !== 'undefined') {
            payload['orderType'] = orderType;
        }

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Create Execution
     *
     * Trigger a function execution. The returned object will return you the
     * current execution status. You can ping the `Get Execution` endpoint to get
     * updates on the current execution status. Once this endpoint is called, your
     * function execution process will start asynchronously.
     *
     * @param {string} functionId
     * @param {string} data
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async createExecution<T extends unknown>(functionId: string, data?: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        let path = '/functions/{functionId}/executions'.replace('{functionId}', functionId);
        let payload: Payload = {};

        if (typeof data !== 'undefined') {
            payload['data'] = data;
        }

        return await this.client.call('post', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Get Execution
     *
     * Get a function execution log by its unique ID.
     *
     * @param {string} functionId
     * @param {string} executionId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async getExecution<T extends unknown>(functionId: string, executionId: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (typeof executionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "executionId"');
        }

        let path = '/functions/{functionId}/executions/{executionId}'.replace('{functionId}', functionId).replace('{executionId}', executionId);
        let payload: Payload = {};

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Update Function Tag
     *
     * Update the function code tag ID using the unique function ID. Use this
     * endpoint to switch the code tag that should be executed by the execution
     * endpoint.
     *
     * @param {string} functionId
     * @param {string} tag
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async updateTag<T extends unknown>(functionId: string, tag: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (typeof tag === 'undefined') {
            throw new AppwriteException('Missing required parameter: "tag"');
        }

        let path = '/functions/{functionId}/tag'.replace('{functionId}', functionId);
        let payload: Payload = {};

        if (typeof tag !== 'undefined') {
            payload['tag'] = tag;
        }

        return await this.client.call('patch', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * List Tags
     *
     * Get a list of all the project's code tags. You can use the query params to
     * filter your results.
     *
     * @param {string} functionId
     * @param {string} search
     * @param {number} limit
     * @param {number} offset
     * @param {string} orderType
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async listTags<T extends unknown>(functionId: string, search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        let path = '/functions/{functionId}/tags'.replace('{functionId}', functionId);
        let payload: Payload = {};

        if (typeof search !== 'undefined') {
            payload['search'] = search;
        }

        if (typeof limit !== 'undefined') {
            payload['limit'] = limit;
        }

        if (typeof offset !== 'undefined') {
            payload['offset'] = offset;
        }

        if (typeof orderType !== 'undefined') {
            payload['orderType'] = orderType;
        }

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
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
     * @param {string} functionId
     * @param {string} command
     * @param {File | Blob} code
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async createTag<T extends unknown>(functionId: string, command: string, code: File | Blob): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (typeof command === 'undefined') {
            throw new AppwriteException('Missing required parameter: "command"');
        }

        if (typeof code === 'undefined') {
            throw new AppwriteException('Missing required parameter: "code"');
        }

        let path = '/functions/{functionId}/tags'.replace('{functionId}', functionId);
        let payload: Payload = {};

        if (typeof command !== 'undefined') {
            payload['command'] = command;
        }

        if (typeof code !== 'undefined') {
            payload['code'] = code;
        }

        return await this.client.call('post', path, {
                    'content-type': 'multipart/form-data',
               }, payload);
    }

    /**
     * Get Tag
     *
     * Get a code tag by its unique ID.
     *
     * @param {string} functionId
     * @param {string} tagId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async getTag<T extends unknown>(functionId: string, tagId: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (typeof tagId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "tagId"');
        }

        let path = '/functions/{functionId}/tags/{tagId}'.replace('{functionId}', functionId).replace('{tagId}', tagId);
        let payload: Payload = {};

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Delete Tag
     *
     * Delete a code tag by its unique ID.
     *
     * @param {string} functionId
     * @param {string} tagId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async deleteTag<T extends unknown>(functionId: string, tagId: string): Promise<T> {
        if (typeof functionId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "functionId"');
        }

        if (typeof tagId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "tagId"');
        }

        let path = '/functions/{functionId}/tags/{tagId}'.replace('{functionId}', functionId).replace('{tagId}', tagId);
        let payload: Payload = {};

        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               }, payload);
    }
}
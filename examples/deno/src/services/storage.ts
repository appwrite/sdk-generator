import { Service } from '../service.ts';
import { Payload } from '../client.ts';
import { AppwriteException } from '../exception.ts';

export class Storage extends Service {

    /**
     * List Files
     *
     * Get a list of all the user files. You can use the query params to filter
     * your results. On admin mode, this endpoint will return a list of all of the
     * project's files. [Learn more about different API modes](/docs/admin).
     *
     * @param {string} search
     * @param {number} limit
     * @param {number} offset
     * @param {string} orderType
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async listFiles<T extends unknown>(search?: string, limit?: number, offset?: number, orderType?: string): Promise<T> {
        let path = '/storage/files';
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
     * Create File
     *
     * Create a new file. The user who creates the file will automatically be
     * assigned to read and write access unless he has passed custom values for
     * read and write arguments.
     *
     * @param {File | Blob} file
     * @param {string[]} read
     * @param {string[]} write
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async createFile<T extends unknown>(file: File | Blob, read?: string[], write?: string[]): Promise<T> {
        if (typeof file === 'undefined') {
            throw new AppwriteException('Missing required parameter: "file"');
        }

        let path = '/storage/files';
        let payload: Payload = {};

        if (typeof file !== 'undefined') {
            payload['file'] = file;
        }

        if (typeof read !== 'undefined') {
            payload['read'] = read;
        }

        if (typeof write !== 'undefined') {
            payload['write'] = write;
        }

        return await this.client.call('post', path, {
                    'content-type': 'multipart/form-data',
               }, payload);
    }

    /**
     * Get File
     *
     * Get a file by its unique ID. This endpoint response returns a JSON object
     * with the file metadata.
     *
     * @param {string} fileId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async getFile<T extends unknown>(fileId: string): Promise<T> {
        if (typeof fileId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        let path = '/storage/files/{fileId}'.replace('{fileId}', fileId);
        let payload: Payload = {};

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Update File
     *
     * Update a file by its unique ID. Only users with write permissions have
     * access to update this resource.
     *
     * @param {string} fileId
     * @param {string[]} read
     * @param {string[]} write
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async updateFile<T extends unknown>(fileId: string, read: string[], write: string[]): Promise<T> {
        if (typeof fileId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        if (typeof read === 'undefined') {
            throw new AppwriteException('Missing required parameter: "read"');
        }

        if (typeof write === 'undefined') {
            throw new AppwriteException('Missing required parameter: "write"');
        }

        let path = '/storage/files/{fileId}'.replace('{fileId}', fileId);
        let payload: Payload = {};

        if (typeof read !== 'undefined') {
            payload['read'] = read;
        }

        if (typeof write !== 'undefined') {
            payload['write'] = write;
        }

        return await this.client.call('put', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Delete File
     *
     * Delete a file by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param {string} fileId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async deleteFile<T extends unknown>(fileId: string): Promise<T> {
        if (typeof fileId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        let path = '/storage/files/{fileId}'.replace('{fileId}', fileId);
        let payload: Payload = {};

        return await this.client.call('delete', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Get File for Download
     *
     * Get a file content by its unique ID. The endpoint response return with a
     * 'Content-Disposition: attachment' header that tells the browser to start
     * downloading the file to user downloads directory.
     *
     * @param {string} fileId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async getFileDownload(fileId: string): Promise<Response> {
        if (typeof fileId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        let path = '/storage/files/{fileId}/download'.replace('{fileId}', fileId);
        let payload: Payload = {};

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Get File Preview
     *
     * Get a file preview image. Currently, this method supports preview for image
     * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
     * and spreadsheets, will return the file icon image. You can also pass query
     * string arguments for cutting and resizing your preview image.
     *
     * @param {string} fileId
     * @param {number} width
     * @param {number} height
     * @param {string} gravity
     * @param {number} quality
     * @param {number} borderWidth
     * @param {string} borderColor
     * @param {number} borderRadius
     * @param {number} opacity
     * @param {number} rotation
     * @param {string} background
     * @param {string} output
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async getFilePreview(fileId: string, width?: number, height?: number, gravity?: string, quality?: number, borderWidth?: number, borderColor?: string, borderRadius?: number, opacity?: number, rotation?: number, background?: string, output?: string): Promise<Response> {
        if (typeof fileId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        let path = '/storage/files/{fileId}/preview'.replace('{fileId}', fileId);
        let payload: Payload = {};

        if (typeof width !== 'undefined') {
            payload['width'] = width;
        }

        if (typeof height !== 'undefined') {
            payload['height'] = height;
        }

        if (typeof gravity !== 'undefined') {
            payload['gravity'] = gravity;
        }

        if (typeof quality !== 'undefined') {
            payload['quality'] = quality;
        }

        if (typeof borderWidth !== 'undefined') {
            payload['borderWidth'] = borderWidth;
        }

        if (typeof borderColor !== 'undefined') {
            payload['borderColor'] = borderColor;
        }

        if (typeof borderRadius !== 'undefined') {
            payload['borderRadius'] = borderRadius;
        }

        if (typeof opacity !== 'undefined') {
            payload['opacity'] = opacity;
        }

        if (typeof rotation !== 'undefined') {
            payload['rotation'] = rotation;
        }

        if (typeof background !== 'undefined') {
            payload['background'] = background;
        }

        if (typeof output !== 'undefined') {
            payload['output'] = output;
        }

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }

    /**
     * Get File for View
     *
     * Get a file content by its unique ID. This endpoint is similar to the
     * download method but returns with no  'Content-Disposition: attachment'
     * header.
     *
     * @param {string} fileId
     * @throws {AppwriteException}
     * @returns {Promise}
     */
    async getFileView(fileId: string): Promise<Response> {
        if (typeof fileId === 'undefined') {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        let path = '/storage/files/{fileId}/view'.replace('{fileId}', fileId);
        let payload: Payload = {};

        return await this.client.call('get', path, {
                    'content-type': 'application/json',
               }, payload);
    }
}
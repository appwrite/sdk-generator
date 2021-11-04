<?php

namespace Appwrite\Services;

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\Service;

class Storage extends Service
{
    /**
     * List Files
     *
     * Get a list of all the user files. You can use the query params to filter
     * your results. On admin mode, this endpoint will return a list of all of the
     * project's files. [Learn more about different API modes](/docs/admin).
     *
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param string $orderType
     * @throws AppwriteException
     * @return array
     */
    public function listFiles(string $search = null, int $limit = null, int $offset = null, string $orderType = null): array
    {
        $path   = str_replace([], [], '/storage/files');
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
     * Create File
     *
     * Create a new file. The user who creates the file will automatically be
     * assigned to read and write access unless he has passed custom values for
     * read and write arguments.
     *
     * @param \CurlFile $file
     * @param array $read
     * @param array $write
     * @throws AppwriteException
     * @return array
     */
    public function createFile(\CurlFile $file, array $read = null, array $write = null): array
    {
        if (!isset($file)) {
            throw new AppwriteException('Missing required parameter: "file"');
        }

        $path   = str_replace([], [], '/storage/files');
        $params = [];

        if (!is_null($file)) {
            $params['file'] = $file;
        }

        if (!is_null($read)) {
            $params['read'] = $read;
        }

        if (!is_null($write)) {
            $params['write'] = $write;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'multipart/form-data',
        ], $params);
    }

    /**
     * Get File
     *
     * Get a file by its unique ID. This endpoint response returns a JSON object
     * with the file metadata.
     *
     * @param string $fileId
     * @throws AppwriteException
     * @return array
     */
    public function getFile(string $fileId): array
    {
        if (!isset($fileId)) {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update File
     *
     * Update a file by its unique ID. Only users with write permissions have
     * access to update this resource.
     *
     * @param string $fileId
     * @param array $read
     * @param array $write
     * @throws AppwriteException
     * @return array
     */
    public function updateFile(string $fileId, array $read, array $write): array
    {
        if (!isset($fileId)) {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        if (!isset($read)) {
            throw new AppwriteException('Missing required parameter: "read"');
        }

        if (!isset($write)) {
            throw new AppwriteException('Missing required parameter: "write"');
        }

        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}');
        $params = [];

        if (!is_null($read)) {
            $params['read'] = $read;
        }

        if (!is_null($write)) {
            $params['write'] = $write;
        }

        return $this->client->call(Client::METHOD_PUT, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Delete File
     *
     * Delete a file by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param string $fileId
     * @throws AppwriteException
     * @return array
     */
    public function deleteFile(string $fileId): array
    {
        if (!isset($fileId)) {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}');
        $params = [];

        return $this->client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get File for Download
     *
     * Get a file content by its unique ID. The endpoint response return with a
     * 'Content-Disposition: attachment' header that tells the browser to start
     * downloading the file to user downloads directory.
     *
     * @param string $fileId
     * @throws AppwriteException
     * @return string
     */
    public function getFileDownload(string $fileId): string
    {
        if (!isset($fileId)) {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}/download');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get File Preview
     *
     * Get a file preview image. Currently, this method supports preview for image
     * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
     * and spreadsheets, will return the file icon image. You can also pass query
     * string arguments for cutting and resizing your preview image.
     *
     * @param string $fileId
     * @param int $width
     * @param int $height
     * @param string $gravity
     * @param int $quality
     * @param int $borderWidth
     * @param string $borderColor
     * @param int $borderRadius
     * @param int $opacity
     * @param int $rotation
     * @param string $background
     * @param string $output
     * @throws AppwriteException
     * @return string
     */
    public function getFilePreview(string $fileId, int $width = null, int $height = null, string $gravity = null, int $quality = null, int $borderWidth = null, string $borderColor = null, int $borderRadius = null, int $opacity = null, int $rotation = null, string $background = null, string $output = null): string
    {
        if (!isset($fileId)) {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}/preview');
        $params = [];

        if (!is_null($width)) {
            $params['width'] = $width;
        }

        if (!is_null($height)) {
            $params['height'] = $height;
        }

        if (!is_null($gravity)) {
            $params['gravity'] = $gravity;
        }

        if (!is_null($quality)) {
            $params['quality'] = $quality;
        }

        if (!is_null($borderWidth)) {
            $params['borderWidth'] = $borderWidth;
        }

        if (!is_null($borderColor)) {
            $params['borderColor'] = $borderColor;
        }

        if (!is_null($borderRadius)) {
            $params['borderRadius'] = $borderRadius;
        }

        if (!is_null($opacity)) {
            $params['opacity'] = $opacity;
        }

        if (!is_null($rotation)) {
            $params['rotation'] = $rotation;
        }

        if (!is_null($background)) {
            $params['background'] = $background;
        }

        if (!is_null($output)) {
            $params['output'] = $output;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get File for View
     *
     * Get a file content by its unique ID. This endpoint is similar to the
     * download method but returns with no  'Content-Disposition: attachment'
     * header.
     *
     * @param string $fileId
     * @throws AppwriteException
     * @return string
     */
    public function getFileView(string $fileId): string
    {
        if (!isset($fileId)) {
            throw new AppwriteException('Missing required parameter: "fileId"');
        }

        $path   = str_replace(['{fileId}'], [$fileId], '/storage/files/{fileId}/view');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }
}

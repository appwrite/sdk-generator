

class Storage: Service
{
    /**
     * List Files
     *
     * Get a list of all the user files. You can use the query params to filter
     * your results. On admin mode, this endpoint will return a list of all of the
     * project files. [Learn more about different API modes](/docs/admin).
     *
     * @param String search
     * @param Int limit
     * @param Int offset
     * @param String orderType
     * @throws Exception
     * @return array
     */

    func listFiles(search: String = "", limit: Int = 25, offset: Int = 0, orderType: String = "ASC")-> Array<Any> {
        let path: String = "/storage/files"


                var params: [String: Any] = [:]
        
        params["search"] = search
        params["limit"] = limit
        params["offset"] = offset
        params["orderType"] = orderType

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create File
     *
     * Create a new file. The user who creates the file will automatically be
     * assigned to read and write access unless he has passed custom values for
     * read and write arguments.
     *
     * @param Array<Any> files
     * @param Array<Any> read
     * @param Array<Any> write
     * @throws Exception
     * @return array
     */

    func createFile(files: Array<Any>, read: Array<Any>, write: Array<Any>)-> Array<Any> {
        let path: String = "/storage/files"


                var params: [String: Any] = [:]
        
        params["files"] = files
        params["read"] = read
        params["write"] = write

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "multipart/form-data",
        ], params: params)];
    }

    /**
     * Get File
     *
     * Get file by its unique ID. This endpoint response returns a JSON object
     * with the file metadata.
     *
     * @param String fileId
     * @throws Exception
     * @return array
     */

    func getFile(fileId: String)-> Array<Any> {
        var path: String = "/storage/files/{fileId}"

        path = path.replacingOccurrences(
          of: "{fileId}",
          with: fileId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update File
     *
     * Update file by its unique ID. Only users with write permissions have access
     * to update this resource.
     *
     * @param String fileId
     * @param Array<Any> read
     * @param Array<Any> write
     * @throws Exception
     * @return array
     */

    func updateFile(fileId: String, read: Array<Any>, write: Array<Any>)-> Array<Any> {
        var path: String = "/storage/files/{fileId}"

        path = path.replacingOccurrences(
          of: "{fileId}",
          with: fileId
        )

                var params: [String: Any] = [:]
        
        params["read"] = read
        params["write"] = write

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete File
     *
     * Delete a file by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param String fileId
     * @throws Exception
     * @return array
     */

    func deleteFile(fileId: String)-> Array<Any> {
        var path: String = "/storage/files/{fileId}"

        path = path.replacingOccurrences(
          of: "{fileId}",
          with: fileId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get File for Download
     *
     * Get file content by its unique ID. The endpoint response return with a
     * 'Content-Disposition: attachment' header that tells the browser to start
     * downloading the file to user downloads directory.
     *
     * @param String fileId
     * @throws Exception
     * @return array
     */

    func getFileDownload(fileId: String)-> Array<Any> {
        var path: String = "/storage/files/{fileId}/download"

        path = path.replacingOccurrences(
          of: "{fileId}",
          with: fileId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get File Preview
     *
     * Get file preview image. Currently, this method supports preview for image
     * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
     * and spreadsheets will return file icon image. You can also pass query
     * string arguments for cutting and resizing your preview image.
     *
     * @param String fileId
     * @param Int width
     * @param Int height
     * @param Int quality
     * @param String background
     * @param String output
     * @throws Exception
     * @return array
     */

    func getFilePreview(fileId: String, width: Int = 0, height: Int = 0, quality: Int = 100, background: String = "", output: String = "")-> Array<Any> {
        var path: String = "/storage/files/{fileId}/preview"

        path = path.replacingOccurrences(
          of: "{fileId}",
          with: fileId
        )

                var params: [String: Any] = [:]
        
        params["width"] = width
        params["height"] = height
        params["quality"] = quality
        params["background"] = background
        params["output"] = output

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get File for View
     *
     * Get file content by its unique ID. This endpoint is similar to the download
     * method but returns with no  'Content-Disposition: attachment' header.
     *
     * @param String fileId
     * @param String as
     * @throws Exception
     * @return array
     */

    func getFileView(fileId: String, as: String = "")-> Array<Any> {
        var path: String = "/storage/files/{fileId}/view"

        path = path.replacingOccurrences(
          of: "{fileId}",
          with: fileId
        )

                var params: [String: Any] = [:]
        
        params["as"] = as

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}

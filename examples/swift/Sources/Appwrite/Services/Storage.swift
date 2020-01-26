

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

    func listFiles(search: String = null, limit: Int = 25, offset: Int = 0, orderType: String = 'ASC')-> Array<Any> {
        let methodPath = "/storage/files"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["search"] = search
        params["limit"] = limit
        params["offset"] = offset
        params["orderType"] = orderType

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create File
     *
     * Create a new file. The user who creates the file will automatically be
     * assigned to read and write access unless he has passed custom values for
     * read and write arguments.
     *
     * @param File files
     * @param Array read
     * @param Array write
     * @throws Exception
     * @return array
     */

    func createFile(files: File, read: Array, write: Array)-> Array<Any> {
        let methodPath = "/storage/files"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["files"] = files
        params["read"] = read
        params["write"] = write

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "multipart/form-data",
        ], params);
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
        let methodPath = "/storage/files/{fileId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{fileId}']",
          with: "$fileId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Update File
     *
     * Update file by its unique ID. Only users with write permissions have access
     * to update this resource.
     *
     * @param String fileId
     * @param Array read
     * @param Array write
     * @throws Exception
     * @return array
     */

    func updateFile(fileId: String, read: Array, write: Array)-> Array<Any> {
        let methodPath = "/storage/files/{fileId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{fileId}']",
          with: "$fileId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["read"] = read
        params["write"] = write

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/storage/files/{fileId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{fileId}']",
          with: "$fileId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/storage/files/{fileId}/download"
        let path = methodPath.replacingOccurrences(
          of: "['//{fileId}']",
          with: "$fileId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func getFilePreview(fileId: String, width: Int = 0, height: Int = 0, quality: Int = 100, background: String = null, output: String = null)-> Array<Any> {
        let methodPath = "/storage/files/{fileId}/preview"
        let path = methodPath.replacingOccurrences(
          of: "['//{fileId}']",
          with: "$fileId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["width"] = width
        params["height"] = height
        params["quality"] = quality
        params["background"] = background
        params["output"] = output

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func getFileView(fileId: String, as: String = null)-> Array<Any> {
        let methodPath = "/storage/files/{fileId}/view"
        let path = methodPath.replacingOccurrences(
          of: "['//{fileId}']",
          with: "$fileId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["as"] = as

        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

}

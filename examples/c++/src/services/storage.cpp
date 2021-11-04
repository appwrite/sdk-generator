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

class Storage : public Service {
 public:
    /**
     * List Files
     *
     * Get a list of all the user files. You can use the query params to filter
     * your results. On admin mode, this endpoint will return a list of all of the
     * project's files. [Learn more about different API modes](/docs/admin).
     *
     * @param string search
     * @param int limit
     * @param int offset
     * @param string orderType
     * @throws AppwriteException
     * @return array
     */
    json listFiles(string* search = NULL, int* limit = NULL, int* offset = NULL, string* orderType = NULL) {
        map<string, string> params;
        string path = "/storage/files";
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
     * Create File
     *
     * Create a new file. The user who creates the file will automatically be
     * assigned to read and write access unless he has passed custom values for
     * read and write arguments.
     *
     * @param file file
     * @param array read
     * @param array write
     * @throws AppwriteException
     * @return array
     */
    json createFile(file* file, array* read = NULL, array* write = NULL) {
        if (!isset(file)) {
            throw new AppwriteException("Missing required parameter: 'file'");
        }

        map<string, string> params;
        string path = "/storage/files";
        size_t pos;
        string searchString;

        if (file != NULL) {
            params["file"] = *file;
        }

        if (read != NULL) {
            params["read"] = *read;
        }

        if (write != NULL) {
            params["write"] = *write;
        }

        map<string, string> headers = {
            {"content-type", "multipart/form-data"},
        };

        return this->client->call(Client::METHOD_POST, path, headers, params);
    }

    /**
     * Get File
     *
     * Get a file by its unique ID. This endpoint response returns a JSON object
     * with the file metadata.
     *
     * @param string fileId
     * @throws AppwriteException
     * @return array
     */
    json getFile(string* fileId) {
        if (!isset(fileId)) {
            throw new AppwriteException("Missing required parameter: 'fileId'");
        }

        map<string, string> params;
        string path = "/storage/files/{fileId}";
        size_t pos;
        string searchString;
        searchString = "{fileId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "fileId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Update File
     *
     * Update a file by its unique ID. Only users with write permissions have
     * access to update this resource.
     *
     * @param string fileId
     * @param array read
     * @param array write
     * @throws AppwriteException
     * @return array
     */
    json updateFile(string* fileId, array* read, array* write) {
        if (!isset(fileId)) {
            throw new AppwriteException("Missing required parameter: 'fileId'");
        }

        if (!isset(read)) {
            throw new AppwriteException("Missing required parameter: 'read'");
        }

        if (!isset(write)) {
            throw new AppwriteException("Missing required parameter: 'write'");
        }

        map<string, string> params;
        string path = "/storage/files/{fileId}";
        size_t pos;
        string searchString;
        searchString = "{fileId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "fileId");

        if (read != NULL) {
            params["read"] = *read;
        }

        if (write != NULL) {
            params["write"] = *write;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_PUT, path, headers, params);
    }

    /**
     * Delete File
     *
     * Delete a file by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param string fileId
     * @throws AppwriteException
     * @return array
     */
    json deleteFile(string* fileId) {
        if (!isset(fileId)) {
            throw new AppwriteException("Missing required parameter: 'fileId'");
        }

        map<string, string> params;
        string path = "/storage/files/{fileId}";
        size_t pos;
        string searchString;
        searchString = "{fileId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "fileId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_DELETE, path, headers, params);
    }

    /**
     * Get File for Download
     *
     * Get a file content by its unique ID. The endpoint response return with a
     * 'Content-Disposition: attachment' header that tells the browser to start
     * downloading the file to user downloads directory.
     *
     * @param string fileId
     * @throws AppwriteException
     * @return string
     */
    string getFileDownload(string* fileId) {
        if (!isset(fileId)) {
            throw new AppwriteException("Missing required parameter: 'fileId'");
        }

        map<string, string> params;
        string path = "/storage/files/{fileId}/download";
        size_t pos;
        string searchString;
        searchString = "{fileId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "fileId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get File Preview
     *
     * Get a file preview image. Currently, this method supports preview for image
     * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
     * and spreadsheets, will return the file icon image. You can also pass query
     * string arguments for cutting and resizing your preview image.
     *
     * @param string fileId
     * @param int width
     * @param int height
     * @param string gravity
     * @param int quality
     * @param int borderWidth
     * @param string borderColor
     * @param int borderRadius
     * @param double opacity
     * @param int rotation
     * @param string background
     * @param string output
     * @throws AppwriteException
     * @return string
     */
    string getFilePreview(string* fileId, int* width = NULL, int* height = NULL, string* gravity = NULL, int* quality = NULL, int* borderWidth = NULL, string* borderColor = NULL, int* borderRadius = NULL, double* opacity = NULL, int* rotation = NULL, string* background = NULL, string* output = NULL) {
        if (!isset(fileId)) {
            throw new AppwriteException("Missing required parameter: 'fileId'");
        }

        map<string, string> params;
        string path = "/storage/files/{fileId}/preview";
        size_t pos;
        string searchString;
        searchString = "{fileId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "fileId");

        if (width != NULL) {
            params["width"] = *width;
        }

        if (height != NULL) {
            params["height"] = *height;
        }

        if (gravity != NULL) {
            params["gravity"] = *gravity;
        }

        if (quality != NULL) {
            params["quality"] = *quality;
        }

        if (borderWidth != NULL) {
            params["borderWidth"] = *borderWidth;
        }

        if (borderColor != NULL) {
            params["borderColor"] = *borderColor;
        }

        if (borderRadius != NULL) {
            params["borderRadius"] = *borderRadius;
        }

        if (opacity != NULL) {
            params["opacity"] = *opacity;
        }

        if (rotation != NULL) {
            params["rotation"] = *rotation;
        }

        if (background != NULL) {
            params["background"] = *background;
        }

        if (output != NULL) {
            params["output"] = *output;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get File for View
     *
     * Get a file content by its unique ID. This endpoint is similar to the
     * download method but returns with no  'Content-Disposition: attachment'
     * header.
     *
     * @param string fileId
     * @throws AppwriteException
     * @return string
     */
    string getFileView(string* fileId) {
        if (!isset(fileId)) {
            throw new AppwriteException("Missing required parameter: 'fileId'");
        }

        map<string, string> params;
        string path = "/storage/files/{fileId}/view";
        size_t pos;
        string searchString;
        searchString = "{fileId}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "fileId");

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }
};
}

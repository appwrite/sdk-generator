#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "storage.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {
/*
 * List Files
 *
     * Get a list of all the user files. You can use the query params to filter
     * your results. On admin mode, this endpoint will return a list of all of the
     * project's files. [Learn more about different API modes](/docs/admin).
 * @param string search
 * @param int limit
 * @param int offset
 * @param string orderType
 * @throws AppwriteException
 * @return array
 */
json Storage::listFiles(string search, int limit, int offset, string orderType) {
    json params = {
        {"search", search},
        {"limit", limit},
        {"offset", offset},
        {"orderType", orderType},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/storage/files";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Create File
 *
     * Create a new file. The user who creates the file will automatically be
     * assigned to read and write access unless he has passed custom values for
     * read and write arguments.
 * @param file file
 * @param array read
 * @param array write
 * @throws AppwriteException
 * @return array
 */
json Storage::createFile(file file, array read, array write) {
    json params = {
        {"file", file},
        {"read", read},
        {"write", write},
    };
    json headers = {
        {"content-type", "multipart/form-data"},
    };
    string path = "/storage/files";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_POST, path, headers, params);
}

/*
 * Get File
 *
     * Get a file by its unique ID. This endpoint response returns a JSON object
     * with the file metadata.
 * @param string fileId
 * @throws AppwriteException
 * @return array
 */
json Storage::getFile(string fileId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/storage/files/{fileId}";
    size_t pos;
    string searchString;
    searchString = "{fileId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "fileId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Update File
 *
     * Update a file by its unique ID. Only users with write permissions have
     * access to update this resource.
 * @param string fileId
 * @param array read
 * @param array write
 * @throws AppwriteException
 * @return array
 */
json Storage::updateFile(string fileId, array read, array write) {
    json params = {
        {"read", read},
        {"write", write},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/storage/files/{fileId}";
    size_t pos;
    string searchString;
    searchString = "{fileId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "fileId");

    return this->client->call(Client::METHOD_PUT, path, headers, params);
}

/*
 * Delete File
 *
     * Delete a file by its unique ID. Only users with write permissions have
     * access to delete this resource.
 * @param string fileId
 * @throws AppwriteException
 * @return array
 */
json Storage::deleteFile(string fileId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/storage/files/{fileId}";
    size_t pos;
    string searchString;
    searchString = "{fileId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "fileId");

    return this->client->call(Client::METHOD_DELETE, path, headers, params);
}

/*
 * Get File for Download
 *
     * Get a file content by its unique ID. The endpoint response return with a
     * 'Content-Disposition: attachment' header that tells the browser to start
     * downloading the file to user downloads directory.
 * @param string fileId
 * @throws AppwriteException
 * @return string
 */
string Storage::getFileDownload(string fileId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/storage/files/{fileId}/download";
    size_t pos;
    string searchString;
    searchString = "{fileId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "fileId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Get File Preview
 *
     * Get a file preview image. Currently, this method supports preview for image
     * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
     * and spreadsheets, will return the file icon image. You can also pass query
     * string arguments for cutting and resizing your preview image.
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
string Storage::getFilePreview(string fileId, int width, int height, string gravity, int quality, int borderWidth, string borderColor, int borderRadius, double opacity, int rotation, string background, string output) {
    json params = {
        {"width", width},
        {"height", height},
        {"gravity", gravity},
        {"quality", quality},
        {"borderWidth", borderWidth},
        {"borderColor", borderColor},
        {"borderRadius", borderRadius},
        {"opacity", opacity},
        {"rotation", rotation},
        {"background", background},
        {"output", output},
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/storage/files/{fileId}/preview";
    size_t pos;
    string searchString;
    searchString = "{fileId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "fileId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * Get File for View
 *
     * Get a file content by its unique ID. This endpoint is similar to the
     * download method but returns with no  'Content-Disposition: attachment'
     * header.
 * @param string fileId
 * @throws AppwriteException
 * @return string
 */
string Storage::getFileView(string fileId) {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/storage/files/{fileId}/view";
    size_t pos;
    string searchString;
    searchString = "{fileId}";
    pos = path.find(searchString);
    if (pos != std::string::npos)
        path = path.replace(pos, searchString.length(), "fileId");

    return this->client->call(Client::METHOD_GET, path, headers, params);
}
} // namespace Appwrite

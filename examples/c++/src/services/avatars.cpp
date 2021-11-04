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

class Avatars : public Service {
 public:
    /**
     * Get Browser Icon
     *
     * You can use this endpoint to show different browser icons to your users.
     * The code argument receives the browser code as it appears in your user
     * /account/sessions endpoint. Use width, height and quality arguments to
     * change the output settings.
     *
     * @param string code
     * @param int width
     * @param int height
     * @param int quality
     * @throws AppwriteException
     * @return string
     */
    string getBrowser(string* code, int* width = NULL, int* height = NULL, int* quality = NULL) {
        if (!isset(code)) {
            throw new AppwriteException("Missing required parameter: 'code'");
        }

        map<string, string> params;
        string path = "/avatars/browsers/{code}";
        size_t pos;
        string searchString;
        searchString = "{code}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "code");

        if (width != NULL) {
            params["width"] = *width;
        }

        if (height != NULL) {
            params["height"] = *height;
        }

        if (quality != NULL) {
            params["quality"] = *quality;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Credit Card Icon
     *
     * The credit card endpoint will return you the icon of the credit card
     * provider you need. Use width, height and quality arguments to change the
     * output settings.
     *
     * @param string code
     * @param int width
     * @param int height
     * @param int quality
     * @throws AppwriteException
     * @return string
     */
    string getCreditCard(string* code, int* width = NULL, int* height = NULL, int* quality = NULL) {
        if (!isset(code)) {
            throw new AppwriteException("Missing required parameter: 'code'");
        }

        map<string, string> params;
        string path = "/avatars/credit-cards/{code}";
        size_t pos;
        string searchString;
        searchString = "{code}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "code");

        if (width != NULL) {
            params["width"] = *width;
        }

        if (height != NULL) {
            params["height"] = *height;
        }

        if (quality != NULL) {
            params["quality"] = *quality;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Favicon
     *
     * Use this endpoint to fetch the favorite icon (AKA favicon) of any remote
     * website URL.
     * 
     *
     * @param string url
     * @throws AppwriteException
     * @return string
     */
    string getFavicon(string* url) {
        if (!isset(url)) {
            throw new AppwriteException("Missing required parameter: 'url'");
        }

        map<string, string> params;
        string path = "/avatars/favicon";
        size_t pos;
        string searchString;

        if (url != NULL) {
            params["url"] = *url;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Country Flag
     *
     * You can use this endpoint to show different country flags icons to your
     * users. The code argument receives the 2 letter country code. Use width,
     * height and quality arguments to change the output settings.
     *
     * @param string code
     * @param int width
     * @param int height
     * @param int quality
     * @throws AppwriteException
     * @return string
     */
    string getFlag(string* code, int* width = NULL, int* height = NULL, int* quality = NULL) {
        if (!isset(code)) {
            throw new AppwriteException("Missing required parameter: 'code'");
        }

        map<string, string> params;
        string path = "/avatars/flags/{code}";
        size_t pos;
        string searchString;
        searchString = "{code}";
        pos = path.find(searchString);
        if (pos != std::string::npos)
            path = path.replace(pos, searchString.length(), "code");

        if (width != NULL) {
            params["width"] = *width;
        }

        if (height != NULL) {
            params["height"] = *height;
        }

        if (quality != NULL) {
            params["quality"] = *quality;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get Image from URL
     *
     * Use this endpoint to fetch a remote image URL and crop it to any image size
     * you want. This endpoint is very useful if you need to crop and display
     * remote images in your app or in case you want to make sure a 3rd party
     * image is properly served using a TLS protocol.
     *
     * @param string url
     * @param int width
     * @param int height
     * @throws AppwriteException
     * @return string
     */
    string getImage(string* url, int* width = NULL, int* height = NULL) {
        if (!isset(url)) {
            throw new AppwriteException("Missing required parameter: 'url'");
        }

        map<string, string> params;
        string path = "/avatars/image";
        size_t pos;
        string searchString;

        if (url != NULL) {
            params["url"] = *url;
        }

        if (width != NULL) {
            params["width"] = *width;
        }

        if (height != NULL) {
            params["height"] = *height;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get User Initials
     *
     * Use this endpoint to show your user initials avatar icon on your website or
     * app. By default, this route will try to print your logged-in user name or
     * email initials. You can also overwrite the user name if you pass the 'name'
     * parameter. If no name is given and no user is logged, an empty avatar will
     * be returned.
     * 
     * You can use the color and background params to change the avatar colors. By
     * default, a random theme will be selected. The random theme will persist for
     * the user's initials when reloading the same theme will always return for
     * the same initials.
     *
     * @param string name
     * @param int width
     * @param int height
     * @param string color
     * @param string background
     * @throws AppwriteException
     * @return string
     */
    string getInitials(string* name = NULL, int* width = NULL, int* height = NULL, string* color = NULL, string* background = NULL) {
        map<string, string> params;
        string path = "/avatars/initials";
        size_t pos;
        string searchString;

        if (name != NULL) {
            params["name"] = *name;
        }

        if (width != NULL) {
            params["width"] = *width;
        }

        if (height != NULL) {
            params["height"] = *height;
        }

        if (color != NULL) {
            params["color"] = *color;
        }

        if (background != NULL) {
            params["background"] = *background;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }

    /**
     * Get QR Code
     *
     * Converts a given plain text to a QR code image. You can use the query
     * parameters to change the size and style of the resulting image.
     *
     * @param string text
     * @param int size
     * @param int margin
     * @param bool download
     * @throws AppwriteException
     * @return string
     */
    string getQR(string* text, int* size = NULL, int* margin = NULL, bool* download = NULL) {
        if (!isset(text)) {
            throw new AppwriteException("Missing required parameter: 'text'");
        }

        map<string, string> params;
        string path = "/avatars/qr";
        size_t pos;
        string searchString;

        if (text != NULL) {
            params["text"] = *text;
        }

        if (size != NULL) {
            params["size"] = *size;
        }

        if (margin != NULL) {
            params["margin"] = *margin;
        }

        if (download != NULL) {
            params["download"] = *download;
        }

        map<string, string> headers = {
            {"content-type", "application/json"},
        };

        return this->client->call(Client::METHOD_GET, path, headers, params);
    }
};
}

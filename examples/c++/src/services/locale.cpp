#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../exception.hpp"
#include "../service.hpp"
#include "locale.hpp"

using string = std::string;
using json = nlohmann::json;

namespace Appwrite {
/*
 * Get User Locale
 *
     * Get the current user location based on IP. Returns an object with user
     * country code, country name, continent name, continent code, ip address and
     * suggested currency. You can use the locale header to get the data in a
     * supported language.
     * 
     * ([IP Geolocation by DB-IP](https://db-ip.com))
 * @throws AppwriteException
 * @return array
 */
json Locale::get() {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/locale";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * List Continents
 *
     * List of all continents. You can use the locale header to get the data in a
     * supported language.
 * @throws AppwriteException
 * @return array
 */
json Locale::getContinents() {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/locale/continents";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * List Countries
 *
     * List of all countries. You can use the locale header to get the data in a
     * supported language.
 * @throws AppwriteException
 * @return array
 */
json Locale::getCountries() {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/locale/countries";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * List EU Countries
 *
     * List of all countries that are currently members of the EU. You can use the
     * locale header to get the data in a supported language.
 * @throws AppwriteException
 * @return array
 */
json Locale::getCountriesEU() {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/locale/countries/eu";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * List Countries Phone Codes
 *
     * List of all countries phone codes. You can use the locale header to get the
     * data in a supported language.
 * @throws AppwriteException
 * @return array
 */
json Locale::getCountriesPhones() {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/locale/countries/phones";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * List Currencies
 *
     * List of all currencies, including currency symbol, name, plural, and
     * decimal digits for all major and minor currencies. You can use the locale
     * header to get the data in a supported language.
 * @throws AppwriteException
 * @return array
 */
json Locale::getCurrencies() {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/locale/currencies";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}

/*
 * List Languages
 *
     * List of all languages classified by ISO 639-1 including 2-letter code, name
     * in English, and name in the respective language.
 * @throws AppwriteException
 * @return array
 */
json Locale::getLanguages() {
    json params = {
    };
    json headers = {
        {"content-type", "application/json"},
    };
    string path = "/locale/languages";
    size_t pos;
    string searchString;

    return this->client->call(Client::METHOD_GET, path, headers, params);
}
} // namespace Appwrite

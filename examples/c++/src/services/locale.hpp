#pragma once

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Locale : public Service {
 public:
    using Service::Service;
    json get();
    json getContinents();
    json getCountries();
    json getCountriesEU();
    json getCountriesPhones();
    json getCurrencies();
    json getLanguages();
};
} // namespace Appwrite

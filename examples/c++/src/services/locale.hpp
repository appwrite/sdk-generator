#ifndef APPWRITE_LOCALE_H
#define APPWRITE_LOCALE_H

#include <cstring>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"
using namespace std;
using json = nlohmann::json;

namespace Appwrite {

class Locale : public Service {
public:
    json get();
    json getContinents();
    json getCountries();
    json getCountriesEU();
    json getCountriesPhones();
    json getCurrencies();
    json getLanguages();
};
}
#endif

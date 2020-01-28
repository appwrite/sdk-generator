

class Locale: Service
{
    /**
     * Get User Locale
     *
     * Get the current user location based on IP. Returns an object with user
     * country code, country name, continent name, continent code, ip address and
     * suggested currency. You can use the locale header to get the data in a
     * supported language.
     *
     * @throws Exception
     * @return array
     */

    func getLocale() -> Array<Any> {
        let path: String = "/locale"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Countries
     *
     * List of all continents. You can use the locale header to get the data in a
     * supported language.
     *
     * @throws Exception
     * @return array
     */

    func getContinents() -> Array<Any> {
        let path: String = "/locale/continents"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Countries
     *
     * List of all countries. You can use the locale header to get the data in a
     * supported language.
     *
     * @throws Exception
     * @return array
     */

    func getCountries() -> Array<Any> {
        let path: String = "/locale/countries"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List EU Countries
     *
     * List of all countries that are currently members of the EU. You can use the
     * locale header to get the data in a supported language. UK brexit date is
     * currently set to 2019-10-31 and will be updated if and when needed.
     *
     * @throws Exception
     * @return array
     */

    func getCountriesEU() -> Array<Any> {
        let path: String = "/locale/countries/eu"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Countries Phone Codes
     *
     * List of all countries phone codes. You can use the locale header to get the
     * data in a supported language.
     *
     * @throws Exception
     * @return array
     */

    func getCountriesPhones() -> Array<Any> {
        let path: String = "/locale/countries/phones"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Currencies
     *
     * List of all currencies, including currency symol, name, plural, and decimal
     * digits for all major and minor currencies. You can use the locale header to
     * get the data in a supported language.
     *
     * @throws Exception
     * @return array
     */

    func getCurrencies() -> Array<Any> {
        let path: String = "/locale/currencies"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}

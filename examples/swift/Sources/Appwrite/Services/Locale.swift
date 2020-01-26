

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

    func getLocale()-> Array<Any> {
        let methodPath = "/locale"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func getContinents()-> Array<Any> {
        let methodPath = "/locale/continents"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func getCountries()-> Array<Any> {
        let methodPath = "/locale/countries"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func getCountriesEU()-> Array<Any> {
        let methodPath = "/locale/countries/eu"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func getCountriesPhones()-> Array<Any> {
        let methodPath = "/locale/countries/phones"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func getCurrencies()-> Array<Any> {
        let methodPath = "/locale/currencies"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

}

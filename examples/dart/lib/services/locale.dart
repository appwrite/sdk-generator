import "package:dart_appwrite/service.dart";
import "package:dart_appwrite/client.dart";

class Locale extends Service {
     
     Locale(Client client): super(client);

     /// Get the current user location based on IP. Returns an object with user
     /// country code, country name, continent name, continent code, ip address and
     /// suggested currency. You can use the locale header to get the data in
     /// supported language.
    getLocale() async {
       String path = '/locale';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// List of all countries. You can use the locale header to get the data in
     /// supported language.
    getCountries() async {
       String path = '/locale/countries';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// List of all countries that are currently members of the EU. You can use the
     /// locale header to get the data in supported language. UK brexit date is
     /// currently set to 2019-10-31 and will be updated if and when needed.
    getCountriesEU() async {
       String path = '/locale/countries/eu';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// List of all countries phone codes. You can use the locale header to get the
     /// data in supported language.
    getCountriesPhones() async {
       String path = '/locale/countries/phones';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// List of all currencies, including currency symol, name, plural, and decimal
     /// digits for all major and minor currencies. You can use the locale header to
     /// get the data in supported language.
    getCurrencies() async {
       String path = '/locale/currencies';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
}
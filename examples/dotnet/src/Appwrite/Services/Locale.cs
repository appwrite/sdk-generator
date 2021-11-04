
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Threading.Tasks;

namespace Appwrite
{
    public class Locale : Service
    {
        public Locale(Client client) : base(client) { }

        /// <summary>
        /// Get User Locale
        /// <para>
        /// Get the current user location based on IP. Returns an object with user
        /// country code, country name, continent name, continent code, ip address and
        /// suggested currency. You can use the locale header to get the data in a
        /// supported language.
        /// 
        /// ([IP Geolocation by DB-IP](https://db-ip.com))
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Get() 
        {
            string path = "/locale";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// List Continents
        /// <para>
        /// List of all continents. You can use the locale header to get the data in a
        /// supported language.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetContinents() 
        {
            string path = "/locale/continents";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// List Countries
        /// <para>
        /// List of all countries. You can use the locale header to get the data in a
        /// supported language.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetCountries() 
        {
            string path = "/locale/countries";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// List EU Countries
        /// <para>
        /// List of all countries that are currently members of the EU. You can use the
        /// locale header to get the data in a supported language.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetCountriesEU() 
        {
            string path = "/locale/countries/eu";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// List Countries Phone Codes
        /// <para>
        /// List of all countries phone codes. You can use the locale header to get the
        /// data in a supported language.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetCountriesPhones() 
        {
            string path = "/locale/countries/phones";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// List Currencies
        /// <para>
        /// List of all currencies, including currency symbol, name, plural, and
        /// decimal digits for all major and minor currencies. You can use the locale
        /// header to get the data in a supported language.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetCurrencies() 
        {
            string path = "/locale/currencies";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// List Languages
        /// <para>
        /// List of all languages classified by ISO 639-1 including 2-letter code, name
        /// in English, and name in the respective language.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetLanguages() 
        {
            string path = "/locale/languages";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }
    };
}
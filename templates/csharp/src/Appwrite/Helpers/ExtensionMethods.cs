using Newtonsoft.Json;
using Newtonsoft.Json.Converters;
using Newtonsoft.Json.Serialization;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace {{ spec.title | caseUcfirst }}
{
    public static class ExtensionMethods
    {

        public static string ToJson(this Dictionary<string, object> dict)
        {
            var settings = new JsonSerializerSettings
            {
                ContractResolver = new CamelCasePropertyNamesContractResolver(),
                Converters = new List<JsonConverter> { new StringEnumConverter() }
            };

            return JsonConvert.SerializeObject(dict, settings);
        }

        public static string ToQueryString(this Dictionary<string, object> parameters)
        {
            var query = HttpUtility.ParseQueryString(string.Empty);

            foreach (var parameter in parameters)
            {
                if (parameter.Value != null)
                {
                    query[parameter.Key] = parameter.Value.ToString();
                }
            }
            return query.ToString();
        }

    }
}
using Newtonsoft.Json;
using Newtonsoft.Json.Converters;
using Newtonsoft.Json.Serialization;
using System;
using System.Collections.Generic;

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
            var query = new List<string>();

            foreach (var (key, value) in parameters)
            {
                if (value != null)
                {
                    if (value is List<object>)
                    {
                        foreach(object entry in (dynamic) value)
                        {
                            query.Add($"{key}[]={Uri.EscapeUriString(entry.ToString()!)}");
                        }
                    }
                    else
                    {
                        query.Add($"{key}={Uri.EscapeUriString(value.ToString()!)}");
                    }
                }
            }
            return string.Join("&", query);
        }
    }
}
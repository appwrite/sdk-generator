using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace Appwrite
{
    public static class ExtensionMethods
    {

        public static string ToJson(this Dictionary<string, object> dict)
        {
            var entries = dict.Select(d => string.Format("\"{0}\": [{1}]", d.Key, string.Join(",", d.Value)));

            return "{" + string.Join(",", entries) + "}";
        }

        public static string ToQueryString(this Dictionary<string, object> parameters)
        {
            var query = HttpUtility.ParseQueryString(string.Empty);

            foreach (var parameter in parameters)
            {
                query[parameter.Key] = parameter.Value.ToString();
            }
            return query.ToString();
        }

    }
}
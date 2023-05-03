using System.Collections;
using System.Collections.Generic;
using System.Linq;

namespace Appwrite
{
    public static class Query
    {
        public static string Equal(string attribute, object value)
        {
            return AddQuery(attribute, "equal", value);
        }

        public static string NotEqual(string attribute, object value)
        {
            return AddQuery(attribute, "notEqual", value);
        }

        public static string LessThan(string attribute, object value)
        {
            return AddQuery(attribute, "lessThan", value);
        }

        public static string LessThanEqual(string attribute, object value)
        {
            return AddQuery(attribute, "lessThanEqual", value);
        }

        public static string GreaterThan(string attribute, object value)
        {
            return AddQuery(attribute, "greaterThan", value);
        }

        public static string GreaterThanEqual(string attribute, object value)
        {
            return AddQuery(attribute, "greaterThanEqual", value);
        }

        public static string Search(string attribute, string value)
        {
            return AddQuery(attribute, "search", value);
        }

        public static string IsNull(string attribute)
        {
            return $"isNull(\"{attribute}\")";
        }

        public static string IsNotNull(string attribute)
        {
            return $"isNotNull(\"{attribute}\")";
        }

        public static string StartsWith(string attribute, string value)
        {
            return AddQuery(attribute, "startsWith", value);
        }

        public static string EndsWith(string attribute, string value)
        {
            return AddQuery(attribute, "endsWith", value);
        }

        public static string Between(string attribute, string start, string end)
        {
            return AddQuery(attribute, "between", new List<string> { start, end });
        }

        public static string Between(string attribute, int start, int end)
        {
            return AddQuery(attribute, "between", new List<int> { start, end });
        }

        public static string Between(string attribute, double start, double end)
        {
            return AddQuery(attribute, "between", new List<double> { start, end });
        }

        public static string Select(List<string> attributes)
        {
            return $"select([{string.Join(",", attributes.Select(attribute => $"\"{attribute}\""))}])";
        }

        public static string CursorAfter(string documentId)
        {
            return $"cursorAfter(\"{documentId}\")";
        }

        public static string CursorBefore(string documentId) {
            return $"cursorBefore(\"{documentId}\")";
        }

        public static string OrderAsc(string attribute) {
            return $"orderAsc(\"{attribute}\")";
        }

        public static string OrderDesc(string attribute) {
            return $"orderDesc(\"{attribute}\")";
        }

        public static string Limit(int limit) {
            return $"limit({limit})";
        }

        public static string Offset(int offset) {
            return $"offset({offset})";
        }

        private static string AddQuery(string attribute, string method, object value)
        {
            if (value is IList list)
            {
                var parsed = new List<object>();
                foreach (var item in list)
                {
                    parsed.Add(ParseValues(item));
                }
                return $"{method}(\"{attribute}\", [{string.Join(",", parsed)}])";
            }
            else
            {
                return $"{method}(\"{attribute}\", [{ParseValues(value)}])";
            }
        }

        private static string ParseValues(object value)
        {
            switch (value)
            {
                case string str:
                    return $"\"{str}\"";
                case bool boolean:
                    return boolean.ToString().ToLower();
                default:
                    return value.ToString();
            }
        }
    }
}
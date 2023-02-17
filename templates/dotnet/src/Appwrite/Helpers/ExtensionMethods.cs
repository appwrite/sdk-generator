﻿
using System;
using System.Collections.Generic;
using System.Text.Json;

namespace {{ spec.title | caseUcfirst }}
{
    public static class ExtensionMethods
    {
        public static string ToJson(this Dictionary<string, object> dict)
        {
            return JsonSerializer.Serialize(dict);
        }

        public static string ToQueryString(this Dictionary<string, object> parameters)
        {
            List<string> query = new List<string>();

            foreach (KeyValuePair<string, object> parameter in parameters)
            {
                if (parameter.Value != null)
                {
                    if (parameter.Value is List<object>)
                    {
                        foreach(object entry in (List<object>) parameter.Value)
                        {
                            query.Add(parameter.Key + "[]=" + Uri.EscapeUriString(entry.ToString()));
                        }
                    } 
                    else 
                    {
                        query.Add(parameter.Key + "=" + Uri.EscapeUriString(parameter.Value.ToString()));
                    }
                }
            }
            return string.Join("&", query);
        }
    }
}
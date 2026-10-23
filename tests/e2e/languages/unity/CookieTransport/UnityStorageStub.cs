using System.Collections.Generic;
using System.Text.Json;
// Only Unity storage/logging are adapted; CookieContainer is compiled from the generated SDK.
namespace UnityEngine
{
    public static class PlayerPrefs
    {
        private static readonly Dictionary<string, string> values = new Dictionary<string, string>();
        public static bool HasKey(string key) => values.ContainsKey(key);
        public static string GetString(string key) => values[key];
        public static void SetString(string key, string value) => values[key] = value;
        public static void DeleteKey(string key) => values.Remove(key);
        public static void Save() { }
    }
    public static class Debug
    {
        public static void Log(object message) { }
        public static void LogWarning(object message) { }
    }
}
namespace Appwrite
{
    public static class Client
    {
        public static JsonSerializerOptions SerializerOptions { get; } = new JsonSerializerOptions { PropertyNamingPolicy = JsonNamingPolicy.CamelCase };
        public static JsonSerializerOptions DeserializerOptions { get; } = new JsonSerializerOptions { PropertyNameCaseInsensitive = true };
    }
}

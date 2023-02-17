using System.Text.Json.Serialization;

namespace Appwrite
{
    public class ApiErrorAnswer
    {
        [JsonPropertyName("message")]
        public string Message { get; set; }
        [JsonPropertyName("code")]
        public int Code { get; set; }
        [JsonPropertyName("type")]
        public string Type { get; set; }
        [JsonPropertyName("version")]
        public string Version { get; set; }
    }
}
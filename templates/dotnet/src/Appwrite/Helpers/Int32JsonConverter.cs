using System;
using System.Collections.Generic;
using Newtonsoft.Json;

namespace {{ spec.title | caseUcfirst }}
{
    public class Int32JsonConverter : JsonConverter
    {
        public override bool CanConvert(Type objectType) =>
            objectType == typeof(Dictionary<string, object>);

        public override bool CanWrite => false;

        public override object ReadJson(
            JsonReader reader,
            Type objectType,
            object? existingValue,
            JsonSerializer serializer)
        {
            var result = new Dictionary<string, object>();
            reader.Read();

            while (reader.TokenType == JsonToken.PropertyName)
            {
                string? propertyName = reader.Value as string;
                reader.Read();

                object? value;
                if (reader.TokenType == JsonToken.Integer)
                {
                    // Convert to Int32 instead of Int64
                    value = Convert.ToInt32(reader.Value);
                }
                else
                {
                    value = serializer.Deserialize(reader);
                }
                result.Add(propertyName!, value!);
                reader.Read();
            }

            return result;
        }

        public override void WriteJson(
            JsonWriter writer,
            object? value,
            JsonSerializer serializer) =>
            throw new NotImplementedException();
    }
}
using System;
using System.Net.Http;
using System.Threading.Tasks;

namespace Appwrite.Test
{
    public class ServiceTest
    {
        public async Task Test()
        {
            Client client = new Client();

            Foo foo = new Foo(client);
            Bar bar = new Bar(client);
            General general = new General(client);

            client.SetEndpoint("http://localhost/v1");
            client.AddHeader("Origin", "http://localhost");
            client.SetSelfSigned(true);

            // Foo Tests
            HttpResponseMessage response;
            response = await foo.get("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = foo.post("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = foo.put("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = foo.patch("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = foo.delete("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            // Bar Tests
            response = bar.get("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = bar.post("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = bar.put("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = bar.patch("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            response = bar.delete("string", 123, new string[] { "string in array" });
            PrintResponse(response);

            // General Tests
            response = general.Redirect();
            PrintResponse(response);
        }

        private async Task PrintResponse(HttpResponseMessage response)
        {
            string content = await response.Content.ReadAsStringAsync();

            Console.WriteLine(content);
        }

    }
}
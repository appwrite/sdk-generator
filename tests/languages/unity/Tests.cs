using System.Collections;
using System.Collections.Generic;
using System.IO;
using System.Threading.Tasks;
using UnityEngine;
using UnityEngine.TestTools;

using Appwrite;
using Appwrite.Models;
using Appwrite.Enums;
using Appwrite.Services;
using NUnit.Framework;

namespace AppwriteTests
{
    public class Tests
    {
        [SetUp]
        public void Setup()
        {
            Debug.Log("Test Started");
        }

        [UnityTest]
        public IEnumerator Test1()
        {
            var task = RunAsyncTest();
            yield return new WaitUntil(() => task.IsCompleted);
            
            if (task.Exception != null)
            {
                Debug.LogError($"Test failed with exception: {task.Exception}");
                throw task.Exception;
            }
            
        }
        
        private async Task RunAsyncTest()
        {
            var client = new Client()
                .SetProject("123456")
                .AddHeader("Origin", "http://localhost")
                .SetSelfSigned(true);

            var foo = new Foo(client);
            var bar = new Bar(client);
            var general = new General(client);

            client.SetProject("console");
            client.SetEndPointRealtime("wss://cloud.appwrite.io/v1");
            
            // Create GameObject for Realtime MonoBehaviour
            var realtimeObject = new GameObject("RealtimeTest");
            var realtime = realtimeObject.AddComponent<Realtime>();
            realtime.Initialize(client);
            
            string realtimeResponse = "No realtime message received within timeout";            
            RealtimeSubscription subscription = null;
            subscription = realtime.Subscribe(new [] { "tests" }, (eventData) => 
            {
                Debug.Log($"[Test] Realtime callback invoked! Payload count: {eventData.Payload?.Count}");
                if (eventData.Payload != null && eventData.Payload.TryGetValue("response", out var value))
                {
                    Debug.Log($"[Test] Found response value: {value}");
                    realtimeResponse = value.ToString();
                    Debug.Log($"[Test] Updated realtimeResponse to: {realtimeResponse}");
                }
                else
                {
                    Debug.Log("[Test] No 'response' key found in payload");
                }
                subscription?.Close();
            });

            await Task.Delay(5000);

            // Ping test
            client.SetProject("123456");
            var ping = await client.Ping();
            Debug.Log(ping);

            // Reset a project for other tests
            client.SetProject("console");

            Mock mock;
            // Foo Tests
            mock = await foo.Get("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await foo.Post("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await foo.Put("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await foo.Patch("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await foo.Delete("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            // Bar Tests
            mock = await bar.Get("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await bar.Post("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await bar.Put("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await bar.Patch("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            mock = await bar.Delete("string", 123, new List<string>() { "string in array" });
            Debug.Log(mock.Result);

            // General Tests
            var result = await general.Redirect();
            Debug.Log((result as Dictionary<string, object>)["result"]);

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../resources/file.png"));
            Debug.Log(mock.Result);

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../resources/large_file.mp4"));
            Debug.Log(mock.Result);

            var info = new FileInfo("../../resources/file.png");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "file.png", "image/png"));
            Debug.Log(mock.Result);

            info = new FileInfo("../../resources/large_file.mp4");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "large_file.mp4", "video/mp4"));
            Debug.Log(mock.Result);

            // Download test
            var downloadResult = await general.Download();
            if (downloadResult != null)
            {
                var downloadString = System.Text.Encoding.UTF8.GetString(downloadResult);
                Debug.Log(downloadString);
            }

            mock = await general.Enum(MockType.First);
            Debug.Log(mock.Result);

            try
            {
                await general.Error400();
            }
            catch (AppwriteException e)
            {
                Debug.Log(e.Message);
                Debug.Log(e.Response);
            }

            try
            {
                await general.Error500();
            }
            catch (AppwriteException e)
            {
                Debug.Log(e.Message);
                Debug.Log(e.Response);
            }

            try
            {
                await general.Error502();
            }
            catch (AppwriteException e)
            {
                Debug.Log(e.Message);
                Debug.Log(e.Response);
            }

            try
            {
                client.SetEndpoint("htp://cloud.appwrite.io/v1");
            }
            catch (AppwriteException e)
            {
                Debug.Log(e.Message);
            }

            await general.Empty();

            await Task.Delay(5000);
            Debug.Log(realtimeResponse);

            // Cookie tests
            mock = await general.SetCookie();
            Debug.Log(mock.Result);

            mock = await general.GetCookie();
            Debug.Log(mock.Result);

            var url = await general.Oauth2(
                clientId: "clientId",
                scopes: new List<string>() {"test"},
                state: "123456",
                success: "https://localhost",
                failure: "https://localhost"
            );
            Debug.Log(url);

            // Query helper tests
            Debug.Log(Query.Equal("released", new List<bool> { true }));
            Debug.Log(Query.Equal("title", new List<string> { "Spiderman", "Dr. Strange" }));
            Debug.Log(Query.NotEqual("title", "Spiderman"));
            Debug.Log(Query.LessThan("releasedYear", 1990));
            Debug.Log(Query.GreaterThan("releasedYear", 1990));
            Debug.Log(Query.Search("name", "john"));
            Debug.Log(Query.IsNull("name"));
            Debug.Log(Query.IsNotNull("name"));
            Debug.Log(Query.Between("age", 50, 100));
            Debug.Log(Query.Between("age", 50.5, 100.5));
            Debug.Log(Query.Between("name", "Anna", "Brad"));
            Debug.Log(Query.StartsWith("name", "Ann"));
            Debug.Log(Query.EndsWith("name", "nne"));
            Debug.Log(Query.Select(new List<string> { "name", "age" }));
            Debug.Log(Query.OrderAsc("title"));
            Debug.Log(Query.OrderDesc("title"));
            Debug.Log(Query.CursorAfter("my_movie_id"));
            Debug.Log(Query.CursorBefore("my_movie_id"));
            Debug.Log(Query.Limit(50));
            Debug.Log(Query.Offset(20));
            Debug.Log(Query.Contains("title", "Spider"));
            Debug.Log(Query.Contains("labels", "first"));
            Debug.Log(Query.Or(new List<string> { Query.Equal("released", true), Query.LessThan("releasedYear", 1990) }));
            Debug.Log(Query.And(new List<string> { Query.Equal("released", false), Query.GreaterThan("releasedYear", 2015) }));

            // Permission & Roles helper tests
            Debug.Log(Permission.Read(Role.Any()));
            Debug.Log(Permission.Write(Role.User(ID.Custom("userid"))));
            Debug.Log(Permission.Create(Role.Users()));
            Debug.Log(Permission.Update(Role.Guests()));
            Debug.Log(Permission.Delete(Role.Team("teamId", "owner")));
            Debug.Log(Permission.Delete(Role.Team("teamId")));
            Debug.Log(Permission.Create(Role.Member("memberId")));
            Debug.Log(Permission.Update(Role.Users("verified")));
            Debug.Log(Permission.Update(Role.User(ID.Custom("userid"), "unverified")));
            Debug.Log(Permission.Create(Role.Label("admin")));

            // ID helper tests
            Debug.Log(ID.Unique());
            Debug.Log(ID.Custom("custom_id"));

            mock = await general.Headers();
            Debug.Log(mock.Result);
            
            // Cleanup Realtime GameObject
            if (realtimeObject)
            {
                Object.DestroyImmediate(realtimeObject);
            }
            
        }
    }
}

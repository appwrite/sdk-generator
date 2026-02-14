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
        private async Task<string> WaitForRealtimeMessage(Realtime realtime, string[] channels, int timeoutMs = 5000, List<string> queries = null)
        {
            var tcs = new TaskCompletionSource<string>();
            var subscription = realtime.Subscribe(channels, (eventData) =>
            {
                if (eventData.Payload != null && eventData.Payload.TryGetValue("response", out var value) && value != null)
                {
                    tcs.TrySetResult(value.ToString());
                }
            }, queries);

            var task = await Task.WhenAny(tcs.Task, Task.Delay(timeoutMs));
            subscription.Close();
            return task == tcs.Task ? await tcs.Task : "No realtime message received within timeout";
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

            // Request model tests
            mock = await general.CreatePlayer(new Player("player1", "John Doe", 100));
            Debug.Log(mock.Result);

            mock = await general.CreatePlayers(new List<Player> {
                new Player("player1", "John Doe", 100),
                new Player("player2", "Jane Doe", 200)
            });
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

            // Realtime tests
            var realtimeNoQueryResponse = await WaitForRealtimeMessage(realtime, new[] { "tests" });
            Debug.Log(realtimeNoQueryResponse);

            var realtimeWithQueryResponse = await WaitForRealtimeMessage(realtime, new[] { "tests" }, queries: new List<string> { Query.Equal("response", new List<string> { "WS:/v1/realtime:passed" }) });
            Debug.Log(realtimeWithQueryResponse);

            var realtimeFailureResponse = await WaitForRealtimeMessage(realtime, new[] { "tests" }, queries: new List<string> { Query.Equal("response", new List<string> { "failed" }) });
            if (realtimeFailureResponse == "No realtime message received within timeout")
            {
                Debug.Log("Realtime failed!");
            }
            else
            {
                Debug.Log("Realtime failed!");
            }

            // Cookie tests
            mock = await general.SetCookie();
            Debug.Log(mock.Result);

            mock = await general.GetCookie();
            Debug.Log(mock.Result);

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
            Debug.Log(Query.OrderRandom());
            Debug.Log(Query.CursorAfter("my_movie_id"));
            Debug.Log(Query.CursorBefore("my_movie_id"));
            Debug.Log(Query.Limit(50));
            Debug.Log(Query.Offset(20));
            Debug.Log(Query.Contains("title", "Spider"));
            Debug.Log(Query.Contains("labels", "first"));

            // New query methods
            Debug.Log(Query.NotContains("title", "Spider"));
            Debug.Log(Query.NotSearch("name", "john"));
            Debug.Log(Query.NotBetween("age", 50, 100));
            Debug.Log(Query.NotStartsWith("name", "Ann"));
            Debug.Log(Query.NotEndsWith("name", "nne"));
            Debug.Log(Query.CreatedBefore("2023-01-01"));
            Debug.Log(Query.CreatedAfter("2023-01-01"));
            Debug.Log(Query.CreatedBetween("2023-01-01", "2023-12-31"));
            Debug.Log(Query.UpdatedBefore("2023-01-01"));
            Debug.Log(Query.UpdatedAfter("2023-01-01"));
            Debug.Log(Query.UpdatedBetween("2023-01-01", "2023-12-31"));
            
            // Spatial Distance query tests
            Debug.Log(Query.DistanceEqual("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }, 1000));
            Debug.Log(Query.DistanceEqual("location", new List<object> { 40.7128, -74 }, 1000, true));
            Debug.Log(Query.DistanceNotEqual("location", new List<object> { 40.7128, -74 }, 1000));
            Debug.Log(Query.DistanceNotEqual("location", new List<object> { 40.7128, -74 }, 1000, true));
            Debug.Log(Query.DistanceGreaterThan("location", new List<object> { 40.7128, -74 }, 1000));
            Debug.Log(Query.DistanceGreaterThan("location", new List<object> { 40.7128, -74 }, 1000, true));
            Debug.Log(Query.DistanceLessThan("location", new List<object> { 40.7128, -74 }, 1000));
            Debug.Log(Query.DistanceLessThan("location", new List<object> { 40.7128, -74 }, 1000, true));
            
            // Spatial query tests
            Debug.Log(Query.Intersects("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.NotIntersects("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.Crosses("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.NotCrosses("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.Overlaps("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.NotOverlaps("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.Touches("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.NotTouches("location", new List<object> { 40.7128, -74 }));
            Debug.Log(Query.Contains("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            Debug.Log(Query.NotContains("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            Debug.Log(Query.Equal("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            Debug.Log(Query.NotEqual("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            
            Debug.Log(Query.Or(new List<string> { Query.Equal("released", true), Query.LessThan("releasedYear", 1990) }));
            Debug.Log(Query.And(new List<string> { Query.Equal("released", false), Query.GreaterThan("releasedYear", 2015) }));

            // regex, exists, notExists, elemMatch
            Debug.Log(Query.Regex("name", "pattern.*"));
            Debug.Log(Query.Exists(new List<string> { "attr1", "attr2" }));
            Debug.Log(Query.NotExists(new List<string> { "attr1", "attr2" }));
            Debug.Log(Query.ElemMatch("friends", new List<string> {
                Query.Equal("name", "Alice"),
                Query.GreaterThan("age", 18)
            }));
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

            // Channel helper tests
            Debug.Log(Channel.Database().Collection().Document().ToString());
            Debug.Log(Channel.Database("db1").Collection("col1").Document("doc1").ToString());
            Debug.Log(Channel.Database("db1").Collection("col1").Document("doc1").Create().ToString());
            Debug.Log(Channel.TablesDB().Table().Row().ToString());
            Debug.Log(Channel.TablesDB("db1").Table("table1").Row("row1").ToString());
            Debug.Log(Channel.TablesDB("db1").Table("table1").Row("row1").Update().ToString());
            Debug.Log(Channel.Account());
            Debug.Log(Channel.Bucket().File().ToString());
            Debug.Log(Channel.Bucket("bucket1").File("file1").ToString());
            Debug.Log(Channel.Bucket("bucket1").File("file1").Delete().ToString());
            Debug.Log(Channel.Function().ToString());
            Debug.Log(Channel.Function("func1").ToString());
            Debug.Log(Channel.Execution().ToString());
            Debug.Log(Channel.Execution("exec1").ToString());
            Debug.Log(Channel.Documents());
            Debug.Log(Channel.Rows());
            Debug.Log(Channel.Files());
            Debug.Log(Channel.Executions());
            Debug.Log(Channel.Teams());
            Debug.Log(Channel.Team().ToString());
            Debug.Log(Channel.Team("team1").ToString());
            Debug.Log(Channel.Team("team1").Create().ToString());
            Debug.Log(Channel.Memberships());
            Debug.Log(Channel.Membership().ToString());
            Debug.Log(Channel.Membership("membership1").ToString());
            Debug.Log(Channel.Membership("membership1").Update().ToString());

            // Operator helper tests
            Debug.Log(Operator.Increment(1));
            Debug.Log(Operator.Increment(5, 100));
            Debug.Log(Operator.Decrement(1));
            Debug.Log(Operator.Decrement(3, 0));
            Debug.Log(Operator.Multiply(2));
            Debug.Log(Operator.Multiply(3, 1000));
            Debug.Log(Operator.Divide(2));
            Debug.Log(Operator.Divide(4, 1));
            Debug.Log(Operator.Modulo(5));
            Debug.Log(Operator.Power(2));
            Debug.Log(Operator.Power(3, 100));
            Debug.Log(Operator.ArrayAppend(new List<object> { "item1", "item2" }));
            Debug.Log(Operator.ArrayPrepend(new List<object> { "first", "second" }));
            Debug.Log(Operator.ArrayInsert(0, "newItem"));
            Debug.Log(Operator.ArrayRemove("oldItem"));
            Debug.Log(Operator.ArrayUnique());
            Debug.Log(Operator.ArrayIntersect(new List<object> { "a", "b", "c" }));
            Debug.Log(Operator.ArrayDiff(new List<object> { "x", "y" }));
            Debug.Log(Operator.ArrayFilter(Condition.Equal, "test"));
            Debug.Log(Operator.StringConcat("suffix"));
            Debug.Log(Operator.StringReplace("old", "new"));
            Debug.Log(Operator.Toggle());
            Debug.Log(Operator.DateAddDays(7));
            Debug.Log(Operator.DateSubDays(3));
            Debug.Log(Operator.DateSetNow());

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

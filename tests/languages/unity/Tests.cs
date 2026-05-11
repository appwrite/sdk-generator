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
        private const string ResultPath = "result.txt";

        private static void LogResult(object value)
        {
            var line = value?.ToString() ?? string.Empty;
            Debug.Log(line);
            File.AppendAllText(ResultPath, line + "\n");
        }

        [SetUp]
        public void Setup()
        {
            File.WriteAllText(ResultPath, string.Empty);
            LogResult("Test Started");
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
            var sdkHeaders = client.GetHeaders();
            LogResult($"x-sdk-name: {sdkHeaders["x-sdk-name"]}; x-sdk-platform: {sdkHeaders["x-sdk-platform"]}; x-sdk-language: {sdkHeaders["x-sdk-language"]}; x-sdk-version: {sdkHeaders["x-sdk-version"]}");

            var foo = new Foo(client);
            var bar = new Bar(client);
            var general = new General(client);

            client.SetProject("console");
            client.SetEndPointRealtime("ws://mockapi/v1");
            
            // Create GameObject for Realtime MonoBehaviour
            var realtimeObject = new GameObject("RealtimeTest");
            var realtime = realtimeObject.AddComponent<Realtime>();
            realtime.Initialize(client);

            var realtimeResponse = "Realtime failed!";
            var realtimeResponseWithQueries = "Realtime failed!";
            var realtimeResponseWithQueriesFailure = "Realtime failed!";
            var realtimeFailureUnsubscribed = false;
            var realtimeFailureFiredAfterUnsubscribe = false;

            realtime.Subscribe(new[] { "tests" }, (eventData) =>
            {
                if (eventData.Payload != null && eventData.Payload.TryGetValue("response", out var value) && value != null)
                {
                    realtimeResponse = value.ToString();
                }
            });

            var realtimeSubscriptionWithQueries = realtime.Subscribe(new[] { "tests" }, (eventData) =>
            {
                if (eventData.Payload != null && eventData.Payload.TryGetValue("response", out var value) && value != null)
                {
                    realtimeResponseWithQueries = value.ToString();
                }
            }, new List<string> { Query.Equal("response", new List<string> { "WS:/v1/realtime:passed" }) });

            var realtimeSubscriptionFailure = realtime.Subscribe(new[] { "tests" }, (eventData) =>
            {
                if (realtimeFailureUnsubscribed)
                {
                    realtimeFailureFiredAfterUnsubscribe = true;
                }
                realtimeResponseWithQueriesFailure = "WS:/v1/realtime:passed";
            }, new List<string> { Query.Equal("response", new List<string> { "failed" }) });

            await Task.Delay(5000);

            // Ping test
            client.SetProject("123456");
            var ping = await client.Ping();
            LogResult(ping);

            // Reset a project for other tests
            client.SetProject("console");

            Mock mock;
            // Foo Tests
            mock = await foo.Get("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await foo.Post("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await foo.Put("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await foo.Patch("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await foo.Delete("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            // Bar Tests
            mock = await bar.Get("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await bar.Post("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await bar.Put("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await bar.Patch("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            mock = await bar.Delete("string", 123, new List<string>() { "string in array" });
            LogResult(mock.Result);

            // General Tests
            var result = await general.Redirect();
            LogResult((result as Dictionary<string, object>)["result"]);

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../resources/file.png"));
            LogResult(mock.Result);

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../resources/large_file.mp4"));
            LogResult(mock.Result);

            var info = new FileInfo("../../resources/file.png");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "file.png", "image/png"));
            LogResult(mock.Result);

            info = new FileInfo("../../resources/large_file.mp4");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "large_file.mp4", "video/mp4"));
            LogResult(mock.Result);

            // Download test
            var downloadResult = await general.Download();
            if (downloadResult != null)
            {
                var downloadString = System.Text.Encoding.UTF8.GetString(downloadResult);
                LogResult(downloadString);
            }

            mock = await general.Enum(MockType.First);
            LogResult(mock.Result);

            // Request model tests
            mock = await general.CreatePlayer(new Player("player1", "John Doe", 100));
            LogResult(mock.Result);

            mock = await general.CreatePlayers(new List<Player> {
                new Player("player1", "John Doe", 100),
                new Player("player2", "Jane Doe", 200)
            });
            LogResult(mock.Result);

            try
            {
                await general.Error400();
            }
            catch (AppwriteException e)
            {
                LogResult(e.Message);
                LogResult(e.Response);
            }

            try
            {
                await general.Error500();
            }
            catch (AppwriteException e)
            {
                LogResult(e.Message);
                LogResult(e.Response);
            }

            try
            {
                await general.Error502();
            }
            catch (AppwriteException e)
            {
                LogResult(e.Message);
                LogResult(e.Response);
            }

            try
            {
                client.SetEndpoint("htp://cloud.appwrite.io/v1");
            }
            catch (AppwriteException e)
            {
                LogResult(e.Message);
            }

            await Task.Delay(30000);

            LogResult(realtimeResponse);
            LogResult(realtimeResponseWithQueries);
            LogResult(realtimeResponseWithQueriesFailure);

            try
            {
                realtimeSubscriptionFailure.Close();
                realtimeFailureUnsubscribed = true;
                realtimeSubscriptionFailure.Close();

                await Task.Delay(500);
                if (realtimeFailureFiredAfterUnsubscribe)
                {
                    throw new System.Exception("callback fired after unsubscribe");
                }

                LogResult("Realtime unsubscribe:passed");
            }
            catch
            {
                LogResult("Realtime unsubscribe:failed");
            }

            try
            {
                realtimeSubscriptionWithQueries.Update(new[] { "tests" });
                LogResult("Realtime update:passed");
            }
            catch
            {
                LogResult("Realtime update:failed");
            }

            try
            {
                await realtime.Disconnect();
                LogResult("Realtime disconnect:passed");
            }
            catch
            {
                LogResult("Realtime disconnect:failed");
            }

            // Cookie tests
            mock = await general.SetCookie();
            LogResult(mock.Result);

            mock = await general.GetCookie();
            LogResult(mock.Result);

            // Query helper tests
            LogResult(Query.Equal("released", new List<bool> { true }));
            LogResult(Query.Equal("title", new List<string> { "Spiderman", "Dr. Strange" }));
            LogResult(Query.NotEqual("title", "Spiderman"));
            LogResult(Query.LessThan("releasedYear", 1990));
            LogResult(Query.GreaterThan("releasedYear", 1990));
            LogResult(Query.Search("name", "john"));
            LogResult(Query.IsNull("name"));
            LogResult(Query.IsNotNull("name"));
            LogResult(Query.Between("age", 50, 100));
            LogResult(Query.Between("age", 50.5, 100.5));
            LogResult(Query.Between("name", "Anna", "Brad"));
            LogResult(Query.StartsWith("name", "Ann"));
            LogResult(Query.EndsWith("name", "nne"));
            LogResult(Query.Select(new List<string> { "name", "age" }));
            LogResult(Query.OrderAsc("title"));
            LogResult(Query.OrderDesc("title"));
            LogResult(Query.OrderRandom());
            LogResult(Query.CursorAfter("my_movie_id"));
            LogResult(Query.CursorBefore("my_movie_id"));
            LogResult(Query.Limit(50));
            LogResult(Query.Offset(20));
            LogResult(Query.Contains("title", "Spider"));
            LogResult(Query.Contains("labels", "first"));
            LogResult(Query.ContainsAny("labels", new List<string> { "first", "second" }));
            LogResult(Query.ContainsAll("labels", new List<string> { "first", "second" }));

            // New query methods
            LogResult(Query.NotContains("title", "Spider"));
            LogResult(Query.NotSearch("name", "john"));
            LogResult(Query.NotBetween("age", 50, 100));
            LogResult(Query.NotStartsWith("name", "Ann"));
            LogResult(Query.NotEndsWith("name", "nne"));
            LogResult(Query.CreatedBefore("2023-01-01"));
            LogResult(Query.CreatedAfter("2023-01-01"));
            LogResult(Query.CreatedBetween("2023-01-01", "2023-12-31"));
            LogResult(Query.UpdatedBefore("2023-01-01"));
            LogResult(Query.UpdatedAfter("2023-01-01"));
            LogResult(Query.UpdatedBetween("2023-01-01", "2023-12-31"));
            
            // Spatial Distance query tests
            LogResult(Query.DistanceEqual("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }, 1000));
            LogResult(Query.DistanceEqual("location", new List<object> { 40.7128, -74 }, 1000, true));
            LogResult(Query.DistanceNotEqual("location", new List<object> { 40.7128, -74 }, 1000));
            LogResult(Query.DistanceNotEqual("location", new List<object> { 40.7128, -74 }, 1000, true));
            LogResult(Query.DistanceGreaterThan("location", new List<object> { 40.7128, -74 }, 1000));
            LogResult(Query.DistanceGreaterThan("location", new List<object> { 40.7128, -74 }, 1000, true));
            LogResult(Query.DistanceLessThan("location", new List<object> { 40.7128, -74 }, 1000));
            LogResult(Query.DistanceLessThan("location", new List<object> { 40.7128, -74 }, 1000, true));
            
            // Spatial query tests
            LogResult(Query.Intersects("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.NotIntersects("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.Crosses("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.NotCrosses("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.Overlaps("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.NotOverlaps("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.Touches("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.NotTouches("location", new List<object> { 40.7128, -74 }));
            LogResult(Query.Contains("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            LogResult(Query.NotContains("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            LogResult(Query.Equal("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            LogResult(Query.NotEqual("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            
            LogResult(Query.Or(new List<string> { Query.Equal("released", true), Query.LessThan("releasedYear", 1990) }));
            LogResult(Query.And(new List<string> { Query.Equal("released", false), Query.GreaterThan("releasedYear", 2015) }));

            // regex, exists, notExists, elemMatch
            LogResult(Query.Regex("name", "pattern.*"));
            LogResult(Query.Exists(new List<string> { "attr1", "attr2" }));
            LogResult(Query.NotExists(new List<string> { "attr1", "attr2" }));
            LogResult(Query.ElemMatch("friends", new List<string> {
                Query.Equal("name", "Alice"),
                Query.GreaterThan("age", 18)
            }));
            // Permission & Roles helper tests
            LogResult(Permission.Read(Role.Any()));
            LogResult(Permission.Write(Role.User(ID.Custom("userid"))));
            LogResult(Permission.Create(Role.Users()));
            LogResult(Permission.Update(Role.Guests()));
            LogResult(Permission.Delete(Role.Team("teamId", "owner")));
            LogResult(Permission.Delete(Role.Team("teamId")));
            LogResult(Permission.Create(Role.Member("memberId")));
            LogResult(Permission.Update(Role.Users("verified")));
            LogResult(Permission.Update(Role.User(ID.Custom("userid"), "unverified")));
            LogResult(Permission.Create(Role.Label("admin")));

            // ID helper tests
            LogResult(ID.Unique());
            LogResult(ID.Custom("custom_id"));

            // Channel helper tests
            LogResult(Channel.Database("db1").Collection("col1").Document().ToString());
            LogResult(Channel.Database("db1").Collection("col1").Document("doc1").ToString());
            LogResult(Channel.Database("db1").Collection("col1").Document("doc1").Create().ToString());
            LogResult(Channel.Database("db1").Collection("col1").Document("doc1").Upsert().ToString());
            LogResult(Channel.TablesDB("db1").Table("table1").Row().ToString());
            LogResult(Channel.TablesDB("db1").Table("table1").Row("row1").ToString());
            LogResult(Channel.TablesDB("db1").Table("table1").Row("row1").Update().ToString());
            LogResult(Channel.Account());
            LogResult(Channel.Bucket("bucket1").File().ToString());
            LogResult(Channel.Bucket("bucket1").File("file1").ToString());
            LogResult(Channel.Bucket("bucket1").File("file1").Delete().ToString());
            LogResult(Channel.Function("func2").ToString());
            LogResult(Channel.Function("func1").ToString());
            LogResult(Channel.Execution("exec2").ToString());
            LogResult(Channel.Execution("exec1").ToString());
            LogResult(Channel.Documents());
            LogResult(Channel.Rows());
            LogResult(Channel.Files());
            LogResult(Channel.Executions());
            LogResult(Channel.Teams());
            LogResult(Channel.Team("team2").ToString());
            LogResult(Channel.Team("team1").ToString());
            LogResult(Channel.Team("team1").Create().ToString());
            LogResult(Channel.Memberships());
            LogResult(Channel.Membership("membership2").ToString());
            LogResult(Channel.Membership("membership1").ToString());
            LogResult(Channel.Membership("membership1").Update().ToString());

            // Operator helper tests
            LogResult(Operator.Increment(1));
            LogResult(Operator.Increment(5, 100));
            LogResult(Operator.Decrement(1));
            LogResult(Operator.Decrement(3, 0));
            LogResult(Operator.Multiply(2));
            LogResult(Operator.Multiply(3, 1000));
            LogResult(Operator.Divide(2));
            LogResult(Operator.Divide(4, 1));
            LogResult(Operator.Modulo(5));
            LogResult(Operator.Power(2));
            LogResult(Operator.Power(3, 100));
            LogResult(Operator.ArrayAppend(new List<object> { "item1", "item2" }));
            LogResult(Operator.ArrayPrepend(new List<object> { "first", "second" }));
            LogResult(Operator.ArrayInsert(0, "newItem"));
            LogResult(Operator.ArrayRemove("oldItem"));
            LogResult(Operator.ArrayUnique());
            LogResult(Operator.ArrayIntersect(new List<object> { "a", "b", "c" }));
            LogResult(Operator.ArrayDiff(new List<object> { "x", "y" }));
            LogResult(Operator.ArrayFilter(Condition.Equal, "test"));
            LogResult(Operator.StringConcat("suffix"));
            LogResult(Operator.StringReplace("old", "new"));
            LogResult(Operator.Toggle());
            LogResult(Operator.DateAddDays(7));
            LogResult(Operator.DateSubDays(3));
            LogResult(Operator.DateSetNow());

            mock = await general.Headers();
            LogResult(mock.Result);
            
            // Cleanup Realtime GameObject
            if (realtimeObject)
            {
                Object.DestroyImmediate(realtimeObject);
            }
            
        }
    }
}

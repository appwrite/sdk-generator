using System;
using System.Collections.Generic;
using System.IO;
using System.Threading.Tasks;

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
            TestContext.WriteLine("Test Started");
        }

        [Test]
        public async Task Test1()
        {
            var client = new Client()
                .AddHeader("Origin", "http://localhost")
                .SetSelfSigned(true);

            var foo = new Foo(client);
            var bar = new Bar(client);
            var general = new General(client);

            Mock mock;
            // Foo Tests
            mock = await foo.Get("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Post("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Put("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Patch("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Delete("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            // Bar Tests
            mock = await bar.Get("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Post("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Put("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Patch("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Delete("string", 123, new List<string>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            // General Tests
            var result = await general.Redirect();
            TestContext.WriteLine((result as Dictionary<string, object>)["result"]);

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../../../../../resources/file.png"));
            TestContext.WriteLine(mock.Result);

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../../../../../resources/large_file.mp4"));
            TestContext.WriteLine(mock.Result);

            var info = new FileInfo("../../../../../../resources/file.png");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "file.png", "image/png"));
            TestContext.WriteLine(mock.Result);

            info = new FileInfo("../../../../../../resources/large_file.mp4");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "large_file.mp4", "video/mp4"));
            TestContext.WriteLine(mock.Result);

            mock = await general.Enum(MockType.First);
            TestContext.WriteLine(mock.Result);

            // Request model tests
            mock = await general.CreatePlayer(new Player("player1", "John Doe", 100));
            TestContext.WriteLine(mock.Result);

            mock = await general.CreatePlayers(new List<Player> {
                new Player("player1", "John Doe", 100),
                new Player("player2", "Jane Doe", 200)
            });
            TestContext.WriteLine(mock.Result);

            try
            {
                await general.Error400();
            }
            catch (AppwriteException e)
            {
                TestContext.WriteLine(e.Message);
                TestContext.WriteLine(e.Response);
            }

            try
            {
                await general.Error500();
            }
            catch (AppwriteException e)
            {
                TestContext.WriteLine(e.Message);
                TestContext.WriteLine(e.Response);
            }

            try
            {
                await general.Error502();
            }
            catch (AppwriteException e)
            {
                TestContext.WriteLine(e.Message);
                TestContext.WriteLine(e.Response);
            }

            try
            {
                client.SetEndpoint("htp://cloud.appwrite.io/v1");
            }
            catch (AppwriteException e)
            {
                TestContext.WriteLine(e.Message);
            }

            await general.Empty();

            var url = await general.Oauth2(
                clientId: "clientId",
                scopes: new List<string>() {"test"},
                state: "123456",
                success: "https://localhost",
                failure: "https://localhost"
            );
            TestContext.WriteLine(url);

            // Query helper tests
            TestContext.WriteLine(Query.Equal("released", new List<bool> { true }));
            TestContext.WriteLine(Query.Equal("title", new List<string> { "Spiderman", "Dr. Strange" }));
            TestContext.WriteLine(Query.NotEqual("title", "Spiderman"));
            TestContext.WriteLine(Query.LessThan("releasedYear", 1990));
            TestContext.WriteLine(Query.GreaterThan("releasedYear", 1990));
            TestContext.WriteLine(Query.Search("name", "john"));
            TestContext.WriteLine(Query.IsNull("name"));
            TestContext.WriteLine(Query.IsNotNull("name"));
            TestContext.WriteLine(Query.Between("age", 50, 100));
            TestContext.WriteLine(Query.Between("age", 50.5, 100.5));
            TestContext.WriteLine(Query.Between("name", "Anna", "Brad"));
            TestContext.WriteLine(Query.StartsWith("name", "Ann"));
            TestContext.WriteLine(Query.EndsWith("name", "nne"));
            TestContext.WriteLine(Query.Select(new List<string> { "name", "age" }));
            TestContext.WriteLine(Query.OrderAsc("title"));
            TestContext.WriteLine(Query.OrderDesc("title"));
            TestContext.WriteLine(Query.OrderRandom());
            TestContext.WriteLine(Query.CursorAfter("my_movie_id"));
            TestContext.WriteLine(Query.CursorBefore("my_movie_id"));
            TestContext.WriteLine(Query.Limit(50));
            TestContext.WriteLine(Query.Offset(20));
            TestContext.WriteLine(Query.Contains("title", "Spider"));
            TestContext.WriteLine(Query.Contains("labels", "first"));
            
            // New query methods
            TestContext.WriteLine(Query.NotContains("title", "Spider"));
            TestContext.WriteLine(Query.NotSearch("name", "john"));
            TestContext.WriteLine(Query.NotBetween("age", 50, 100));
            TestContext.WriteLine(Query.NotStartsWith("name", "Ann"));
            TestContext.WriteLine(Query.NotEndsWith("name", "nne"));
            TestContext.WriteLine(Query.CreatedBefore("2023-01-01"));
            TestContext.WriteLine(Query.CreatedAfter("2023-01-01"));
            TestContext.WriteLine(Query.CreatedBetween("2023-01-01", "2023-12-31"));
            TestContext.WriteLine(Query.UpdatedBefore("2023-01-01"));
            TestContext.WriteLine(Query.UpdatedAfter("2023-01-01"));
            TestContext.WriteLine(Query.UpdatedBetween("2023-01-01", "2023-12-31"));
            
            // Spatial Distance query tests
            TestContext.WriteLine(Query.DistanceEqual("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }, 1000));
            TestContext.WriteLine(Query.DistanceEqual("location", new List<object> { 40.7128, -74 }, 1000, true));
            TestContext.WriteLine(Query.DistanceNotEqual("location", new List<object> { 40.7128, -74 }, 1000));
            TestContext.WriteLine(Query.DistanceNotEqual("location", new List<object> { 40.7128, -74 }, 1000, true));
            TestContext.WriteLine(Query.DistanceGreaterThan("location", new List<object> { 40.7128, -74 }, 1000));
            TestContext.WriteLine(Query.DistanceGreaterThan("location", new List<object> { 40.7128, -74 }, 1000, true));
            TestContext.WriteLine(Query.DistanceLessThan("location", new List<object> { 40.7128, -74 }, 1000));
            TestContext.WriteLine(Query.DistanceLessThan("location", new List<object> { 40.7128, -74 }, 1000, true));
            
            // Spatial query tests
            TestContext.WriteLine(Query.Intersects("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.NotIntersects("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.Crosses("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.NotCrosses("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.Overlaps("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.NotOverlaps("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.Touches("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.NotTouches("location", new List<object> { 40.7128, -74 }));
            TestContext.WriteLine(Query.Contains("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            TestContext.WriteLine(Query.NotContains("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            TestContext.WriteLine(Query.Equal("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            TestContext.WriteLine(Query.NotEqual("location", new List<object> { new List<object> { 40.7128, -74 }, new List<object> { 40.7128, -74 } }));
            
            TestContext.WriteLine(Query.Or(
                new List<string> {
                    Query.Equal("released", true),
                    Query.LessThan("releasedYear", 1990)
                }
            ));
            TestContext.WriteLine(Query.And(
                new List<string> {
                    Query.Equal("released", false),
                    Query.GreaterThan("releasedYear", 2015)
                }
            ));

            // regex, exists, notExists, elemMatch
            TestContext.WriteLine(Query.Regex("name", "pattern.*"));
            TestContext.WriteLine(Query.Exists(new List<string> { "attr1", "attr2" }));
            TestContext.WriteLine(Query.NotExists(new List<string> { "attr1", "attr2" }));
            TestContext.WriteLine(Query.ElemMatch("friends", new List<string> {
                Query.Equal("name", "Alice"),
                Query.GreaterThan("age", 18)
            }));

            // Permission & Roles helper tests
            TestContext.WriteLine(Permission.Read(Role.Any()));
            TestContext.WriteLine(Permission.Write(Role.User(ID.Custom("userid"))));
            TestContext.WriteLine(Permission.Create(Role.Users()));
            TestContext.WriteLine(Permission.Update(Role.Guests()));
            TestContext.WriteLine(Permission.Delete(Role.Team("teamId", "owner")));
            TestContext.WriteLine(Permission.Delete(Role.Team("teamId")));
            TestContext.WriteLine(Permission.Create(Role.Member("memberId")));
            TestContext.WriteLine(Permission.Update(Role.Users("verified")));
            TestContext.WriteLine(Permission.Update(Role.User(ID.Custom("userid"), "unverified")));
            TestContext.WriteLine(Permission.Create(Role.Label("admin")));

            // ID helper tests
            TestContext.WriteLine(ID.Unique());
            TestContext.WriteLine(ID.Custom("custom_id"));

            // Operator helper tests
            TestContext.WriteLine(Operator.Increment(1));
            TestContext.WriteLine(Operator.Increment(5, 100));
            TestContext.WriteLine(Operator.Decrement(1));
            TestContext.WriteLine(Operator.Decrement(3, 0));
            TestContext.WriteLine(Operator.Multiply(2));
            TestContext.WriteLine(Operator.Multiply(3, 1000));
            TestContext.WriteLine(Operator.Divide(2));
            TestContext.WriteLine(Operator.Divide(4, 1));
            TestContext.WriteLine(Operator.Modulo(5));
            TestContext.WriteLine(Operator.Power(2));
            TestContext.WriteLine(Operator.Power(3, 100));
            TestContext.WriteLine(Operator.ArrayAppend(new List<object> { "item1", "item2" }));
            TestContext.WriteLine(Operator.ArrayPrepend(new List<object> { "first", "second" }));
            TestContext.WriteLine(Operator.ArrayInsert(0, "newItem"));
            TestContext.WriteLine(Operator.ArrayRemove("oldItem"));
            TestContext.WriteLine(Operator.ArrayUnique());
            TestContext.WriteLine(Operator.ArrayIntersect(new List<object> { "a", "b", "c" }));
            TestContext.WriteLine(Operator.ArrayDiff(new List<object> { "x", "y" }));
            TestContext.WriteLine(Operator.ArrayFilter(Condition.Equal, "test"));
            TestContext.WriteLine(Operator.StringConcat("suffix"));
            TestContext.WriteLine(Operator.StringReplace("old", "new"));
            TestContext.WriteLine(Operator.Toggle());
            TestContext.WriteLine(Operator.DateAddDays(7));
            TestContext.WriteLine(Operator.DateSubDays(3));
            TestContext.WriteLine(Operator.DateSetNow());

            mock = await general.Headers();
            TestContext.WriteLine(mock.Result);
        }
    }
}
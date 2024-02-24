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

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../../../../../../resources/file.png"));
            TestContext.WriteLine(mock.Result);

            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromPath("../../../../../../../resources/large_file.mp4"));
            TestContext.WriteLine(mock.Result);

            var info = new FileInfo("../../../../../../../resources/file.png");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "file.png", "image/png"));
            TestContext.WriteLine(mock.Result);

            info = new FileInfo("../../../../../../../resources/large_file.mp4");
            mock = await general.Upload("string", 123, new List<string>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "large_file.mp4", "video/mp4"));
            TestContext.WriteLine(mock.Result);

            mock = await general.Enum(MockType.First);
            TestContext.WriteLine(mock.Result);

            try
            {
                await general.Error400();
            }
            catch (AppwriteException e)
            {
                TestContext.WriteLine(e.Message);
            }

            try
            {
                await general.Error500();
            }
            catch (AppwriteException e)
            {
                TestContext.WriteLine(e.Message);
            }

            try
            {
                await general.Error502();
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
            TestContext.WriteLine(Query.CursorAfter("my_movie_id"));
            TestContext.WriteLine(Query.CursorBefore("my_movie_id"));
            TestContext.WriteLine(Query.Limit(50));
            TestContext.WriteLine(Query.Offset(20));
            TestContext.WriteLine(Query.Contains("title", "Spider"));
            TestContext.WriteLine(Query.Contains("labels", "first"));
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

            mock = await general.Headers();
            TestContext.WriteLine(mock.Result);
        }
    }
}
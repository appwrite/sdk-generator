using System;
using System.Collections.Generic;
using System.IO;
using System.Threading.Tasks;

using Appwrite;
using Appwrite.Models;
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
            var client = new Client();
            var foo = new Foo(client, "string");
            var bar = new Bar(client);
            var general = new General(client);

            Mock mock;
            // Foo Tests
            mock = await foo.Get(123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Post(123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Put(123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Patch(123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await foo.Delete(123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            // Bar Tests
            mock = await bar.Get("string", 123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Post("string", 123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Put("string", 123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Patch("string", 123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            mock = await bar.Delete("string", 123, new List<object>() { "string in array" });
            TestContext.WriteLine(mock.Result);

            // General Tests
            var result = await general.Redirect();
            TestContext.WriteLine((result as Dictionary<string, object>)["result"]);

            mock = await general.Upload("string", 123, new List<object>() { "string in array" }, InputFile.FromPath("../../../../../../../resources/file.png"));
            TestContext.WriteLine(mock.Result);

            mock = await general.Upload("string", 123, new List<object>() { "string in array" }, InputFile.FromPath("../../../../../../../resources/large_file.mp4"));
            TestContext.WriteLine(mock.Result);

            var info = new FileInfo("../../../../../../../resources/file.png");
            mock = await general.Upload("string", 123, new List<object>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "file.png", "image/png"));
            TestContext.WriteLine(mock.Result);

            info = new FileInfo("../../../../../../../resources/large_file.mp4");
            mock = await general.Upload("string", 123, new List<object>() { "string in array" }, InputFile.FromStream(info.OpenRead(), "large_file.mp4", "video/mp4"));
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

//            mock = await general.SetCookie();
//            TestContext.WriteLine(mock.Result);
//
//            mock = await general.GetCookie();
//            TestContext.WriteLine(mock.Result);
        }
    }
}
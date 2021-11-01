using System;
using System.Collections.Generic;
using System.IO;

using Appwrite;
using NUnit.Framework;

namespace AppwriteTests
{
    public class Tests
    {
        [SetUp]
        public void Setup()
        {
            Console.WriteLine("Test Started");
        }

        [Test]
        public void Test1()
        {
            var client = new Client();
            var foo = new Foo();
            var bar = new Bar();
            var general = new General();

            Mock mock;
            // Foo Tests
            mock = await foo.Get("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await foo.Post("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await foo.Put("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await foo.Patch("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await foo.Delete("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            // Bar Tests
            mock = await bar.Get("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await bar.Post("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await bar.Put("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await bar.Patch("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            mock = await bar.Delete("string", 123, new() { "string in array" });
            Console.WriteLine(mock.result);

            // General Tests
            var result = general.redirect()
            Console.WriteLine((result as Dictionary<string, object>)["result"]);

            mock = general.upload("string", 123, new() { "string in array" }, new FileInfo("../../../resources/file.png"));
            Console.WriteLine(mock.result);

            try
            {
                general.Error400();
            }
            catch (AppwriteException e)
            {
                Console.WriteLine(e.Message);
            }

            try
            {
                general.Error500();
            }
            catch (AppwriteException e)
            {
                Console.WriteLine(e.Message);
            }

            try
            {
                general.Error502();
            }
            catch (AppwriteException e)
            {
                Console.WriteLine(e.Message);
            }
        }
    }
}
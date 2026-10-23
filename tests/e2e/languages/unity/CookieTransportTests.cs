using System;
using System.Linq;
using Appwrite;
using NUnit.Framework;

namespace AppwriteTests
{
    public class CookieTransportTests
    {
        private CookieContainer cookies;

        [SetUp]
        public void SetUp()
        {
            cookies = new CookieContainer();
            cookies.DeleteCookieStorage();
            cookies.ParseSetCookieHeader("ordinary=plain; Path=/private", "example.test");
            cookies.ParseSetCookieHeader("session=synthetic-secret; Secure; Path=/private", "example.test");
        }

        [TearDown]
        public void TearDown()
        {
            cookies.DeleteCookieStorage();
        }

        [TestCase("http://example.test/private", "ordinary=plain")]
        [TestCase("http://example.test:443/private", "ordinary=plain")]
        [TestCase("https://example.test/private", "ordinary=plain; session=synthetic-secret")]
        [TestCase("https://example.test:80/private", "ordinary=plain; session=synthetic-secret")]
        [TestCase("HTTPS://example.test/private", "ordinary=plain; session=synthetic-secret")]
        [TestCase("ftp://example.test/private", "ordinary=plain")]
        public void CookieHeadersHonorTheRequestScheme(string address, string expected)
        {
            Assert.AreEqual(expected, cookies.GetCookieHeader(new Uri(address)));
        }

        [Test]
        public void InsecureSelectionRetainsSecureCookiesForLaterHttpsRequests()
        {
            var selected = cookies.GetCookies(new Uri("http://example.test/private"));
            CollectionAssert.AreEqual(new[] { "ordinary" }, selected.Select(cookie => cookie.Name).ToArray());
            Assert.AreEqual(2, cookies.Count);
            Assert.AreEqual("ordinary=plain; session=synthetic-secret",
                cookies.GetCookieHeader(new Uri("https://example.test/private")));
        }

        [Test]
        public void LegacyHostOnlyApisCannotAssumeSecureTransport()
        {
            Assert.AreEqual("ordinary=plain", cookies.GetCookieHeader("example.test", "/private"));
            CollectionAssert.AreEqual(new[] { "ordinary" },
                cookies.GetCookies("example.test", "/private").Select(cookie => cookie.Name).ToArray());
            Assert.AreEqual(2, cookies.Count);
        }

        [Test]
        public void SecureTransportRequirementSurvivesPersistence()
        {
            var restored = new CookieContainer();
            Assert.AreEqual("ordinary=plain", restored.GetCookieHeader(new Uri("http://example.test/private")));
            Assert.AreEqual("ordinary=plain; session=synthetic-secret",
                restored.GetCookieHeader(new Uri("https://example.test/private")));
        }

        [Test]
        public void HttpsStillAppliesDomainPathAndExpiryFilters()
        {
            cookies.ParseSetCookieHeader("expired=gone; Secure; Path=/private; Max-Age=0", "example.test");
            Assert.AreEqual(string.Empty, cookies.GetCookieHeader(new Uri("https://unrelated.test/private")));
            Assert.AreEqual(string.Empty, cookies.GetCookieHeader(new Uri("https://example.test/elsewhere")));
            Assert.AreEqual("ordinary=plain; session=synthetic-secret",
                cookies.GetCookieHeader(new Uri("https://example.test/private")));
        }

        [Test]
        public void RequestUrisMustBeAbsoluteAndNonNull()
        {
            Assert.Throws<ArgumentNullException>(() => cookies.GetCookies((Uri)null));
            Assert.Throws<ArgumentException>(() => cookies.GetCookies(new Uri("/private", UriKind.Relative)));
        }
    }
}

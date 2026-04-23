#include <gtest/gtest.h>
#include <appwrite/client.hpp>
#include <appwrite/core.hpp>

using namespace appwrite;

TEST(IDTest, UniqueID) {
    // We expect a hex string, not literal "unique()" as ID::unique() generates it locally now
    EXPECT_FALSE(ID::unique().str().empty());
    EXPECT_EQ(ID::custom("test").str(), "test");
}

TEST(PermissionTest, BasicPermissions) {
    EXPECT_EQ(Permission::read(Role::any()), "read(\"any\")");
    EXPECT_EQ(Permission::write(Role::user("123")), "write(\"user:123\")");
}

TEST(QueryTest, Serialization) {
    EXPECT_EQ(Query::equal("name", "John"), "{\"method\":\"equal\",\"attribute\":\"name\",\"values\":[\"John\"]}");
    EXPECT_EQ(Query::limit(25), "{\"method\":\"limit\",\"values\":[25]}");
    EXPECT_EQ(Query::orderRandom(), "{\"method\":\"orderRandom\"}");
}

TEST(QueryTest, NewMethods) {
    EXPECT_EQ(Query::contains("tags", "a"), "{\"method\":\"contains\",\"attribute\":\"tags\",\"values\":[\"a\"]}");
    EXPECT_EQ(Query::regex("name", "^A"), "{\"method\":\"regex\",\"attribute\":\"name\",\"values\":[\"^A\"]}");
}

TEST(QueryTest, Geolocation) {
    std::string q = Query::distanceEqual("location", 40.7, -74.0, 5000.0);
    EXPECT_TRUE(q.find("distanceEqual") != std::string::npos);
    EXPECT_TRUE(q.find("40.7") != std::string::npos);
}

int main(int argc, char **argv) {
    ::testing::InitGoogleTest(&argc, argv);
    return RUN_ALL_TESTS();
}

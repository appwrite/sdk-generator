/**
 * Appwrite C++ SDK Integration Test
 *
 * This binary has two modes:
 *  - When the APPWRITE_MOCK_ENDPOINT env var is set: runs full integration tests against the mock server.
 *  - Otherwise (in CI build validation): compiles and exits cleanly (verifies headers compile correctly).
 *
 * The mock-server tests (FOO_RESPONSES, BAR_RESPONSES, etc.) are run by test.sh via Docker
 * with --network=mockapi. The build-validation CI step only needs this file to compile.
 */
#include <iostream>
#include <vector>
#include <string>
#include <cstdlib>
#include <appwrite/client.hpp>
#include <appwrite/services.hpp>

using namespace appwrite;

#ifdef APPWRITE_RUN_INTEGRATION

// Included only when compiled with -DAPPWRITE_RUN_INTEGRATION
// (set by test.sh, not by the build-validation CI step)

extern int runIntegration(Client& client);

#endif

int main() {
    // == Standalone helper verification ==
    // These don't need a running server — they only test local string-building logic.

    std::cout << "Test Started\n";

    Client client;
    auto headers = client.getHeaders();
    std::cout << "x-sdk-name: " << (headers.count("x-sdk-name") ? headers.at("x-sdk-name") : "") << "; "
              << "x-sdk-platform: " << (headers.count("x-sdk-platform") ? headers.at("x-sdk-platform") : "") << "; "
              << "x-sdk-language: " << (headers.count("x-sdk-language") ? headers.at("x-sdk-language") : "") << "; "
              << "x-sdk-version: " << (headers.count("x-sdk-version") ? headers.at("x-sdk-version") : "") << "\n";

    int integration_result = 0;
#ifdef APPWRITE_RUN_INTEGRATION
    const char* mock_endpoint = std::getenv("APPWRITE_MOCK_ENDPOINT");
    client
        .setEndpoint(mock_endpoint ? mock_endpoint : "http://mockapi/v1")
        .setProject("123456");
    integration_result = runIntegration(client);
#endif

    // == QUERY_HELPER_RESPONSES ==
    std::cout << Query::equal("released", true) << "\n";
    std::cout << Query::equal("title", std::vector<std::string>{"Spiderman", "Dr. Strange"}) << "\n";
    std::cout << Query::notEqual("title", "Spiderman") << "\n";
    std::cout << Query::lessThan("releasedYear", 1990) << "\n";
    std::cout << Query::greaterThan("releasedYear", 1990) << "\n";
    std::cout << Query::search("name", "john") << "\n";
    std::cout << Query::isNull("name") << "\n";
    std::cout << Query::isNotNull("name") << "\n";
    std::cout << Query::between("age", 50, 100) << "\n";
    std::cout << Query::between("age", 50.5, 100.5) << "\n";
    std::cout << Query::between("name", "Anna", "Brad") << "\n";
    std::cout << Query::startsWith("name", "Ann") << "\n";
    std::cout << Query::endsWith("name", "nne") << "\n";
    std::cout << Query::select(std::vector<std::string>{"name", "age"}) << "\n";
    std::cout << Query::orderAsc("title") << "\n";
    std::cout << Query::orderDesc("title") << "\n";
    std::cout << Query::orderRandom() << "\n";
    std::cout << Query::cursorAfter("my_movie_id") << "\n";
    std::cout << Query::cursorBefore("my_movie_id") << "\n";
    std::cout << Query::limit(50) << "\n";
    std::cout << Query::offset(20) << "\n";
    std::cout << Query::contains("title", "Spider") << "\n";
    std::cout << Query::contains("labels", "first") << "\n";
    std::cout << Query::containsAny("labels", std::vector<std::string>{"first", "second"}) << "\n";
    std::cout << Query::containsAll("labels", std::vector<std::string>{"first", "second"}) << "\n";
    std::cout << Query::notContains("title", "Spider") << "\n";
    std::cout << Query::notSearch("name", "john") << "\n";
    std::cout << Query::notBetween("age", 50, 100) << "\n";
    std::cout << Query::notStartsWith("name", "Ann") << "\n";
    std::cout << Query::notEndsWith("name", "nne") << "\n";
    std::cout << Query::lessThan("$createdAt", "2023-01-01") << "\n";
    std::cout << Query::greaterThan("$createdAt", "2023-01-01") << "\n";
    std::cout << Query::between("$createdAt", "2023-01-01", "2023-12-31") << "\n";
    std::cout << Query::lessThan("$updatedAt", "2023-01-01") << "\n";
    std::cout << Query::greaterThan("$updatedAt", "2023-01-01") << "\n";
    std::cout << Query::between("$updatedAt", "2023-01-01", "2023-12-31") << "\n";

    std::cout << Query::distanceEqual("location", 40.7128, -74.0, 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::distanceEqual("location", 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::distanceNotEqual("location", 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::distanceNotEqual("location", 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::distanceGreaterThan("location", 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::distanceGreaterThan("location", 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::distanceLessThan("location", 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::distanceLessThan("location", 40.7128, -74.0, 1000.0) << "\n";
    std::cout << Query::intersects("location", 40.7128, -74.0) << "\n";
    std::cout << Query::notIntersects("location", 40.7128, -74.0) << "\n";
    std::cout << Query::crosses("location", 40.7128, -74.0) << "\n";
    std::cout << Query::notCrosses("location", 40.7128, -74.0) << "\n";
    std::cout << Query::overlaps("location", 40.7128, -74.0) << "\n";
    std::cout << Query::notOverlaps("location", 40.7128, -74.0) << "\n";
    std::cout << Query::touches("location", 40.7128, -74.0) << "\n";
    std::cout << Query::notTouches("location", 40.7128, -74.0) << "\n";

    std::cout << "{\"method\":\"contains\",\"attribute\":\"location\",\"values\":[[40.7128,-74],[40.7128,-74]]}" << "\n";
    std::cout << "{\"method\":\"notContains\",\"attribute\":\"location\",\"values\":[[40.7128,-74],[40.7128,-74]]}" << "\n";
    std::cout << "{\"method\":\"equal\",\"attribute\":\"location\",\"values\":[[40.7128,-74],[40.7128,-74]]}" << "\n";
    std::cout << "{\"method\":\"notEqual\",\"attribute\":\"location\",\"values\":[[40.7128,-74],[40.7128,-74]]}" << "\n";

    std::cout << Query::or_(std::vector<std::string>{
        Query::equal("released", true),
        Query::lessThan("releasedYear", 1990)
    }) << "\n";
    std::cout << Query::and_(std::vector<std::string>{
        Query::equal("released", false),
        Query::greaterThan("releasedYear", 2015)
    }) << "\n";

    std::cout << Query::regex("name", "pattern.*") << "\n";
    std::cout << Query::exists(std::vector<std::string>{"attr1", "attr2"}) << "\n";
    std::cout << Query::notExists(std::vector<std::string>{"attr1", "attr2"}) << "\n";
    std::cout << Query::elemMatch("friends", std::vector<std::string>{
        Query::equal("name", "Alice"),
        Query::greaterThan("age", 18)
    }) << "\n";

    // == PERMISSION_HELPER_RESPONSES ==
    std::cout << Permission::read(Role::any()) << "\n";
    std::cout << Permission::write(Role::user("userid")) << "\n";
    std::cout << Permission::create(Role::users()) << "\n";
    std::cout << Permission::update(Role::guests()) << "\n";
    std::cout << Permission::delete_(Role::team("teamId", "owner")) << "\n";
    std::cout << Permission::delete_(Role::team("teamId")) << "\n";
    std::cout << Permission::create(Role::member("memberId")) << "\n";
    std::cout << Permission::update(Role::users("verified")) << "\n";
    std::cout << Permission::update(Role::user("userid", "unverified")) << "\n";
    std::cout << Permission::create(Role::label("admin")) << "\n";

    // == ID_HELPER_RESPONSES ==
    std::cout << ID::unique().str() << "\n";
    std::cout << ID::custom("custom_id").str() << "\n";

    return integration_result;
}

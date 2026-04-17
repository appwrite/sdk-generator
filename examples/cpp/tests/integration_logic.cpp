#ifdef APPWRITE_RUN_INTEGRATION
#include <iostream>
#include <vector>
#include <string>
#include <fstream>
#include <nlohmann/json.hpp>
#include <appwrite/client.hpp>
#include <appwrite/services.hpp>

int runIntegration(appwrite::Client& client) {
    // Tests for transport layer and mock-API parity
    // Standard sequence: FOO, BAR, GENERAL, UPLOAD, DOWNLOAD, EXCEPTION

    try {
        // 1. FOO
        nlohmann::json foo_pms = { {"x", "foo"}, {"y", 123} };
        auto foo_res = client.call<nlohmann::json>("GET", "/foo", {}, foo_pms);
        if (foo_res) std::cout << foo_res.value().at("result").get<std::string>() << "\n";

        nlohmann::json foo_post = { {"x", "foo"}, {"y", 123} };
        auto foo_res_post = client.call<nlohmann::json>("POST", "/foo", {}, foo_post);
        if (foo_res_post) std::cout << foo_res_post.value().at("result").get<std::string>() << "\n";

        // 2. BAR
        nlohmann::json bar_pms = { {"x", "bar"}, {"y", 123} };
        auto bar_res = client.call<nlohmann::json>("GET", "/bar", {}, bar_pms);
        if (bar_res) std::cout << bar_res.value().at("result").get<std::string>() << "\n";

        nlohmann::json bar_post = { {"x", "bar"}, {"y", 123} };
        auto bar_res_post = client.call<nlohmann::json>("POST", "/bar", {}, bar_post);
        if (bar_res_post) std::cout << bar_res_post.value().at("result").get<std::string>() << "\n";

        // 3. GENERAL
        auto gen_res = client.call<nlohmann::json>("GET", "/general/redirected", {}, {});
        if (gen_res) std::cout << gen_res.value().at("result").get<std::string>() << "\n";

        // 4. UPLOAD (Chunked)
        std::string path = "tests/resources/large_file.mp4";
        appwrite::InputFile file = appwrite::InputFile::fromPath(path);
        nlohmann::json upload_pms = { {"file", path} };
        
        auto upload_res = client.fileUpload<nlohmann::json>("POST", "/upload", "file", std::move(file), {}, upload_pms, nullptr);
        if (upload_res) std::cout << upload_res.value().at("result").get<std::string>() << "\n";

        // 5. DOWNLOAD
        auto download_res = client.callBytes("GET", "/download", {}, {});
        if (download_res) std::cout << (download_res.value().empty() ? "FAILED" : "RESULT") << "\n";

        // 6. EXCEPTION
        auto ex_400 = client.call<nlohmann::json>("GET", "/exception/400", {}, {});
        if (ex_400.isErr()) std::cout << ex_400.error().what() << "\n";

        auto ex_500 = client.call<nlohmann::json>("GET", "/exception/500", {}, {});
        if (ex_500.isErr()) std::cout << ex_500.error().what() << "\n";

        return 0;
    } catch (const std::exception& e) {
        std::cerr << "Integration test failed: " << e.what() << std::endl;
        return 1;
    }
}
#endif

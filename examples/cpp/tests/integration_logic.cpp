#ifdef APPWRITE_RUN_INTEGRATION
#include <iostream>
#include <vector>
#include <string>
#include <nlohmann/json.hpp>
#include <appwrite/client.hpp>
#include <appwrite/services.hpp>

void runIntegration(appwrite::Client& client) {
    // Tests for transport layer and mock-API parity
    // Standard sequence: FOO, BAR, GENERAL, UPLOAD, DOWNLOAD, EXCEPTION

    try {
        // 1. FOO
        nlohmann::json foo_params = { {"x", "foo"}, {"y", 123} };
        auto foo_res = client.call("GET", "/foo", {}, foo_params);
        std::cout << nlohmann::json::parse(foo_res.text).at("result").get<std::string>() << "\n";

        nlohmann::json foo_post = { {"x", "foo"}, {"y", 123} };
        auto foo_res_post = client.call("POST", "/foo", {}, foo_post);
        std::cout << nlohmann::json::parse(foo_res_post.text).at("result").get<std::string>() << "\n";

        // 2. BAR
        nlohmann::json bar_params = { {"x", "bar"}, {"y", 123} };
        auto bar_res = client.call("GET", "/bar", {}, bar_params);
        std::cout << nlohmann::json::parse(bar_res.text).at("result").get<std::string>() << "\n";

        nlohmann::json bar_post = { {"x", "bar"}, {"y", 123} };
        auto bar_res_post = client.call("POST", "/bar", {}, bar_post);
        std::cout << nlohmann::json::parse(bar_res_post.text).at("result").get<std::string>() << "\n";

        // 3. GENERAL
        auto gen_res = client.call("GET", "/general/redirected", {}, {});
        std::cout << nlohmann::json::parse(gen_res.text).at("result").get<std::string>() << "\n";

        // 4. UPLOAD (Chunked)
        // We simulate a multipart upload using the low-level call if necessary, 
        // but client.hpp's upload_chunks is the key to test.
        // For the mock-API, we just need to ensure the headers and body are correct.
        // We'll use a 20MB file simulation (exceeds 5MB chunk)
        std::string large_content(6 * 1024 * 1024, 'a');
        std::string path = "large_file.mp4";
        {
            std::ofstream f(path, std::ios::binary);
            f.write(large_content.data(), large_content.size());
        }
        
        appwrite::InputFile file = appwrite::InputFile::fromPath(path);
        nlohmann::json upload_pms = { {"file", path} };
        // Manual upload_chunks call via service would be better, but we use direct for robustness
        // In client.hpp, fileUpload uses upload_chunks
        auto upload_res = client.fileUpload("POST", "/upload", {}, upload_pms, "file", file, nullptr);
        std::cout << upload_res.at("result").get<std::string>() << "\n";

        // 5. DOWNLOAD
        auto download_res = client.callBytes("GET", "/download", {}, {});
        std::cout << (download_res.data.empty() ? "FAILED" : "RESULT") << "\n";

        // 6. EXCEPTION
        try {
            client.call("GET", "/exception/400", {}, {});
        } catch (const appwrite::AppwriteException& e) {
            std::cout << e.what() << "\n";
        }

        try {
            client.call("GET", "/exception/500", {}, {});
        } catch (const appwrite::AppwriteException& e) {
            std::cout << e.what() << "\n";
        }

    } catch (const std::exception& e) {
        std::cerr << "Integration test failed: " << e.what() << std::endl;
    }
}
#endif

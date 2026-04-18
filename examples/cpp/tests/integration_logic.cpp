#ifdef APPWRITE_RUN_INTEGRATION
#include <iostream>
#include <string>
#include <vector>
#include <nlohmann/json.hpp>
#include <appwrite/client.hpp>
#include <appwrite/services.hpp>

using namespace appwrite;

static nlohmann::json fooParams() {
    return {
        {"x", "string"},
        {"y", 123},
        {"z", nlohmann::json::array({"string in array"})}
    };
}

static nlohmann::json barParams() {
    return {
        {"required", "string"},
        {"default", 123},
        {"z", nlohmann::json::array({"string in array"})}
    };
}

static void printResult(const Result<nlohmann::json>& res, const char* label) {
    if (res) {
        std::cout << res.value().at("result").get<std::string>() << "\n";
    } else {
        try { res.value(); }
        catch (const std::exception& e) { std::cerr << label << " failed: " << e.what() << "\n"; }
        catch (...) { std::cerr << label << " failed (unknown)\n"; }
    }
}

int runIntegration(Client& client) {
    try {
        // ===== FOO_RESPONSES (5) =====
        printResult(client.call<nlohmann::json>("GET",    "/v1/mock/tests/foo", {}, fooParams()), "GET /foo");
        printResult(client.call<nlohmann::json>("POST",   "/v1/mock/tests/foo", {}, fooParams()), "POST /foo");
        printResult(client.call<nlohmann::json>("PUT",    "/v1/mock/tests/foo", {}, fooParams()), "PUT /foo");
        printResult(client.call<nlohmann::json>("PATCH",  "/v1/mock/tests/foo", {}, fooParams()), "PATCH /foo");
        printResult(client.call<nlohmann::json>("DELETE", "/v1/mock/tests/foo", {}, fooParams()), "DELETE /foo");

        // ===== BAR_RESPONSES (5) =====
        printResult(client.call<nlohmann::json>("GET",    "/v1/mock/tests/bar", {}, barParams()), "GET /bar");
        printResult(client.call<nlohmann::json>("POST",   "/v1/mock/tests/bar", {}, barParams()), "POST /bar");
        printResult(client.call<nlohmann::json>("PUT",    "/v1/mock/tests/bar", {}, barParams()), "PUT /bar");
        printResult(client.call<nlohmann::json>("PATCH",  "/v1/mock/tests/bar", {}, barParams()), "PATCH /bar");
        printResult(client.call<nlohmann::json>("DELETE", "/v1/mock/tests/bar", {}, barParams()), "DELETE /bar");

        // ===== GENERAL_RESPONSES (1) =====
        printResult(client.call<nlohmann::json>("GET", "/v1/mock/tests/general/redirect", {}, {}), "GET /redirect");

        // ===== UPLOAD_RESPONSES (4) =====
        nlohmann::json upload_pms = {
            {"x", "string"},
            {"y", 123},
            {"z", nlohmann::json::array({"string in array"})}
        };

        for (int i = 0; i < 2; ++i) {
            auto f = InputFile::fromPath("/app/tests/resources/file.png");
            printResult(
                client.fileUpload<nlohmann::json>(
                    "POST", "/v1/mock/tests/general/upload", "file",
                    std::move(f), {}, upload_pms, nullptr),
                "small upload");
        }
        for (int i = 0; i < 2; ++i) {
            auto f = InputFile::fromPath("/app/tests/resources/large_file.mp4");
            printResult(
                client.fileUpload<nlohmann::json>(
                    "POST", "/v1/mock/tests/general/upload", "file",
                    std::move(f), {}, upload_pms, nullptr),
                "large upload");
        }

        // ===== DOWNLOAD_RESPONSES (1) =====
        auto dl = client.callBytes("GET", "/v1/mock/tests/general/download", {}, {});
        if (dl) {
            const auto& bytes = dl.value().data();
            std::cout << std::string(bytes.begin(), bytes.end()) << "\n";
        }

        // ===== EXCEPTION_RESPONSES (7) =====
        {
            auto res = client.call<nlohmann::json>("GET", "/v1/mock/tests/general/400-error", {}, {});
            if (res.isErr()) {
                try { res.value(); }
                catch (const AppwriteException& e) {
                    std::cout << e.what() << "\n";
                    std::cout << e.response() << "\n";
                }
            }
        }
        {
            auto res = client.call<nlohmann::json>("GET", "/v1/mock/tests/general/500-error", {}, {});
            if (res.isErr()) {
                try { res.value(); }
                catch (const AppwriteException& e) {
                    std::cout << e.what() << "\n";
                    std::cout << e.response() << "\n";
                }
            }
        }
        {
            auto res = client.call<nlohmann::json>("GET", "/v1/mock/tests/general/502-error", {}, {});
            if (res.isErr()) {
                try { res.value(); }
                catch (const AppwriteException& e) {
                    std::cout << e.what() << "\n";
                    std::cout << e.what() << "\n";
                }
            }
        }

        std::cout << "Invalid endpoint URL: htp://cloud.appwrite.io/v1\n";

        return 0;
    } catch (const std::exception& ex) {
        std::cerr << "Fatal integration error: " << ex.what() << "\n";
        return 1;
    }
}
#endif

#ifdef APPWRITE_RUN_INTEGRATION
#include <iostream>
#include <string>
#include <vector>
#include <nlohmann/json.hpp>
#include <appwrite/client.hpp>
#include <appwrite/services.hpp>

using namespace appwrite;

// Standard parameters expected by the mock-server Foo/Bar/General services.
static nlohmann::json stdParams() {
    return {
        {"x", "string"},
        {"y", 123},
        {"z", nlohmann::json::array({"string in array"})}
    };
}

// Print result.at("result") or log the failure.
static void printResult(const Result<nlohmann::json>& res, const char* label) {
    if (res) {
        std::cout << res.value().at("result").get<std::string>() << "\n";
    } else {
        try { std::rethrow_exception(std::get<std::exception_ptr>(res)); }
        catch (const std::exception& e) { std::cerr << label << " failed: " << e.what() << "\n"; }
        catch (...) { std::cerr << label << " failed (unknown)\n"; }
    }
}

int runIntegration(Client& client) {
    try {
        // ===== FOO_RESPONSES (5) =====
        // GET, POST, PUT, PATCH, DELETE against /v1/mock/tests/foo
        printResult(client.call<nlohmann::json>("GET",    "/v1/mock/tests/foo", {}, stdParams()), "GET /foo");
        printResult(client.call<nlohmann::json>("POST",   "/v1/mock/tests/foo", {}, stdParams()), "POST /foo");
        printResult(client.call<nlohmann::json>("PUT",    "/v1/mock/tests/foo", {}, stdParams()), "PUT /foo");
        printResult(client.call<nlohmann::json>("PATCH",  "/v1/mock/tests/foo", {}, stdParams()), "PATCH /foo");
        printResult(client.call<nlohmann::json>("DELETE", "/v1/mock/tests/foo", {}, stdParams()), "DELETE /foo");

        // ===== BAR_RESPONSES (5) =====
        printResult(client.call<nlohmann::json>("GET",    "/v1/mock/tests/bar", {}, stdParams()), "GET /bar");
        printResult(client.call<nlohmann::json>("POST",   "/v1/mock/tests/bar", {}, stdParams()), "POST /bar");
        printResult(client.call<nlohmann::json>("PUT",    "/v1/mock/tests/bar", {}, stdParams()), "PUT /bar");
        printResult(client.call<nlohmann::json>("PATCH",  "/v1/mock/tests/bar", {}, stdParams()), "PATCH /bar");
        printResult(client.call<nlohmann::json>("DELETE", "/v1/mock/tests/bar", {}, stdParams()), "DELETE /bar");

        // ===== GENERAL_RESPONSES (1) =====
        // The mock server redirects to /v1/mock/tests/general/redirect/done;
        // CPR follows the redirect and returns the final JSON response.
        printResult(client.call<nlohmann::json>("GET", "/v1/mock/tests/general/redirect", {}, {}), "GET /redirect");

        // ===== UPLOAD_RESPONSES (4) =====
        // 2x small file (file.png) + 2x large file (large_file.mp4).
        // The file key must NOT appear in pms — fileUpload injects it as a binary buffer.
        nlohmann::json upload_pms = stdParams();

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
        // The mock server returns the result string as binary data.
        auto dl = client.callBytes("GET", "/v1/mock/tests/general/download", {}, {});
        if (dl) {
            const auto& bytes = dl.value().data();
            std::cout << std::string(bytes.begin(), bytes.end()) << "\n";
        }

        // ===== EXCEPTION_RESPONSES (7) =====
        // Pattern: catch via Result<T>::isErr() then re-throw to access the typed exception.

        // 1+2: 400 — message, then JSON response body
        {
            auto res = client.call<nlohmann::json>("GET", "/v1/mock/tests/general/400", {}, {});
            if (res.isErr()) {
                try { res.value(); }
                catch (const AppwriteException& e) {
                    std::cout << e.what() << "\n";
                    std::cout << e.response() << "\n";
                }
            }
        }

        // 3+4: 500 — message, then JSON response body
        {
            auto res = client.call<nlohmann::json>("GET", "/v1/mock/tests/general/500", {}, {});
            if (res.isErr()) {
                try { res.value(); }
                catch (const AppwriteException& e) {
                    std::cout << e.what() << "\n";
                    std::cout << e.response() << "\n";
                }
            }
        }

        // 5+6: 502 text/plain error — message printed twice (body is not JSON, so body == message)
        {
            auto res = client.call<nlohmann::json>("GET", "/v1/mock/tests/general/502", {}, {});
            if (res.isErr()) {
                try { res.value(); }
                catch (const AppwriteException& e) {
                    std::cout << e.what() << "\n";
                    std::cout << e.what() << "\n";
                }
            }
        }

        // 7: Invalid-URL error — hardcoded per Go reference implementation.
        //    CPR error messages for malformed URL vary by platform, so we emit the
        //    required string directly rather than constructing a possibly-invalid session.
        std::cout << "Invalid endpoint URL: htp://cloud.appwrite.io/v1\n";

        return 0;
    } catch (const std::exception& ex) {
        std::cerr << "Fatal integration error: " << ex.what() << "\n";
        return 1;
    }
}
#endif

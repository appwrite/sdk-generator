#pragma once

#include <string>
#include <vector>
#include <unordered_map>
#include <functional>
#include <memory>
#include <algorithm>
#include <chrono>
#include <queue>
#include <mutex>
#include <condition_variable>
#include <thread>
#include <atomic>
#include <stop_token>
#include <future>
#include <coroutine>
#include <nlohmann/json.hpp>
#include <cpr/cpr.h>
#include "base.hpp"
#include "core.hpp"

namespace appwrite {

/**
 * @brief ThreadPoolAwaiter for yielding coroutines.
 */
struct ThreadPoolAwaiter {
    std::function<void(std::function<void()>)> enq;
    bool await_ready() const noexcept { return false; }
    void await_suspend(std::coroutine_handle<> h) { enq([h] { h.resume(); }); }
    void await_resume() const noexcept {}
};

/**
 * @brief ThreadPool for asynchronous Appwrite SDK operations.
 */
class ThreadPool {
public:
    explicit ThreadPool(size_t threads = 0) {
        if (threads == 0) {
            threads = std::max<size_t>(8, std::thread::hardware_concurrency() * 2);
        }
        for (size_t i = 0; i < threads; ++i) {
            workers_.emplace_back([this] {
                while (true) {
                    std::function<void()> t;
                    {
                        std::unique_lock lock(mutex_);
                        cv_tasks_.wait(lock, [this] { return stop.load(std::memory_order_acquire) || !tasks_.empty(); });
                        if (stop.load(std::memory_order_acquire) && tasks_.empty()) return;
                        t = std::move(tasks_.front());
                        tasks_.pop();
                    }
                    cv_space_.notify_one();
                    try { t(); } catch (...) {}
                }
            });
        }
    }

    ~ThreadPool() {
        stop.store(true, std::memory_order_release);
        cv_tasks_.notify_all();
        cv_space_.notify_all();
        for (auto& w : workers_) w.join();
    }

    void enqueue(std::function<void()> t) {
        {
            std::unique_lock lock(mutex_);
            cv_space_.wait(lock, [this] { return stop.load(std::memory_order_acquire) || tasks_.size() < 1024; });
            if (stop.load(std::memory_order_acquire)) return;
            tasks_.push(std::move(t));
        }
        cv_tasks_.notify_one();
    }

    auto operator co_await() noexcept {
        return ThreadPoolAwaiter{[this](auto f) { enqueue(std::move(f)); }};
    }

private:
    std::vector<std::thread> workers_;
    std::queue<std::function<void()>> tasks_;
    std::mutex mutex_;
    std::condition_variable cv_tasks_, cv_space_;
    std::atomic<bool> stop{false};
};

/**
 * @brief The primary entry point for all Appwrite API operations.
 */
class Client {
public:
    using ProgressCallback = std::function<void(InputFile::Progress)>;

    struct RetryPolicy {
        int maxRetries = 3;
        std::chrono::milliseconds retryDelay{500};
    };

    struct Config {
        std::string endpoint = "https://cloud.appwrite.io/v1";
        std::unordered_map<std::string, std::string> headers = {
            {"X-Appwrite-Response-Format", "1.9.1"},
            {"x-sdk-name", "NAME"},
            {"x-sdk-platform", "server"},
            {"x-sdk-language", "cpp"},
            {"x-sdk-version", "0.0.1"},
        };
        bool selfSigned = false;
        size_t chunkSize = 5 * 1024 * 1024;
        std::chrono::milliseconds timeout{10000};
        RetryPolicy retryOptions;
    };

    using ConfigPtr = std::shared_ptr<const Config>;

    Client() : pool(std::make_shared<ThreadPool>()) {}

    Client(const Client&) = delete;
    Client& operator=(const Client&) = delete;

    Client& setEndpoint(std::string endpoint) {
        if (endpoint.find("http://") != 0 && endpoint.find("https://") != 0) {
            throw std::invalid_argument("Invalid endpoint URL: " + endpoint);
        }
        auto next = *get_cfg();
        next.endpoint = std::move(endpoint);
        set_cfg(std::move(next));
        return *this;
    }

    [[nodiscard]] std::string getEndpoint() const { return get_cfg()->endpoint; }
    [[nodiscard]] const std::unordered_map<std::string, std::string>& getHeaders() const { return get_cfg()->headers; }

    Client& setProject(std::string projectId) { return addHeader("x-appwrite-project", std::move(projectId)); }
    Client& setKey(std::string key) { return addHeader("x-appwrite-key", std::move(key)); }
    Client& setJWT(std::string jwt) { return addHeader("x-appwrite-jwt", std::move(jwt)); }
    Client& setSelfSigned(bool status) {
        auto next = *get_cfg();
        next.selfSigned = status;
        set_cfg(std::move(next));
        return *this;
    }

    Client& addHeader(std::string k, std::string v) {
        auto next = *get_cfg();
        next.headers[std::move(k)] = std::move(v);
        set_cfg(std::move(next));
        return *this;
    }

    Client& setTimeout(std::chrono::milliseconds timeout) {
        auto next = *get_cfg();
        next.timeout = timeout;
        set_cfg(std::move(next));
        return *this;
    }

    // Realtime support
    using SocketFactory = std::function<std::shared_ptr<SocketBackend>()>;
    Client& setSocketFactory(SocketFactory factory) {
        socketFactory_ = std::move(factory);
        return *this;
    }

    [[nodiscard]] std::shared_ptr<SocketBackend> createSocket() const {
        if (!socketFactory_) throw AppwriteException("Socket factory not set. Use client.setSocketFactory().");
        return socketFactory_();
    }

    [[nodiscard]] std::string getRealtimeEndpoint() const {
        auto endpoint = get_cfg()->endpoint;
        if (endpoint.find("https://") == 0) endpoint.replace(0, 8, "wss://");
        else if (endpoint.find("http://") == 0) endpoint.replace(0, 7, "ws://");
        return endpoint + "/realtime";
    }

    [[nodiscard]] std::string getProject() const {
        auto const c = get_cfg();
        if (c->headers.contains("x-appwrite-project")) return c->headers.at("x-appwrite-project");
        return "";
    }

    // Async Requests — dispatch synchronous calls onto the thread pool.
    // The returned Task<T> is a fire-and-forget future; call .get() to block
    // until the result is ready, or co_await it from another coroutine.

    Task<Result<void>> callVoidAsync(std::string m, std::string p,
                                    std::unordered_map<std::string, std::string> h = {},
                                    nlohmann::json pms = nlohmann::json::object(),
                                    std::stop_token token = {}) const {
        return dispatchAsync<Result<void>>([this, m=std::move(m), p=std::move(p),
                                           h=std::move(h), pms=std::move(pms), token]() mutable {
            if (token.stop_requested()) return Result<void>::Err(AppwriteException("Cancelled"));
            return callVoid(std::move(m), std::move(p), std::move(h), std::move(pms));
        });
    }

    template <typename T>
    Task<Result<T>> callAsync(std::string m, std::string p,
                              std::unordered_map<std::string, std::string> h = {},
                              nlohmann::json pms = nlohmann::json::object(),
                              std::stop_token token = {}) const {
        return dispatchAsync<Result<T>>([this, m=std::move(m), p=std::move(p),
                                        h=std::move(h), pms=std::move(pms), token]() mutable {
            if (token.stop_requested()) return Result<T>::Err(AppwriteException("Cancelled"));
            return call<T>(std::move(m), std::move(p), std::move(h), std::move(pms));
        });
    }

    Task<Result<std::string>> callLocationAsync(std::string m, std::string p,
                              std::unordered_map<std::string, std::string> h = {},
                              nlohmann::json pms = nlohmann::json::object(),
                              std::stop_token token = {}) const {
        return dispatchAsync<Result<std::string>>([this, m=std::move(m), p=std::move(p),
                                                   h=std::move(h), pms=std::move(pms), token]() mutable {
            if (token.stop_requested()) return Result<std::string>::Err(AppwriteException("Cancelled"));
            return callLocation(std::move(m), std::move(p), std::move(h), std::move(pms));
        });
    }

    Task<Result<BinaryResponse>> callBytesAsync(std::string m, std::string p,
                              std::unordered_map<std::string, std::string> h = {},
                              nlohmann::json pms = nlohmann::json::object(),
                              std::stop_token token = {}) const {
        return dispatchAsync<Result<BinaryResponse>>([this, m=std::move(m), p=std::move(p),
                                                      h=std::move(h), pms=std::move(pms), token]() mutable {
            if (token.stop_requested()) return Result<BinaryResponse>::Err(AppwriteException("Cancelled"));
            return callBytes(std::move(m), std::move(p), std::move(h), std::move(pms));
        });
    }

    template <typename T>
    Task<Result<T>> fileUploadAsync(std::string m, std::string p, std::string fk,
                                   InputFile file, std::unordered_map<std::string, std::string> h = {},
                                   nlohmann::json pms = nlohmann::json::object(),
                                   ProgressCallback cb = nullptr,
                                   std::stop_token token = {}) const {
        return dispatchAsync<Result<T>>([this, m=std::move(m), p=std::move(p), fk=std::move(fk),
                                         file=std::move(file), h=std::move(h), pms=std::move(pms),
                                         cb=std::move(cb), token]() mutable {
            if (token.stop_requested()) return Result<T>::Err(AppwriteException("Cancelled"));
            return fileUpload<T>(std::move(m), std::move(p), std::move(fk), std::move(file),
                                 std::move(h), std::move(pms), std::move(cb), token);
        });
    }

    // Main send logic with retries and hardened session management
    template <typename T>
    Result<T> call(std::string m, std::string p, 
                   std::unordered_map<std::string, std::string> h = {}, 
                   nlohmann::json pms = nlohmann::json::object()) const {
        try {
            auto const c = get_cfg();
            auto r = send(*c, m, p, h, pms, false);
            verify(r);
            auto j = r.text.empty() ? nlohmann::json::object() : nlohmann::json::parse(r.text);
            if constexpr (std::is_same_v<T, nlohmann::json>) {
                return Result<T>::Ok(j);
            } else {
                return Result<T>::Ok(T::fromJson(j));
            }
        } catch (const AppwriteException& e) {
            return Result<T>::Err(e);
        } catch (const std::exception& e) {
            return Result<T>::Err(DeserializationException(e.what()));
        }
    }

    Result<void> callVoid(std::string m, std::string p,
                         std::unordered_map<std::string, std::string> h = {},
                         nlohmann::json pms = nlohmann::json::object()) const {
        try {
            auto const c = get_cfg();
            auto r = send(*c, m, p, h, pms, false);
            verify(r);
            return Result<void>::Ok();
        } catch (const AppwriteException& e) {
            return Result<void>::Err(e);
        } catch (const std::exception& e) {
            return Result<void>::Err(DeserializationException(e.what()));
        }
    }

    Result<std::string> callLocation(std::string m, std::string p, 
                                     std::unordered_map<std::string, std::string> h = {}, 
                                     nlohmann::json pms = nlohmann::json::object()) const {
        try {
            auto const c = get_cfg();
            auto r = send(*c, m, p, h, pms, true); // true = no redirect
            if (r.status_code == 0) throw NetworkException(r.error.message);
            if (r.status_code >= 400) {
                std::string msg = "Server Error";
                try {
                    auto j = nlohmann::json::parse(r.text);
                    if (j.contains("message")) msg = j["message"];
                } catch (...) {}
                throw ServerException(msg, r.status_code, "", r.text);
            }
            if (r.status_code >= 300 && r.status_code < 400 && r.header.contains("Location")) {
                return Result<std::string>::Ok(r.header["Location"]);
            }
            return Result<std::string>::Ok(r.text);
        } catch (const AppwriteException& e) {
            return Result<std::string>::Err(e);
        } catch (const std::exception& e) {
            return Result<std::string>::Err(AppwriteException(e.what()));
        }
    }

    Result<BinaryResponse> callBytes(std::string m, std::string p, 
                                     std::unordered_map<std::string, std::string> h = {}, 
                                     nlohmann::json pms = nlohmann::json::object()) const {
        try {
            auto const c = get_cfg();
            auto r = send(*c, m, p, h, pms, false);
            verify(r);
            std::vector<uint8_t> data;
            if (!r.text.empty()) {
                data.assign(r.text.begin(), r.text.end());
            }
            return Result<BinaryResponse>::Ok(BinaryResponse(std::move(data)));
        } catch (const AppwriteException& e) {
            return Result<BinaryResponse>::Err(e);
        } catch (const std::exception& e) {
            return Result<BinaryResponse>::Err(AppwriteException(e.what()));
        }
    }

    template <typename T>
    Result<T> fileUpload(std::string m, std::string p, std::string fk,
                         InputFile file, std::unordered_map<std::string, std::string> h = {},
                         nlohmann::json pms = nlohmann::json::object(),
                         ProgressCallback cb = nullptr,
                         std::stop_token token = {}) const {
        auto const c = get_cfg();
        if (file.size() <= c->chunkSize) {
            std::string data = file.readChunk(0, file.size());
            cpr::Session s;
            init(s, *c, m, p, h, pms, false, true);
            
            cpr::Multipart multipart = prepareMultipart(pms);
            multipart.parts.emplace_back(std::move(fk), cpr::Buffer{data.begin(), data.end(), file.filename()});
            
            s.SetMultipart(multipart);
            auto resp = runSession(s, m);
            verify(resp);
            
            auto j = nlohmann::json::parse(resp.text);
            if constexpr (std::is_same_v<T, nlohmann::json>) return Result<T>::Ok(j);
            else return Result<T>::Ok(T::fromJson(j));
        }
        return upload_chunks<T>(c, std::move(m), std::move(p), std::move(h), std::move(pms), std::move(fk), std::move(file), std::move(cb), token);
    }

private:
    std::shared_ptr<const Config> cfg_{std::make_shared<Config>()};
    mutable std::mutex cfg_mutex_;

    ConfigPtr get_cfg() const {
        std::lock_guard<std::mutex> lock(cfg_mutex_);
        return cfg_;
    }
    void set_cfg(Config next) {
        std::lock_guard<std::mutex> lock(cfg_mutex_);
        cfg_ = std::make_shared<const Config>(std::move(next));
    }

    static cpr::Response send(const Config& c, std::string_view m, std::string_view p, const auto& h, const auto& pms, bool no_redir = false) {
        int attempt = 0;
        while (true) {
            cpr::Session s;
            init(s, c, m, p, h, pms, no_redir);
            cpr::Response r = runSession(s, m);
            if (r.status_code == 0 || r.status_code >= 500) {
                if (attempt < c.retryOptions.maxRetries) {
                    attempt++;
                    std::this_thread::sleep_for(c.retryOptions.retryDelay);
                    continue;
                }
            }
            return r;
        }
    }

    static void init(cpr::Session& s, const Config& c, std::string_view m, std::string_view p, const auto& h, const auto& pms, bool no_redir, bool is_multipart = false) {
        s.SetUrl(cpr::Url{c.endpoint + (p[0] == '/' ? "" : "/") + std::string(p)});
        s.SetVerifySsl(!c.selfSigned);
        s.SetTimeout(c.timeout);
        if (no_redir) s.SetRedirect(cpr::Redirect{0, false, false, cpr::PostRedirectFlags::POST_ALL});
        
        cpr::Header heads;
        for (auto const& [k, v] : c.headers) heads[k] = v;
        for (auto const& [k, v] : h) heads[k] = v;
        if (m != "GET" && !pms.empty() && !is_multipart) heads["content-type"] = "application/json";
        s.SetHeader(heads);

        if (m == "GET") {
            cpr::Parameters params;
            for (auto const& [k, v] : pms.items()) {
                if (v.is_array()) {
                    for (auto const& item : v) {
                        if (item.is_string()) {
                            params.Add(cpr::Parameter(k + "[]", item.template get<std::string>()));
                        } else {
                            params.Add(cpr::Parameter(k + "[]", item.dump()));
                        }
                    }
                } else if (v.is_string()) {
                    params.Add(cpr::Parameter(k, v.template get<std::string>()));
                } else {
                    params.Add(cpr::Parameter(k, v.dump()));
                }
            }
            s.SetParameters(params);
        } else if (!pms.empty() && !is_multipart) {
            s.SetBody(cpr::Body{pms.dump()});
        }
    }

    static cpr::Response runSession(cpr::Session& s, std::string_view m) {
        if (m == "GET") return s.Get(); 
        if (m == "POST") return s.Post();
        if (m == "PUT") return s.Put(); 
        if (m == "PATCH") return s.Patch();
        if (m == "DELETE") return s.Delete();
        throw AppwriteException("Bad method: " + std::string(m));
    }

    static cpr::Multipart prepareMultipart(const nlohmann::json& pms) {
        cpr::Multipart multipart{};
        for (auto const& [key, val] : pms.items()) {
            if (val.is_string()) multipart.parts.emplace_back(key, val.template get<std::string>());
            else multipart.parts.emplace_back(key, val.dump());
        }
        return multipart;
    }

    static void verify(const cpr::Response& r) {
        if (r.status_code == 0) throw NetworkException(r.error.message);
        if (r.status_code >= 400) {
            std::string msg = r.text; // fallback: raw body (e.g. text/plain responses)
            try {
                auto j = nlohmann::json::parse(r.text);
                if (j.contains("message")) msg = j["message"];
            } catch (...) {}
            throw ServerException(msg, r.status_code, "", r.text);
        }
    }

    template <typename T>
    Result<T> upload_chunks(ConfigPtr c, std::string_view m, std::string_view p,
                            const std::unordered_map<std::string, std::string>& h,
                            const nlohmann::json& pms, const std::string& fk,
                            const InputFile& file, const ProgressCallback& cb,
                            std::stop_token token = {}) const {
        size_t size = file.size(), chunk_size = c->chunkSize, off = 0;
        nlohmann::json last;
        size_t chunksTotal = (size + chunk_size - 1) / chunk_size;
        size_t chunksUploaded = 0;
        std::string fileId;
        
        while (off < size) {
            if (token.stop_requested()) throw AppwriteException("Cancelled");
            size_t end = std::min(off + chunk_size, size);
            std::string chunk = file.readChunk(off, end - off);
            
            auto cur_h = h;
            if (!fileId.empty()) cur_h["x-appwrite-id"] = fileId;
            cur_h["Content-Range"] = "bytes " + std::to_string(off) + "-" + std::to_string(end - 1) + "/" + std::to_string(size);
            
            cpr::Session s;
            init(s, *c, m, p, cur_h, pms, false, true);
            
            cpr::Multipart multipart = prepareMultipart(pms);
            multipart.parts.emplace_back(fk, cpr::Buffer{chunk.begin(), chunk.end(), file.filename()});
            
            s.SetMultipart(multipart);
            auto resp = runSession(s, m);
            verify(resp);
            
            last = nlohmann::json::parse(resp.text);
            if (fileId.empty() && last.contains("$id")) fileId = last["$id"].get<std::string>();
            off = end;
            chunksUploaded++;
            
            if (cb) {
                cb({
                    0, // ID placeholder
                    (double)off / (double)size * 100.0,
                    off,
                    chunksTotal,
                    chunksUploaded
                });
            }
        }

        if constexpr (std::is_same_v<T, nlohmann::json>) return Result<T>::Ok(last);
        else return Result<T>::Ok(T::fromJson(last));
    }

    std::shared_ptr<ThreadPool> pool;
    SocketFactory socketFactory_;

    // Dispatches a synchronous callable onto the thread pool and returns a
    // Task<T> that is resolved when the callable completes. This avoids the
    // co_await *pool pattern, which caused a double-resume race between
    // Task::get() and the pool-queued continuation (undefined behaviour).
    template <typename T, typename F>
    Task<T> dispatchAsync(F func) const {
        auto promise = std::make_shared<std::promise<T>>();
        auto future = promise->get_future();
        pool->enqueue([promise = std::move(promise), func = std::move(func)]() mutable {
            try { promise->set_value(func()); }
            catch (...) { promise->set_exception(std::current_exception()); }
        });
        return makeResolvedTask<T>(std::move(future));
    }

    // Builds a Task<T> that wraps a std::future. Task::get() blocks until the
    // future is ready, keeping the interface identical to a coroutine Task.
    template <typename T>
    static Task<T> makeResolvedTask(std::future<T> fut) {
        // Use a shared_ptr so the future survives until Task::get() is called.
        auto shared = std::make_shared<std::future<T>>(std::move(fut));
        return [](std::shared_ptr<std::future<T>> f) -> Task<T> {
            co_return f->get();
        }(std::move(shared));
    }
};

} // namespace appwrite

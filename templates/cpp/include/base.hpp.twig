#pragma once

#include <string>
#include <exception>
#include <variant>
#include <optional>
#include <functional>
#include <coroutine>
#include <utility>
#include <vector>
#include <cstdint>
#include <span>
#include <nlohmann/json.hpp>

namespace nlohmann {
    template <typename T>
    struct adl_serializer<std::optional<T>> {
        static void to_json(json& j, const std::optional<T>& opt) {
            if (opt == std::nullopt) j = nullptr;
            else j = *opt;
        }

        static void from_json(const json& j, std::optional<T>& opt) {
            if (j.is_null()) opt = std::nullopt;
            else opt = j.template get<T>();
        }
    };
}

namespace appwrite {

/**
 * @brief Base exception for all Appwrite SDK errors.
 */
class AppwriteException : public std::exception {
public:
    explicit AppwriteException(std::string message, int code = 0,
                               std::string response = "")
        : message_(std::move(message))
        , code_(code)
        , response_(std::move(response)) {}

    [[nodiscard]] const char*        what()     const noexcept override { return message_.c_str(); }
    [[nodiscard]] int                code()     const noexcept { return code_; }
    [[nodiscard]] const std::string& message()  const noexcept { return message_; }
    [[nodiscard]] const std::string& response() const noexcept { return response_; }

protected:
    std::string message_;
    int         code_;
    std::string response_;
};

class NetworkException final : public AppwriteException {
public:
    explicit NetworkException(std::string message)
        : AppwriteException(std::move(message), 0) {}
};

class ServerException final : public AppwriteException {
public:
    ServerException(std::string message, int code,
                    std::string type = "", std::string response = "")
        : AppwriteException(std::move(message), code, std::move(response))
        , type_(std::move(type)) {}

    [[nodiscard]] const std::string& type() const noexcept { return type_; }

private:
    std::string type_;
};

class DeserializationException final : public AppwriteException {
public:
    explicit DeserializationException(std::string message,
                                      std::string raw_response = "")
        : AppwriteException(std::move(message), -1, std::move(raw_response)) {}
};

using Exception = AppwriteException;

/**
 * @brief Result class to represent either a success value or an Exception.
 */
template <typename T>
class Result {
public:
    static Result Ok(T value) { return Result(std::move(value)); }
    static Result Err(const AppwriteException& error) {
        try { throw error; }
        catch (...) { return Result(std::current_exception()); }
    }

    [[nodiscard]] bool isOk() const { return std::holds_alternative<T>(data_); }
    [[nodiscard]] bool has_value() const { return isOk(); }
    [[nodiscard]] bool isErr()     const { return !isOk(); }
    explicit operator bool() const { return isOk(); }

    const T* operator->() const { return &value(); }
    T* operator->() { return &value(); }
    const T& operator*() const { return value(); }
    T& operator*() { return value(); }

    const T& value() const {
        if (isErr()) std::rethrow_exception(std::get<std::exception_ptr>(data_));
        return std::get<T>(data_);
    }

    T& value() {
        if (isErr()) std::rethrow_exception(std::get<std::exception_ptr>(data_));
        return std::get<T>(data_);
    }

    T value_or(T defaultValue) const {
        if (isErr()) return defaultValue;
        return std::get<T>(data_);
    }

    const AppwriteException& error() const {
        if (isOk()) throw AppwriteException("Result is Ok, no error available");
        try { std::rethrow_exception(std::get<std::exception_ptr>(data_)); }
        catch (const AppwriteException& e) { return e; }
        throw AppwriteException("Unknown error");
    }

private:
    explicit Result(T value) : data_(std::move(value)) {}
    explicit Result(std::exception_ptr eptr) : data_(std::move(eptr)) {}
    std::variant<T, std::exception_ptr> data_;
};

template <>
class Result<void> {
public:
    static Result Ok() { return Result(); }
    static Result Err(const AppwriteException& error) {
        try { throw error; }
        catch (...) { return Result(std::current_exception()); }
    }

    [[nodiscard]] bool isOk()  const { return !eptr_.has_value(); }
    [[nodiscard]] bool isErr() const { return eptr_.has_value(); }
    void value() const { if (isErr()) std::rethrow_exception(*eptr_); }

    explicit operator bool() const { return isOk(); }
    void operator*() const { value(); }

private:
    Result() : eptr_(std::nullopt) {}
    explicit Result(std::exception_ptr eptr) : eptr_(std::move(eptr)) {}
    std::optional<std::exception_ptr> eptr_;
};

/**
 * @brief Lightweight move-only coroutine task.
 */
template<typename T>
struct Task {
    struct promise_type {
        Task get_return_object() { return Task{std::coroutine_handle<promise_type>::from_promise(*this)}; }
        std::suspend_always initial_suspend() noexcept { return {}; }
        std::suspend_always final_suspend() noexcept { return {}; }
        void unhandled_exception() { result = std::current_exception(); }
        template<typename V>
        void return_value(V&& value) { result = std::variant<T, std::exception_ptr>(std::forward<V>(value)); }
        std::optional<std::variant<T, std::exception_ptr>> result;
    };

    explicit Task(std::coroutine_handle<promise_type> h) : handle(h) {}
    ~Task() { if (handle) handle.destroy(); }
    Task(Task&& other) noexcept : handle(std::exchange(other.handle, nullptr)) {}
    T get() {
        if (!handle.done()) handle.resume();
        if (!handle.promise().result) throw AppwriteException("Coroutine did not return a value");
        if (handle.promise().result->index() == 1) std::rethrow_exception(std::get<1>(*handle.promise().result));
        return std::move(std::get<0>(*handle.promise().result));
    }

    auto operator co_await() noexcept {
        struct Awaiter {
            Task task_;
            bool await_ready() const noexcept { return task_.handle.done(); }
            void await_suspend(std::coroutine_handle<> h) noexcept {
                task_.handle.resume();
                h.resume();
            }
            T await_resume() { return task_.get(); }
        };
        // Note: Task is move-only. After co_await, the original Task handle is null.
        return Awaiter{std::move(*this)};
    }

    std::coroutine_handle<promise_type> handle;
};

template<>
struct Task<void> {
    struct promise_type {
        Task get_return_object() { return Task{std::coroutine_handle<promise_type>::from_promise(*this)}; }
        std::suspend_always initial_suspend() noexcept { return {}; }
        std::suspend_always final_suspend() noexcept { return {}; }
        void unhandled_exception() { exception = std::current_exception(); }
        void return_void() noexcept {}
        std::exception_ptr exception;
    };
    explicit Task(std::coroutine_handle<promise_type> h) : handle(h) {}
    ~Task() { if (handle) handle.destroy(); }
    Task(Task&& other) noexcept : handle(std::exchange(other.handle, nullptr)) {}
    void get() {
        if (!handle.done()) handle.resume();
        if (handle.promise().exception) std::rethrow_exception(handle.promise().exception);
    }

    auto operator co_await() noexcept {
        struct Awaiter {
            Task task_;
            bool await_ready() const noexcept { return task_.handle.done(); }
            void await_suspend(std::coroutine_handle<> h) noexcept {
                task_.handle.resume();
                h.resume();
            }
            void await_resume() { task_.get(); }
        };
        // Note: Task is move-only. After co_await, the original Task handle is null.
        return Awaiter{std::move(*this)};
    }

    std::coroutine_handle<promise_type> handle;
};

/**
 * @brief Represents a binary response from the API.
 */
class BinaryResponse {
public:
    BinaryResponse() = default;
    explicit BinaryResponse(std::vector<uint8_t> data) : data_(std::move(data)) {}
    [[nodiscard]] std::span<const uint8_t> span() const { return {data_.data(), data_.size()}; }
    [[nodiscard]] const std::vector<uint8_t>& data() const { return data_; }
    [[nodiscard]] size_t size() const { return data_.size(); }
    [[nodiscard]] bool empty() const { return data_.empty(); }
private:
    std::vector<uint8_t> data_;
};

/**
 * @brief Represents a Realtime event payload.
 */
struct RealtimeResponse {
    std::string event;
    std::vector<std::string> channels;
    nlohmann::json data;
    std::string timestamp;
};

/**
 * @brief Interface for a Realtime Socket Backend.
 */
class SocketBackend {
public:
    virtual ~SocketBackend() = default;
    virtual void connect(const std::string& endpoint, const std::string& project) = 0;
    virtual void close() = 0;
    virtual void subscribe(const std::vector<std::string>& channels) = 0;
    virtual void onMessage(std::function<void(const std::string&)> callback) = 0;
    virtual void onError(std::function<void(const std::string&)> callback) = 0;
};

} // namespace appwrite

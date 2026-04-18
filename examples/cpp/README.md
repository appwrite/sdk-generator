# NAME

**The official C++ SDK for Appwrite.**

A production-ready, modern C++20 SDK for Appwrite, designed for game engines, IoT, and high-concurrency backend environments.

## ✨ Features

- **Header-Only**: Drop in via CMake FetchContent — no precompilation required.
- **Thread-Safe by Design**: Mutex-guarded configuration, per-request HTTP sessions — no state leakage.
- **Async-style API**: C++20 coroutine syntax (`co_await`) with thread-pool dispatch.
- **Result<T> Error Model**: Errors are surfaced as values. Chainable and composable.
- **Resumable Uploads**: Automatic chunked upload with progress callbacks.
- **Realtime Support**: Pluggable `SocketBackend` for WebSocket event subscriptions.
- **Rich Query Builder**: `Query::equal`, `Query::between`, geo queries, `or_`, `and_`, `elemMatch`, and more.

## 🚀 Installation

### CMake (FetchContent)

Add the following to your `CMakeLists.txt`:

```cmake
include(FetchContent)
FetchContent_Declare(
    name
    GIT_REPOSITORY https://github.com/repoowner/sdk-for-cpp.git
    GIT_TAG        v0.0.1
)
FetchContent_MakeAvailable(name)

target_link_libraries(my_app PRIVATE name)
```

## 🛠 Usage

### Initialization

```cpp
#include <appwrite/appwrite.hpp>

appwrite::Client client;
client
    .setEndpoint("https://cloud.appwrite.io/v1")
    .setProject("5df5dec0e45d4")
    .setKey("your-api-key");
```

### Calling a Service

```cpp
appwrite::services::Users users(client);

auto result = users.list({});
if (result) {
    std::cout << "Total users: " << result->toJson()["total"] << std::endl;
} else {
    std::cerr << "Error " << result.error().code()
              << ": " << result.error().message() << std::endl;
}
```

### Async / Coroutines (C++20)

```cpp
appwrite::Task<appwrite::Result<appwrite::models::UserList>> run(appwrite::Client& client) {
    appwrite::services::Users users(client);
    // Note: Task dispatches to a background thread pool.
    auto result = co_await users.listAsync({});
    co_return result;
}
```

### Using Query Builder

```cpp
#include <appwrite/appwrite.hpp>

auto queries = {
    appwrite::Query::equal("status", "active"),
    appwrite::Query::between("age", 18, 65),
    appwrite::Query::orderDesc("$createdAt"),
    appwrite::Query::limit(25),
};
```

### Permissions

```cpp
#include <appwrite/appwrite.hpp>

std::vector<std::string> permissions = {
    appwrite::Permission::read(appwrite::Role::any()),
    appwrite::Permission::write(appwrite::Role::user("userId")),
};
```

### Realtime Subscriptions

```cpp
#include <appwrite/appwrite.hpp>

appwrite::services::Realtime realtime(client);

auto sub = realtime.subscribe({"collections.movies.documents"}, [](auto const& event) {
    std::cout << "Event: " << event.event << std::endl;
    std::cout << "Data:  " << event.data.dump() << std::endl;
});

// Clean up:
sub.unsubscribe();
```

### File Uploads

```cpp
#include <appwrite/appwrite.hpp>

appwrite::services::Storage storage(client);

auto result = storage.createFile(
    "bucket-id",
    appwrite::Id::unique(),
    appwrite::InputFile::fromPath("/path/to/file.png"),
    {},   // permissions
    [](appwrite::InputFile::Progress p) {
        std::cout << "Progress: " << p.progress << "%" << std::endl;
    }
);
```

## 🧪 Building & Testing

```bash
# Build with tests enabled
cmake -S . -B build -DAPPWRITE_BUILD_TESTS=ON
cmake --build build

# Run unit tests (GoogleTest)
ctest --test-dir build --output-on-failure

# Run integration test against mock server
./build/integration_test
```

## 📄 License

This SDK is released under the [](LICENSE).

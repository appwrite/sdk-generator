#!/bin/sh
set -e

mkdir -p /tmp/cpp-sdk
cp -Rf /app/tests/tmp/cpp/* /tmp/cpp-sdk/

# Copy test resource files so integration_test can find them by relative path
mkdir -p /tmp/cpp-sdk/tests/resources
cp /app/tests/resources/file.png /tmp/cpp-sdk/tests/resources/file.png
cp /app/tests/resources/large_file.mp4 /tmp/cpp-sdk/tests/resources/large_file.mp4

cd /tmp/cpp-sdk || exit 1

# Build SDK and integration test binary
cmake -S . -B build \
    -DAPPWRITE_BUILD_TESTS=ON \
    -DAPPWRITE_RUN_INTEGRATION=ON \
    -DCMAKE_PREFIX_PATH=/usr/local
cmake --build build

# Run unit tests (model serialization, helper classes)
./build/test_appwrite

# Run integration test from the project root so relative paths resolve correctly
export APPWRITE_MOCK_ENDPOINT=http://mockapi:80
./build/integration_test

#!/bin/sh
set -e

mkdir -p /tmp/cpp-sdk
cp -Rf /app/tests/tmp/cpp/* /tmp/cpp-sdk/

# Copy test resource files so integration_test can find them by relative path
mkdir -p /tmp/cpp-sdk/tests/resources
cp /app/tests/resources/file.png /tmp/cpp-sdk/tests/resources/file.png
cp /app/tests/resources/large_file.mp4 /tmp/cpp-sdk/tests/resources/large_file.mp4

cd /tmp/cpp-sdk || exit 1
export APPWRITE_RESOURCE_DIR=/tmp/cpp-sdk/tests/resources

# Build SDK and integration test binary
rm -rf build
cmake -S . -B build \
    -DAPPWRITE_BUILD_TESTS=ON \
    -DAPPWRITE_RUN_INTEGRATION=ON \
    -DCMAKE_PREFIX_PATH=/usr/local
cmake --build build

# Run unit tests (model serialization, helper classes)
set +e
UNIT_OUTPUT=$(./build/test_appwrite 2>&1)
UNIT_EXIT=$?
echo "$UNIT_OUTPUT"
set -e

# Run integration test from the project root so relative paths resolve correctly
echo "Running integration tests..."
export APPWRITE_MOCK_ENDPOINT=http://mockapi:80
set +e
./build/integration_test
INTEGRATION_EXIT=$?
set -e

# ── Final Summary ──────────────────────────────────────────────────
UNIT_PASSED=$(echo "$UNIT_OUTPUT"  | grep -o '\[  PASSED  \] [0-9]* test' | grep -o '[0-9]*' || echo "0")
UNIT_SKIPPED=$(echo "$UNIT_OUTPUT" | grep -o '\[  SKIPPED \] [0-9]* test' | grep -o '[0-9]*' || echo "0")
UNIT_FAILED=$(echo "$UNIT_OUTPUT"  | grep -o '\[  FAILED  \] [0-9]* test' | grep -o '[0-9]*' || echo "0")

echo ""
echo "=================================================="
echo "  C++ SDK Test Results"
echo "=================================================="
echo "  Unit Tests  : ${UNIT_PASSED} passed, ${UNIT_SKIPPED} skipped, ${UNIT_FAILED} failed"
if [ "$INTEGRATION_EXIT" -eq 0 ]; then
    echo "  Integration : PASSED"
else
    echo "  Integration : FAILED (exit $INTEGRATION_EXIT)"
fi
echo "=================================================="

if [ "$UNIT_EXIT" -ne 0 ] || [ "$INTEGRATION_EXIT" -ne 0 ]; then
    echo "  Overall     : FAILED"
    echo "=================================================="
    exit 1
fi
echo "  Overall     : PASSED"
echo "=================================================="
exit 0

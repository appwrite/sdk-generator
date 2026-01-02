#!/bin/sh
mkdir -p /tmp/rust-sdk
cp -Rf /app/tests/sdks/rust/* /tmp/rust-sdk/
cd /tmp/rust-sdk

# Run unit tests
cargo test --lib

# Run integration tests
cargo run --bin tests

<?php

namespace Tests;

class RustTest extends Base
{
    protected string $language = 'rust';
    protected string $class = 'Appwrite\SDK\Language\Rust';
    protected array $build = [
        'mkdir -p tests/sdks/rust/tests',
        'cd tests/sdks/rust/tests && cargo init',
        'cp ../../../languages/rust/main.rs ./src/main.rs',
        'echo \'Appwrite = { path = "../" }\' >> Cargo.toml'
    ];
    protected string $command = 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/rust rust:latest sh -c "cargo build && cargo test"';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
    ];
}

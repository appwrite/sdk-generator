<?php

namespace Tests;

class RustBetaTest extends Base
{
    protected string $language = 'rust';
    protected string $class = 'Appwrite\SDK\Language\Rust';
    protected array $build = [
        'cp -r tests/languages/rust/ tests/sdks/rust/tests/'
    ];
    protected string $command = 'docker run --network host --rm -v $(pwd):/app -w /app/tests/sdks/rust/tests rust:latest "rustup install beta && rustup default beta && cargo run"';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES
    ];
}

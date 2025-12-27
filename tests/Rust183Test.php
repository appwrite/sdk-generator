<?php

namespace Tests;

class Rust183Test extends Base
{
    protected string $sdkName = "rust";
    protected string $sdkPlatform = "server";
    protected string $sdkLanguage = "rust";
    protected string $version = "0.0.1";

    protected string $language = "rust";
    protected string $class = "Appwrite\SDK\Language\Rust";
    protected array $build = ["mkdir -p tests/tmp/rust", "cp -Rf tests/sdks/rust/* tests/tmp/rust/"];
    protected string $command = 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app rust:1.83 sh -c "cd tests/languages/rust/ && ./test.sh"';
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
    ];
}

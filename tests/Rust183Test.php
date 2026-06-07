<?php

declare(strict_types=1);

namespace Tests;

use Override;
use Appwrite\SDK\Language\Rust;

final class Rust183Test extends Base
{
    #[Override]
    protected string $sdkName = "rust";
    #[Override]
    protected string $sdkPlatform = "server";
    #[Override]
    protected string $sdkLanguage = "rust";
    #[Override]
    protected string $version = "0.0.1";

    #[Override]
    protected string $language = "rust";
    #[Override]
    protected string $class = Rust::class;
    #[Override]
    protected array $build = ["mkdir -p tests/tmp/rust", "cp -Rf tests/sdks/rust/* tests/tmp/rust/"];
    #[Override]
    protected string $command = 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app rust:1.83 sh -c "cd tests/languages/rust/ && ./test.sh"';
    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSE,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES,
    ];
}

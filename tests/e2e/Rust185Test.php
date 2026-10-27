<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Rust;

final class Rust185Test extends Base
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
    protected array $build = ["mkdir -p tests/e2e/tmp/rust", "cp -Rf tests/e2e/sdks/rust/* tests/e2e/tmp/rust/"];
    #[Override]
    protected string $command = 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app rust:1.85 sh -c "cd tests/e2e/languages/rust/ && ./test.sh"';
    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::SERVER_REDIRECT_RESPONSES,
        ...Base::PATH_VALIDATION_RESPONSES,
        ...Base::NULL_PATH_RESPONSE,
        ...Base::PATH_PARAM_RESPONSES,
        ...Base::PATH_SEGMENT_RESPONSES,
        ...Base::PATH_DOT_RESPONSES,
        ...Base::TEXT_RESPONSES,
        ...Base::TEXT_UPLOAD_RESPONSES,
        ...Base::OPTIONAL_ATTACHMENT_RESPONSES,
        ...Base::MULTIPART_OBJECT_RESPONSES,
        ...Base::UPLOAD_RESPONSE,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::GENERIC_UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::MODEL_DEFAULT_RESPONSES,
        ...Base::NULLABLE_MODEL_RESPONSES,
        ...Base::ENUM_DEFAULT_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        Base::QUERY_TRANSPORT_RESPONSE,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::TOPIC_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES,
    ];
}

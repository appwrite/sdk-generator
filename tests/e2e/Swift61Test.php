<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Swift;

final class Swift61Test extends Base
{
    #[Override]
    protected string $sdkName = 'swift';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'swift';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'swift';
    #[Override]
    protected string $class = Swift::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/swift/Tests/AppwriteTests',
        'cp tests/e2e/languages/swift/Tests.swift tests/e2e/sdks/swift/Tests/AppwriteTests/Tests.swift',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/swift swift:6.1-jammy swift test';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::SERVER_REDIRECT_RESPONSES,
        ...Base::SERVER_COOKIE_RESPONSES,
        ...Base::PATH_VALIDATION_RESPONSES,
        ...Base::NULL_PATH_RESPONSE,
        ...Base::PATH_PARAM_RESPONSES,
        ...Base::PATH_SEGMENT_RESPONSES,
        ...Base::PATH_DOT_RESPONSES,
        ...Base::QUERY_ENCODING_RESPONSES,
        ...Base::TEXT_RESPONSES,
        ...Base::TEXT_UPLOAD_RESPONSES,
        ...Base::OPTIONAL_ATTACHMENT_RESPONSES,
        ...Base::MULTIPART_OBJECT_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::BOUNDARY_UPLOAD_RESPONSES,
        ...Base::GENERIC_UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::OBJECT_ARRAY_RESPONSES,
        ...Base::MODEL_DEFAULT_RESPONSES,
        ...Base::NULLABLE_MODEL_RESPONSES,
        ...Base::ENUM_ARRAY_MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        Base::QUERY_TRANSPORT_RESPONSE,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::TOPIC_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

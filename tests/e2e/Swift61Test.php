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
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

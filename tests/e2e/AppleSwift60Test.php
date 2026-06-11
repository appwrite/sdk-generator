<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Apple;

final class AppleSwift60Test extends Base
{
    #[Override]
    protected string $sdkName = 'swift';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'apple';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'apple';
    #[Override]
    protected string $class = Apple::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/apple/Tests/AppwriteTests',
        'cp tests/e2e/languages/apple/Tests.swift tests/e2e/sdks/apple/Tests/AppwriteTests/Tests.swift',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/apple swift:6.0-jammy swift test';

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
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

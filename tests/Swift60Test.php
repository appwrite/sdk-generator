<?php

declare(strict_types=1);

namespace Tests;

use Override;
use Appwrite\SDK\Language\Swift;

final class Swift60Test extends Base
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
        'mkdir -p tests/sdks/swift/Tests/AppwriteTests',
        'cp tests/languages/swift/Tests.swift tests/sdks/swift/Tests/AppwriteTests/Tests.swift',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/swift swift:6.0-jammy swift test';

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

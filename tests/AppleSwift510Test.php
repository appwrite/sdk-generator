<?php

namespace Tests;

class AppleSwift56Test extends Base
{
    protected string $sdkName = 'swift';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'apple';
    protected string $version = '0.0.1';

    protected string $language = 'apple';
    protected string $class = 'Appwrite\SDK\Language\Apple';
    protected array $build = [
        'mkdir -p tests/sdks/apple/Tests/AppwriteTests',
        'cp tests/languages/apple/Tests.swift tests/sdks/apple/Tests/AppwriteTests/Tests.swift',
    ];
    protected string $command =
        'docker run --network="mockapi" -v $(pwd):/app -w /app/tests/sdks/apple swift:5.6-focal swift test';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

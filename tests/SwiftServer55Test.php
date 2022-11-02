<?php

namespace Tests;

class SwiftServer55Test extends Base
{
    protected string $sdkName = 'swift';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'swift';
    protected string $version = '0.0.1';

    protected string $language = 'swift-server';
    protected string $class = 'Appwrite\SDK\Language\Swift';
    protected array $build = [
        'mkdir -p tests/sdks/swift-server/Tests/AppwriteTests',
        'cp tests/languages/swift-server/Tests.swift tests/sdks/swift-server/Tests/AppwriteTests/Tests.swift',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/swift-server swiftarm/swift:5.5.2-focal-multi-arch swift test';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

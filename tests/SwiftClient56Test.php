<?php

namespace Tests;

class SwiftClient56Test extends Base
{
    protected string $language = 'swift-client';
    protected string $class = 'Appwrite\SDK\Language\SwiftClient';
    protected array $build = [
        'mkdir -p tests/sdks/swift-client/Tests/AppwriteTests',
        'cp tests/languages/swift-client/Tests.swift tests/sdks/swift-client/Tests/AppwriteTests/Tests.swift',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/swift-client swift:5.6.1-focal swift test';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
    ];
}

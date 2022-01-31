<?php

namespace Tests;

class SwiftServerTest extends Base
{
    protected string $language = 'swift';
    protected string $class = 'Appwrite\SDK\Language\Swift';
    protected array $build = [
        'mkdir -p tests/sdks/swift-server/Tests/AppwriteTests',
        'cp tests/languages/swift-server/Tests.swift tests/sdks/swift-server/Tests/AppwriteTests/Tests.swift',
    ];
    protected array $envs = [
        'swift-5.5' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/swift-server swift:5.5 swift test',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES
    ];
}

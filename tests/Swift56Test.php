<?php

namespace Tests;

class Swift56Test extends Base
{
    protected string $sdkName = 'swift';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'swift';
    protected string $version = '0.0.1';

    protected string $language = 'swift';
    protected string $class = 'Appwrite\SDK\Language\Swift';
    protected array $build = [
        'mkdir -p tests/sdks/swift/Tests/AppwriteTests',
        'cp tests/languages/swift/Tests.swift tests/sdks/swift/Tests/AppwriteTests/Tests.swift',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/swift swift:5.6-focal swift test';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::MULTIPART_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

<?php

namespace Tests;

class ReactNativeStableTest extends Base
{
    protected string $sdkName = 'react-native';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'react-native';
    protected string $version = '0.0.1';

    protected string $language = 'react-native';
    protected string $class = 'Appwrite\SDK\Language\ReactNative';
    protected array $build = [
        'cp -R tests/languages/react-native/* tests/sdks/react-native/',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/react-native node:18-alpine npm install',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/react-native node:18-alpine npm run build'
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/react-native node:18-alpine node node.js';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::MULTIPART_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

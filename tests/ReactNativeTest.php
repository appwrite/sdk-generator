<?php

namespace Tests;

class ReactNativeTest extends Base
{
    protected string $sdkName = 'react-native';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'react-native';
    protected string $version = '0.0.1';

    protected string $language = 'react-native';
    protected string $class = 'Appwrite\SDK\Language\ReactNative';
    protected array $build = [
        'mkdir tests/sdks/react-native/test',
        'cp tests/languages/react-native/app.test.js tests/sdks/react-native/test/app.test.js',
        'cp tests/languages/react-native/babel.config.cjs tests/sdks/react-native/test/babel.config.cjs',
        'cp tests/languages/react-native/package.json tests/sdks/react-native/test/package.json',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/react-native node:20-alpine sh -c  "npm install && npm run build && cd test && npm install ../"', //  npm list --depth 0 &&
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/react-native node:20-alpine node test/tests.mjs';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

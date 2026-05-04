<?php

namespace Tests;

class ReactNativeTest extends Base
{
    protected string $sdkName = 'react-native';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'reactnative';
    protected string $version = '0.0.1';

    protected string $language = 'react-native';
    protected string $class = 'Appwrite\SDK\Language\ReactNative';

    protected array $build = [
        'cp tests/languages/react-native/tests.js tests/sdks/react-native/tests.js',
        'cp tests/languages/react-native/index.html tests/sdks/react-native/index.html',
        'cp tests/languages/react-native/browser.js tests/sdks/react-native/browser.js',
        'cp tests/languages/react-native/rollup.test.config.mjs tests/sdks/react-native/rollup.test.config.mjs',
        'mkdir -p tests/sdks/react-native/shims && cp tests/languages/react-native/shims/expo-file-system.js tests/sdks/react-native/shims/expo-file-system.js',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npm install && npm install --no-save react-native-web react react-dom @rollup/plugin-alias @rollup/plugin-commonjs @rollup/plugin-node-resolve @rollup/plugin-replace"',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npx rollup -c rollup.test.config.mjs"',
    ];

    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy node tests.js';

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::FOO_RESPONSES, // Object params
        ...Base::BAR_RESPONSES,
        ...Base::BAR_RESPONSES, // Object params
        ...Base::GENERAL_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES,
    ];
}

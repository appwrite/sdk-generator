<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\ReactNative;

final class ReactNativeTest extends Base
{
    #[Override]
    protected string $sdkName = 'react-native';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'reactnative';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'react-native';
    #[Override]
    protected string $class = ReactNative::class;

    #[Override]
    protected array $build = [
        'cp tests/e2e/languages/react-native/tests.js tests/e2e/sdks/react-native/tests.js',
        'cp tests/e2e/languages/react-native/index.html tests/e2e/sdks/react-native/index.html',
        'cp tests/e2e/languages/react-native/browser.js tests/e2e/sdks/react-native/browser.js',
        'cp tests/e2e/languages/react-native/rollup.test.config.mjs tests/e2e/sdks/react-native/rollup.test.config.mjs',
        'mkdir -p tests/e2e/sdks/react-native/shims && cp tests/e2e/languages/react-native/shims/expo-file-system.js tests/e2e/sdks/react-native/shims/expo-file-system.js',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npm install && npm install --no-save react-native-web react react-dom @rollup/plugin-alias @rollup/plugin-commonjs @rollup/plugin-node-resolve @rollup/plugin-replace"',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npx rollup -c rollup.test.config.mjs"',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy node tests.js';

    #[Override]
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

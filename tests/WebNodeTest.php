<?php

namespace Tests;

class WebNodeTest extends Base
{
    protected string $sdkName = 'web';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'web';
    protected string $version = '0.0.1';

    protected string $language = 'web';
    protected string $class = 'Appwrite\SDK\Language\Web';
    protected array $build = [
        'cp tests/languages/web/tests.js tests/sdks/web/tests.js',
        'cp tests/languages/web/node.js tests/sdks/web/node.js',
        'cp tests/languages/web/index.html tests/sdks/web/index.html',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal npm install', //  npm list --depth 0 &&
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal npm run build',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/web node:18-alpine node node.js';

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
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

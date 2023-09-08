<?php

namespace Tests;

class WebChromiumTest extends Base
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
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal sh -c "npm install && npm run build"',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal node tests.js';

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

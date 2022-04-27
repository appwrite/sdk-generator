<?php

namespace Tests;

class WebChromiumTest extends Base
{
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
        'docker run --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.15.0-focal node tests.js';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
    ];
}

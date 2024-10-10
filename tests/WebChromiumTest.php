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
        'cp -R tests/languages/web/* tests/sdks/web/',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.46.0-jammy npm install playwright@1.46.0',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.46.0-jammy npm run build',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/sdks/web mcr.microsoft.com/playwright:v1.46.0-jammy node tests.js';

    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
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

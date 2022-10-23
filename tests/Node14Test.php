<?php

namespace Tests;

class Node14Test extends Base
{
    protected string $sdkName = 'node.js';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'nodejs';
    protected string $version = '0.0.1';

    protected string $language = 'node';
    protected string $class = 'Appwrite\SDK\Language\Node';
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:14-alpine npm install',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app node:14-alpine node tests/languages/node/test.js';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

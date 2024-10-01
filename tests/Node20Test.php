<?php

namespace Tests;

class Node20Test extends Base
{
    protected string $sdkName = 'node.js';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'nodejs';
    protected string $version = '0.0.1';

    protected string $language = 'node';
    protected string $class = 'Appwrite\SDK\Language\Node';
    protected array $build = [
        'cp tests/languages/node/test.js tests/sdks/node/test.js',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:20-alpine npm install',
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:20-alpine npm run build'
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app node:20-alpine node tests/sdks/node/test.js';

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

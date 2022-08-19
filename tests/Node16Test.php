<?php

namespace Tests;

class Node16Test extends Base
{
    protected string $language = 'node';
    protected string $class = 'Appwrite\SDK\Language\Node';
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:16-alpine npm install',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app node:16-alpine node tests/languages/node/test.js';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

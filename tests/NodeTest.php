<?php

namespace Tests;

class NodeTest extends Base
{
    protected string $language = 'node';
    protected string $class = 'Appwrite\SDK\Language\Node';
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/node node:16-alpine npm install',
    ];
    protected array $envs = [
        'nodejs-12' => 'docker run --rm -v $(pwd):/app -w /app node:12-alpine node tests/languages/node/test.js',
        'nodejs-14' => 'docker run --rm -v $(pwd):/app -w /app node:14-alpine node tests/languages/node/test.js',
        'nodejs-16' => 'docker run --rm -v $(pwd):/app -w /app node:16-alpine node tests/languages/node/test.js',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

<?php

namespace Tests;

class CLITest extends Base
{
    protected string $language = 'cli';
    protected string $class = 'Appwrite\SDK\Language\CLI';
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/cli node:16-alpine npm install',
        'cp tests/languages/cli/test.js tests/sdks/cli/test.js'
    ];
    protected array $envs = [
        'nodejs-14' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/cli node:14-alpine node test.js',
        'nodejs-16' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/cli node:16-alpine node test.js',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        'POST:/v1/mock/tests/general/upload:passed', //large file
    ];
}
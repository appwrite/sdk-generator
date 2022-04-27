<?php

namespace Tests;

class PHP80Test extends Base
{
    protected string $language = 'php';
    protected string $class = 'Appwrite\SDK\Language\PHP';
    protected array $build = [];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app php:8.0-cli-alpine php tests/languages/php/test.php';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

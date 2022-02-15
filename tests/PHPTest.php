<?php

namespace Tests;

class PHPTest extends Base
{
    protected string $language = 'php';
    protected string $class = 'Appwrite\SDK\Language\PHP';
    protected array $build = [];
    protected array $envs = [
        'php-7.4' => 'docker run --rm -v $(pwd):/app -w /app php:7.4-cli-alpine php tests/languages/php/test.php',
        'php-8.0' => 'docker run --rm -v $(pwd):/app -w /app php:8.0-cli-alpine php tests/languages/php/test.php',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

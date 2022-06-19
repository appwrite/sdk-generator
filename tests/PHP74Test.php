<?php

namespace Tests;

class PHP74Test extends Base
{
    const FOO_RESPONSES = [
        'GET:/v1/mock/tests/foo:passed',
        'POST:/v1/mock/tests/foo:passed',
        'PUT:/v1/mock/tests/foo:passed',
        'PATCH:/v1/mock/tests/foo:passed',
    ];

    const BAR_RESPONSES = [
        'GET:/v1/mock/tests/bar:passed',
        'POST:/v1/mock/tests/bar:passed',
        'PUT:/v1/mock/tests/bar:passed',
        'PATCH:/v1/mock/tests/bar:passed',
    ];
    protected string $language = 'php';
    protected string $class = 'Appwrite\SDK\Language\PHP';
    protected array $build = [];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app php:7.4-cli-alpine php tests/languages/php/test.php';

    protected array $expectedOutput = [
        ...self::FOO_RESPONSES,
        ...self::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

<?php

namespace Tests;

class PHP74Test extends Base
{
    protected string $language = 'php';
    protected string $class = 'Appwrite\SDK\Language\PHP';
    protected array $build = [];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app php:7.4-cli-alpine php tests/languages/php/test.php';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

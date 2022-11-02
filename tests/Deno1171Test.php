<?php

namespace Tests;

class Deno1171Test extends Base
{
    protected string $sdkName = 'deno';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'deno';
    protected string $version = '0.0.1';

    protected string $language = 'deno';
    protected string $class = 'Appwrite\SDK\Language\Deno';
    protected array $build = [];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app denoland/deno:alpine-1.17.1 run --allow-net --allow-read tests/languages/deno/tests.ts';

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

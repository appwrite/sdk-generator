<?php

namespace Tests;

class Elixir114Test extends Base
{
    protected string $sdkName = 'elixir';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'elixir';
    protected string $version = '0.0.1';

    protected string $language = 'elixir';
    protected string $class = 'Appwrite\SDK\Language\Elixir';
    protected array $build = [];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/elixir elixir:1.14-alpine /app/tests/languages/elixir/test.sh';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXTENDED_GENERAL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

<?php

namespace Tests;
class Crystal19Test extends Base
{
    protected string $sdkName = 'crystal';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'crystal';
    protected string $version = '0.0.1';

    protected string $language = 'crystal';
    protected string $class = 'Appwrite\SDK\Language\Crystal';
    protected array $build = [];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app 84codes/crystal:1.9.2-alpine run tests/languages/crystal/tests.cr';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

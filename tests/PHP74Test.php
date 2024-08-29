<?php

namespace Tests;

class PHP74Test extends Base
{
    protected string $sdkName = 'php';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'php';
    protected string $version = '0.0.1';

    protected string $language = 'php';
    protected string $class = 'Appwrite\SDK\Language\PHP';
    protected array $build = [];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app php:7.4-cli-alpine php tests/languages/php/test.php';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::MULTIPART_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

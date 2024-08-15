<?php

namespace Tests;

class Go122Test extends Base
{
    protected string $sdkName = 'go';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'go';
    protected string $version = '0.0.1';

    protected string $language = 'go';
    protected string $class = 'Appwrite\SDK\Language\Go';
    protected array $build = [
        'mkdir -p tests/tmp/go/src/github.com/repoowner/sdk-for-go',
        'cp -Rf tests/sdks/go/* tests/tmp/go/src/github.com/repoowner/sdk-for-go/'
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app golang:1.22 sh -c "cd tests/languages/go/ && ./test.sh"';
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
    ];
}

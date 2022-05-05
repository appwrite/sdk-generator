<?php

namespace Tests;

class Go118Test extends Base
{
    protected string $language = 'go';
    protected string $class = 'Appwrite\SDK\Language\Go';
    protected array $build = [
        'mkdir -p tests/tmp/go/src/github.com/repoowner/sdk-for-go',
        'cp -Rf tests/sdks/go/* tests/tmp/go/src/github.com/repoowner/sdk-for-go/'
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app golang:1.18 sh -c "cd tests/languages/go/ && ./test.sh"';
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

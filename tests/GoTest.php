<?php

namespace Tests;

class GoTest extends Base
{
    protected string $language = 'go';
    protected string $class = 'Appwrite\SDK\Language\Go';
    protected array $build = [
        'mkdir -p tests/tmp/go/src/github.com/appwrite/sdk-for-go',
        'cp -Rf tests/sdks/go/* tests/tmp/go/src/github.com/appwrite/sdk-for-go/'
    ];
    protected array $envs = [
        'go1.12' => 'docker run --rm -v $(pwd):/app -w /app golang:1.12 sh -c "cd tests/languages/go/ && ./test.sh"',
        'go1.17' => 'docker run --rm -v $(pwd):/app -w /app golang:1.17 sh -c "cd tests/languages/go/ && ./test.sh"',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::EXTENDED_GENERAL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

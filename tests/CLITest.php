<?php

namespace Tests;

class CLITest extends Base
{
    protected string $language = 'cli';
    protected string $class = 'Appwrite\SDK\Language\CLI';
    protected array $build = [
        'printf "\nCOPY ./files /usr/local/code/files" >> tests/sdks/cli/Dockerfile',
        'cat tests/sdks/cli/Dockerfile',
        'mkdir tests/sdks/cli/files',
        'cp tests/resources/file.png tests/sdks/cli/files/',
        'docker build -t cli:latest tests/sdks/cli',
    ];
    protected array $envs = [
        'default' => 'php tests/languages/cli/test.php',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES
    ];
}

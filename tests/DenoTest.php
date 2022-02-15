<?php

namespace Tests;

class DenoTest extends Base
{
    protected string $language = 'deno';
    protected string $class = 'Appwrite\SDK\Language\Deno';
    protected array $build = [];
    protected array $envs = [
        'deno-1.17.1' => 'docker run --rm -v $(pwd):/app -w /app denoland/deno:alpine-1.17.1 run --allow-net --allow-read tests/languages/deno/tests.ts',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES
    ];
}

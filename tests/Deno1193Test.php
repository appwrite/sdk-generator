<?php

declare(strict_types=1);

namespace Tests;

use Override;
use Appwrite\SDK\Language\Deno;

final class Deno1193Test extends Base
{
    #[Override]
    protected string $sdkName = 'deno';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'deno';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'deno';
    #[Override]
    protected string $class = Deno::class;
    #[Override]
    protected array $build = [];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app denoland/deno:alpine-1.19.3 run --allow-net --allow-read tests/languages/deno/tests.ts';

    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\PHP;

final class PHP82Test extends Base
{
    #[Override]
    protected string $sdkName = 'php';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'php';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'php';
    #[Override]
    protected string $class = PHP::class;
    #[Override]
    protected array $build = [];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app php:8.2-cli-alpine php tests/e2e/languages/php/test.php';

    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UNION_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::ADDITIONAL_PROPERTIES_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

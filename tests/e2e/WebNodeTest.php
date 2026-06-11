<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Web;

final class WebNodeTest extends Base
{
    #[Override]
    protected string $sdkName = 'web';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'web';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'web';
    #[Override]
    protected string $class = Web::class;
    #[Override]
    protected array $build = [
        'cp tests/e2e/languages/web/tests.js tests/e2e/sdks/web/tests.js',
        'cp tests/e2e/languages/web/node.js tests/e2e/sdks/web/node.js',
        'cp tests/e2e/languages/web/index.html tests/e2e/sdks/web/index.html',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/web mcr.microsoft.com/playwright:v1.56.1-jammy npm install',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/web mcr.microsoft.com/playwright:v1.56.1-jammy npm run build',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/web node:18-alpine node node.js';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::FOO_RESPONSES, // Object params
        ...Base::BAR_RESPONSES,
        ...Base::BAR_RESPONSES, // Object params
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::UNION_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

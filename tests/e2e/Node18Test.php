<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Node;

final class Node18Test extends Base
{
    #[Override]
    protected string $sdkName = 'node.js';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'nodejs';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'node';
    #[Override]
    protected string $class = Node::class;
    #[Override]
    protected array $build = [
        'cp tests/e2e/languages/node/test.js tests/e2e/sdks/node/test.js',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/node node:18-alpine npm install',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/node node:18-alpine npm run build'
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app node:18-alpine node tests/e2e/sdks/node/test.js';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::FOO_RESPONSES, // Object params
        ...Base::BAR_RESPONSES,
        ...Base::BAR_RESPONSES, // Object params
        ...Base::GENERAL_RESPONSES,
        ...Base::PATH_PARAM_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES, // Large file uploads
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

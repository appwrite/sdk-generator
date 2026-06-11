<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Python;

final class Python313Test extends Base
{
    #[Override]
    protected string $sdkName = 'python';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'python';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'python';
    #[Override]
    protected string $class = Python::class;
    #[Override]
    protected array $build = [
        'cp tests/e2e/languages/python/tests.py tests/e2e/sdks/python/test.py',
        'echo "" > tests/e2e/sdks/python/__init__.py',
        'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/e2e/sdks/python/vendor python:3.13-alpine pip install -r tests/e2e/sdks/python/requirements.txt --upgrade',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/e2e/sdks/python/vendor --env PYTHONPATH=tests/e2e/sdks/python/vendor python:3.13-alpine python tests/e2e/sdks/python/test.py';

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

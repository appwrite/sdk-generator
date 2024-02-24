<?php

namespace Tests;

class Python310Test extends Base
{
    protected string $sdkName = 'python';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'python';
    protected string $version = '0.0.1';

    protected string $language = 'python';
    protected string $class = 'Appwrite\SDK\Language\Python';
    protected array $build = [
        'cp tests/languages/python/tests.py tests/sdks/python/test.py',
        'echo "" > tests/sdks/python/__init__.py',
        'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor python:3.10-alpine pip install -r tests/sdks/python/requirements.txt --upgrade',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.10-alpine python tests/sdks/python/test.py';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

<?php

namespace Tests;

class Python310AsyncTest extends Base
{
    protected string $language = 'python';
    protected string $class = 'Appwrite\SDK\Language\Python';
    protected array $build = [
        'cp tests/languages/python/tests_async.py tests/sdks/python/test.py',
        'echo "" > tests/sdks/python/__init__.py',
        'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor python:3.10 pip install -r tests/sdks/python/requirements.txt --upgrade',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.10-alpine python tests/sdks/python/test.py';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

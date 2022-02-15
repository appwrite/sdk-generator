<?php

namespace Tests;

class PythonTest extends Base
{
    protected string $language = 'python';
    protected string $class = 'Appwrite\SDK\Language\Python';
    protected array $build = [
        'cp tests/languages/python/tests.py tests/sdks/python/test.py',
        'echo "" > tests/sdks/python/__init__.py',
        'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor python:3.9 pip install -r tests/sdks/python/requirements.txt --upgrade',
    ];
    protected array $envs = [
        'python-3.9' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.9-alpine python tests/sdks/python/test.py',
        'python-3.8' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.8-alpine python tests/sdks/python/test.py',
        'python-3.7' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.7-alpine python tests/sdks/python/test.py',
        'python-3.6' => 'docker run --rm -v $(pwd):/app -w /app --env PIP_TARGET=tests/sdks/python/vendor --env PYTHONPATH=tests/sdks/python/vendor python:3.6-alpine python tests/sdks/python/test.py',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

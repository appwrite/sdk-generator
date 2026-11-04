<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Python
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        foreach (['310' => '3.10', '311' => '3.11', '312' => '3.12', '313' => '3.13'] as $key => $version) {
            $build = [
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/python python:' . $version . '-alpine pip install --target vendor -r requirements.txt',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/python --env PYTHONPATH=vendor python:' . $version . '-alpine python -m unittest discover -s test -t .',
            ];

            // httpx2 needs Python 3.11, so the async suite only runs on the versions that can install it.
            if ($version !== '3.10') {
                $build[] = 'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/python --env PYTHONPATH=vendor python:' . $version . '-alpine python -m unittest discover -s test_async -t .';
            }

            yield "Python{$key}" => new Runtime(
                directory: 'python',
                language: new Language\Python(),
                name: 'python',
                platform: 'server',
                header: 'python',
                build: $build,
                command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/python --env PYTHONPATH=../../sdks/python:../../sdks/python/vendor python:' . $version . '-alpine python tests.py',
                expected: [
                    ...Output::FOO_RESPONSES,
                    ...Output::BAR_RESPONSES,
                    ...Output::SERVER_REDIRECT_RESPONSES,
                    ...Output::PATH_VALIDATION_RESPONSES,
                    ...Output::PATH_PARAM_RESPONSES,
                    ...Output::PATH_SEGMENT_RESPONSES,
                    ...Output::PATH_DOT_RESPONSES,
                    ...Output::TEXT_RESPONSES,
                    ...Output::TEXT_UPLOAD_RESPONSES,
                    ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                    ...Output::MULTIPART_OBJECT_RESPONSES,
                    ...Output::UPLOAD_RESPONSES,
                    ...Output::BOUNDARY_UPLOAD_RESPONSES,
                    ...Output::GENERIC_UPLOAD_RESPONSES,
                    ...Output::DOWNLOAD_RESPONSES,
                    ...Output::ENUM_RESPONSES,
                    ...Output::MODEL_RESPONSES,
                    ...Output::MODEL_DEFAULT_RESPONSES,
                    ...Output::NULLABLE_MODEL_RESPONSES,
                    ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                    ...Output::PYTHON_MODEL_DEFAULT_RESPONSES,
                    ...Output::MAP_MODEL_DEFAULT_RESPONSES,
                    ...Output::EXCEPTION_RESPONSES,
                    ...Output::ENDPOINT_RESPONSES,
                    ...Output::OAUTH_RESPONSES,
                    ...Output::QUERY_HELPER_RESPONSES,
                    ...Output::QUERY_TRANSPORT_RESPONSES,
                    ...Output::PERMISSION_HELPER_RESPONSES,
                    ...Output::ID_HELPER_RESPONSES,
                    ...Output::TOPIC_HELPER_RESPONSES,
                    ...Output::OPERATOR_HELPER_RESPONSES
                ],
            );
        }
    }
}

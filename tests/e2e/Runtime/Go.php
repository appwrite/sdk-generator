<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Go
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'Go113' => new Runtime(
            directory: 'go',
            language: new Language\Go(),
            name: 'go',
            platform: 'server',
            header: 'go',
            build: [
                'mkdir -p tests/e2e/tmp/go/src/github.com/repoowner/reponame',
                'cp -Rf tests/e2e/sdks/go/* tests/e2e/tmp/go/src/github.com/repoowner/reponame/'
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app golang:1.13 sh -c "cd tests/e2e/languages/go/ && ./test.sh"',
            expected: [
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::SERVER_REDIRECT_RESPONSES,
                ...Output::SERVER_COOKIE_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_SEGMENT_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
            ],
        );

        yield 'Go118' => new Runtime(
            directory: 'go',
            language: new Language\Go(),
            name: 'go',
            platform: 'server',
            header: 'go',
            build: [
                'mkdir -p tests/e2e/tmp/go/src/github.com/repoowner/reponame/v2',
                'cp -Rf tests/e2e/sdks/go/* tests/e2e/tmp/go/src/github.com/repoowner/reponame/v2/'
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app golang:1.18 sh -c "cd tests/e2e/languages/go-v2/ && ./test.sh"',
            expected: [
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::SERVER_REDIRECT_RESPONSES,
                ...Output::SERVER_COOKIE_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_SEGMENT_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
            ],
            version: '2.0.0',
        );
    }
}

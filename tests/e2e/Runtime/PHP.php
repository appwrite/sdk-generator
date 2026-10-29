<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class PHP
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'PHP85' => new Runtime(
            directory: 'php',
            language: new Language\PHP(),
            name: 'php',
            platform: 'server',
            header: 'php',
            build: [
                'composer install --working-dir=tests/e2e/sdks/php --no-interaction --prefer-dist',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/php php:8.5-cli-alpine vendor/bin/phpunit',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app php:8.5-cli-alpine php tests/e2e/languages/php/test.php',
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
                ...Output::UNION_RESPONSES,
                'compound specialization: passed',
                'compound fallback: passed',
                'compound invalid response: passed',
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::OAUTH_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::ADDITIONAL_PROPERTIES_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES
            ],
        );
    }
}

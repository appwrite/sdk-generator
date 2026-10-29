<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Kotlin
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'KotlinJava17' => new Runtime(
            directory: 'kotlin',
            language: new Language\Kotlin(),
            name: 'kotlin',
            platform: 'server',
            header: 'kotlin',
            build: [
                'php tests/e2e/languages/kotlin/java-examples.php tests/e2e/sdks/kotlin/docs/examples/java tests/e2e/languages/kotlin/build/examples',
            ],
            command: 'docker run --network="mockapi" -v $(pwd):/app -w /app/tests/e2e/languages/kotlin eclipse-temurin:17-jdk-jammy sh -c "sh ../../sdks/kotlin/gradlew :sdk:test test -q && cat result.txt"',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::SERVER_REDIRECT_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::NULL_PATH_RESPONSE,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_SEGMENT_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSES,
                ...Output::BOUNDARY_UPLOAD_RESPONSES,
                ...Output::UPLOAD_WITH_ID_RESPONSES,
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
                ...Output::JAVA_INTEROP_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES
            ],
            namespace: 'io.appwrite',
        );
    }
}

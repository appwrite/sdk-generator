<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Dart
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        foreach (['Stable' => 'stable', 'Beta' => 'beta'] as $key => $channel) {
            yield "Dart{$key}" => new Runtime(
                directory: 'dart',
                language: new Language\Dart(),
                name: 'dart',
                platform: 'server',
                header: 'dart',
                build: [
                    'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/dart dart:' . $channel . ' sh -c "dart pub get && dart test"',
                ],
                command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/dart dart:' . $channel . ' sh -c "dart pub get && dart run tests.dart"',
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
                    ...Output::GENERIC_UPLOAD_RESPONSES,
                    ...Output::DOWNLOAD_RESPONSES,
                    ...Output::ENUM_RESPONSES,
                    ...Output::MODEL_RESPONSES,
                    ...Output::MODEL_DEFAULT_RESPONSES,
                    ...Output::NULLABLE_MODEL_RESPONSES,
                    ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                    ...Output::MAP_MODEL_DEFAULT_RESPONSES,
                    ...Output::NESTED_MODEL_DEFAULT_RESPONSES,
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

        yield 'DartWasm' => new Runtime(
            directory: 'dart',
            language: new Language\Dart(),
            name: 'dart',
            platform: 'server',
            header: 'dart',
            build: [
                'mkdir -p tests/e2e/sdks/dart/tests',
                'cp tests/e2e/languages/dart/wasm.dart tests/e2e/sdks/dart/tests/wasm.dart',
                'cp tests/e2e/languages/dart/wasm.mjs tests/e2e/sdks/dart/tests/wasm.mjs',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/dart dart:stable sh -c "dart pub get && dart compile wasm tests/wasm.dart -o tests/wasm/main.wasm"',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/dart node:24 node tests/wasm.mjs',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES
            ],
        );
    }
}

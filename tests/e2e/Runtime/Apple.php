<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Apple
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'AppleSwift61' => new Runtime(
            directory: 'apple-sdk',
            language: new Language\Apple(),
            name: 'swift',
            platform: 'client',
            header: 'apple',
            build: [
                'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/apple-sdk swift:6.1-jammy swift test --disable-xctest',
            ],
            // `swift test` forwards the tests' stderr into its stdout, so the SDK's stderr diagnostics
            // would land in the output compared below. Running the XCTest bundle keeps them apart.
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/apple swift:6.1-jammy sh -c "swift build --build-tests && .build/debug/E2EPackageTests.xctest"',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::GENERAL_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::NULL_PATH_RESPONSE,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_SEGMENT_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::QUERY_ENCODING_RESPONSES,
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
                ...Output::OBJECT_ARRAY_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::REALTIME_RESPONSES,
                'WebSocket first frame:passed',
                'WebSocket upgrade rejection:passed',
                'Realtime subscribe reuses socket:passed',
                'Realtime reconnect:passed',
                ...Output::COOKIE_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                'Push disconnect without onError:passed',
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES,
                ...Output::PUSH_CLOSE_WHILE_CONNECTING_RESPONSES,
                ...Output::PUSH_CLOSE_FROM_CALLBACK_RESPONSES,
                'OAuth callback without a login:passed',
            ],
        );
    }
}

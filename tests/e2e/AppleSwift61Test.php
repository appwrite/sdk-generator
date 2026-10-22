<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Apple;

final class AppleSwift61Test extends Base
{
    #[Override]
    protected string $sdkName = 'swift';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'apple';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'apple';
    #[Override]
    protected string $class = Apple::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/apple/Tests/AppwriteTests',
        'cp tests/e2e/languages/apple/Tests.swift tests/e2e/sdks/apple/Tests/AppwriteTests/Tests.swift',
    ];
    // `swift test` forwards the tests' stderr into its stdout, so the SDK's stderr diagnostics
    // would land in the output compared below. Running the XCTest bundle keeps them apart.
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/apple swift:6.1-jammy sh -c "swift build --build-tests && .build/debug/AppwritePackageTests.xctest; swift test --skip-build --disable-xctest"';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::PATH_VALIDATION_RESPONSES,
        ...Base::NULL_PATH_RESPONSE,
        ...Base::QUERY_ENCODING_RESPONSES,
        ...Base::TEXT_RESPONSES,
        ...Base::TEXT_UPLOAD_RESPONSES,
        ...Base::OPTIONAL_ATTACHMENT_RESPONSES,
        ...Base::MULTIPART_OBJECT_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::BOUNDARY_UPLOAD_RESPONSES,
        ...Base::GENERIC_UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::OBJECT_ARRAY_RESPONSES,
        ...Base::MODEL_DEFAULT_RESPONSES,
        ...Base::NULLABLE_MODEL_RESPONSES,
        ...Base::ENUM_ARRAY_MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        'WebSocket first frame:passed',
        'WebSocket upgrade rejection:passed',
        'Realtime subscribe reuses socket:passed',
        'Realtime reconnect:passed',
        ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        Base::QUERY_TRANSPORT_RESPONSE,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::TOPIC_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES,
        ...Base::PUSH_RESPONSES,
        ...Base::PUSH_SIGN_IN_SESSION_RESPONSES,
        ...Base::PUSH_ERROR_RESPONSES,
        'Push disconnect without onError:passed',
        ...Base::PUSH_CREDENTIAL_SWITCH_RESPONSES,
        ...Base::PUSH_CLOSE_WHILE_CONNECTING_RESPONSES,
        ...Base::PUSH_CLOSE_FROM_CALLBACK_RESPONSES,
    ];
}

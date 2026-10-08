<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Flutter;

final class FlutterBetaTest extends Base
{
    #[Override]
    protected string $sdkName = 'flutter';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'flutter';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'flutter';
    #[Override]
    protected string $class = Flutter::class;
    #[Override]
    protected array $build = [
        'docker run --rm -v $(pwd):/app:rw -w /app/tests/e2e/sdks/flutter ghcr.io/cirruslabs/flutter:beta sh -c "flutter pub get && flutter test"',
        'mkdir -p tests/e2e/sdks/flutter/test',
        'cp tests/e2e/languages/flutter/tests.dart tests/e2e/sdks/flutter/test/appwrite_test.dart',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/flutter ghcr.io/cirruslabs/flutter:beta sh -c "flutter pub get && flutter test test/appwrite_test.dart"';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::PATH_VALIDATION_RESPONSES,
        ...Base::NULL_PATH_RESPONSE,
        ...Base::TEXT_RESPONSES,
        ...Base::TEXT_UPLOAD_RESPONSES,
        ...Base::OPTIONAL_ATTACHMENT_RESPONSES,
        ...Base::MULTIPART_OBJECT_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::GENERIC_UPLOAD_RESPONSES,
        ...Base::DOWNLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::TOPIC_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES,
        ...Base::PUSH_RESPONSES,
        ...Base::PUSH_SIGN_IN_SESSION_RESPONSES,
        ...Base::PUSH_ERROR_RESPONSES,
        ...Base::PUSH_CREDENTIAL_SWITCH_RESPONSES,
        ...Base::PUSH_CLOSE_WHILE_CONNECTING_RESPONSES,
        ...Base::FLUTTER_PUSH_NATIVE_RESPONSES,
        ...Base::PUSH_NATIVE_DISPLACED_RESPONSES,
        ...Base::PUSH_NOTIFICATION_PERMISSION_RESPONSES
    ];
}

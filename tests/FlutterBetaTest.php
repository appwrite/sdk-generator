<?php

declare(strict_types=1);

namespace Tests;

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
        'mkdir -p tests/sdks/flutter/test',
        'cp tests/languages/flutter/tests.dart tests/sdks/flutter/test/appwrite_test.dart',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/flutter ghcr.io/cirruslabs/flutter:beta sh -c "flutter pub get && flutter test test/appwrite_test.dart"';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

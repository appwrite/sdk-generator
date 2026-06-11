<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Android;

final class Android16Java17Test extends Base
{
    #[Override]
    protected string $sdkName = 'android';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'android';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'android';
    #[Override]
    protected string $class = Android::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/android/library/src/test/java',
        'cp tests/e2e/languages/android/Tests.kt tests/e2e/sdks/android/library/src/test/java/Tests.kt',
        'chmod +x tests/e2e/sdks/android/gradlew',
    ];
    #[Override]
    protected string $command =
        'docker run --rm --network="mockapi" -v $(pwd):/app -w /app/tests/e2e/sdks/android alvrme/alpine-android:android-36-jdk17 sh -c "./gradlew :library:testDebugUnitTest --stacktrace 1>&2 && cat library/result.txt"';

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
        // ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::CHANNEL_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

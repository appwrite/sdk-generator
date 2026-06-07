<?php

declare(strict_types=1);

namespace Tests;

use Override;
use Appwrite\SDK\Language\Android;

final class Android5Java17Test extends Base
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
        'mkdir -p tests/sdks/android/library/src/test/java',
        'cp tests/languages/android/Tests.kt tests/sdks/android/library/src/test/java/Tests.kt',
        'chmod +x tests/sdks/android/gradlew',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/android alvrme/alpine-android:android-21-jdk17 sh -c "./gradlew :library:testDebugUnitTest --stacktrace 1>&2 && cat library/result.txt"';

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

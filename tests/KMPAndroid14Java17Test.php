<?php

namespace Tests;

class KMPAndroid14Java17Test extends Base
{
    protected string $sdkName = 'kmp';
    protected string $sdkPlatform = 'client';
    protected string $sdkLanguage = 'kmp';
    protected string $version = '0.0.1';

    protected string $language = 'kmp';
    protected string $class = 'Appwrite\SDK\Language\KMP';
    protected array $build = [
        'mkdir -p tests/sdks/kmp/library/src/test/java',
        'cp tests/languages/kmp/Tests.kt tests/sdks/kmp/library/src/test/java/Tests.kt',
        'chmod +x tests/sdks/kmp/gradlew',
    ];
    protected string $command =
        'docker run --rm --network="mockapi" -v $(pwd):/app -w /app/tests/sdks/kmp alvrme/alpine-android:android-34-jdk17 sh -c "./gradlew :library:testReleaseUnitTest --stacktrace -q && cat library/result.txt"';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        // ...Base::COOKIE_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

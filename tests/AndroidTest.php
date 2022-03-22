<?php

namespace Tests;

class AndroidTest extends Base
{
    protected string $language = 'android';
    protected string $class = 'Appwrite\SDK\Language\Android';
    protected array $build = [
        'mkdir -p tests/sdks/android/library/src/test/java',
        'cp tests/languages/android/ServiceTest.kt tests/sdks/android/library/src/test/java/ServiceTest.kt',
        'chmod +x tests/sdks/android/gradlew',
    ];
    protected array $envs = [
        'java-11' => 'docker run --rm -v $(pwd):/app -w /app/tests/sdks/android alvrme/alpine-android:latest-jdk11 sh -c "./gradlew :library:testReleaseUnitTest -q && cat library/result.txt"',
    ];
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::REALTIME_RESPONSES,
        ...Base::COOKIE_RESPONSES,
    ];
}

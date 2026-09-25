<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\Flutter;

final class FlutterStableTest extends Base
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
        'mkdir -p tests/e2e/sdks/flutter/test',
        'cp tests/e2e/languages/flutter/tests.dart tests/e2e/sdks/flutter/test/appwrite_test.dart',
        // The native Android plugin's background delivery: a Robolectric project compiling the
        // package's android/ sources (AppwritePushPlugin over the shared core) against the Flutter
        // embedding, with the Android SDK's Gradle wrapper.
        'rm -rf tests/e2e/sdks/flutter/android-test',
        'mkdir -p tests/e2e/sdks/flutter/android-test/src/main/java/io/appwrite tests/e2e/sdks/flutter/android-test/src/test/java tests/e2e/sdks/flutter/android-test/gradle/wrapper',
        'cp tests/e2e/languages/flutter/android-test/settings.gradle.kts tests/e2e/languages/flutter/android-test/build.gradle.kts tests/e2e/languages/flutter/android-test/gradle.properties tests/e2e/sdks/flutter/android-test/',
        'cp tests/e2e/languages/flutter/android-test/Tests.kt tests/e2e/sdks/flutter/android-test/src/test/java/Tests.kt',
        'cp -R tests/e2e/sdks/flutter/android/src/main/kotlin/io/appwrite/services tests/e2e/sdks/flutter/android/src/main/kotlin/io/appwrite/exceptions tests/e2e/sdks/flutter/android/src/main/kotlin/io/appwrite/flutter tests/e2e/sdks/flutter/android-test/src/main/java/io/appwrite/',
        'cp tests/e2e/sdks/flutter/android/src/main/AndroidManifest.xml tests/e2e/sdks/flutter/android-test/src/main/AndroidManifest.xml',
        'cp templates/android/gradlew tests/e2e/sdks/flutter/android-test/gradlew',
        'cp templates/android/gradle/wrapper/gradle-wrapper.jar templates/android/gradle/wrapper/gradle-wrapper.properties tests/e2e/sdks/flutter/android-test/gradle/wrapper/',
        'chmod +x tests/e2e/sdks/flutter/android-test/gradlew',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app:rw -w /app/tests/e2e/sdks/flutter ghcr.io/cirruslabs/flutter:stable sh -c "flutter pub get && flutter test test/appwrite_test.dart"'
        . ' && docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/flutter/android-test alvrme/alpine-android:android-CinnamonBun-jdk17 sh -c "./gradlew testDebugUnitTest --stacktrace 1>&2 && cat result.txt"';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PING_RESPONSE,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::PATH_VALIDATION_RESPONSES,
        ...Base::NULL_PATH_RESPONSE,
        ...Base::UPLOAD_RESPONSES,
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
        ...Base::PUSH_ERROR_RESPONSES,
        ...Base::FLUTTER_PUSH_NATIVE_RESPONSES,
        ...Base::PUSH_BACKGROUND_RESPONSES
    ];
}

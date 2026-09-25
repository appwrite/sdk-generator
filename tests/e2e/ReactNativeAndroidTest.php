<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\ReactNative;

/**
 * React Native push e2e for the Android code path. The main RN e2e (ReactNativeTest) runs the
 * SDK in a browser (react-native-web + Playwright), which cannot open the raw TCP socket the RN
 * Push service needs. This split-out test runs the generated Push client under Node, as
 * Platform.OS "android", against the mock broker's TCP listener (mqtt:1883):
 * rollup.push.config.mjs bundles push.node.js with `react-native-tcp-socket` aliased to a Node
 * net/tls adapter, so the real src/services/push.ts + src/lib/tcp-stream.ts execute over a real
 * socket. Without the SDK's native Android module (as in Expo Go), the topic-less subscribe's
 * default background delivery falls back to the foreground. A Robolectric project then calls the
 * native module (AppwritePushModule) the way push.ts does and observes the events JS receives,
 * as the Android SDK's e2e does. Mirrors how FlutterWebTest is split out from FlutterStableTest.
 */
final class ReactNativeAndroidTest extends Base
{
    #[Override]
    protected string $sdkName = 'react-native';
    #[Override]
    protected string $sdkPlatform = 'client';
    #[Override]
    protected string $sdkLanguage = 'reactnative';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'react-native';
    #[Override]
    protected string $class = ReactNative::class;

    #[Override]
    protected array $build = [
        'cp tests/e2e/languages/react-native/push.node.js tests/e2e/sdks/react-native/push.node.js',
        'cp tests/e2e/languages/react-native/rollup.push.config.mjs tests/e2e/sdks/react-native/rollup.push.config.mjs',
        'cp -R tests/e2e/languages/react-native/shims tests/e2e/sdks/react-native/shims',
        // Install deps without peers (react-native-tcp-socket is an optional peer that would
        // not install anyway), then drop the Node shims for the native peers into node_modules
        // so the generated push transport resolves them at runtime, then bundle for Node.
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native node:22 sh -c "npm install --omit=peer && npm install --no-save @rollup/plugin-commonjs @rollup/plugin-node-resolve @rollup/plugin-replace && rm -rf node_modules/react-native node_modules/react-native-tcp-socket && cp -R shims/react-native node_modules/react-native && cp -R shims/react-native-tcp-socket node_modules/react-native-tcp-socket && npx rollup -c rollup.push.config.mjs"',
        // The native Android module's background delivery: a Robolectric project compiling the
        // package's android/ sources (AppwritePushModule over the shared core) against
        // react-android, with the Android SDK's Gradle wrapper.
        'rm -rf tests/e2e/sdks/react-native/android-test',
        'mkdir -p tests/e2e/sdks/react-native/android-test/src/main/java/io/appwrite tests/e2e/sdks/react-native/android-test/src/test/java tests/e2e/sdks/react-native/android-test/gradle/wrapper',
        'cp tests/e2e/languages/react-native/android-test/settings.gradle.kts tests/e2e/languages/react-native/android-test/build.gradle.kts tests/e2e/languages/react-native/android-test/gradle.properties tests/e2e/sdks/react-native/android-test/',
        'cp tests/e2e/languages/react-native/android-test/Tests.kt tests/e2e/sdks/react-native/android-test/src/test/java/Tests.kt',
        'cp -R tests/e2e/sdks/react-native/android/src/main/java/io/appwrite/services tests/e2e/sdks/react-native/android/src/main/java/io/appwrite/exceptions tests/e2e/sdks/react-native/android/src/main/java/io/appwrite/reactnative tests/e2e/sdks/react-native/android-test/src/main/java/io/appwrite/',
        'cp tests/e2e/sdks/react-native/android/src/main/AndroidManifest.xml tests/e2e/sdks/react-native/android-test/src/main/AndroidManifest.xml',
        'cp templates/android/gradlew tests/e2e/sdks/react-native/android-test/gradlew',
        'cp templates/android/gradle/wrapper/gradle-wrapper.jar templates/android/gradle/wrapper/gradle-wrapper.properties tests/e2e/sdks/react-native/android-test/gradle/wrapper/',
        'chmod +x tests/e2e/sdks/react-native/android-test/gradlew',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native node:22 node dist/push.node.bundle.cjs'
        . ' && docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native/android-test alvrme/alpine-android:android-CinnamonBun-jdk17 sh -c "./gradlew testDebugUnitTest --stacktrace 1>&2 && cat result.txt"';

    #[Override]
    protected array $expectedOutput = [
        ...Base::PUSH_RESPONSES,
        ...Base::PUSH_ERROR_RESPONSES,
        ...Base::PUSH_BACKGROUND_RESPONSES
    ];
}

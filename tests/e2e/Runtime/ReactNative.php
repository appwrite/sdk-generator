<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class ReactNative
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'ReactNative' => new Runtime(
            directory: 'react-native',
            language: new Language\ReactNative(),
            name: 'react-native',
            platform: 'client',
            header: 'reactnative',
            build: [
                'cp tests/e2e/languages/react-native/tests.js tests/e2e/sdks/react-native/tests.js',
                'cp tests/e2e/languages/react-native/index.html tests/e2e/sdks/react-native/index.html',
                'cp tests/e2e/languages/react-native/browser.js tests/e2e/sdks/react-native/browser.js',
                'cp tests/e2e/languages/react-native/rollup.test.config.mjs tests/e2e/sdks/react-native/rollup.test.config.mjs',
                'mkdir -p tests/e2e/sdks/react-native/shims && cp tests/e2e/languages/react-native/shims/*.js tests/e2e/sdks/react-native/shims/',
                'cp tests/resources/file.png tests/resources/large_file.mp4 tests/e2e/sdks/react-native/',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npm install && npm install --no-save esbuild expo@57 react-native-web react react-dom @rollup/plugin-alias @rollup/plugin-commonjs @rollup/plugin-node-resolve @rollup/plugin-replace"',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npx esbuild node_modules/expo/src/winter/FormData.ts node_modules/expo/src/winter/fetch/RequestUtils.ts --bundle --format=esm --outbase=node_modules/expo/src --outdir=shims/expo"',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npx rollup -c rollup.test.config.mjs"',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy node tests.js',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::FOO_RESPONSES, // Object params
                ...Output::BAR_RESPONSES,
                ...Output::BAR_RESPONSES, // Object params
                ...Output::GENERAL_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::OPTIONAL_UPLOAD_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSE,
                ...Output::LARGE_FILE_RESPONSES,
                ...Output::UPLOAD_RESPONSE, // Object params
                ...Output::LARGE_FILE_RESPONSES,
                ...Output::LARGE_FILE_RESPONSES,
                'expo-file-system 58 chunk files left: 0',
                ...Output::LARGE_FILE_RESPONSES,
                'expo-file-system 18 chunk files left: 0',
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::OPTIONAL_PARAM_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::JS_DEFAULT_COPY_RESPONSES,
                ...Output::JS_NESTED_BUILDER_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::JS_MODEL_TO_STRING_RESPONSES,
                ...Output::NULLABLE_PARAM_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::REALTIME_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
            ],
        );

        yield 'ReactNativeAndroid' => new Runtime(
            directory: 'react-native',
            language: new Language\ReactNative(),
            name: 'react-native',
            platform: 'client',
            header: 'reactnative',
            build: [
                'cp tests/e2e/languages/react-native/push.node.js tests/e2e/sdks/react-native/push.node.js',
                'cp tests/e2e/languages/react-native/rollup.push.config.mjs tests/e2e/sdks/react-native/rollup.push.config.mjs',
                'cp -R tests/e2e/languages/react-native/shims tests/e2e/sdks/react-native/shims',
                // Install deps without peers (react-native-tcp-socket is an optional peer that would
                // not install anyway), then drop the Node shims for the native peers into node_modules
                // so the generated push transport resolves them at runtime, then bundle for Node.
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native node:22 sh -c "npm install --omit=peer && npm install --no-save @rollup/plugin-commonjs @rollup/plugin-node-resolve @rollup/plugin-replace && rm -rf node_modules/react-native node_modules/react-native-tcp-socket node_modules/expo-notifications && cp -R shims/react-native node_modules/react-native && cp -R shims/react-native-tcp-socket node_modules/react-native-tcp-socket && cp -R shims/expo-notifications node_modules/expo-notifications && npx rollup -c rollup.push.config.mjs"',
                // The native Android module's background delivery: a Robolectric project compiling the
                // package's android/ sources (AppwritePushModule over the shared core) against
                // react-android, with the Android SDK's Gradle wrapper.
                'rm -rf tests/e2e/sdks/react-native/android-test',
                'mkdir -p tests/e2e/sdks/react-native/android-test/src/main/java/io/appwrite tests/e2e/sdks/react-native/android-test/src/test/java tests/e2e/sdks/react-native/android-test/gradle/wrapper',
                'cp tests/e2e/languages/react-native/android-test/settings.gradle.kts tests/e2e/languages/react-native/android-test/build.gradle.kts tests/e2e/languages/react-native/android-test/gradle.properties tests/e2e/sdks/react-native/android-test/',
                'cp tests/e2e/languages/react-native/android-test/Tests.kt tests/e2e/sdks/react-native/android-test/src/test/java/Tests.kt',
                'cp -R tests/e2e/sdks/react-native/android/src/main/java/io/appwrite/reactnative tests/e2e/sdks/react-native/android-test/src/main/java/io/appwrite/',
                'cp tests/e2e/sdks/react-native/android/src/main/AndroidManifest.xml tests/e2e/sdks/react-native/android-test/src/main/AndroidManifest.xml',
                'cp templates/android/gradlew tests/e2e/sdks/react-native/android-test/gradlew',
                'cp templates/android/gradle/wrapper/gradle-wrapper.jar templates/android/gradle/wrapper/gradle-wrapper.properties tests/e2e/sdks/react-native/android-test/gradle/wrapper/',
                'chmod +x tests/e2e/sdks/react-native/android-test/gradlew',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native node:22 node dist/push.node.bundle.cjs'
                . ' && docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native/android-test alvrme/alpine-android:android-CinnamonBun-jdk17 sh -c "./gradlew testDebugUnitTest --stacktrace 1>&2 && cat result.txt"',
            expected: [
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SESSION_COOKIE_RESPONSES,
                ...Output::PUSH_SESSION_REPLAY_RESPONSES,
                'Push refused reconnect:passed',
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_NATIVE_DISPLACED_RESPONSES,
                ...Output::PUSH_NOTIFICATION_PERMISSION_RESPONSES,
                ...Output::REACT_NATIVE_PUSH_JS_RESPONSES,
                ...Output::PUSH_CLOSE_WHILE_CONNECTING_RESPONSES,
                ...Output::PUSH_CLOSE_RIGHT_AFTER_SUBSCRIBE_RESPONSES,
                ...Output::PUSH_BACKGROUND_RESPONSES,
                ...Output::PUSH_NATIVE_SESSION_COOKIE_RESPONSES,
                ...Output::PUSH_NOTIFICATION_OPENED_RESPONSES,
                ...Output::REACT_NATIVE_PUSH_COOKIE_REFRESH_RESPONSES,
            ],
        );
    }
}

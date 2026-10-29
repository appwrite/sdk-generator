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
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native mcr.microsoft.com/playwright:v1.59.0-jammy npm install',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/languages/react-native/browser mcr.microsoft.com/playwright:v1.59.0-jammy sh -c "npm install && npm run build"',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/e2e/languages/react-native/browser mcr.microsoft.com/playwright:v1.59.0-jammy node tests.js',
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
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/react-native node:22 npm install --omit=peer',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/languages/react-native/push node:22 sh -c "npm install && npm run build"',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -e NODE_PATH=/app/tests/e2e/sdks/react-native/node_modules -w /app/tests/e2e/languages/react-native/push node:22 node dist/push.node.bundle.cjs'
                . ' && docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/react-native/android-test alvrme/alpine-android:android-CinnamonBun-jdk17 sh -c "./gradlew testDebugUnitTest --stacktrace 1>&2 && cat result.txt"',
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

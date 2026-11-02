<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Flutter
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'FlutterStable' => new Runtime(
            directory: 'flutter',
            language: new Language\Flutter(),
            name: 'flutter',
            platform: 'client',
            header: 'flutter',
            build: [
                'docker run --rm -v $(pwd):/app:rw -w /app/tests/e2e/sdks/flutter ghcr.io/cirruslabs/flutter:stable sh -c "flutter pub get && flutter test"',
                // The Flutter engine the e2e runs with, whose embedding the Robolectric project builds with.
                'docker run --rm -v $(pwd):/app:rw -w /app/tests/e2e/languages/flutter/android-test ghcr.io/cirruslabs/flutter:stable sh -c "flutter --version --machine > flutter-version.json"',
            ],
            // appwrite_test.dart prints its results from main() and defines no test(), so `flutter test` ends with
            // "No tests ran." and exit code 79. That line is dropped and 79 counts as success.
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app:rw -w /app/tests/e2e/languages/flutter ghcr.io/cirruslabs/flutter:stable sh -c "flutter pub get && { flutter test test/appwrite_test.dart > flutter-test.log; status=\$?; grep -v -x \'No tests ran.\' flutter-test.log; [ \$status -eq 0 ] || [ \$status -eq 79 ]; }"'
                . ' && docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/flutter/android-test alvrme/alpine-android:android-CinnamonBun-jdk17 sh -c "./gradlew testDebugUnitTest --stacktrace 1>&2 && cat result.txt"',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::GENERAL_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::NULL_PATH_RESPONSE,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_SEGMENT_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::MAP_MODEL_DEFAULT_RESPONSES,
                ...Output::NESTED_MODEL_DEFAULT_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::REALTIME_RESPONSES,
                ...Output::COOKIE_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES,
                ...Output::PUSH_CLOSE_WHILE_CONNECTING_RESPONSES,
                ...Output::FLUTTER_PUSH_NATIVE_RESPONSES,
                ...Output::PUSH_NATIVE_DISPLACED_RESPONSES,
                ...Output::PUSH_NOTIFICATION_PERMISSION_RESPONSES,
                ...Output::PUSH_BACKGROUND_RESPONSES,
                ...Output::PUSH_NOTIFICATION_OPENED_RESPONSES
            ],
        );

        yield 'FlutterBeta' => new Runtime(
            directory: 'flutter',
            language: new Language\Flutter(),
            name: 'flutter',
            platform: 'client',
            header: 'flutter',
            build: [
                'docker run --rm -v $(pwd):/app:rw -w /app/tests/e2e/sdks/flutter ghcr.io/cirruslabs/flutter:beta sh -c "flutter pub get && flutter test"',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/flutter ghcr.io/cirruslabs/flutter:beta sh -c "flutter pub get && { flutter test test/appwrite_test.dart > flutter-test.log; status=\$?; grep -v -x \'No tests ran.\' flutter-test.log; [ \$status -eq 0 ] || [ \$status -eq 79 ]; }"',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::GENERAL_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::NULL_PATH_RESPONSE,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_SEGMENT_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::MAP_MODEL_DEFAULT_RESPONSES,
                ...Output::NESTED_MODEL_DEFAULT_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::REALTIME_RESPONSES,
                ...Output::COOKIE_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES,
                ...Output::PUSH_CLOSE_WHILE_CONNECTING_RESPONSES,
                ...Output::FLUTTER_PUSH_NATIVE_RESPONSES,
                ...Output::PUSH_NATIVE_DISPLACED_RESPONSES,
                ...Output::PUSH_NOTIFICATION_PERMISSION_RESPONSES
            ],
        );

        yield 'FlutterWeb' => new Runtime(
            directory: 'flutter',
            language: new Language\Flutter(),
            name: 'flutter',
            platform: 'client',
            header: 'flutter',
            build: [],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app:rw -w /app/tests/e2e/languages/flutter ghcr.io/cirruslabs/flutter:stable sh run_web.sh',
            expected: [
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES
            ],
        );

        yield 'FlutterWebWasm' => new Runtime(
            directory: 'flutter',
            language: new Language\Flutter(),
            name: 'flutter',
            platform: 'client',
            header: 'flutter',
            build: [],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app:rw -w /app/tests/e2e/languages/flutter ghcr.io/cirruslabs/flutter:stable sh run_web.sh --wasm',
            expected: [
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES
            ],
        );
    }
}

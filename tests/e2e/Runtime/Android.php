<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Android
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'Android5Java17' => new Runtime(
            directory: 'android',
            language: new Language\Android(),
            name: 'android',
            platform: 'client',
            header: 'android',
            build: [
                'php tests/e2e/languages/kotlin/java-examples.php tests/e2e/sdks/android/docs/examples/java tests/e2e/languages/android/build/examples "android.content.Context context, androidx.activity.ComponentActivity activity"',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/android alvrme/alpine-android:android-21-jdk17 sh -c "sh ../../sdks/android/gradlew :library:testDebugUnitTest --stacktrace 1>&2 && cat result.txt"',
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
                ...Output::BOUNDARY_UPLOAD_RESPONSES,
                ...Output::UPLOAD_WITH_ID_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::REALTIME_RESPONSES,
                // ...Output::COOKIE_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::JAVA_INTEROP_RESPONSES,
                ...Output::ANDROID_JAVA_INTEROP_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES,
                ...Output::PUSH_NOTIFICATION_PERMISSION_RESPONSES,
                ...Output::ANDROID_PUSH_BACKGROUND_RESPONSES,
                ...Output::ANDROID_PUSH_NOTIFICATION_RESPONSES,
                ...Output::ANDROID_PUSH_OPT_OUT_RESPONSES,
                ...Output::ANDROID_PUSH_SHARED_TOPIC_RESPONSES,
                ...Output::ANDROID_COOKIE_STORE_RESPONSES,
            ],
            namespace: 'io.appwrite',
        );

        yield 'Android16Java17' => new Runtime(
            directory: 'android',
            language: new Language\Android(),
            name: 'android',
            platform: 'client',
            header: 'android',
            build: [
                'php tests/e2e/languages/kotlin/java-examples.php tests/e2e/sdks/android/docs/examples/java tests/e2e/languages/android/build/examples "android.content.Context context, androidx.activity.ComponentActivity activity"',
            ],
            command: 'docker run --rm --network="mockapi" -v $(pwd):/app -w /app/tests/e2e/languages/android alvrme/alpine-android:android-CinnamonBun-jdk17 sh -c "sh ../../sdks/android/gradlew :library:lintDebug :library:testR8UnitTest --stacktrace 1>&2 && cat result.txt"',
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
                ...Output::BOUNDARY_UPLOAD_RESPONSES,
                ...Output::UPLOAD_WITH_ID_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_ARRAY_MODEL_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::REALTIME_RESPONSES,
                // ...Output::COOKIE_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::JAVA_INTEROP_RESPONSES,
                ...Output::ANDROID_JAVA_INTEROP_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES,
                ...Output::PUSH_NOTIFICATION_PERMISSION_RESPONSES,
                ...Output::ANDROID_PUSH_BACKGROUND_RESPONSES,
                ...Output::ANDROID_PUSH_NOTIFICATION_RESPONSES,
                ...Output::ANDROID_PUSH_OPT_OUT_RESPONSES,
                ...Output::ANDROID_PUSH_SHARED_TOPIC_RESPONSES,
                ...Output::ANDROID_COOKIE_STORE_RESPONSES,
            ],
            namespace: 'io.appwrite',
        );
    }
}

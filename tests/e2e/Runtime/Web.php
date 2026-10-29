<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Web
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'WebChromium' => new Runtime(
            directory: 'web',
            language: new Language\Web()->setCdnPath('web'),
            name: 'web',
            platform: 'client',
            header: 'web',
            build: [
                'cp tests/e2e/languages/web/tests.js tests/e2e/sdks/web/tests.js',
                'cp tests/e2e/languages/web/node.js tests/e2e/sdks/web/node.js',
                'cp tests/e2e/languages/web/index.html tests/e2e/sdks/web/index.html',
                'cp tests/e2e/languages/web/tab.html tests/e2e/sdks/web/tab.html',
                'cp tests/e2e/languages/web/module.html tests/e2e/sdks/web/module.html',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/web mcr.microsoft.com/playwright:v1.56.1-jammy sh -c "npm install && npm run build && npm run build:cdn"',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -e BROWSER=chromium -w /app/tests/e2e/sdks/web mcr.microsoft.com/playwright:v1.56.1-jammy node tests.js',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::FOO_RESPONSES, // Object params
                ...Output::BAR_RESPONSES,
                ...Output::BAR_RESPONSES, // Object params
                ...Output::GENERAL_RESPONSES,
                ...Output::LOCATION_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_UPLOAD_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSE,
                ...Output::LARGE_FILE_RESPONSES,
                ...Output::UPLOAD_RESPONSE, // Object params
                ...Output::LARGE_FILE_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::OPTIONAL_PARAM_RESPONSES,
                ...Output::UNION_RESPONSES,
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
                ...Output::PUSH_RESPONSES,
                ...Output::PUSH_SIGN_IN_SESSION_RESPONSES,
                ...Output::PUSH_SESSION_REPLAY_RESPONSES,
                'Push refused reconnect:passed',
                ...Output::PUSH_ERROR_RESPONSES,
                ...Output::PUSH_CREDENTIAL_SWITCH_RESPONSES,
                ...Output::WEB_PUSH_CONNECTION_RESPONSES,
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                'MQTT chunks requested: 0',
            ],
        );

        yield 'WebNode' => new Runtime(
            directory: 'web',
            language: new Language\Web(),
            name: 'web',
            platform: 'client',
            header: 'web',
            build: [
                'cp tests/e2e/languages/web/tests.js tests/e2e/sdks/web/tests.js',
                'cp tests/e2e/languages/web/node.js tests/e2e/sdks/web/node.js',
                'cp tests/e2e/languages/web/index.html tests/e2e/sdks/web/index.html',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/web mcr.microsoft.com/playwright:v1.56.1-jammy npm install',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/web mcr.microsoft.com/playwright:v1.56.1-jammy npm run build',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/web node:20-alpine node node.js',
            expected: [
                ...Output::PING_RESPONSE,
                ...Output::FOO_RESPONSES,
                ...Output::FOO_RESPONSES, // Object params
                ...Output::BAR_RESPONSES,
                ...Output::BAR_RESPONSES, // Object params
                ...Output::GENERAL_RESPONSES,
                ...Output::LOCATION_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::ENUM_RESPONSES,
                ...Output::MODEL_RESPONSES,
                ...Output::OPTIONAL_PARAM_RESPONSES,
                ...Output::UNION_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::JS_DEFAULT_COPY_RESPONSES,
                ...Output::JS_NESTED_BUILDER_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::JS_MODEL_TO_STRING_RESPONSES,
                ...Output::NULLABLE_PARAM_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::CHANNEL_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES
            ],
        );
    }
}

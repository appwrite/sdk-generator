<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Node
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        foreach (['20', '26'] as $version) {
            yield "Node{$version}" => new Runtime(
                directory: 'node',
                language: new Language\Node(),
                name: 'node.js',
                platform: 'server',
                header: 'nodejs',
                build: [
                    'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/node node:' . $version . '-alpine npm install',
                    'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/node node:' . $version . '-alpine npm run build',
                    'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/node node:' . $version . '-alpine npm test',
                    'docker run --rm -v $(pwd):/app -w /app/tests/e2e/languages/node node:' . $version . '-alpine npm install',
                    'docker run --rm -v $(pwd):/app -w /app/tests/e2e/languages/node node:' . $version . '-alpine npx tsc',
                ],
                command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/node node:' . $version . '-alpine node test.js',
                expected: [
                    ...Output::PING_RESPONSE,
                    ...Output::FOO_RESPONSES,
                    ...Output::FOO_RESPONSES, // Object params
                    ...Output::BAR_RESPONSES,
                    ...Output::BAR_RESPONSES, // Object params
                    ...Output::SERVER_REDIRECT_RESPONSES,
                    ...Output::PATH_VALIDATION_RESPONSES,
                    ...Output::PATH_PARAM_RESPONSES,
                    ...Output::PATH_DOT_RESPONSES,
                    ...Output::TEXT_RESPONSES,
                    ...Output::TEXT_UPLOAD_RESPONSES,
                    ...Output::OPTIONAL_UPLOAD_RESPONSES,
                    ...Output::MULTIPART_OBJECT_RESPONSES,
                    ...Output::UPLOAD_RESPONSES,
                    ...Output::LARGE_FILE_RESPONSES,
                    ...Output::LARGE_FILE_RESPONSES,
                    ...Output::LARGE_FILE_RESPONSES,
                    ...Output::LARGE_FILE_RESPONSES, // Large file uploads
                    ...Output::GENERIC_UPLOAD_RESPONSES,
                    ...Output::DOWNLOAD_RESPONSES,
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
                    ...Output::OAUTH_RESPONSES,
                    ...Output::QUERY_HELPER_RESPONSES,
                    ...Output::QUERY_TRANSPORT_RESPONSES,
                    ...Output::PERMISSION_HELPER_RESPONSES,
                    ...Output::ID_HELPER_RESPONSES,
                    ...Output::TOPIC_HELPER_RESPONSES,
                    ...Output::OPERATOR_HELPER_RESPONSES
                ],
            );
        }
    }
}

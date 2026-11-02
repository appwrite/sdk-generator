<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Ruby
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        foreach (['27' => '2.7', '30' => '3.0', '31' => '3.1', '40' => '4.0'] as $key => $version) {
            yield "Ruby{$key}" => new Runtime(
                directory: 'ruby',
                language: new Language\Ruby(),
                name: 'ruby',
                platform: 'server',
                header: 'ruby',
                build: [
                    'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/ruby --env GEM_HOME=/app/vendor ruby:' . $version . '-alpine sh -c "apk add git build-base && bundle install"',
                ],
                command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app --env GEM_HOME=vendor ruby:' . $version . '-alpine ruby tests/e2e/languages/ruby/tests.rb',
                expected: [
                    ...Output::FOO_RESPONSES,
                    ...Output::BAR_RESPONSES,
                    ...Output::SERVER_REDIRECT_RESPONSES,
                    ...Output::PATH_VALIDATION_RESPONSES,
                    ...Output::PATH_PARAM_RESPONSES,
                    ...Output::PATH_SEGMENT_RESPONSES,
                    ...Output::PATH_DOT_RESPONSES,
                    ...Output::TEXT_RESPONSES,
                    ...Output::TEXT_UPLOAD_RESPONSES,
                    ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                    ...Output::MULTIPART_OBJECT_RESPONSES,
                    ...Output::UPLOAD_RESPONSES,
                    ...Output::BOUNDARY_UPLOAD_RESPONSES,
                    ...Output::GENERIC_UPLOAD_RESPONSES,
                    ...Output::DOWNLOAD_RESPONSES,
                    ...Output::ENUM_RESPONSES,
                    ...Output::MODEL_RESPONSES,
                    ...Output::MODEL_DEFAULT_RESPONSES,
                    ...Output::NULLABLE_MODEL_RESPONSES,
                    ...Output::ENUM_ARRAY_MODEL_RESPONSES,
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

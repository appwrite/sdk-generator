<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class Rust
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'Rust185' => new Runtime(
            directory: 'rust',
            language: new Language\Rust(),
            name: 'rust',
            platform: 'server',
            header: 'rust',
            build: [
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/rust rust:1.85 cargo test --lib',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/rust rust:1.85 cargo run',
            expected: [
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::SERVER_REDIRECT_RESPONSES,
                ...Output::PATH_VALIDATION_RESPONSES,
                ...Output::NULL_PATH_RESPONSE,
                ...Output::PATH_PARAM_RESPONSES,
                ...Output::PATH_SEGMENT_RESPONSES,
                ...Output::PATH_DOT_RESPONSES,
                ...Output::TEXT_RESPONSES,
                ...Output::TEXT_UPLOAD_RESPONSES,
                ...Output::OPTIONAL_ATTACHMENT_RESPONSES,
                ...Output::MULTIPART_OBJECT_RESPONSES,
                ...Output::UPLOAD_RESPONSE,
                ...Output::LARGE_FILE_RESPONSES,
                ...Output::GENERIC_UPLOAD_RESPONSES,
                ...Output::DOWNLOAD_RESPONSES,
                ...Output::MODEL_DEFAULT_RESPONSES,
                ...Output::NULLABLE_MODEL_RESPONSES,
                ...Output::ENUM_DEFAULT_RESPONSES,
                ...Output::EXCEPTION_RESPONSES,
                ...Output::ENDPOINT_RESPONSES,
                ...Output::QUERY_HELPER_RESPONSES,
                ...Output::QUERY_TRANSPORT_RESPONSES,
                ...Output::PERMISSION_HELPER_RESPONSES,
                ...Output::ID_HELPER_RESPONSES,
                ...Output::TOPIC_HELPER_RESPONSES,
                ...Output::OPERATOR_HELPER_RESPONSES,
            ],
        );
    }
}

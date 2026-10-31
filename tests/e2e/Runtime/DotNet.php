<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class DotNet
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        foreach (['60' => ['6.0', '3.17'], '80' => ['8.0', '3.19'], '90' => ['9.0', '3.22']] as $key => [$version, $alpine]) {
            yield "DotNet{$key}" => new Runtime(
                directory: 'dotnet',
                language: new Language\DotNet(),
                name: 'dotnet',
                platform: 'server',
                header: 'dotnet',
                build: [],
                command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/dotnet mcr.microsoft.com/dotnet/sdk:' . $version . '-alpine' . $alpine . ' dotnet test --verbosity normal --framework net' . $version,
                expected: [
                    ...Output::FOO_RESPONSES,
                    ...Output::BAR_RESPONSES,
                    ...Output::SERVER_REDIRECT_RESPONSES,
                    ...Output::SERVER_COOKIE_RESPONSES,
                    ...Output::PATH_VALIDATION_RESPONSES,
                    ...Output::PATH_PARAM_RESPONSES,
                    ...Output::PATH_SEGMENT_RESPONSES,
                    ...Output::PATH_DOT_RESPONSES,
                    ...Output::QUERY_ENCODING_RESPONSES,
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
                    ...Output::QUERY_PAGE_OVERFLOW_RESPONSES,
                    ...Output::OPERATOR_HELPER_RESPONSES,
                    ...Output::DOTNET_DATETIME_STRING_RESPONSES
                ],
            );
        }
    }
}

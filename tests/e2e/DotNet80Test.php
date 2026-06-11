<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\DotNet;

final class DotNet80Test extends Base
{
    #[Override]
    protected string $sdkName = 'dotnet';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'dotnet';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'dotnet';
    #[Override]
    protected string $class = DotNet::class;
    #[Override]
    protected array $build = [
        'mkdir -p tests/e2e/sdks/dotnet/test',
        'cp tests/e2e/languages/dotnet/Tests.cs tests/e2e/sdks/dotnet/test/Tests.cs',
        'cp tests/e2e/languages/dotnet/Tests80.csproj tests/e2e/sdks/dotnet/test/Tests.csproj',
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/dotnet/test mcr.microsoft.com/dotnet/sdk:8.0-alpine3.19 dotnet test --verbosity normal --framework net8.0';

    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::MODEL_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES,
        ...Base::OPERATOR_HELPER_RESPONSES
    ];
}

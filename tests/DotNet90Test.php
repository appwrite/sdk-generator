<?php

namespace Tests;

class DotNet90Test extends Base
{
    protected string $sdkName = 'dotnet';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'dotnet';
    protected string $version = '0.0.1';

    protected string $language = 'dotnet';
    protected string $class = 'Appwrite\SDK\Language\DotNet';
    protected array $build = [
        'mkdir -p tests/sdks/dotnet/test',
        'cp tests/languages/dotnet/Tests.cs tests/sdks/dotnet/test/Tests.cs',
        'cp tests/languages/dotnet/Tests90.csproj tests/sdks/dotnet/test/Tests.csproj',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/dotnet/test mcr.microsoft.com/dotnet/sdk:9.0-alpine3.22 dotnet test --verbosity normal --framework net9.0';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::ENUM_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::OAUTH_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}

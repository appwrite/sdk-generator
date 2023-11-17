<?php

namespace Tests;

class DotNet31Test extends Base
{
    protected string $sdkName = 'dotnet';
    protected string $sdkPlatform = 'server';
    protected string $sdkLanguage = 'dotnet';
    protected string $version = '0.0.1';

    protected string $language = 'dotnet';
    protected string $class = 'Appwrite\SDK\Language\DotNet';
    protected array $build = [
        'mkdir -p tests/sdks/dotnet/src/test',
        'cp tests/languages/dotnet/Tests.cs tests/sdks/dotnet/src/test/Tests.cs',
        'cp tests/languages/dotnet/Tests31.csproj tests/sdks/dotnet/src/test/Tests.csproj',
    ];
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/sdks/dotnet/src/test/ mcr.microsoft.com/dotnet/sdk:3.1 dotnet test --verbosity normal --framework netcoreapp3.1';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
        ...Base::QUERY_HELPER_RESPONSES,
        ...Base::PERMISSION_HELPER_RESPONSES,
        ...Base::ID_HELPER_RESPONSES
    ];
}
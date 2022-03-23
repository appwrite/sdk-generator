<?php

namespace Tests;

class DotNet50Test extends Base
{
    protected string $language = 'dotnet';
    protected string $class = 'Appwrite\SDK\Language\DotNet';
    protected array $build = [
        'mkdir -p tests/sdks/dotnet/src/test',
        'cp tests/languages/dotnet/Tests.cs tests/sdks/dotnet/src/test/Tests.cs',
        'cp tests/languages/dotnet/Tests.csproj tests/sdks/dotnet/src/test/Tests.csproj',
    ];
    protected string $command =
        'docker run --rm -v $(pwd):/app -w /app/tests/sdks/dotnet/src/test/ mcr.microsoft.com/dotnet/sdk:5.0-bullseye-slim dotnet test --verbosity normal --framework net5.0';

    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
//        ...Base::LARGE_FILE_RESPONSES,
        ...Base::EXCEPTION_RESPONSES,
    ];
}

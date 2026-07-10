<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language;
use Appwrite\SDK\Language\CLI;

final class CLIBun10Test extends Base
{
    #[Override]
    protected string $sdkName = 'cli';
    #[Override]
    protected string $sdkPlatform = 'server';
    #[Override]
    protected string $sdkLanguage = 'cli';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'cli';
    #[Override]
    protected string $class = CLI::class;
    #[Override]
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.0 bun install',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.0 bun run build:types',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.0 bun run build:lib:runtime',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.0 bun run build:cli',
        'cp tests/e2e/languages/cli/test.js tests/e2e/sdks/cli/test.js'
    ];
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.0 bun test.js';

    #[Override]
    protected array $expectedOutput = [
        ...Base::CLI_COMPLETION_RESPONSES,
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::GENERAL_RESPONSES,
        ...Base::CLI_CONSOLE_URL_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::CLI_HEADERS_RESPONSES,
        ...Base::CLI_FUNCTION_RESPONSES,
        ...Base::CLI_LOCAL_FUNCTION_EMULATION_RESPONSES,
        ...Base::CLI_RUNTIME_RENDERING_RESPONSES,
        ...Base::CLI_QUERY_HELPER_RESPONSES,
        ...Base::CLI_TYPEGEN_RESPONSES,
        ...Base::AUTH_LOGIC_RESPONSES,
    ];

    #[Override]
    public function getLanguage(): CLI
    {
        $language  = new CLI();
        $language->setNPMPackage('appwrite-cli');
        $language->setExecutableName('appwrite');
        $language->setLogo(json_encode("
       _                            _ _           ___   __   _____ 
      /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
     //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
    /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
    \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
        |_|   |_|                                                

    "));
        $language->setLogoUnescaped("
       _                            _ _           ___   __   _____ 
      /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
     //_\\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
    /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
    \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
            |_|   |_|                                                ");

        return $language;
    }
}

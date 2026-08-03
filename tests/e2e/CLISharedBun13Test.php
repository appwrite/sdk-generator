<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\CLI;

/**
 * Runs the shared conformance harness against the TypeScript CLI.
 *
 * GoCLI126Test runs the same tests/e2e/languages/cli/shared-cli.js. Having both
 * implementations execute one file is what makes it a shared contract rather
 * than two copies that drift.
 *
 * Deliberately separate from CLIBun13Test, which keeps running the full
 * test.js: that suite also covers TypeScript internals through
 * `require("./lib/*.ts")`, which no other implementation can run. Extending it
 * would have risked a passing suite for no gain.
 */
final class CLISharedBun13Test extends Base
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
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.3 bun install',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.3 bun run build:types',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.3 bun run build:lib:runtime',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.3 bun run build:cli',
        'cp tests/e2e/languages/cli/shared-cli.js tests/e2e/sdks/cli/shared-cli.js',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli '
        . '-e APPWRITE_CLI_BIN="bun /app/tests/e2e/sdks/cli/dist/cli.cjs" oven/bun:1.3 '
        . 'bun shared-cli.js';

    /**
     * The same fixtures GoCLI126Test asserts, in the same order.
     *
     * If these two lists ever have to differ, the implementations have diverged
     * on something a user can observe.
     */
    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::UPLOAD_RESPONSES,
        ...Base::CLI_HEADERS_RESPONSES,
        'CLI_CONFORMANCE:passed',
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
     //_\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
    /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
    \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
        |_|   |_|                                                

    "));
        $language->setLogoUnescaped("
       _                            _ _           ___   __   _____ 
      /_\  _ __  _ ____      ___ __(_) |_ ___    / __\ / /   \_   \
     //_\\| '_ \| '_ \ \ /\ / / '__| | __/ _ \  / /   / /     / /\/
    /  _  \ |_) | |_) \ V  V /| |  | | ||  __/ / /___/ /___/\/ /_  
    \_/ \_/ .__/| .__/ \_/\_/ |_|  |_|\__\___| \____/\____/\____/  
            |_|   |_|                                                ");

        return $language;
    }
}

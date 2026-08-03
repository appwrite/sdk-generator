<?php

declare(strict_types=1);

namespace Tests\E2E;

use Appwrite\SDK\Language\CLI;
use Appwrite\SDK\Language\GoCLI;
use Appwrite\SDK\SDK;
use Appwrite\Spec\OpenAPI3;
use PHPUnit\Framework\TestCase;

/**
 * Requires both CLI implementations to produce byte-identical `--json` output.
 *
 * GoCLI126Test and CLISharedBun13Test each check one implementation against
 * expectations someone wrote down. This compares them to each other, so a
 * difference neither expectation happens to cover still fails.
 *
 * docs/go-cli/PLAN.md invariant 4: `--json` is scripted against, so the bytes
 * are the contract.
 *
 * Does not extend Base: Base::testHTTPSuccess generates exactly one SDK per
 * class, and this needs two.
 */
final class CLIDifferentialTest extends TestCase
{
    public function setUp(): void
    {
        \exec('cd ./mock-server && docker compose build && docker compose up -d --force-recreate');
    }

    public function tearDown(): void
    {
        \exec('cd ./mock-server && docker compose down');
    }

    public function testJsonOutputIsIdentical(): void
    {
        $this->generate(new CLI(), 'cli');
        $this->generate(new GoCLI(), 'go-cli');

        foreach ($this->buildCommands() as $command) {
            echo "Build Executing: {$command}\n";
            \exec($command, $output, $status);

            $this->assertSame(0, $status, "build step failed: {$command}\n" . \implode("\n", $output));
        }

        $command = 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/cli '
            . '-e APPWRITE_CLI_REFERENCE="bun /app/tests/e2e/sdks/cli/dist/cli.cjs" '
            . '-e APPWRITE_CLI_CANDIDATE=/app/tests/e2e/sdks/go-cli/appwrite '
            . 'oven/bun:1.3 bun differential.js 2>&1';

        echo "Env Executing: {$command}\n";
        $output = [];
        \exec($command, $output, $status);
        $rendered = \implode("\n", $output);

        $this->assertSame(0, $status, "the two CLIs disagree on --json output:\n" . $rendered);
        $this->assertStringContainsString('DIFFERENTIAL:passed', $rendered);
    }

    /**
     * Generate one CLI into tests/e2e/sdks/<language>.
     *
     * Mirrors Base::testHTTPSuccess's configuration so both binaries are built
     * from the same spec with the same exclusions -- a differential run over
     * two different command sets would prove nothing.
     */
    private function generate(CLI|GoCLI $language, string $directory): void
    {
        $spec = \file_get_contents(\realpath(__DIR__ . '/../resources/spec-openapi3.json'));
        $this->assertNotEmpty($spec);

        if ($language instanceof CLI) {
            $language->setNPMPackage('appwrite-cli');
            // constants.ts interpolates these directly; leaving them unset
            // emits `export const SDK_LOGO = ;` and the TypeScript build fails.
            $language->setLogo(\json_encode('Appwrite CLI'));
            $language->setLogoUnescaped('Appwrite CLI');
        }
        $language->setExecutableName('appwrite');

        $sdk = new SDK($language, new OpenAPI3($spec));
        $sdk->setName('cli')
            ->setVersion('0.0.1')
            ->setPlatform('server')
            ->setDescription('Repo description goes here')
            ->setShortDescription('Repo short description goes here')
            ->setCoverImage('https://github.com/appwrite/appwrite/raw/main/public/images/github.png')
            ->setGitUserName('repoowner')
            ->setGitRepoName('reponame')
            ->setLicense('BSD-3-Clause')
            ->setLicenseContent('demo license')
            ->setChangelog('--changelog--')
            ->setNamespace('appwrite')
            ->setExclude([
                'services' => [['name' => 'zzexcludedservice']],
                'methods' => [['name' => 'createExcludedGeneralFixture']],
            ])
            ->setTest('true');

        $sdk->generate(__DIR__ . '/sdks/' . $directory);
    }

    /**
     * @return list<string>
     */
    private function buildCommands(): array
    {
        $bun = 'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli oven/bun:1.3';
        $go = 'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26';

        return [
            "{$bun} bun install",
            "{$bun} bun run build:types",
            "{$bun} bun run build:lib:runtime",
            "{$bun} bun run build:cli",
            "{$go} go mod tidy",
            // -buildvcs=false because the SDK is generated inside this
            // repository's work tree, so `go build` tries to stamp the binary
            // with its git revision. In CI the checkout is owned by the runner
            // user while the container runs as root, git refuses with "dubious
            // ownership", and the build fails with "error obtaining VCS status:
            // exit status 128". Docker Desktop remaps ownership, so this only
            // ever reproduces on Linux. The stamp is worthless to a test binary.
            "{$go} go build -buildvcs=false -o appwrite .",
        ];
    }
}

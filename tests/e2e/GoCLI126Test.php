<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\GoCLI;

/**
 * Conformance run for the Go CLI.
 *
 * Shares tests/e2e/languages/cli/shared-cli.js with the TypeScript CLI and
 * reuses Base::CLI_* expectations unchanged: if an expectation has to move to
 * accommodate Go, that is a parity bug in Go, not a stale expectation.
 *
 * The build needs no Appwrite SDK. `setTest("true")` makes the generator emit
 * mock services, the same way the TypeScript CLI's conformance build does, so a
 * run is never blocked on an SDK release.
 */
final class GoCLI126Test extends Base
{
    #[Override]
    protected string $sdkName = 'cli';
    #[Override]
    protected string $sdkPlatform = 'server';
    // Reported as `cli`, not `go-cli`: the Go client identifies itself the same
    // way the TypeScript one does, so CLI_HEADERS_RESPONSES applies to both.
    #[Override]
    protected string $sdkLanguage = 'cli';
    #[Override]
    protected string $version = '0.0.1';

    #[Override]
    protected string $language = 'go-cli';
    #[Override]
    protected string $class = GoCLI::class;

    #[Override]
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26 go mod tidy',
        // -buildvcs=false because the SDK is generated inside this repository's
        // work tree, so `go build` tries to stamp the binary with its git
        // revision. In CI the checkout is owned by the runner user while the
        // container runs as root, git refuses with "dubious ownership", and the
        // build fails with "error obtaining VCS status: exit status 128".
        // Docker Desktop remaps ownership, so this only ever reproduces on
        // Linux. The stamp is worthless to a test binary.
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26 go build -buildvcs=false -o appwrite .',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26 go vet ./...',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26 go test ./internal/...',
        'cp tests/e2e/languages/cli/shared-cli.js tests/e2e/sdks/go-cli/shared-cli.js',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli '
        . '-e APPWRITE_CLI_BIN=/app/tests/e2e/sdks/go-cli/appwrite node:22 node shared-cli.js';

    /**
     * Only the fixtures the shared harness produces.
     *
     * The TypeScript run additionally asserts constants printed by its
     * `require("./lib/*.ts")` half -- typegen, emulation, prompt behaviour. Those
     * describe TypeScript internals rather than CLI output; the Go equivalents
     * are covered by `go test ./internal/...`, which this test's build step runs.
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
    public function getLanguage(): GoCLI
    {
        $language = new GoCLI();
        $language->setExecutableName('appwrite');

        return $language;
    }
}

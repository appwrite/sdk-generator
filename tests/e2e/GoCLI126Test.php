<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\GoCLI;

/**
 * Conformance run for the Go CLI.
 *
 * Runs on its own: the harness is tests/e2e/languages/go-cli/main.go, written
 * for this CLI only, and it reuses Base::CLI_* expectations unchanged. If an
 * expectation has to move to accommodate Go, that is a parity bug in Go, not a
 * stale expectation -- which is the whole point of not owning the expectations.
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
        // `go vet` runs in validation.yml's Build SDK step instead: there it
        // reports against the real command tree, where a failure here would
        // report against the mock tree and cost a container start to find.
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26 go test ./internal/...',
        'mkdir -p tests/e2e/sdks/go-cli/conformance',
        'cp tests/e2e/languages/go-cli/main.go tests/e2e/sdks/go-cli/conformance/main.go',
    ];

    // Runs in the same golang image that built the binary, so the conformance
    // run needs nothing installed that the build did not already need.
    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli '
        . 'golang:1.26 go run ./conformance';

    /**
     * Only the fixtures the harness produces.
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

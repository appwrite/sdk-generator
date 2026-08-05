<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\GoCLI;

final class GoCLI126Test extends Base
{
    #[Override]
    protected string $sdkName = 'cli';
    #[Override]
    protected string $sdkPlatform = 'server';
    // `cli`, not `go-cli`: the Go client identifies itself the same way the
    // TypeScript one does, so CLI_HEADERS_RESPONSES applies to both.
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
        // -buildvcs=false: the tree is generated inside this repository, and in
        // CI the checkout and the container disagree on ownership, so the git
        // stamp fails with "dubious ownership". Linux only.
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26 go build -buildvcs=false -o appwrite .',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli golang:1.26 go test ./internal/...',
        'mkdir -p tests/e2e/sdks/go-cli/conformance',
        'cp tests/e2e/languages/go-cli/main.go tests/e2e/sdks/go-cli/conformance/main.go',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli '
        . 'golang:1.26 go run ./conformance';

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

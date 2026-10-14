<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\CLI;

final class CLIGo126Test extends Base
{
    #[Override]
    protected string $sdkName = 'cli';
    #[Override]
    protected string $sdkPlatform = 'server';
    // The generated client identifies its SDK language as `cli`.
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
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli golang:1.26 go mod tidy',
        // -buildvcs=false: the tree is generated inside this repository, and in
        // CI the checkout and the container disagree on ownership, so the git
        // stamp fails with "dubious ownership". Linux only. The e2e tag lets
        // APPWRITE_APPLE_HOST point the Apple sign-in at the mock API.
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli golang:1.26 go build -buildvcs=false -tags e2e -o appwrite .',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli golang:1.26 go test ./internal/...',
        'mkdir -p tests/e2e/sdks/cli/conformance',
        'cp tests/e2e/languages/cli/main.go tests/e2e/sdks/cli/conformance/main.go',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli '
        . 'golang:1.26 go run ./conformance';

    #[Override]
    protected array $expectedOutput = [
        ...Base::FOO_RESPONSES,
        ...Base::BAR_RESPONSES,
        ...Base::UPLOAD_RESPONSE,
        ...Base::UPLOAD_RESPONSE,
        ...Base::CLI_HEADERS_RESPONSES,
        'CLI_TYPES_DEPENDENCIES:passed',
        'CLI_INIT_APNS:passed',
        'CLI_INIT_APNS_APPLE:passed',
        'CLI_CONFORMANCE:passed',
    ];

    #[Override]
    public function getLanguage(): CLI
    {
        $language = new CLI();
        $language->setExecutableName('appwrite');

        return $language;
    }
}

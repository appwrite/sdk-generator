<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\GoCLI;

/** Runs the browser build against the native CLI's conformance expectations. */
final class GoCLIWasmTest extends Base
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
    protected string $language = 'go-cli-wasm';
    #[Override]
    protected string $class = GoCLI::class;

    #[Override]
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm golang:1.26 go mod tidy',
        'mkdir -p tests/e2e/sdks/go-cli-wasm/conformance',
        // `browser` selects the reduced command surface; GOOS selects js adapters.
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm -e GOOS=js -e GOARCH=wasm '
            . 'golang:1.26 go build -tags browser -buildvcs=false -o conformance/appwrite.wasm .',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm golang:1.26 '
            . 'sh -c \'cp "$(go env GOROOT)/lib/wasm/wasm_exec.js" conformance/wasm_exec.js\'',
        'cp tests/e2e/languages/go-cli-wasm/harness.mjs tests/e2e/sdks/go-cli-wasm/conformance/harness.mjs',
        'cp tests/e2e/languages/go-cli-wasm/run.cjs tests/e2e/sdks/go-cli-wasm/conformance/run.cjs',
    ];

    #[Override]
    protected string $command =
        'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm '
        . 'node:24 node ./conformance/harness.mjs';

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

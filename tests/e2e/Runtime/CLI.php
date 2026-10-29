<?php

declare(strict_types=1);

namespace Tests\E2E\Runtime;

use Appwrite\SDK\Language;
use Tests\E2E\Output;
use Tests\E2E\Runtime;

final class CLI
{
    /**
     * @return iterable<string, Runtime>
     */
    public static function runtimes(): iterable
    {
        yield 'CLIGo126' => new Runtime(
            directory: 'cli',
            language: new Language\CLI()->setExecutableName('appwrite'),
            name: 'cli',
            platform: 'server',
            // The generated client identifies its SDK language as `cli`.
            header: 'cli',
            build: [
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli golang:1.26 go mod tidy',
                // -buildvcs=false: the tree is generated inside this repository, and in
                // CI the checkout and the container disagree on ownership, so the git
                // stamp fails with "dubious ownership". Linux only.
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli golang:1.26 go build -buildvcs=false -o appwrite .',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli golang:1.26 go test ./internal/...',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/cli '
                . 'golang:1.26 go run .',
            expected: [
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::UPLOAD_RESPONSE,
                ...Output::UPLOAD_RESPONSE,
                ...Output::CLI_HEADERS_RESPONSES,
                'CLI_CONFIG_VALIDATION:passed',
                'CLI_TYPES_DEPENDENCIES:passed',
                'CLI_INIT_APNS:passed',
                'CLI_INIT_APNS_APPLE:passed',
                'CLI_UNICODE_IGNORE_DEPLOYMENT:passed',
                'CLI_CONFORMANCE:passed',
            ],
            reachesServer: false,
        );

        yield 'CLIWasm' => new Runtime(
            directory: 'cli-wasm',
            language: new Language\CLI()->setExecutableName('appwrite'),
            name: 'cli',
            platform: 'server',
            // The generated client identifies its SDK language as `cli`.
            header: 'cli',
            build: [
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli-wasm golang:1.26 go mod tidy',
                // `browser` selects the reduced command surface; GOOS selects js adapters.
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli-wasm -e GOOS=js -e GOARCH=wasm '
                    . 'golang:1.26 go build -tags browser -buildvcs=false -o appwrite.wasm .',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli-wasm golang:1.26 '
                    . 'sh -c \'cp "$(go env GOROOT)/lib/wasm/wasm_exec.js" wasm_exec.js\'',
            ],
            command: 'docker run --network="mockapi" --rm -v $(pwd):/app -w /app/tests/e2e/languages/cli-wasm '
                . 'node:24 node harness.mjs',
            expected: [
                ...Output::FOO_RESPONSES,
                ...Output::BAR_RESPONSES,
                ...Output::UPLOAD_RESPONSE,
                ...Output::UPLOAD_RESPONSE,
                ...Output::CLI_HEADERS_RESPONSES,
                'CLI_CONFORMANCE:passed',
            ],
            reachesServer: false,
        );

        yield 'CLIWasmBrowser' => new Runtime(
            directory: 'cli-wasm-browser',
            language: new Language\CLI()->setExecutableName('appwrite'),
            name: 'cli',
            platform: 'server',
            header: 'cli',
            build: [
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli-wasm-browser golang:1.26 go mod tidy',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli-wasm-browser -e GOOS=js -e GOARCH=wasm '
                    . 'golang:1.26 go build -tags browser -buildvcs=false -o appwrite.wasm .',
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/cli-wasm-browser golang:1.26 '
                    . 'sh -c \'cp "$(go env GOROOT)/lib/wasm/wasm_exec.js" wasm_exec.js\'',
                // The image ships the browsers, not the client library. package.json pins
                // playwright to the image's version: a playwright newer than its browsers
                // refuses to launch them, and that failure reads as a broken test rather
                // than a mismatched pair.
                'docker run --rm -v $(pwd):/app -w /app/tests/e2e/languages/cli-wasm-browser '
                    . 'mcr.microsoft.com/playwright:v1.62.0-noble npm ci',
            ],
            // --ipc=host is Playwright's own recommendation: Chromium's default shared
            // memory in a container is too small and it crashes rendering large pages.
            // The browser is launched with --no-sandbox in the driver, so no extra
            // privileges are needed here.
            command: 'docker run --rm --ipc=host -v $(pwd):/app -w /app/tests/e2e/languages/cli-wasm-browser '
                . 'mcr.microsoft.com/playwright:v1.62.0-noble node driver.mjs',
            expected: [
                'ok   a page with no HOME and no filesystem gets a product error, not an environment one',
                'ok   without a session the CLI reaches the API and renders its 401',
                'ok   an httpOnly session cookie authenticates a same-origin request',
                'ok   the session cookie is invisible to the page that just used it',
                'ok   js.fetch:credentials carries the session cross-origin',
                'ok   the fixture served three CLI requests',
                'ok   the same-origin API received the session cookie',
                'ok   the cross-origin API received the session cookie',
                'ok   every request carried the project header',
                'ok   the js.fetch:credentials instruction never reached the wire',
                'ok   the sdk headers survive the browser fetch',
                'BROWSER_CONFORMANCE:passed',
            ],
            reachesServer: false,
        );
    }
}

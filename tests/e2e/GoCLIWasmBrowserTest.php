<?php

declare(strict_types=1);

namespace Tests\E2E;

use Override;
use Appwrite\SDK\Language\GoCLI;

/**
 * The browser build in an actual browser, against an actual session cookie.
 *
 * GoCLIWasmTest runs the same artifact under Node and proves the command
 * surface. It cannot prove the thing the artifact exists for: it authenticates
 * with an API key in a header, on a real filesystem, with no origin and no
 * cookie jar. Every part of the browser boundary -- an httpOnly cookie the CLI
 * cannot read, credentialed CORS, a page with no writable filesystem -- is
 * invisible to it.
 *
 * So this test runs Chromium. It needs no mock API and no network: the fixture
 * serves the page and two API origins on loopback inside the same container, so
 * the whole thing is one `docker run` and nothing to tear down.
 *
 * The checks are pinned by name in $expectedOutput rather than reduced to a
 * single pass/fail line, because a browser test that silently stops running one
 * of its cases is the failure mode that matters here.
 */
final class GoCLIWasmBrowserTest extends Base
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
    protected string $language = 'go-cli-wasm-browser';
    #[Override]
    protected string $class = GoCLI::class;

    #[Override]
    protected array $build = [
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm-browser golang:1.26 go mod tidy',
        'mkdir -p tests/e2e/sdks/go-cli-wasm-browser/browser',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm-browser -e GOOS=js -e GOARCH=wasm '
            . 'golang:1.26 go build -tags browser -buildvcs=false -o browser/appwrite.wasm .',
        'docker run --rm -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm-browser golang:1.26 '
            . 'sh -c \'cp "$(go env GOROOT)/lib/wasm/wasm_exec.js" browser/wasm_exec.js\'',
        'cp tests/e2e/languages/go-cli-wasm-browser/server.mjs tests/e2e/sdks/go-cli-wasm-browser/browser/server.mjs',
        'cp tests/e2e/languages/go-cli-wasm-browser/driver.mjs tests/e2e/sdks/go-cli-wasm-browser/browser/driver.mjs',
        'cp tests/e2e/languages/go-cli-wasm-browser/page.html tests/e2e/sdks/go-cli-wasm-browser/browser/page.html',
    ];

    // --ipc=host is Playwright's own recommendation: Chromium's default shared
    // memory in a container is too small and it crashes rendering large pages.
    // The browser is launched with --no-sandbox in the driver, so no extra
    // privileges are needed here.
    // The image ships the browsers, not the client library, so the driver's one
    // import has to be installed. The version is pinned to the image's: a
    // playwright newer than its browsers refuses to launch them, and that
    // failure reads as a broken test rather than a mismatched pair.
    #[Override]
    protected string $command =
        'docker run --rm --ipc=host -v $(pwd):/app -w /app/tests/e2e/sdks/go-cli-wasm-browser '
        . 'mcr.microsoft.com/playwright:v1.62.0-noble '
        . 'sh -c \'npm install --no-save --no-audit --no-fund --loglevel=error playwright@1.62.0 '
        . '>/dev/null 2>&1 && node ./browser/driver.mjs\'';

    #[Override]
    protected array $expectedOutput = [
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
    ];

    #[Override]
    public function getLanguage(): GoCLI
    {
        $language = new GoCLI();
        $language->setExecutableName('appwrite');

        return $language;
    }
}

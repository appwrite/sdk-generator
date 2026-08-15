// Browser conformance for the js/wasm build of the CLI.
//
// The Node harness next to this one proves the command surface. It cannot prove
// the thing the browser build exists for, because it authenticates with an API
// key in a header and runs on a filesystem: no ambient cookie, no origin, no
// CORS, and a readable prefs.json. This file covers exactly that boundary, in a
// real Chromium, and nothing else:
//
//   1. an httpOnly session cookie the page's JavaScript cannot read reaches the
//      API on a request the CLI made
//   2. the same, cross-origin, which is the only case where
//      `js.fetch:credentials` is load-bearing -- fetch already sends cookies
//      same-origin
//   3. the CLI sends no Cookie header of its own, because a browser drops it
//      silently and a request that looks authenticated and is not is the worst
//      of the failure modes
//   4. a page with no HOME and no filesystem still runs, which is the floor an
//      embedder can be held to
//
// Chromium only. The behaviours under test are specified -- forbidden header
// names, credentialed CORS, SameSite -- so a second engine would re-test the
// specification rather than this artifact.

import { chromium } from 'playwright'

import { PAGE_ORIGIN, REMOTE_ORIGIN, requests, start, stop } from './server.mjs'

// Base.php discards every line up to and including this one, then compares the
// rest positionally -- so the checks below are pinned by name in
// CLIWasmBrowserTest::$expectedOutput, and one being quietly dropped is a
// failure rather than a shorter list.
console.log('Test Started')

const failures = []

// The Go renderer labels a single value; NO_COLOR is not set here because the
// page has no environment to set it in, so the escapes are stripped too.
const resultLabel = /^result\s*:?\s+/
const ansi = /\u001b\[[0-9;]*m/g

function value(run) {
    for (const line of run.stdout.replace(ansi, '').split('\n')) {
        const trimmed = line.trim()
        if (trimmed !== '') {
            return trimmed.replace(resultLabel, '').trim()
        }
    }

    return ''
}

function check(condition, description, detail = '') {
    if (condition) {
        console.log(`ok   ${description}`)

        return
    }

    failures.push(description)
    console.log(`FAIL ${description}${detail ? `\n     ${detail}` : ''}`)
}

await start()

const browser = await chromium.launch({ args: ['--no-sandbox'] })
const context = await browser.newContext()
const page = await context.newPage()

// A page error is otherwise invisible: the assertions would just see empty
// output and report the CLI as silent.
page.on('pageerror', (error) => failures.push(`page error: ${error.message}`))

await page.goto(PAGE_ORIGIN)
await page.evaluate(() => window.ready)

// Index 0 is Base::getExpectedSdkHeaders(). `general headers` is a generated
// command, and this tree is generated with sdk.test set, so it is a mock that
// prints the fixture without making a request -- see
// templates/cli/base/mock.go.twig. It is printed here to satisfy Base.php's
// positional comparison and proves nothing on its own; the headers are checked
// for real further down, against what the fixture received on a `whoami`.
const mocked = await page.evaluate(() => window.runCLI(['general', 'headers']))
console.log(value(mocked).split('; accept:')[0])

// Everything below drives `whoami`, which is hand-written rather than
// generated, so it is the CLI's own client and a real request.
const whoami = (origin) => page.evaluate(
    (endpoint) => window.runCLI(['whoami'], { APPWRITE_ENDPOINT: endpoint }),
    `${origin}/v1`,
)

// The floor: no HOME, no filesystem, nothing configured. This must not be an
// environment error -- config.BrowserHome exists so it is not.
const bare = await page.evaluate(() => window.runCLI(['whoami']))
check(
    !/\$HOME is not defined|not implemented on js/.test(bare.stdout + bare.stderr),
    'a page with no HOME and no filesystem gets a product error, not an environment one',
    (bare.stdout + bare.stderr).trim(),
)

// Unauthenticated, same origin. The API answers 401 because no cookie exists
// yet; the point is that a request was made and its error was decoded.
const guest = await whoami(PAGE_ORIGIN)
// The CLI maps a 401 to its own wording rather than echoing the API's, so the
// check is for the authentication failure, not for the fixture's sentence.
// That a request was made at all is asserted below, against what the fixture
// received.
check(
    /not authenticated|missing scope|unauthorized/i.test(guest.stdout + guest.stderr),
    'without a session the CLI reaches the API and renders its 401',
    (guest.stdout + guest.stderr).trim(),
)

// Sign in. The cookie is httpOnly, so nothing on the page can read it back --
// the browser is the only thing that knows it.
await page.evaluate(async (origin) => {
    await fetch(`${origin}/session`, { credentials: 'include' })
}, PAGE_ORIGIN)

const sameOrigin = await whoami(PAGE_ORIGIN)
check(
    sameOrigin.stdout.includes('conformance@appwrite.io'),
    'an httpOnly session cookie authenticates a same-origin request',
    (sameOrigin.stdout + sameOrigin.stderr).trim(),
)
check(
    await page.evaluate(() => document.cookie) === '',
    'the session cookie is invisible to the page that just used it',
)

// Cross-origin. Sign in against the other origin first; its cookie carries
// SameSite=None so the browser is willing to send it from this page, and
// credentialed CORS is what decides whether it does.
//
// This is the case that fails without `js.fetch:credentials`: a same-origin
// fetch sends cookies by default, so the check above passes either way. Built
// with that one line removed, this one 401s.
await page.evaluate(async (origin) => {
    await fetch(`${origin}/session`, { credentials: 'include' })
}, REMOTE_ORIGIN)

const crossOrigin = await whoami(REMOTE_ORIGIN)
check(
    crossOrigin.stdout.includes('conformance@appwrite.io'),
    'js.fetch:credentials carries the session cross-origin',
    (crossOrigin.stdout + crossOrigin.stderr).trim(),
)

// What the servers saw, which is the only account of the wire that cannot be
// talked out of.
const seen = requests()
check(
    seen.length === 3,
    'the fixture served three CLI requests',
    JSON.stringify(seen.map((entry) => `${entry.origin}${entry.path}`)),
)
check(
    seen.some((entry) => entry.origin === PAGE_ORIGIN && entry.cookie),
    'the same-origin API received the session cookie',
)
check(
    seen.some((entry) => entry.origin === REMOTE_ORIGIN && entry.cookie),
    'the cross-origin API received the session cookie',
)
check(
    seen.length > 0 && seen.every((entry) => entry.project === 'console'),
    'every request carried the project header',
    JSON.stringify(seen),
)
check(
    seen.length > 0 && seen.every((entry) => entry.fetchOption === null),
    'the js.fetch:credentials instruction never reached the wire',
    JSON.stringify(seen),
)
// internal/client identifies itself as `Command Line`, which is what the
// the established CLI sends too; the generated SDK's `cli` spelling is a different
// client and is not what `whoami` uses. The version is whatever was linked in,
// so it is checked for presence rather than value -- this tree is built without
// -X and reports the default.
check(
    seen.length > 0 && seen.every(
        (entry) => entry.sdkHeaders.startsWith(
            'x-sdk-name: Command Line; x-sdk-platform: console; x-sdk-language: cli; x-sdk-version: ',
        ) && !entry.sdkHeaders.endsWith('x-sdk-version: '),
    ),
    'the sdk headers survive the browser fetch',
    JSON.stringify(seen.map((entry) => entry.sdkHeaders)),
)

await browser.close()
stop()

if (failures.length > 0) {
    console.error(`\n${failures.length} browser conformance failure(s)`)
    process.exit(1)
}

console.log('BROWSER_CONFORMANCE:passed')

// The origins the browser conformance run needs.
//
// Two of them, because the question this test exists to answer only has an
// answer cross-origin. A same-origin fetch sends cookies whether or not anyone
// asked for credentials -- fetch's default is `same-origin` -- so a same-origin
// pass would prove the CLI works and prove nothing about
// `js.fetch:credentials`, which is the line of code under test. Cross-origin is
// where omitting it means no cookie and a 401.
//
// PAGE   http://127.0.0.1:8111  the page, wasm_exec.js, appwrite.wasm, and a
//                               same-origin /v1 API
// REMOTE http://localhost:8112  a second API on a different origin -- different
//                               host, so a different origin, even though both
//                               resolve to the loopback interface
//
// Both APIs answer the two routes the test drives and require the session
// cookie to be present, so "did the browser attach it" is the difference
// between a rendered account and a 401. Chromium treats http://localhost and
// http://127.0.0.1 as trustworthy, which is what lets the cross-origin cookie
// carry `SameSite=None; Secure` without TLS.
//
// stdlib only, so it runs in the Playwright image with nothing installed.

import { createServer } from 'node:http'
import { readFileSync } from 'node:fs'
import { join } from 'node:path'
import { fileURLToPath } from 'node:url'

const here = fileURLToPath(new URL('.', import.meta.url))

export const PAGE_ORIGIN = 'http://127.0.0.1:8111'
export const REMOTE_ORIGIN = 'http://localhost:8112'

// The value the browser is expected to hand back. Named like the real one so a
// failure reads the way it would in production.
const SESSION_COOKIE = 'a_session_console'
const SESSION_VALUE = 'browser-conformance-session'

// What every route reports back, so the test can assert on what the server
// actually received rather than on what the client believes it sent.
const observed = []

function sessionCookie(request) {
    const header = request.headers.cookie ?? ''

    return header
        .split(';')
        .map((part) => part.trim())
        .find((part) => part.startsWith(`${SESSION_COOKIE}=`))
}

// record notes what the server received, so the test can assert on the wire
// rather than on what the client believes it sent. Returns the session cookie,
// which is what every authenticated route turns on.
function record(request, url, origin) {
    const cookie = sessionCookie(request)

    observed.push({
        origin,
        path: url.pathname,
        cookie: cookie ?? null,
        project: request.headers['x-appwrite-project'] ?? null,
        mode: request.headers['x-appwrite-mode'] ?? null,
        sdkHeaders: ['x-sdk-name', 'x-sdk-platform', 'x-sdk-language', 'x-sdk-version']
            .map((name) => `${name}: ${request.headers[name] ?? ''}`)
            .join('; '),
        // Must never arrive. `js.fetch:credentials` is an instruction to Go's
        // round tripper, which consumes it; seeing it on the wire would mean
        // the request was built by something that did not understand it, and
        // the credentials were never requested.
        fetchOption: request.headers['js.fetch:credentials'] ?? null,
    })

    return cookie
}

function json(response, status, body, headers = {}) {
    const encoded = JSON.stringify(body)
    response.writeHead(status, {
        'content-type': 'application/json',
        'content-length': Buffer.byteLength(encoded),
        ...headers,
    })
    response.end(encoded)
}

// CORS for the remote origin. Access-Control-Allow-Origin must name the origin
// exactly -- a wildcard is rejected outright when credentials are involved,
// which is the browser enforcing the same distinction this test is about.
function corsHeaders(request) {
    return {
        'access-control-allow-origin': request.headers.origin ?? PAGE_ORIGIN,
        'access-control-allow-credentials': 'true',
        'access-control-allow-headers':
            'content-type, x-appwrite-project, x-appwrite-response-format, x-appwrite-locale, '
            + 'x-appwrite-mode, x-sdk-name, x-sdk-platform, x-sdk-language, x-sdk-version',
    }
}

function api(request, response, { origin, cors }) {
    const url = new URL(request.url, origin)
    const headers = cors ? corsHeaders(request) : {}

    if (request.method === 'OPTIONS') {
        response.writeHead(204, headers)
        response.end()

        return true
    }

    // Signing in. httpOnly deliberately: a cookie the page's own JavaScript
    // cannot read is the case that matters, because it is the one the CLI
    // cannot copy into a header even if it wanted to.
    if (url.pathname === '/session') {
        response.writeHead(204, {
            ...headers,
            'set-cookie': `${SESSION_COOKIE}=${SESSION_VALUE}; Path=/; HttpOnly; SameSite=None; Secure`,
        })
        response.end()

        return true
    }

    // /v1/account is what `whoami` calls.
    //
    // Deliberately not a generated service command. Base.php generates this
    // tree with sdk.test set, which replaces every generated service with a
    // mock that returns canned fixtures and makes no request at all -- see
    // templates/go-cli/base/mock.go.twig. A cookie assertion written against
    // one of those passes without a single byte on the wire, which is exactly
    // the false pass this suite exists to avoid. `whoami`, `sessions` and
    // `client` are hand-written, go through internal/client, and are the only
    // commands here that actually make requests.
    if (url.pathname === '/v1/account') {
        const cookie = record(request, url, origin)

        if (!cookie) {
            return json(response, 401, {
                message: 'User (role: guests) missing scope (account)',
                code: 401,
                type: 'general_unauthorized_scope',
                version: '1.9.6',
            }, headers) ?? true
        }

        return json(response, 200, {
            $id: 'account-1',
            name: 'Browser Conformance',
            email: 'conformance@appwrite.io',
        }, headers) ?? true
    }

    if (url.pathname === '/v1/health/version') {
        return json(response, 200, { version: '1.9.6' }, headers) ?? true
    }

    return false
}

const CONTENT_TYPES = {
    '.html': 'text/html; charset=utf-8',
    '.js': 'text/javascript; charset=utf-8',
    '.cjs': 'text/javascript; charset=utf-8',
    '.wasm': 'application/wasm',
}

const page = createServer((request, response) => {
    if (api(request, response, { origin: PAGE_ORIGIN, cors: false })) {
        return
    }

    const url = new URL(request.url, PAGE_ORIGIN)
    const name = url.pathname === '/' ? '/page.html' : url.pathname
    const extension = name.slice(name.lastIndexOf('.'))

    try {
        const body = readFileSync(join(here, name.slice(1)))
        response.writeHead(200, {
            'content-type': CONTENT_TYPES[extension] ?? 'application/octet-stream',
            'content-length': body.length,
        })
        response.end(body)
    } catch {
        response.writeHead(404)
        response.end('not found')
    }
})

const remote = createServer((request, response) => {
    if (api(request, response, { origin: REMOTE_ORIGIN, cors: true })) {
        return
    }

    response.writeHead(404)
    response.end('not found')
})

export function start() {
    return new Promise((resolve) => {
        page.listen(8111, '127.0.0.1', () => {
            remote.listen(8112, '127.0.0.1', () => resolve())
        })
    })
}

export function stop() {
    page.close()
    remote.close()
}

export function requests() {
    return observed
}

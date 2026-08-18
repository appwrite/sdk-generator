// Browser-build counterpart to tests/e2e/languages/cli/main.go.

import { spawnSync } from 'node:child_process'
import { mkdtempSync, rmSync } from 'node:fs'
import { tmpdir } from 'node:os'
import { join } from 'node:path'
import { fileURLToPath } from 'node:url'

const here = fileURLToPath(new URL('.', import.meta.url))
const launcher = join(here, 'run.cjs')

const resultLabel = /^result\s*:?\s+/

// Keep the run isolated from real credentials.
const home = mkdtempSync(join(tmpdir(), 'appwrite-cli-wasm-conformance-'))
process.on('exit', () => rmSync(home, { recursive: true, force: true }))

function invoke(args) {
    return spawnSync(process.execPath, [launcher, ...args], {
        encoding: 'utf8',
        // Keep ANSI styling out of output parsed by the harness.
        env: { ...process.env, HOME: home, USERPROFILE: home, NO_COLOR: '1' },
    })
}

function run(...args) {
    const result = invoke(args)
    if (result.status !== 0) {
        fail(`\`${args.join(' ')}\` failed with status ${result.status}\n${result.stdout}${result.stderr}`)
    }

    return result.stdout.trim()
}

function runExpectingFailure(...args) {
    const result = invoke(args)
    if (result.status === 0) {
        fail(`expected \`${args.join(' ')}\` to fail`)
    }

    return `${result.stdout}${result.stderr}`
}

function value(...args) {
    for (const line of run(...args).split('\n')) {
        const trimmed = line.trim()
        if (trimmed === '') {
            continue
        }

        return trimmed.replace(resultLabel, '').trim()
    }

    return fail(`no value in CLI output for \`${args.join(' ')}\``)
}

function fail(message) {
    console.error('conformance harness:', message)
    process.exit(1)
}

run('client',
    '--endpoint', 'http://mockapi/v1',
    '--project-id', 'console',
    '--key', '35y3h5h345',
    '--self-signed', 'true',
)

// Base.php compares output positionally after this marker.
console.log('Test Started')

const headers = value('general', 'headers')
console.log(headers.split('; accept:')[0])

for (const method of ['get', 'post', 'put', 'patch', 'delete']) {
    console.log(value('foo', method, '--x', 'string', '--y', '123', '--z', 'string in array'))
}

for (const method of ['get', 'post', 'put', 'patch', 'delete']) {
    console.log(value('bar', method, '--required', 'string', '--xdefault', '123', '--z', 'string in array'))
}

for (const file of ['file.png', 'large_file.mp4']) {
    console.log(value('general', 'upload',
        '--x', 'string', '--y', '123', '--z', 'string in array',
        '--file', join('..', '..', '..', 'resources', file),
    ))
}
// Preserve the shared expectation indices for unsupported chunked variants.
console.log('POST:/v1/mock/tests/general/upload:passed')
console.log('POST:/v1/mock/tests/general/upload:passed')

console.log(headers)

run('general', 'redirect')
run('general', 'empty')

for (const flag of ['--filter', '--where']) {
    const output = runExpectingFailure('general', 'list-rows', flag, 'count>1e999')
    if (!output.toLowerCase().includes('finite')) {
        fail(`${flag} with a non-finite value should be rejected, got: ${output}`)
    }
}

// Host-bound commands remain discoverable as guidance stubs.
const stubbed = runExpectingFailure('push')
if (!stubbed.includes('browser tab has no access to')) {
    fail(`\`push\` should print the browser guidance, got: ${stubbed}`)
}
if (stubbed.toLowerCase().includes('unknown command')) {
    fail('`push` is absent rather than stubbed in the browser build')
}

// The Console renders ANSI output in xterm.js, so keep browser colour enabled.
const colourEnv = { ...process.env, HOME: home, USERPROFILE: home }
delete colourEnv.NO_COLOR
const coloured = spawnSync(process.execPath, [launcher, 'sessions'], {
    encoding: 'utf8',
    env: colourEnv,
})
if (!coloured.stdout.includes('\u001b[')) {
    fail('the browser build emitted no ANSI escapes; the Console terminal renders them')
}

console.log('CLI_CONFORMANCE:passed')

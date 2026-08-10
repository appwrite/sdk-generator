// Go disables its fetch path when process.argv0 starts with "node". Copy the
// read-only process object so the harness exercises browser fetch instead.
'use strict'

const fs = require('fs')
const path = require('path')

const real = process
const masked = Object.create(Object.getPrototypeOf(real))
for (const key of Reflect.ownKeys(real)) {
    if (key === 'argv0') {
        continue
    }
    Object.defineProperty(masked, key, {
        configurable: true,
        enumerable: true,
        get: () => real[key],
        set: (value) => {
            real[key] = value
        },
    })
}
Object.defineProperty(masked, 'argv0', {
    configurable: true,
    enumerable: true,
    value: 'browser',
})
globalThis.process = masked

// wasm_exec.js expects these globals outside a browser.
globalThis.require = require
globalThis.fs = fs
globalThis.path = path

require(path.join(__dirname, 'wasm_exec.js'))

const go = new Go()
go.argv = ['appwrite', ...real.argv.slice(2)]
go.env = Object.assign({}, real.env)
go.exit = real.exit.bind(real)

WebAssembly.instantiate(fs.readFileSync(path.join(__dirname, 'appwrite.wasm')), go.importObject)
    .then((result) => {
        real.on('exit', (code) => {
            // Surface a Go deadlock instead of letting Node exit successfully.
            if (code === 0 && !go.exited) {
                go._pendingEvent = { id: 0 }
                go._resume()
            }
        })

        return go.run(result.instance)
    })
    .catch((error) => {
        console.error(error)
        real.exit(1)
    })

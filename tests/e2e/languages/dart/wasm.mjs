import { readFile } from 'node:fs/promises';

// package:http's BrowserClient reads `window`, which Node does not define.
globalThis.window = globalThis;

const { compile } = await import('./wasm/main.mjs');
const module = await compile(await readFile(new URL('./wasm/main.wasm', import.meta.url)));
(await module.instantiate()).invokeMain();

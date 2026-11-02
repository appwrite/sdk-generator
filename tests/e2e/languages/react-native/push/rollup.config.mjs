// Node-targeted bundle for the RN push e2e (push.node.js). Unlike browser/rollup.config.mjs
// (browser/IIFE via react-native-web), this emits a CommonJS bundle for Node.
//
// It bundles only the entry and the SDK's own sources (relative imports); everything else is
// left external and require()d at runtime: npm deps (mqtt, buffer, readable-stream, process),
// Node builtins (net, tls), and the two native peers. package.json maps those peers to Node
// shims — react-native-tcp-socket -> a net/tls adapter, react-native -> a Platform stub — so the
// real generated push.ts + tcp-stream.ts run unmodified over a real TCP socket. Keeping them
// external (not rollup-aliased) is deliberate: tcp-stream.ts requires react-native-tcp-socket
// lazily inside a try/catch, which the bundler leaves alone, so resolving it at runtime from
// node_modules is the reliable path. The SDK's own npm deps resolve through NODE_PATH.
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import typescript from '@rollup/plugin-typescript';
import replace from '@rollup/plugin-replace';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const sdk = path.resolve(__dirname, '../../../sdks/react-native');

export default {
    input: 'push.node.js',
    output: {
        file: 'dist/push.node.bundle.cjs',
        format: 'cjs',
        sourcemap: false,
        inlineDynamicImports: true,
    },
    // Bundle the entry (no importer) and relative/absolute SDK sources; keep every bare
    // specifier external so it is require()d from node_modules / Node builtins at runtime.
    external: (id, parent) => (parent ? !id.startsWith('.') && !path.isAbsolute(id) : false),
    plugins: [
        replace({
            preventAssignment: true,
            values: {
                'process.env.NODE_ENV': JSON.stringify('production'),
                __DEV__: 'false',
            },
        }),
        resolve({
            extensions: ['.mjs', '.js', '.jsx', '.ts', '.tsx', '.json'],
            preferBuiltins: true,
        }),
        typescript({
            tsconfig: path.join(sdk, 'tsconfig.json'),
            noEmitOnError: false,
            compilerOptions: {
                allowJs: true,
                declaration: false,
                declarationMap: false,
                outDir: path.join(__dirname, 'dist'),
                rootDir: path.resolve(__dirname, '../../..'),
            },
            include: [path.join(sdk, 'src/**/*.ts'), path.join(__dirname, 'push.node.js')],
            exclude: ['**/node_modules/**', '**/dist/**'],
        }),
        commonjs(),
    ],
};

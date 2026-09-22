// Node-targeted bundle for the RN push e2e (push.node.js). Unlike rollup.test.config.mjs
// (browser/IIFE via react-native-web), this emits a CommonJS bundle for Node.
//
// It bundles only the entry and the SDK's own sources (relative imports); everything else is
// left external and require()d at runtime: npm deps (mqtt, buffer, readable-stream, process),
// Node builtins (net, tls), and the two native peers. The push build (ReactNativePushTest)
// installs Node shims for those peers into node_modules — react-native-tcp-socket -> a net/tls
// adapter, react-native -> a Platform stub — so the real generated push.ts + tcp-stream.ts run
// unmodified over a real TCP socket. Keeping them external (not rollup-aliased) is deliberate:
// tcp-stream.ts requires react-native-tcp-socket lazily inside a try/catch, which the bundler
// leaves alone, so resolving it at runtime from node_modules is the reliable path.
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import typescript from '@rollup/plugin-typescript';
import replace from '@rollup/plugin-replace';
import path from 'path';

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
            tsconfig: './tsconfig.json',
            noEmitOnError: false,
            compilerOptions: {
                allowJs: true,
                declaration: false,
                declarationMap: false,
                outDir: 'dist',
                rootDir: '.',
            },
            include: ['src/**/*.ts', 'push.node.js'],
            exclude: ['node_modules/**', 'dist/**'],
        }),
        commonjs(),
    ],
};

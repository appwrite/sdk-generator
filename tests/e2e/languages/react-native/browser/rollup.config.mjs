import alias from '@rollup/plugin-alias';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import typescript from '@rollup/plugin-typescript';
import replace from '@rollup/plugin-replace';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const sdk = path.resolve(__dirname, '../../../sdks/react-native');

export default {
    input: 'browser.js',
    output: {
        file: 'dist/browser.bundle.js',
        format: 'iife',
        name: 'AppwriteTest',
        sourcemap: false,
        inlineDynamicImports: true,
    },
    plugins: [
        alias({
            entries: [
                { find: 'react-native', replacement: 'react-native-web' },
                {
                    find: /^expo-file-system\/legacy$/,
                    replacement: path.resolve(__dirname, 'shims/expo-file-system.js'),
                },
                {
                    find: /^expo-file-system$/,
                    replacement: path.resolve(__dirname, 'shims/expo-file-system-root.js'),
                },
            ],
        }),
        replace({
            preventAssignment: true,
            values: {
                'process.env.NODE_ENV': JSON.stringify('production'),
                __DEV__: 'false',
            },
        }),
        resolve({
            browser: true,
            extensions: ['.mjs', '.js', '.jsx', '.ts', '.tsx', '.json'],
            preferBuiltins: false,
            modulePaths: [path.join(__dirname, 'node_modules')],
        }),
        commonjs(),
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
            include: [
                path.join(sdk, 'src/**/*.ts'),
                path.join(__dirname, 'browser.js'),
                path.join(__dirname, 'shims/**/*.js'),
            ],
            exclude: ['**/node_modules/**', '**/dist/**'],
        }),
    ],
};

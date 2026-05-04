import alias from '@rollup/plugin-alias';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import typescript from '@rollup/plugin-typescript';
import replace from '@rollup/plugin-replace';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

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
                    find: 'expo-file-system',
                    replacement: path.resolve(__dirname, 'shims/expo-file-system.js'),
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
        }),
        commonjs(),
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
            include: ['src/**/*.ts', 'browser.js'],
            exclude: ['node_modules/**', 'dist/**'],
        }),
    ],
};

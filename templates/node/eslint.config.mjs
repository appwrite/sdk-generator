import eslint from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    eslint.configs.recommended,
    tseslint.configs.recommended,
    {
        ignores: ['dist/', 'types/', 'docs/', 'rollup.config.mjs'],
    },
    {
        // The generated Jest suite is CommonJS and runs against the build
        // output, so it needs Node globals and `require`.
        files: ['test/**/*.js'],
        languageOptions: {
            globals: { ...globals.node, ...globals.jest },
        },
        rules: {
            '@typescript-eslint/no-require-imports': 'off',
        },
    },
    {
        rules: {
            // The SDK deliberately exposes loosely-typed surfaces (payloads,
            // model generics defaulting to open records), so `any` is part of
            // its public contract rather than an oversight.
            '@typescript-eslint/no-explicit-any': 'off',
            // Empty responses are typed `Promise<{}>` and the models are
            // published under the `Models` namespace; both are part of the
            // SDK's public API and cannot change without a breaking release.
            '@typescript-eslint/no-empty-object-type': 'off',
            '@typescript-eslint/no-namespace': 'off',
            '@typescript-eslint/no-unused-vars': [
                'error',
                {
                    argsIgnorePattern: '^_',
                    caughtErrorsIgnorePattern: '^_',
                    destructuredArrayIgnorePattern: '^_',
                },
            ],
        },
    },
);

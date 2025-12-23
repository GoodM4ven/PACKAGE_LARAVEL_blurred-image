module.exports = {
    root: true,
    env: { browser: true, node: true, es2023: true },
    parserOptions: { ecmaVersion: 'latest', sourceType: 'module' },
    plugins: ['unused-imports'],
    extends: ['eslint:recommended'],
    rules: {
        'no-empty': ['error', { allowEmptyCatch: true }],

        // Let the plugin delete unused IMPORTS only
        'unused-imports/no-unused-imports': 'error',
        'unused-imports/no-unused-vars': 'off',

        // Use core rule for vars/params/catch; allow "_" and "__" everywhere
        'no-unused-vars': [
            'error',
            {
                vars: 'all',
                args: 'after-used',
                varsIgnorePattern: '^_{1,2}$',
                argsIgnorePattern: '^_{1,2}$',
                caughtErrors: 'all',
                caughtErrorsIgnorePattern: '^_{1,2}$',
                ignoreRestSiblings: true,
            },
        ],
    },
    ignorePatterns: ['node_modules/', 'vendor/', 'public/', 'storage/'],
};

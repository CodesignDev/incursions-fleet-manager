const restrictedGlobals = require('confusing-browser-globals')

/** @type {import('eslint').Linter.Config} */
module.exports = {
    root: true,
    env: {
        browser: true,
        es2021: true,
    },
    extends: [
        'airbnb',
        'airbnb/hooks',
        'airbnb-typescript',
        // 'standard-with-typescript',
        'plugin:eslint-comments/recommended',
        'plugin:import/errors',
        'plugin:import/warnings',
        'plugin:react/recommended',
        'plugin:react-hooks/recommended',
        'plugin:@typescript-eslint/recommended',
        'plugin:@typescript-eslint/recommended-requiring-type-checking',
        'prettier',
        'plugin:jsx-a11y/recommended',
        'plugin:tailwindcss/recommended',
        'plugin:prettier/recommended',
    ],
    ignorePatterns: ['config/', 'scripts/', 'node_modules/'],
    parser: '@typescript-eslint/parser',
    parserOptions: {
        project: true,
        ecmaVersion: 'latest',
        ecmaFeatures: {
            jsx: true,
        },
        sourceType: 'module',
        tsconfigRootDir: __dirname,
    },
    plugins: [
        'react',
        'react-hooks',
        'eslint-comments',
        '@typescript-eslint',
        'prettier',
        'security',
    ],
    rules: {
        'no-param-reassign': ['error', { props: true, ignorePropertyModificationsFor: ['draft'] }],
        'no-restricted-globals': ['error'].concat(restrictedGlobals),
        'no-useless-constructor': 'off',
        'no-undef': 'off',
        '@typescript-eslint/no-array-constructor': 'warn',
        '@typescript-eslint/no-namespace': 'error',
        '@typescript-eslint/no-useless-constructor': 'warn',
        '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
        'import/no-unresolved': 'off',
        'import/extensions': [
            'error',
            'never',
            { ignorePackages: true, pattern: { json: 'always' } },
        ],
        'import/named': 'off',
        'import/order': [
            'error',
            {
                groups: [
                    'builtin',
                    'external',
                    'internal',
                    ['parent', 'sibling'],
                    'index',
                    'object',
                    'type',
                ],
                pathGroups: [
                    { group: 'external', pattern: 'react', position: 'before' },
                    { group: 'external', pattern: 'react-dom', position: 'before' },
                    { group: 'external', pattern: 'react-dom/**', position: 'before' },
                ],
                pathGroupsExcludedImportTypes: ['react'],
                'newlines-between': 'always',
                alphabetize: {
                    order: 'asc',
                    caseInsensitive: true,
                },
            },
        ],

        // Disable the 'React' must be in scope error as the import is no longer required under React 17+
        'react/jsx-uses-react': 'off',
        'react/react-in-jsx-scope': 'off',

        // Disable the prop-types related rules since prop types aren't used
        'react/prop-types': 'off',
        'react/require-default-props': 'off',

        'react/jsx-props-no-spreading': 'off',

        // Tailwind classnames order is handled by the tailwindcss prettier plugin
        'tailwindcss/classnames-order': 'off',

        // Configure tailwind plugin
        'tailwindcss/no-custom-classname': [
            'warn',
            {
                cssFiles: ['resources/css/app.css'],
            },
        ],

        // Configure jsx-ally rules
        'jsx-a11y/label-has-associated-control': [
            'error',
            {
                labelComponents: ['InputLabel'],
                labelAttributes: ['value'],
                controlComponents: ['Checkbox', 'Dropdown', 'TextInput'],
                depth: 3,
            },
        ],

        // Configure what eslint comments are allowed and that some require an extra comment
        'eslint-comments/no-unused-disable': 'warn',
        'eslint-comments/no-use': [
            'error',
            {
                allow: [
                    'eslint',
                    'eslint-disable',
                    'eslint-disable-line',
                    'eslint-disable-next-line',
                    'eslint-enable',
                ],
            },
        ],
        'eslint-comments/require-description': [
            'error',
            {
                ignore: ['eslint-disable-line', 'eslint-disable-next-line', 'eslint-enable'],
            },
        ],
    },
    settings: {
        'import/extensions': ['.js', '.jsx', '.ts', '.tsx'],
        'import/parsers': {
            '@typescript-eslint/parser': ['.ts', '.tsx'],
        },
        'import/resolver': {
            node: {
                extensions: ['.js', '.jsx', '.ts', '.tsx'],
            },
        },
        react: {
            version: 'detect',
        },
    },
    overrides: [
        {
            files: ['*.d.ts'],
            rules: {
                // 'import/export': 0,
                'import/no-extraneous-dependencies': [
                    'error',
                    {
                        devDependencies: true,
                        bundledDependencies: true,
                    },
                ],
                'import/order': [
                    'error',
                    {
                        'newlines-between': 'ignore',
                        alphabetize: {
                            order: 'asc',
                            caseInsensitive: true,
                        },
                    },
                ],
                'no-redeclare': 0,
                'no-var': 0,
                'vars-on-top': 0,
            },
        },
        {
            files: ['./*.config.js', './*.cjs'],
            parserOptions: {
                project: './tsconfig.tooling.json',
                projectFolderIgnoreList: [],
            },
            extends: ['plugin:@typescript-eslint/disable-type-checked'],
            rules: {
                '@typescript-eslint/no-var-requires': 0,
                // 'import/export': 0,
                'import/no-extraneous-dependencies': [
                    'error',
                    {
                        devDependencies: true,
                        bundledDependencies: true,
                    },
                ],
                'import/order': [
                    'error',
                    {
                        'newlines-between': 'ignore',
                        alphabetize: {
                            order: 'asc',
                            caseInsensitive: true,
                        },
                    },
                ],
                'no-redeclare': 0,
                'no-var': 0,
                'vars-on-top': 0,
            },
        },
        {
            files: ['./tailwind.config.js'],
            rules: {
                'tailwindcss/classnames-order': 'off',
                'tailwindcss/no-custom-classname': 'off',
            },
        },
    ],
}

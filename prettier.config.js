/** @type {import('prettier').Config} */
export default {
    printWidth: 120,
    trailingComma: 'es5',
    tabWidth: 4,
    semi: false,
    singleQuote: true,
    plugins: ['prettier-plugin-tailwindcss'],
    tailwindFunctions: ['clsx', 'tw', 'twMerge', 'twJoin'],
    overrides: [
        {
            files: ['./*.config.js', './*.cjs'],
            options: { printWidth: 100 },
        },
    ],
}

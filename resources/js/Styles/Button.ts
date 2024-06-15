import { tw } from '@/utils'

export const ButtonBaseStyle = tw`group/button inline-flex items-center justify-center gap-x-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:text-gray-300 dark:focus:ring-offset-gray-800`
export const ButtonStyleVariants = {
    default: tw`bg-white shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-gray-200 dark:focus:bg-gray-700 dark:focus:text-gray-200 dark:active:bg-gray-900`,
    link: tw`inline border-0 border-transparent p-0 text-sm font-normal shadow-none hover:bg-transparent hover:underline focus:bg-transparent focus:underline focus:ring-0 focus:ring-offset-0 active:bg-transparent active:text-gray-900 active:underline dark:border-transparent dark:hover:bg-transparent dark:focus:bg-transparent dark:active:bg-transparent`,
    primary: tw`border-transparent bg-gray-800 px-4 text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white dark:hover:text-gray-800 dark:focus:bg-white dark:focus:text-gray-800 dark:active:bg-gray-300`,
    accept: tw`border-green-300 bg-green-50 text-green-600 hover:border-green-500 hover:bg-green-500 hover:text-white focus:ring-green-500 active:border-green-700 active:bg-green-700 active:text-white dark:border-gray-600 dark:bg-gray-800 dark:text-green-400 dark:hover:border-green-600 dark:hover:bg-green-600 dark:hover:text-white dark:focus:ring-green-600 dark:hover:focus:bg-green-600 dark:active:border-green-800 dark:active:bg-green-800 dark:active:text-white dark:focus:active:bg-green-800`,
    danger: tw`border-red-300 bg-red-50 text-red-600 hover:border-red-500 hover:bg-red-500 hover:text-white focus:ring-red-500 active:border-red-700 active:bg-red-700 active:text-white dark:border-gray-600 dark:bg-gray-800 dark:text-red-400 dark:hover:border-red-500 dark:hover:bg-red-500 dark:hover:text-white dark:hover:focus:bg-red-500 dark:active:border-red-700 dark:active:bg-red-700 dark:active:text-white dark:focus:active:bg-red-700`,
    dropdown: tw`w-full border-0 px-2 text-base font-medium text-gray-800 hover:bg-gray-100 focus:ring-0 focus:ring-offset-0 focus-visible:ring-2 focus-visible:ring-white/75 dark:text-gray-200 dark:hover:bg-gray-700`,
    disabled: tw`opacity-25`,
}

export const ButtonDefaultStyle = tw(ButtonBaseStyle, ButtonStyleVariants.default)

export type ButtonVariants = Omit<keyof typeof ButtonStyleVariants, 'default' | 'dropdown' | 'disabled'>
export type DropdownButtonVariants = Omit<keyof typeof ButtonStyleVariants, 'disabled'>

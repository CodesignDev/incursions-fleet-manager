import { forwardRef } from 'react'

import { InertiaLinkProps } from '@inertiajs/react'

import Link from '@/Components/Link'
import { tw } from '@/utils'

type NavLinkProps = {
    active?: boolean
    responsive?: boolean
}

export default forwardRef(function NavBarLink(
    { active = false, responsive = false, className = '', children, ...props }: InertiaLinkProps & NavLinkProps,
    ref
) {
    return (
        <Link
            {...props}
            className={tw(
                'font-medium transition duration-150 ease-in-out focus:outline-none',
                {
                    'inline-flex items-center border-b-2 px-1 pt-1 text-sm leading-5': !responsive,
                    'flex w-full items-start border-l-4 py-2 pl-3 pr-4 text-base': responsive,
                    'border-primary-400 text-gray-900 focus:border-primary-700 dark:border-primary-600 dark:text-gray-100':
                        active,
                    'bg-primary-50 text-primary-700 focus:bg-primary-100 focus:text-primary-800 dark:bg-primary-900/50 dark:text-primary-50 dark:focus:border-primary-300 dark:focus:bg-primary-900 dark:focus:text-primary-200':
                        active && responsive,
                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:text-gray-700 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:text-gray-300 dark:focus:border-gray-700 dark:focus:text-gray-300 ':
                        !active,
                    'text-gray-600 hover:bg-gray-50 hover:text-gray-800 focus:bg-gray-50 focus:text-gray-800 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 dark:focus:border-gray-600 dark:focus:bg-gray-700 dark:focus:text-gray-200':
                        !active && responsive,
                },
                className
            )}
            ref={ref}
        >
            {children}
        </Link>
    )
})

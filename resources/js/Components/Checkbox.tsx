import { ForwardedRef, forwardRef, InputHTMLAttributes } from 'react'

import { tw } from '@/utils'

export default forwardRef(function Checkbox(
    { className = '', ...props }: InputHTMLAttributes<HTMLInputElement>,
    ref: ForwardedRef<HTMLInputElement>
) {
    return (
        <input
            {...props}
            type="checkbox"
            className={tw(
                'rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-primary-600 dark:focus:ring-offset-gray-800',
                className
            )}
            ref={ref}
        />
    )
})

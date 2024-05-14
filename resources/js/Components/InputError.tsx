import { HTMLAttributes } from 'react'

import { tw } from '@/utils'

export default function InputError({
    message,
    className = '',
    ...props
}: HTMLAttributes<HTMLInputElement> & { message?: string }) {
    if (!message) return null

    return (
        <p {...props} className={tw('text-sm text-red-600 dark:text-red-400', className)}>
            {message}
        </p>
    )
}

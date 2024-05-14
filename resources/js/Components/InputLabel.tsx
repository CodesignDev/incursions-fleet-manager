import { LabelHTMLAttributes } from 'react'

import { tw } from '@/utils'

export default function InputLabel({
    value,
    className = '',
    hidden = false,
    children,
    ...props
}: LabelHTMLAttributes<HTMLLabelElement> & { value?: string }) {
    return (
        <label
            {...props}
            className={tw(
                'block text-sm font-medium text-gray-700 dark:text-gray-300',
                { 'sr-only': hidden },
                className
            )}
        >
            {value || children}
        </label>
    )
}

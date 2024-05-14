import { forwardRef, useEffect, useImperativeHandle, useRef, InputHTMLAttributes } from 'react'

import { tw } from '@/utils'

export type TextInputProps = {
    isFocused?: boolean
}

export default forwardRef(function TextInput(
    {
        type = 'text',
        className = '',
        isFocused = false,
        ...props
    }: InputHTMLAttributes<HTMLInputElement> & TextInputProps,
    ref
) {
    const localRef = useRef<HTMLInputElement>(null)

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }))

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus()
        }
    }, []) // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <input
            {...props}
            type={type}
            className={tw(
                'rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300',
                'focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-600 dark:focus:ring-primary-600',
                className
            )}
            ref={localRef}
        />
    )
})

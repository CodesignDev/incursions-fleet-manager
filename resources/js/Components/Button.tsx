import { ButtonHTMLAttributes, ForwardedRef, forwardRef } from 'react'

import { ButtonBaseStyle, ButtonStyleVariants, ButtonVariants } from '@/Styles/Button'
import { tw, applyStyleVariants } from '@/utils'

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
    variant?: ButtonVariants | ''
    unstyled?: boolean
    submit?: boolean
}
export default forwardRef(function Button(
    { className = '', disabled, variant = '', unstyled, submit = false, children, ...props }: ButtonProps,
    ref: ForwardedRef<HTMLButtonElement>
) {
    return (
        <button
            type={submit ? 'submit' : 'button'}
            className={tw(
                !unstyled && [
                    ButtonBaseStyle,
                    applyStyleVariants(ButtonStyleVariants, variant, {
                        default: variant === '',
                        disabled,
                    }),
                ],
                className
            )}
            disabled={disabled}
            {...props}
            ref={ref}
        >
            {children}
        </button>
    )
})

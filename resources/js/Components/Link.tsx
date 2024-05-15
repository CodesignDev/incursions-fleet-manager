import { createElement, forwardRef } from 'react'

import { InertiaLinkProps, Link as InertiaLink } from '@inertiajs/react'

import { ButtonBaseStyle, ButtonStyleVariants, ButtonVariants } from '@/Styles/Button'
import { applyStyleVariants, tw } from '@/utils'

type LinkProps = {
    styledAsButton?: boolean | Omit<ButtonVariants, 'link'>
    disableInertiaHandler?: boolean
}

export default forwardRef(function Link(
    {
        href,
        method = 'get',
        as = 'a',
        styledAsButton = false,
        className = '',
        disabled,
        disableInertiaHandler,
        children,
        ...props
    }: InertiaLinkProps & LinkProps,
    ref
) {
    const applyButtonStyle = typeof styledAsButton === 'boolean' ? styledAsButton : true
    const buttonStyle = typeof styledAsButton === 'boolean' ? 'default' : styledAsButton

    let linkAs = as
    if (method !== 'get') linkAs = 'button'

    let buttonStyles = ''
    if (applyButtonStyle) {
        buttonStyles = tw(
            ButtonBaseStyle,
            applyStyleVariants(ButtonStyleVariants, buttonStyle, {
                disabled,
            })
        )
    }
    const cssClassName = tw(buttonStyles, className)

    if (linkAs !== 'a' && disableInertiaHandler) {
        // eslint-disable-next-line no-console
        console.warn('Creating POST/PUT/DELETE links is not supported when the Inertia Link handler is disabled.')
    }

    if (disableInertiaHandler) {
        return createElement(
            linkAs,
            {
                href,
                className: cssClassName,
                ref,
                ...props,
            },
            children
        )
    }

    return (
        <InertiaLink href={href} className={cssClassName} method={method} as={linkAs} ref={ref} {...props}>
            {children || href}
        </InertiaLink>
    )
})

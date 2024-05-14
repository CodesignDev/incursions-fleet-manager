import { createElement, forwardRef } from 'react'

import { InertiaLinkProps, Link as InertiaLink } from '@inertiajs/react'

import { tw } from '@/utils'

type LinkProps = {
    disableInertiaHandler?: boolean
}

export default forwardRef(function Link(
    {
        href,
        method = 'get',
        as = 'a',
        className = '',
        disableInertiaHandler,
        children,
        ...props
    }: InertiaLinkProps & LinkProps,
    ref
) {
    let linkAs = as
    if (method !== 'get') linkAs = 'button'

    const cssClassName = tw(className)

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

import { InertiaLinkProps } from '@inertiajs/react'

import { LinkIsActive, LinkProps, RouteBasedLinkProps, StandardLinkProps } from '@/types'

type ParsedLinkProps = InertiaLinkProps & {
    key: string
    active: boolean
}

export function isLinkActive(active?: LinkIsActive): boolean {
    if (!active) return false

    return active instanceof Function ? active() : active
}

export function parseLinkRoute(link: StandardLinkProps | RouteBasedLinkProps): InertiaLinkProps {
    if ('route' in link) {
        if (typeof link.route === 'function') {
            const { route, ...props } = link
            return {
                ...props,
                href: route(),
            }
        }

        // @ts-expect-error -- The params object is being flagged as not listed, but it is listed
        const { route: routeName, params = {}, ...props } = link
        return {
            ...props,
            href: route(routeName, params as Record<string, unknown>),
        }
    }

    return link
}
export function parseLinks(links: Record<string, LinkProps>): ParsedLinkProps[] {
    return Object.entries(links).map(([key, link]) => {
        const { active: isActive, ...linkProps } = link

        const { href, label, ...props } = parseLinkRoute(linkProps)
        const active = isLinkActive(isActive)

        return { key, href, label, active, ...props }
    })
}

import { InertiaLinkProps } from '@inertiajs/react'

type BaseInertiaLinkProps = Pick<InertiaLinkProps, 'href' | 'data' | 'method' | 'as' | 'headers' | 'onClick'>

type LinkIsActive = boolean | (() => boolean)

type StandardLinkProps = BaseInertiaLinkProps & {
    active?: LinkIsActive
}

type RouteStringBasedLinkProps = {
    route: string
    params?: Record<string, unknown>
    active?: LinkIsActive
}

type RouteFuncBasedLinkProps = {
    route: () => string
    active?: LinkIsActive
}

type RouteBasedLinkProps = Omit<BaseInertiaLinkProps, 'href'> & (RouteStringBasedLinkProps | RouteFuncBasedLinkProps)

export type LinkProps = (StandardLinkProps | RouteBasedLinkProps) & { label?: string }
export type LinkPropsWithLabel = LinkProps & Required<Pick<LinkProps, 'label'>>

export type LinkGroup<T extends LinkProps = LinkProps> = {
    label?: string
    links: T[]
}

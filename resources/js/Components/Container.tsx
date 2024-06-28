import { createElement, ElementType } from 'react'

import { PropsWithout } from '@/types'
import { tw } from '@/utils'

type ContainerProps = {
    width?: ContainerWidths | ''
    disableResponsive?: boolean
    fluid?: boolean
    addVerticalPadding?: boolean
    noPadding?: boolean
    noBasePadding?: boolean
    noBaseVerticalPadding?: boolean
}

const containerWidths = {
    sm: tw`max-w-screen-sm`,
    md: tw`max-w-screen-md`,
    lg: tw`max-w-screen-lg`,
    xl: tw`max-w-screen-xl`,
    '2xl': tw`max-w-screen-2xl`,
    container: tw`container`,
    'container-sm': tw`sm:container`,
    'container-md': tw`md:container`,
    'container-lg': tw`lg:container`,
    'container-xl': tw`xl:container`,
    'container-2xl': tw`2xl:container`,
}
type ContainerWidths = keyof typeof containerWidths

export default function Container<T extends ElementType = 'div'>({
    as,
    className,
    width = 'xl',
    disableResponsive = false,
    fluid = false,
    addVerticalPadding = false,
    noPadding = false,
    noBasePadding = false,
    noBaseVerticalPadding = false,
    children,
    ...props
}: PropsWithout<T, ContainerProps>) {
    let containerWidth = ''
    if (width) {
        containerWidth = containerWidths[width]
    }

    return createElement(
        as || 'div',
        {
            className: tw(
                {
                    [containerWidth]: !fluid,
                    'mx-auto': !disableResponsive,
                    'sm:px-6 lg:px-8': !noPadding,
                    'sm:py-6 lg:py-8': addVerticalPadding,
                    'px-4': !noBasePadding && !noPadding,
                    'py-4': !noBaseVerticalPadding && addVerticalPadding,
                },
                className
            ),
            ...props,
        },
        children
    )
}

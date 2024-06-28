import { createElement, ElementType } from 'react'

import { Props } from '@/types'
import { tw } from '@/utils'

type SectionProps = {
    noPadding?: boolean
    addRounding?: boolean
}

export default function Section<T extends ElementType = 'div'>({
    as,
    noPadding,
    addRounding,
    children,
    ...props
}: Props<T, SectionProps>) {
    return createElement(
        as || 'div',
        {
            ...props,
            className: tw(
                'bg-white text-gray-800 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-200',
                {
                    'p-4': !noPadding,
                    'rounded-lg': addRounding,
                },
                props.className
            ),
        },
        children
    )
}

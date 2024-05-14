import { ReactNode } from 'react'

import { PropsWithChildrenPlusRenderProps } from '@/types'

const defaultRenderFunc = (children: ReactNode) => children

export function renderChildren<T>(
    children: PropsWithChildrenPlusRenderProps<T>['children'],
    props: T,
    renderFn?: (children: ReactNode) => ReactNode
) {
    if (typeof children !== 'function') {
        const renderFunc = renderFn || defaultRenderFunc
        return renderFunc(children)
    }

    return children(props)
}

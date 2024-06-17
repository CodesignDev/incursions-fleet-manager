import { ReactNode } from 'react'

import { PropsWithChildrenPlusRenderProps } from '@/types'

const defaultRenderFunc = (children: ReactNode) => children

export function renderChildren<TRenderProps>(
    children: PropsWithChildrenPlusRenderProps<TRenderProps>['children'],
    props: TRenderProps,
    renderFn?: (children: ReactNode) => ReactNode
) {
    if (typeof children !== 'function') {
        const renderFunc = renderFn || defaultRenderFunc
        return renderFunc(children)
    }

    return children(props)
}

export function processProps<TReturnValue, TRenderProps>(
    value: TReturnValue | ((props: TRenderProps) => TReturnValue) | undefined,
    props: TRenderProps
): TReturnValue | undefined {
    if (value instanceof Function) {
        return value(props)
    }

    return value
}

import { PropsWithChildren } from 'react'

import { tw } from '@/utils'

type PageHeaderProps = {
    className?: string
}

export default function PageHeader({ className, children }: PropsWithChildren<PageHeaderProps>) {
    return (
        <h2 className={tw('text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200', className)}>
            {children}
        </h2>
    )
}

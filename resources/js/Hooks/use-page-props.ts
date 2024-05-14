import { usePage } from '@inertiajs/react'

import { PageProps } from '@/types'

export default function usePageProps<TPageProps extends PageProps = PageProps>(): TPageProps {
    const { props } = usePage<TPageProps>()
    return props
}

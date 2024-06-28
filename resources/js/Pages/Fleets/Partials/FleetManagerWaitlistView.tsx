import { useEffect, useState } from 'react'

import { router } from '@inertiajs/react'

import PageLoadingSpinner from '@/Components/PageLoadingSpinner'
import { FleetManagementPageType } from '@/Constants/FleetManagementPageType'
import usePageProps from '@/Hooks/usePageProps'
import { FleetManagerExtendedPageProps } from '@/Pages/Fleets/FleetManager'
import { useFleet } from '@/Providers/FleetProvider'
import { PageProps } from '@/types'

export default function FleetManagerWaitlistView() {
    const { waitlist_entries } = usePageProps<PageProps<FleetManagerExtendedPageProps>>()

    const [loading, setLoading] = useState(false)
    const [entryData, setEntryData] = useState(() => (waitlist_entries !== undefined ? waitlist_entries : []))

    const { fleet } = useFleet()

    useEffect(() => {
        const currentPage = route().params.page
        if (currentPage === FleetManagementPageType.Waitlist) return

        setLoading(true)
        router.get(
            route(route().current() as string, {
                fleet,
                page: FleetManagementPageType.Waitlist,
            }),
            {},
            {
                only: ['waitlist_entries'],
                replace: !currentPage,
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setLoading(false),
            }
        )
    }, [])

    useEffect(() => {
        if (waitlist_entries === undefined) return
        setEntryData(waitlist_entries)
    }, [waitlist_entries])

    if (loading) return <PageLoadingSpinner />

    return <>Waitlist</>
}

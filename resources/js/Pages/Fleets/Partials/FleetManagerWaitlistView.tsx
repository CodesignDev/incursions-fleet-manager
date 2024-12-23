import PageLoadingSpinner from '@/Components/PageLoadingSpinner'
import { FleetManagementPageType } from '@/Constants/FleetManagementPageType'
import { FleetManagerExtendedPageProps } from '@/Pages/Fleets/FleetManager'
import useFleetPageLoader from '@/Pages/Fleets/Partials/Hooks/useFleetPageLoader'

export default function FleetManagerWaitlistView() {
    const { loading, error, hasData, data, updateData } = useFleetPageLoader<FleetManagerExtendedPageProps>(
        FleetManagementPageType.Waitlist,
        'waitlists',
        { initialValue: [] }
    )

    if (loading && !hasData) return <PageLoadingSpinner />
    if (error) return <div>There was an error while fetching the waitlist information</div>

    return <>Waitlist</>
}

import PageLoadingSpinner from '@/Components/PageLoadingSpinner'
import { FleetManagementPageType } from '@/Constants/FleetManagementPageType'
import { FleetManagerExtendedPageProps } from '@/Pages/Fleets/FleetManager'
import useFleetPageLoader from '@/Pages/Fleets/Partials/Hooks/useFleetPageLoader'

export default function FleetManagerMembersView() {
    const { loading, error, hasData, data, updateData } = useFleetPageLoader<FleetManagerExtendedPageProps>(
        FleetManagementPageType.FleetMembers,
        'members',
        { initialValue: [] }
    )

    if (loading && !hasData) return <PageLoadingSpinner />
    if (error) return <div>There was an error while fetching the members information</div>

    return <>Fleet Members</>
}

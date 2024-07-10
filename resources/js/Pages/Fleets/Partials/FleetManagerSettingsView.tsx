import PageLoadingSpinner from '@/Components/PageLoadingSpinner'
import { FleetManagementPageType } from '@/Constants/FleetManagementPageType'
import { FleetManagerExtendedPageProps } from '@/Pages/Fleets/FleetManager'
import useFleetPageLoader from '@/Pages/Fleets/Partials/Hooks/useFleetPageLoader'

export default function FleetManagerSettingsView() {
    const { loading, error, hasData, data, updateData } = useFleetPageLoader<FleetManagerExtendedPageProps>(
        FleetManagementPageType.FleetSettings,
        'fleet_settings',
        { initialValue: [] }
    )

    if (loading && !hasData) return <PageLoadingSpinner />
    if (error) return <div>There was an error while fetching the fleet settings</div>

    return <>Fleet Settings Page</>
}

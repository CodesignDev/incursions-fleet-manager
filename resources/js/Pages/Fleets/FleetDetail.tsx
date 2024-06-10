import Link from '@/Components/Link'
import PageHeader from '@/Components/PageHeader'
import ApplicationLayout from '@/Layouts/ApplicationLayout'
import { Fleet } from '@/types'

type FleetDetailProps = {
    fleet: Fleet
}

export default function FleetDetail({ fleet }: FleetDetailProps) {
    const { name: fleetName = 'Unknown' } = fleet
    return (
        <ApplicationLayout
            header={
                <PageHeader>
                    <Link className="underline-offset-2 hover:underline" href={route('fleets.list')}>
                        Fleet Manager
                    </Link>{' '}
                    &raquo; Fleet: {fleetName}
                </PageHeader>
            }
        >
            <div />
        </ApplicationLayout>
    )
}

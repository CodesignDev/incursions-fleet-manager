import Link from '@/Components/Link'
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
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    <Link className="underline-offset-2 hover:underline" href={route('fleets.list')}>
                        Fleet Manager
                    </Link>{' '}
                    &raquo; Fleet: {fleetName}
                </h2>
            }
        >
            <div />
        </ApplicationLayout>
    )
}

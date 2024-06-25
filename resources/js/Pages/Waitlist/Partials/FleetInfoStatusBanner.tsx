import { FleetStatus } from '@/types'
import { match, tw } from '@/utils'

type FleetStatusBannerProps = {
    status: FleetStatus
    shortStatus?: boolean
    className?: string
}

const fleetStatusLookups: Record<string, Record<Exclude<FleetStatus, 'unknown'>, string>> = {
    full: {
        forming: 'Fleet is currently forming',
        running: 'Fleet is running',
        'on-break': 'Fleet is currently on break',
        docking: 'Fleet will be docking soon',
        'standing-down': 'Fleet will be standing down shortly',
    },
    short: {
        forming: 'Forming',
        running: 'Running',
        'on-break': 'On Break',
        docking: 'Docking Soon',
        'standing-down': 'Standing Down',
    },
    colours: {
        forming: 'bg-blue-500 dark:bg-blue-700',
        running: 'bg-green-600 dark:bg-green-700',
        'on-break': 'bg-amber-600 dark:bg-amber-700',
        docking: 'bg-purple-500 dark:bg-purple-700',
        'standing-down': 'bg-red-600 dark:bg-red-700',
    },
}

export default function FleetInfoStatusBanner({
    status = 'unknown',
    shortStatus = false,
    className = '',
}: FleetStatusBannerProps) {
    if (status === 'unknown') return null
    return (
        <div
            className={tw(
                'rounded-md px-2 py-1 text-center text-sm font-bold text-white shadow-sm',
                match(status, fleetStatusLookups.colours),
                className
            )}
        >
            {match(status, shortStatus ? fleetStatusLookups.short : fleetStatusLookups.full)}
        </div>
    )
}

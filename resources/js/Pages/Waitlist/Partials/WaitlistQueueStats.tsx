import { useMemo } from 'react'

import { InformationCircleIcon } from '@heroicons/react/20/solid'
import { useToggle } from 'usehooks-ts'

import { Waitlist } from '@/types'
import { match, tw } from '@/utils'

type WaitlistQueueStatsProps = {
    waitlists?: Waitlist[]
}

type WaitlistQueueStatsRowProps = {
    waitlist: Waitlist
    showWaitlistName?: boolean
}

function StatsInfo() {
    const [showMoreInfo, toggleShowMoreInfo] = useToggle(false)

    return (
        <div className="flex flex-row gap-x-2 text-sm text-gray-600 dark:text-gray-400">
            <div className="row-span-2">
                <InformationCircleIcon className="size-5" />
            </div>
            <div className="flex flex-col items-start gap-y-1.5">
                <p>
                    Your queue position is only indicative of where you are on the waitlist, it does not indicate how
                    long it will be before you are invited to the fleet.
                </p>
                <p className={tw({ hidden: !showMoreInfo })}>
                    If you are unable to fly a ship that can fill the next slot that opens in the fleet, you may not be
                    the next person to be invited, likewise if you are the only person on the waitlist in that ship and
                    a a slot opens you will be the next person invited.
                </p>

                <button
                    type="button"
                    className="hover:text-gray-800 hover:underline focus:outline-none dark:hover:text-gray-200"
                    onClick={() => toggleShowMoreInfo()}
                >
                    {showMoreInfo ? 'Less info...' : 'More info...'}
                </button>
            </div>
        </div>
    )
}

function StatsRow({ waitlist, showWaitlistName = false }: WaitlistQueueStatsRowProps) {
    const { name, on_waitlist: onWaitlist = false, queue_position: position = 0, total_entries: total = 0 } = waitlist

    const queuePositionLabel = useMemo(() => {
        const suffix = name.toLowerCase().endsWith(' waitlist') ? '' : ' Waitlist'
        return showWaitlistName ? `Your Queue Position on ${name}${suffix}` : 'Your Queue Position'
    }, [showWaitlistName, name])

    return (
        <div className="col-span-full grid grid-cols-subgrid py-2">
            <div className="text-nowrap">{queuePositionLabel}:</div>

            <div className="flex flex-row items-center gap-x-1 text-gray-400 dark:text-gray-500">
                <span className="font-medium text-gray-800 dark:text-gray-200">{onWaitlist ? position : '-'}</span>/
                <span className="text-gray-800 dark:text-gray-200">{total}</span>
            </div>
        </div>
    )
}

export default function WaitlistQueueStats({ waitlists = [] }: WaitlistQueueStatsProps) {
    const onWaitlist = useMemo(() => waitlists.some(({ on_waitlist: queued }) => queued), [waitlists])

    return (
        <>
            <h3 className="text-lg font-semibold text-gray-800 dark:text-gray-200">Waitlist Queue</h3>

            {onWaitlist && <StatsInfo />}

            <div className="grid grid-cols-[min-content_1fr] gap-x-4 divide-y divide-gray-600">
                {match(waitlists.length, 2, {
                    0: <span>There are no active waitlists at this moment in time.</span>,
                    1: <StatsRow waitlist={waitlists[0]} />,
                    2: waitlists.map((waitlist) => <StatsRow key={waitlist.id} waitlist={waitlist} showWaitlistName />),
                })}
            </div>
        </>
    )
}

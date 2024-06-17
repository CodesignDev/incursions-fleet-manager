import { PropsWithChildren, useMemo } from 'react'

import { ChevronRightIcon, EyeIcon, UsersIcon } from '@heroicons/react/24/solid'

import Link from '@/Components/Link'
import Tooltip from '@/Components/Tooltip'
import useTailwindBreakpoint from '@/Hooks/useTailwindBreakpoint'
import { Fleet } from '@/types'

type FleetListEntryProps = {
    fleet: Fleet
}

function FleetEntryWrapper({ fleet, children }: PropsWithChildren<Pick<FleetListEntryProps, 'fleet'>>) {
    const { isAboveMd } = useTailwindBreakpoint('md')

    if (isAboveMd) return children

    return (
        <Link href={route('fleets.show', fleet)} className="first:rounded-t-lg last:rounded-b-lg hover:bg-gray-600">
            {children}
        </Link>
    )
}

export default function FleetListEntry({ fleet }: FleetListEntryProps) {
    const { isBelowMd, isAboveMd } = useTailwindBreakpoint('md')

    const { name, fleet_boss: fleetBoss, locations = [], member_count: memberCount } = fleet

    const currentFleetBoss = useMemo(() => {
        const { character, user = '' } = fleetBoss

        let text = character
        if (user && user !== character) text += ` (${user})`

        return text
    }, [fleetBoss])

    return (
        <FleetEntryWrapper fleet={fleet}>
            <div className="flex grid-cols-[repeat(3,_minmax(0,_1fr))_40px_140px] flex-row items-center gap-x-4 py-2 pl-4 pr-2 lg:grid lg:gap-x-8">
                <div className="flex flex-1 flex-row items-center gap-x-8 lg:contents">
                    <div className="col-span-2 flex flex-col gap-x-4 gap-y-0.5 lg:grid lg:grid-cols-subgrid lg:gap-x-8">
                        <span className="font-bold">{name}</span>
                        <span className="text-sm lg:text-base">Boss: {currentFleetBoss}</span>
                    </div>

                    <div className="flex-1 justify-self-end">
                        {locations.length > 0 && (
                            <Tooltip
                                content={
                                    <div className="flex flex-col gap-y-1">
                                        {locations.map((location) => (
                                            <span key={location.solar_system_id}>{location.solar_system_name}</span>
                                        ))}
                                    </div>
                                }
                            >
                                <div className="inline-flex items-center justify-center rounded bg-gray-300 px-2 py-1 text-sm font-bold text-gray-800">
                                    {locations[0].solar_system_name} - {locations[0].count}
                                </div>
                            </Tooltip>
                        )}
                    </div>

                    <div className="block">
                        <Tooltip content={`Total Fleet Members: ${memberCount}`}>
                            <div className="relative block w-fit p-2">
                                <UsersIcon className="size-6 text-gray-800 dark:text-gray-200" />
                                <span className="absolute bottom-0 right-0 inline-flex aspect-square size-5 select-none items-center justify-center rounded-full bg-gray-300 px-1 text-xs font-bold text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {memberCount}
                                </span>
                            </div>
                        </Tooltip>
                    </div>
                </div>

                <div className="w-8 lg:w-full">
                    {isBelowMd && <ChevronRightIcon className="size-6" />}
                    {isAboveMd && (
                        <Link
                            href={route('fleets.show', fleet)}
                            styledAsButton
                            className="flex items-center justify-center py-2"
                        >
                            <EyeIcon className="size-5" />
                            View Fleet
                        </Link>
                    )}
                </div>
            </div>
        </FleetEntryWrapper>
    )
}

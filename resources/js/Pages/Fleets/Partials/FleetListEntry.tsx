import { PropsWithChildren, useMemo } from 'react'

import { ChevronRightIcon, EyeIcon, UsersIcon } from '@heroicons/react/24/solid'

import Link from '@/Components/Link'
import Tooltip from '@/Components/Tooltip'
import useTailwindBreakpoint from '@/Hooks/use-tailwind-breakpoint'
import { Fleet } from '@/types'

type FleetListEntryProps = {
    fleet: Fleet
}

function FleetEntryWrapper({ fleetId = '', children }: PropsWithChildren<{ fleetId: string }>) {
    const { isAboveMd } = useTailwindBreakpoint('md')

    if (isAboveMd) return children

    return <Link href="#">{children}</Link>
}

export default function FleetListEntry({ fleet }: FleetListEntryProps) {
    const { isBelowMd, isAboveMd } = useTailwindBreakpoint('md')

    const currentFleetBoss = useMemo(() => {
        const {
            fleet_boss: { character, user = '' },
        } = fleet

        let text = character
        if (user && user !== character) text += ` (${user})`

        return text
    }, [fleet])

    return (
        <FleetEntryWrapper fleetId={fleet.id}>
            <div className="flex grid-cols-[repeat(3,_minmax(0,_1fr))_40px_140px] flex-row items-center gap-x-4 px-4 py-2 hover:bg-gray-600 lg:grid lg:gap-x-8 lg:hover:bg-gray-900">
                <div className="flex flex-1 flex-row items-center gap-x-8 lg:contents">
                    <div className="col-span-2 flex flex-col gap-x-4 gap-y-0.5 lg:grid lg:grid-cols-subgrid lg:gap-x-8">
                        <span className="font-bold">{fleet.name}</span>
                        <span className="text-sm lg:text-base">Boss: {currentFleetBoss}</span>
                    </div>
                    <div className="flex-1" />
                    <div className="block">
                        <Tooltip content={`Total Fleet Members: ${fleet.member_count}`}>
                            <div className="relative block w-fit p-2">
                                <UsersIcon className="size-6 text-gray-200" />
                                <span className="absolute bottom-0 right-0 inline-flex aspect-square size-5 items-center justify-center rounded-full text-xs dark:bg-gray-700">
                                    {fleet.member_count}
                                </span>
                            </div>
                        </Tooltip>
                    </div>
                </div>
                <div className="w-8 lg:w-full">
                    {isBelowMd && <ChevronRightIcon className="size-6" />}
                    {isAboveMd && (
                        <Link href="#" styledAsButton className="flex items-center justify-center py-2">
                            <EyeIcon className="size-5" />
                            View Fleet
                        </Link>
                    )}
                </div>
            </div>
        </FleetEntryWrapper>
    )
}

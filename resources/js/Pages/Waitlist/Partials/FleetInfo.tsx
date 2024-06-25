import { PropsWithChildren, ReactNode, useMemo } from 'react'

import { Disclosure, Transition } from '@headlessui/react'
import { ArrowTopRightOnSquareIcon } from '@heroicons/react/16/solid'
import { ChevronDownIcon } from '@heroicons/react/20/solid'

import useTailwindBreakpoint from '@/Hooks/useTailwindBreakpoint'
import FleetInfoStatusBanner from '@/Pages/Waitlist/Partials/FleetInfoStatusBanner'
import { Fleet } from '@/types'
import { tw } from '@/utils'

type FleetInfoProps = {
    fleets?: Fleet[]
}

type FleetInfoContainerProps = {
    count?: number
    fleetStatus?: ReactNode
    className?: string
}

type FleetInfoSectionProps = {
    fleet: Fleet
}

type FleetInfoRowProps = {
    label: string
    className?: string
    labelClassName?: string
}

function FleetInfoContainer({
    count = 0,
    fleetStatus,
    className = '',
    children,
}: PropsWithChildren<FleetInfoContainerProps>) {
    const { isAboveSm } = useTailwindBreakpoint('sm')

    const header = useMemo(
        () => (
            <div className="flex items-center gap-x-2.5">
                <h3 className="text-lg font-semibold text-gray-800 dark:text-gray-200">Fleet Info</h3>
                {count > 0 && (
                    <span className="rounded-full bg-gray-300 px-2.5 py-0.5 text-xs font-bold dark:bg-gray-950">
                        {count}
                    </span>
                )}
            </div>
        ),
        [count]
    )

    return (
        <Disclosure>
            {({ open }) => (
                <>
                    {isAboveSm ? (
                        header
                    ) : (
                        <Disclosure.Button className="flex w-full flex-row items-center justify-between focus:outline-none">
                            <div className="flex flex-row items-center gap-x-3.5">
                                {header}

                                {fleetStatus && (
                                    <Transition
                                        show={!open}
                                        enter="transition duration-100 ease-out"
                                        enterFrom="transform opacity-0"
                                        enterTo="transform opacity-100"
                                        leave="transition duration-100 ease-out"
                                        leaveFrom="transform opacity-100"
                                        leaveTo="transform opacity-0"
                                    >
                                        {fleetStatus}
                                    </Transition>
                                )}
                            </div>
                            <ChevronDownIcon
                                className={tw('size-6 transition-transform', {
                                    'rotate-180': open,
                                })}
                            />
                        </Disclosure.Button>
                    )}

                    <Transition
                        show={open || isAboveSm}
                        enter="transition duration-100 ease-out"
                        enterFrom="transform -translate-y-8 opacity-0"
                        enterTo="transform translate-y-0 opacity-100"
                        leave="transition duration-75 ease-out"
                        leaveFrom="transform translate-y-0 opacity-100"
                        leaveTo="transform -translate-y-8 opacity-0"
                    >
                        <Disclosure.Panel static className={className}>
                            {children}
                        </Disclosure.Panel>
                    </Transition>
                </>
            )}
        </Disclosure>
    )
}

function FleetInfoRow({ label, className = '', labelClassName = '', children }: PropsWithChildren<FleetInfoRowProps>) {
    return (
        <>
            <div className={tw('text-nowrap text-gray-500 dark:text-gray-400', labelClassName)}>{label}:</div>
            <div className={className}>{children}</div>
        </>
    )
}

function FleetInfoSection({ fleet }: FleetInfoSectionProps) {
    const { name: fleetName, status, fleet_boss: boss, comms, member_count: memberCount } = fleet

    const fleetBoss = useMemo(() => {
        const { character, user } = boss

        if (!user || character === user) return character
        return (
            <div className="flex flex-col items-start gap-y-0.5">
                {user}
                <span className="text-xs font-normal text-gray-600 dark:text-gray-400">
                    (Fleet is under {character})
                </span>
            </div>
        )
    }, [boss])

    const fleetComms = useMemo(() => {
        const { label, url } = comms

        if (!url) return label
        return (
            <a
                href={url}
                target="_blank"
                rel="nofollow noreferrer"
                className="underline decoration-gray-200 underline-offset-4 hover:text-sky-600 hover:decoration-inherit focus:text-sky-600 focus:decoration-inherit focus:outline-none active:text-sky-700 active:decoration-inherit dark:decoration-gray-600 dark:hover:text-sky-500 dark:focus:text-sky-500 dark:active:text-sky-600"
            >
                {label}{' '}
                <span className="inline-block align-middle">
                    <ArrowTopRightOnSquareIcon className="inline size-4 align-baseline" />
                </span>
            </a>
        )
    }, [comms])

    return (
        <div className="space-y-4 py-4 first:pt-0 last:pb-0">
            {status !== 'unknown' && <FleetInfoStatusBanner status={status} />}

            <div className="grid grid-cols-[min-content_1fr] gap-x-4 gap-y-1.5">
                <FleetInfoRow label="FC">{fleetBoss}</FleetInfoRow>
                <FleetInfoRow label="Fleet Name">{fleetName}</FleetInfoRow>
                <FleetInfoRow label="Fleet Type">Headquarters</FleetInfoRow>
                <FleetInfoRow label="Fleet Location">1DQ1-A</FleetInfoRow>
                <FleetInfoRow label="Mumble Channel">{fleetComms}</FleetInfoRow>
                <FleetInfoRow label="Fleet Members">{memberCount}</FleetInfoRow>
                <FleetInfoRow label="Characters in Fleet">None</FleetInfoRow>
            </div>
        </div>
    )
}

export default function FleetInfo({ fleets = [] }: FleetInfoProps) {
    const overallFleetStatus = useMemo(() => {
        return fleets
            .filter(({ status }) => status !== 'unknown')
            .slice(0, 3)
            .map(({ id, status }) => (
                <FleetInfoStatusBanner key={id} status={status} shortStatus className="rounded py-0.5" />
            ))
    }, [fleets])

    return (
        <FleetInfoContainer
            fleetStatus={overallFleetStatus}
            count={fleets.length}
            className="divide-y divide-gray-300 dark:divide-gray-600"
        >
            {fleets
                ? fleets.map((fleet) => <FleetInfoSection key={fleet.id} fleet={fleet} />)
                : 'There are no available fleets.'}
            {/* <div className="py-8"> */}
            {/*    <div className="h-16 bg-gray-900 dark:bg-gray-50" /> */}
            {/* </div> */}
        </FleetInfoContainer>
    )
}

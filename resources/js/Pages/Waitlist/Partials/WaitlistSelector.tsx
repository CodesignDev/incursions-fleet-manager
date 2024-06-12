import { ChangeEventHandler, useCallback, useId, useMemo, useState } from 'react'

import Tabs from '@/Components/Tabs'
import { PropsWithChildrenPlusRenderProps, Waitlist } from '@/types'
import { clampNumber, renderChildren } from '@/utils'

type WaitlistSelectorProps = {
    waitlists: Waitlist[]
}

type WaitlistSelectorRenderProps = {
    waitlist: Waitlist
}

export default function WaitlistSelector({
    waitlists = [],
    children,
}: PropsWithChildrenPlusRenderProps<WaitlistSelectorRenderProps, WaitlistSelectorProps>) {
    const [selectedWaitlist, setSelectedWaitlist] = useState(0)

    const elementId = useId()

    const waitlistDropdownValue = useMemo(() => {
        const index = clampNumber(selectedWaitlist, 0, waitlists.length)
        return waitlists[index].id
    }, [waitlists, selectedWaitlist])

    const handleWaitlistDropdownChange: ChangeEventHandler<HTMLSelectElement> = useCallback(
        (e) => {
            const waitlist = e.target.value
            setSelectedWaitlist(waitlists.findIndex(({ id }) => waitlist === id))
        },
        [waitlists]
    )

    return (
        <Tabs.TabGroup selectedIndex={selectedWaitlist} onChange={setSelectedWaitlist} className="">
            {waitlists.length > 1 && (
                <>
                    <div className="mb-6 sm:hidden">
                        <label htmlFor={`waitlist-selector-${elementId}`} className="sr-only">
                            Select waitlist
                        </label>

                        <div className="border-b border-gray-300 px-4 dark:border-gray-600">
                            <select
                                id={`waitlist-selector-${elementId}`}
                                className="-mb-px block w-full appearance-none border-0 border-b-2 border-transparent bg-transparent px-2 py-2.5 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:border-primary-500 focus:outline-none focus:ring-0 dark:border-transparent dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300 dark:focus:bg-gray-800 dark:focus:text-gray-300 dark:active:bg-gray-800 dark:active:text-gray-300"
                                value={waitlistDropdownValue}
                                onChange={handleWaitlistDropdownChange}
                            >
                                {waitlists.map(({ id, name }) => (
                                    <option key={id} value={id}>
                                        {name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className="hidden flex-col gap-y-2 sm:flex">
                        <h3 className="not-sr-only font-medium sm:hidden">Select Waitlist</h3>
                        <Tabs.TabList className="mb-6">
                            {waitlists.map(({ id, name }) => (
                                <Tabs.Tab key={id}>{name}</Tabs.Tab>
                            ))}
                        </Tabs.TabList>
                    </div>
                </>
            )}

            <Tabs.TabPanels>
                {waitlists.map((waitlist) => {
                    const { id } = waitlist
                    return <Tabs.TabPanel key={id}>{renderChildren(children, { waitlist })}</Tabs.TabPanel>
                })}
            </Tabs.TabPanels>
        </Tabs.TabGroup>
    )
}

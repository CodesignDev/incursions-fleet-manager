import { useMemo } from 'react'

import { isArray } from 'lodash-es'

import { useDoctrines } from '@/Providers/DoctrineProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { DoctrineShip, WaitlistCharacterEntryData } from '@/types'
import { tw, wrapArray } from '@/utils'

type CharacterShipDisplayProps = {
    value: WaitlistCharacterEntryData
}

export default function CharacterShipDisplay({ value }: CharacterShipDisplayProps) {
    const { getDoctrineShip } = useDoctrines()
    const { hasDoctrine } = useWaitlist()

    const isMultiValue = useMemo(() => hasDoctrine || isArray(value), [value])

    const displayValue = useMemo(() => (hasDoctrine ? wrapArray(value) : value), [hasDoctrine, value])

    return (
        <div
            className={tw(
                'flex w-full select-none flex-wrap rounded-md border border-gray-300 bg-gray-100 text-sm text-gray-600 shadow-sm empty:h-10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400',
                {
                    'cursor-not-allowed px-2 py-2.5': !isMultiValue,
                    'p-0.5': isMultiValue,
                }
            )}
        >
            {isArray(displayValue)
                ? displayValue
                      .map((item) => getDoctrineShip(item, { includeUnknown: true }))
                      .filter((item): item is DoctrineShip => item !== null)
                      .map(({ id, name }) => (
                          <span
                              key={id}
                              className="m-0.5 cursor-default select-none rounded bg-gray-100 px-2 py-1.5 dark:bg-gray-800"
                          >
                              {name}
                          </span>
                      ))
                : displayValue}
        </div>
    )
}

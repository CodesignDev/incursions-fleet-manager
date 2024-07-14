import { createContext, PropsWithChildren, useCallback, useContext, useMemo } from 'react'

import { v7 as generateUuid } from 'uuid'

import { Doctrine, DoctrineDropdownOptions, DoctrineShip, DoctrineShipOptions } from '@/types'
import {
    formatDoctrineDropdownEntries,
    getDoctrineId,
    getDoctrineShipId,
    getFlattenedDoctrineShips,
    getFlattenedDropdownEntries,
} from '@/utils'

type GetDoctrineShipOptions = {
    includeUnknown?: boolean
}

type GetShipsForDoctrineOptions<T extends boolean> = {
    asDropdownEntries?: T
    shipsOnly?: boolean
}

type GetShipsForDoctrineReturnType<AsDropdown> = AsDropdown extends true ? DoctrineDropdownOptions : DoctrineShipOptions

type ContextProps = {
    doctrines: Doctrine[]
    getDoctrine: (doctrine: Doctrine | string) => Doctrine | null
    getDoctrineShip: (ship: DoctrineShip | string, options?: GetDoctrineShipOptions) => DoctrineShip | null
    getShipsForDoctrine: <AsDropdown extends boolean = false>(
        doctrine: Doctrine | string,
        options?: GetShipsForDoctrineOptions<AsDropdown>
    ) => GetShipsForDoctrineReturnType<AsDropdown>
}

type ProviderProps = {
    doctrines: Doctrine[]
}

const defaultProps: ContextProps = {
    doctrines: [],
    getDoctrine: () => null,
    getDoctrineShip: () => null,
    getShipsForDoctrine: () => [],
}

const DoctrineContext = createContext(defaultProps)

function DoctrineProvider({ doctrines, children }: PropsWithChildren<ProviderProps>) {
    const findDoctrine = useCallback(
        (doctrine: Doctrine | string) => {
            return doctrines.find((item) => item.id === getDoctrineId(doctrine)) || null
        },
        [doctrines]
    )

    const findDoctrineShip = useCallback(
        (ship: DoctrineShip | string, options: GetDoctrineShipOptions = {}) => {
            const { includeUnknown = false } = options
            const defaultOption = typeof ship === 'string' && includeUnknown ? { id: generateUuid(), name: ship } : null

            const allShips = doctrines.flatMap(({ ships }) => getFlattenedDoctrineShips(ships))
            return allShips.find((item) => item.id === getDoctrineShipId(ship)) || defaultOption
        },
        [doctrines]
    )

    const getDoctrineShips = useCallback(
        <AsDropdown extends boolean = false>(
            doctrine: Doctrine | string,
            { asDropdownEntries, shipsOnly }: GetShipsForDoctrineOptions<AsDropdown> = {}
        ): GetShipsForDoctrineReturnType<AsDropdown> => {
            const selectedDoctrine = findDoctrine(doctrine)
            if (!selectedDoctrine) return []

            const { ships } = selectedDoctrine

            if (asDropdownEntries === true) {
                const formattedEntries = formatDoctrineDropdownEntries(ships)

                // @ts-expect-error This is returning the correct data but typescript is struggling to parse the return type
                return shipsOnly ? getFlattenedDropdownEntries(formattedEntries) : formattedEntries
            }

            // @ts-expect-error See above comment
            return shipsOnly ? getFlattenedDoctrineShips(ships) : ships
        },
        [findDoctrine]
    )

    const contextValue = useMemo(
        () => ({
            doctrines,
            getDoctrine: findDoctrine,
            getDoctrineShip: findDoctrineShip,
            getShipsForDoctrine: getDoctrineShips,
        }),
        [doctrines, findDoctrine, findDoctrineShip, getDoctrineShips]
    )
    return <DoctrineContext.Provider value={contextValue}>{children}</DoctrineContext.Provider>
}

function useDoctrines() {
    return useContext(DoctrineContext)
}

export { DoctrineProvider, useDoctrines }

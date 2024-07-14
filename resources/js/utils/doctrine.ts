import { Doctrine, DoctrineShip, DoctrineShipDropdown, DoctrineShipDropdownGroup, DoctrineShipGroup } from '@/types'
import { flattenGroupedArray } from '@/utils/array'

export const getDoctrineId = (doctrine: Doctrine | string): string =>
    typeof doctrine === 'string' ? doctrine : doctrine.id
export const getDoctrineShipId = (ship: DoctrineShip | string): string => (typeof ship === 'string' ? ship : ship.id)

function isShipGroup(item: DoctrineShip | DoctrineShipGroup): item is DoctrineShipGroup {
    return (item as DoctrineShip).id === undefined
}

function isShipDropdownGroup(
    item: DoctrineShipDropdown | DoctrineShipDropdownGroup
): item is DoctrineShipDropdownGroup {
    return (item as DoctrineShipDropdownGroup).options !== undefined
}

function toDropdownGroup({ name }: DoctrineShipGroup, ships: DoctrineShipDropdown[]): DoctrineShipDropdownGroup {
    return { label: name, options: ships }
}

function toDropdownItem({ id, name }: DoctrineShip): DoctrineShipDropdown {
    return { label: name, value: id }
}

function createUnknownGroup(ships: DoctrineShipDropdown[]): DoctrineShipDropdownGroup {
    return { label: 'Other', options: ships }
}

function sortGroup({ order: order1 = 99 }: DoctrineShipGroup, { order: order2 = 99 }: DoctrineShipGroup): number {
    return order1 - order2
}

export function getFlattenedDoctrineShips(entries: DoctrineShipGroup[] | DoctrineShip[]): DoctrineShip[] {
    return flattenGroupedArray<DoctrineShip, DoctrineShipGroup>(entries, isShipGroup, ({ ships }) => ships)
}

export function formatDoctrineDropdownEntries(
    ships: DoctrineShip[] | DoctrineShipGroup[]
): DoctrineShipDropdown[] | DoctrineShipDropdownGroup[] {
    const groups: DoctrineShipGroup[] = []
    const unknownShips: DoctrineShip[] = []

    ships.forEach((item) => {
        if (isShipGroup(item)) {
            groups.push(item)
        } else {
            unknownShips.push(item)
        }
    })

    const doctrineGroups = groups.sort(sortGroup).map((item) => toDropdownGroup(item, item.ships.map(toDropdownItem)))
    if (unknownShips.length > 0) {
        doctrineGroups.push(createUnknownGroup(unknownShips.map(toDropdownItem)))
    }

    if (doctrineGroups.length > 1) {
        return doctrineGroups
    }
    if (unknownShips.length > 0) {
        return unknownShips.map(toDropdownItem)
    }

    return []
}

export function getFlattenedDropdownEntries(
    entries: readonly DoctrineShipDropdown[] | DoctrineShipDropdownGroup[]
): DoctrineShipDropdown[] {
    return flattenGroupedArray<DoctrineShipDropdown, DoctrineShipDropdownGroup>(
        entries,
        isShipDropdownGroup,
        ({ options }) => options
    )
}

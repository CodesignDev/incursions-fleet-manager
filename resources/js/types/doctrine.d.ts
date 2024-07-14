import type { GroupBase } from 'react-select'
import { DropdownEntry } from '@/types/dropdown'

export type Doctrine = {
    id: string
    name: string
    ships: DoctrineShip[] | DoctrineShipGroup[]
}

export type DoctrineShipGroup = {
    name: string
    order?: number
    ships: DoctrineShip[]
}

export type DoctrineShip = {
    id: string
    name: string
}

export type DoctrineShipDropdown = DropdownEntry<string>

export type DoctrineShipDropdownValue = string

export type DoctrineShipDropdownGroup = GroupBase<DoctrineShipDropdown>

export type DoctrineShipOptions = DoctrineShip[] | DoctrineShipGroup[]
export type DoctrineDropdownOptions = DoctrineShipDropdown[] | DoctrineShipDropdownGroup[]

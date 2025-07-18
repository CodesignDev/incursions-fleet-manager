import { Fleet } from '@/types/fleets'

export type WaitlistInfo = {
    id: string
    name: string
}

export type Waitlist = WaitlistInfo & {
    doctrine: string | null
    total_entries?: number
    on_waitlist: boolean
    queue_position?: number
    characters?: WaitlistActiveCharacters
}

export type WaitlistCategory = {
    id: string
    name: string
    fleets?: Fleet[]
    waitlists?: Waitlist[]
}

type WaitlistActiveCharacters = {
    [character: string]: WaitlistCharacterEntry
}

type WaitlistCharacterEntry = {
    character: number
    ships: WaitlistCharacterEntryData
}

export type WaitlistCharacterEntryData = string | string[]

export type WaitlistJoinEntry = WaitlistCharacterEntry
export type WaitlistUpdateEntry =
    | ({ action: 'add' } & WaitlistCharacterEntry)
    | ({ action: 'update' } & WaitlistCharacterEntry)
    | ({ action: 'remove' } & Pick<WaitlistCharacterEntry, 'character'>)

type WaitlistCharacterDataDiff = {
    added: WaitlistCharacterEntry[]
    updated: WaitlistCharacterEntry[]
    removed: Pick<WaitlistCharacterEntry, 'character'>[]
}

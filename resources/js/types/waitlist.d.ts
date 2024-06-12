import { Fleet } from '@/types/fleets'

export type WaitlistInfo = {
    id: string
    name: string
}

export type Waitlist = WaitlistInfo & {
    on_waitlist: boolean
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
    requested_ship: string
}

export type WaitlistJoinEntry = WaitlistCharacterEntry
export type WaitlistUpdateEntry =
    | ({ action: 'add' } & WaitlistCharacterEntry)
    | ({ action: 'update' } & WaitlistCharacterEntry)
    | ({ action: 'remove' } & Pick<WaitlistCharacterEntry, 'character'>)

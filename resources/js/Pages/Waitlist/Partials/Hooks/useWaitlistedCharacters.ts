import { useMemo } from 'react'

import { Character, CharacterOrId } from '@/types'
import { isActiveCharacter } from '@/utils/waitlist'

export default function useWaitlistedCharacters(
    characters: Character[],
    charactersOnWaitlist: CharacterOrId[] = []
): [Character[], Character[]] {
    const hasWaitlistedCharacters = useMemo(() => charactersOnWaitlist.length > 0, [charactersOnWaitlist])

    const waitlistedCharacters = useMemo(() => {
        if (!hasWaitlistedCharacters) return []
        return characters.filter((character) => isActiveCharacter(charactersOnWaitlist, character))
    }, [characters, charactersOnWaitlist, hasWaitlistedCharacters])

    const remainingCharacters = useMemo(() => {
        if (hasWaitlistedCharacters) return characters
        return characters.filter((character) => !waitlistedCharacters.includes(character))
    }, [characters, hasWaitlistedCharacters, waitlistedCharacters])

    return [waitlistedCharacters, remainingCharacters]
}

import { difference, differenceBy, map, pick, remove } from 'lodash-es'

import { CharacterOrId, WaitlistCharacterDataDiff, WaitlistCharacterEntry } from '@/types'
import { getCharacterId } from '@/utils/characters'

export function isActiveCharacter(characters: CharacterOrId[], character: CharacterOrId): boolean {
    if (characters.length === 0) return false
    return characters.map(getCharacterId).includes(getCharacterId(character))
}

export function getCharacterDataDifferences(
    currentData: WaitlistCharacterEntry[],
    previousData: WaitlistCharacterEntry[]
): WaitlistCharacterDataDiff {
    // Get list of new and removed characters
    const currentCharacters = map(currentData, 'character')
    const previousCharacters = map(previousData, 'character')

    const newCharacters = difference(currentCharacters, previousCharacters)
    const removedCharacters = difference(previousCharacters, currentCharacters)

    // Handle new entries
    const newEntries = currentData.filter((item) => newCharacters.includes(item.character))
    const removedEntries = previousData
        .filter((item) => removedCharacters.includes(item.character))
        .map((item) => pick(item, 'character'))

    // Handle data differences
    const updatedEntries = differenceBy(
        currentData.filter((item) => !newCharacters.includes(item.character)),
        previousData.filter((item) => !removedCharacters.includes(item.character)),
        'ship'
    )

    return {
        added: newEntries,
        updated: updatedEntries,
        removed: removedEntries,
    }
}

export function getWaitlistEntryDiffAction(
    diff: WaitlistCharacterDataDiff,
    searchItem: WaitlistCharacterEntry | undefined
) {
    if (!searchItem) return undefined

    const { character } = searchItem
    switch (true) {
        case diff.added.some((item) => item.character === character):
            return 'add'
        case diff.updated.some((item) => item.character === character):
            return 'update'
        case diff.removed.some((item) => item.character === character):
            return 'remove'
    }
}

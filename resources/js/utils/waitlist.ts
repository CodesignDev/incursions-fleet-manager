import { Character, CharacterOrId } from '@/types'
import { getCharacterId } from '@/utils/characters'

export function isActiveCharacter(characters: CharacterOrId[], character: CharacterOrId): boolean {
    if (characters.length === 0) return false
    return characters.map(getCharacterId).includes(getCharacterId(character))
}
export function getWaitlistedCharacters(characters: Character[], charactersOnWaitlist: CharacterOrId[]) {
    const hasWaitlistedCharacters = charactersOnWaitlist && charactersOnWaitlist.length > 0

    const waitlistedCharacters = characters.filter((character) => isActiveCharacter(charactersOnWaitlist, character))
    const remainingCharacters = characters.filter(
        (character) => !hasWaitlistedCharacters || !waitlistedCharacters.includes(character)
    )

    return hasWaitlistedCharacters
        ? { characters, waitlistedCharacters: [], remainingCharacters: characters }
        : { characters, waitlistedCharacters, remainingCharacters }
}

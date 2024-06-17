import { CharacterOrId } from '@/types'
import { getCharacterId } from '@/utils/characters'

// eslint-disable-next-line import/prefer-default-export
export function isActiveCharacter(characters: CharacterOrId[], character: CharacterOrId): boolean {
    if (characters.length === 0) return false
    return characters.map(getCharacterId).includes(getCharacterId(character))
}

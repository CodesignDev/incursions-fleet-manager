import { GroupBase } from 'react-select'

import { Character, CharacterDropdownEntry, CharacterOrId, GroupedCharacters } from '@/types'

export function flattenCharacterList(characters: Character[] | GroupedCharacters): Character[] {
    if (Array.isArray(characters)) {
        return characters
    }

    return Object.entries(characters).flatMap(([, items]) => items)
}

export function formatCharacterDropdownEntries(characters: Character[] | GroupedCharacters) {
    return flattenCharacterList(characters).map(({ id, name }) => ({ label: name, value: id }))
}

export function formatCharacterGroupedDropdownEntries(
    characters: Character[] | GroupedCharacters
): CharacterDropdownEntry[] | GroupBase<CharacterDropdownEntry>[] {
    const formatCharacterEntry = ({ id, name }: Character): CharacterDropdownEntry => ({ label: name, value: id })

    if (Array.isArray(characters)) {
        return characters.map((character) => formatCharacterEntry(character))
    }

    return Object.entries(characters).map(([label, entries]) => ({
        label,
        options: entries.map((character) => formatCharacterEntry(character)),
    }))
}

export function getCharacterId(character: CharacterOrId): number {
    return typeof character === 'number' ? character : character.id
}

export function isMatchingCharacter(a: CharacterOrId, b: CharacterOrId) {
    return getCharacterId(a) === getCharacterId(b)
}

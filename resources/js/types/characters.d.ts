import { DropdownEntry } from '@/types/dropdown'

export type Character = {
    id: number
    name: string
}

export type CharacterWithAffiliation = Character & {
    corporation: string
    alliance?: string
}

export type GroupedCharacters = Record<string, Character[]>

export type CharacterGroup = {
    label?: string
    characters: Character[]
}

export type CharacterOrId = Character | number
export type CharacterDropdownEntry = DropdownEntry<string, number>

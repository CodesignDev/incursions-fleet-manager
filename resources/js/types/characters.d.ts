type Character = {
    id: number
    name: string
}

type CharacterWithAffiliation = Character & {
    corporation: string
    alliance?: string
}

type CharacterGroup = Record<string, Character[]>

import { createContext, PropsWithChildren, useCallback, useContext, useEffect, useMemo, useState } from 'react'

import { CharacterOrId, Nullable, WaitlistCharacterEntry, WaitlistCharacterEntryData } from '@/types'
import { getCharacterId, noop } from '@/utils'

type ContextProps = {
    characterData: WaitlistCharacterEntry[]
    updateCharacterData: (character: CharacterOrId, ship: Nullable<WaitlistCharacterEntryData>) => void
}

type WaitlistCharacterDataOutput = ContextProps & [ContextProps['characterData'], ContextProps['updateCharacterData']]
type WaitlistScopedCharacterDataOutput<T = WaitlistCharacterEntryData> = [
    T,
    (value: Nullable<WaitlistCharacterEntryData>) => void,
] & {
    value: T
    setValue: (value: Nullable<WaitlistCharacterEntryData>) => void
}

type ProviderProps = {
    initialData?: WaitlistCharacterEntry[]
    onCharacterDataUpdate?: (characterData: WaitlistCharacterEntry[]) => void
}

const defaultContextProps: ContextProps = {
    characterData: [],
    updateCharacterData: noop,
}

const CharacterDataContext = createContext(defaultContextProps)

function WaitlistCharacterDataProvider({
    initialData = [],
    onCharacterDataUpdate,
    children,
}: PropsWithChildren<ProviderProps>) {
    const [characterData, setCharacterData] = useState<WaitlistCharacterEntry[]>(() => initialData || [])

    const handleDataUpdate = useCallback((character: CharacterOrId, ships: Nullable<WaitlistCharacterEntryData>) => {
        const characterId = getCharacterId(character)

        setCharacterData((previousCharacterData) => {
            if (ships === null) {
                return previousCharacterData.filter((item) => item.character !== characterId)
            }

            if (previousCharacterData.some((item) => item.character === characterId)) {
                return previousCharacterData.map((item) => {
                    if (item.character === characterId) {
                        return { ...item, ships }
                    }
                    return item
                })
            }

            return [...previousCharacterData, { character: characterId, ships }]
        })
    }, [])

    useEffect(() => {
        onCharacterDataUpdate?.(characterData)
    }, [characterData, onCharacterDataUpdate])

    const contextValue = useMemo(
        () => ({
            characterData,
            updateCharacterData: handleDataUpdate,
        }),
        [characterData, handleDataUpdate]
    )

    return <CharacterDataContext.Provider value={contextValue}>{children}</CharacterDataContext.Provider>
}

function useWaitlistCharacterData(): WaitlistCharacterDataOutput
function useWaitlistCharacterData(
    character: CharacterOrId
): WaitlistScopedCharacterDataOutput<WaitlistCharacterEntryData | undefined>
function useWaitlistCharacterData(
    character: CharacterOrId,
    initialValue: WaitlistCharacterEntryData
): WaitlistScopedCharacterDataOutput

function useWaitlistCharacterData(character?: CharacterOrId, initialValue?: WaitlistCharacterEntryData) {
    const { characterData, updateCharacterData } = useContext(CharacterDataContext)

    const requestedCharacterData = useMemo(() => {
        if (!character) return undefined

        return characterData.find((item) => item.character === getCharacterId(character))?.ships || initialValue
    }, [characterData, character, initialValue])

    const handleUpdateCharacterData = useCallback(
        (value: Nullable<WaitlistCharacterEntryData>) => {
            if (character) {
                updateCharacterData(character, value)
            }
        },
        [updateCharacterData, character]
    )

    if (!character) {
        return Object.assign([characterData, updateCharacterData], {
            characterData,
            updateCharacterData,
        })
    }

    return Object.assign([requestedCharacterData, handleUpdateCharacterData], {
        value: requestedCharacterData,
        setValue: handleUpdateCharacterData,
    })
}

export { WaitlistCharacterDataProvider, useWaitlistCharacterData }

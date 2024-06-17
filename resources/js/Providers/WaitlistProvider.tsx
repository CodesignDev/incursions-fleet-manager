import { createContext, PropsWithChildren, useCallback, useContext, useMemo } from 'react'

import { CharacterOrId, Waitlist, WaitlistCharacterEntry, WaitlistInfo } from '@/types'
import { getCharacterId } from '@/utils'

type ContextProps = {
    waitlist: WaitlistInfo
    onWaitlist: boolean
    characterData: WaitlistCharacterEntry[]
    charactersOnWaitlist: number[]
    isCharacterOnWaitlist: (character: CharacterOrId) => boolean
}

type UseWaitlistOutput = ContextProps
type UseWaitlistCharacterScopedOutput = Pick<ContextProps, 'waitlist' | 'onWaitlist'> & {
    characterOnWaitlist: boolean
}

type ProviderProps = {
    waitlist: Waitlist
}

const defaultContextProps: ContextProps = {
    waitlist: { id: '', name: '' },
    onWaitlist: false,
    characterData: [],
    charactersOnWaitlist: [],
    isCharacterOnWaitlist: () => false,
}

const WaitlistContext = createContext(defaultContextProps)

function WaitlistProvider({ waitlist, children }: PropsWithChildren<ProviderProps>) {
    const { on_waitlist: onWaitlist = false, characters } = waitlist

    const characterData = useMemo(() => {
        if (!characters || !onWaitlist) return []

        return Object.values(characters)
    }, [characters, onWaitlist])

    const charactersOnWaitlist = useMemo(() => {
        return characterData.map(({ character }) => character)
    }, [characterData])

    const isCharacterOnWaitlist = useCallback(
        (character: CharacterOrId) => onWaitlist && charactersOnWaitlist.includes(getCharacterId(character)),
        [charactersOnWaitlist, onWaitlist]
    )

    const contextValue = useMemo(() => {
        return {
            waitlist,
            onWaitlist,
            characterData,
            charactersOnWaitlist,
            isCharacterOnWaitlist,
        }
    }, [waitlist, onWaitlist, characterData, charactersOnWaitlist, isCharacterOnWaitlist])

    return <WaitlistContext.Provider value={contextValue}>{children}</WaitlistContext.Provider>
}

function useWaitlist(): UseWaitlistOutput
function useWaitlist(character: CharacterOrId): UseWaitlistCharacterScopedOutput

function useWaitlist(character?: CharacterOrId) {
    const { waitlist, onWaitlist, isCharacterOnWaitlist, ...context } = useContext(WaitlistContext)

    const characterOnWaitlist = useMemo(() => {
        if (!character || !onWaitlist) return false
        return isCharacterOnWaitlist(character)
    }, [character, onWaitlist, isCharacterOnWaitlist])

    if (character) {
        return {
            waitlist,
            onWaitlist,
            characterOnWaitlist,
        }
    }

    return { waitlist, onWaitlist, isCharacterOnWaitlist, ...context }
}

export { WaitlistProvider, useWaitlist }

import { createContext, PropsWithChildren, useCallback, useContext, useMemo } from 'react'

import { useDoctrines } from '@/Providers/DoctrineProvider'
import { CharacterOrId, Doctrine, Nullable, Waitlist, WaitlistCharacterEntry, WaitlistInfo } from '@/types'
import { getCharacterId } from '@/utils'

type ContextProps = {
    waitlist: WaitlistInfo
    onWaitlist: boolean
    doctrine: Nullable<Doctrine>
    hasDoctrine: boolean
    characterData: WaitlistCharacterEntry[]
    charactersOnWaitlist: number[]
    isCharacterOnWaitlist: (character: CharacterOrId) => boolean
}

type UseWaitlistOutput = ContextProps
type UseWaitlistCharacterScopedOutput = Pick<ContextProps, 'waitlist' | 'onWaitlist' | 'doctrine' | 'hasDoctrine'> & {
    characterOnWaitlist: boolean
}

type ProviderProps = {
    waitlist: Waitlist
}

const defaultContextProps: ContextProps = {
    waitlist: { id: '', name: '' },
    onWaitlist: false,
    doctrine: null,
    hasDoctrine: false,
    characterData: [],
    charactersOnWaitlist: [],
    isCharacterOnWaitlist: () => false,
}

const WaitlistContext = createContext(defaultContextProps)

function WaitlistProvider({ waitlist, children }: PropsWithChildren<ProviderProps>) {
    const { getDoctrine } = useDoctrines()
    const { on_waitlist: onWaitlist = false, characters } = waitlist

    const doctrine = useMemo(() => {
        const { doctrine: doctrineId } = waitlist
        return doctrineId ? getDoctrine(doctrineId) : null
    }, [waitlist, getDoctrine])

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
            doctrine,
            hasDoctrine: !!doctrine,
            characterData,
            charactersOnWaitlist,
            isCharacterOnWaitlist,
        }
    }, [waitlist, onWaitlist, doctrine, characterData, charactersOnWaitlist, isCharacterOnWaitlist])

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

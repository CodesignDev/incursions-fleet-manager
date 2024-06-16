import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { Waitlist, WaitlistCharacterEntry, WaitlistInfo } from '@/types'

type ContextProps = {
    waitlist: WaitlistInfo
    onWaitlist: boolean
    characterData: WaitlistCharacterEntry[]
    charactersOnWaitlist: number[]
}

type ProviderProps = {
    waitlist: Waitlist
}

const defaultContextProps: ContextProps = {
    waitlist: { id: '', name: '' },
    onWaitlist: false,
    characterData: [],
    charactersOnWaitlist: [],
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

    const contextValue = useMemo(() => {
        return {
            waitlist,
            onWaitlist,
            characterData,
            charactersOnWaitlist,
        }
    }, [waitlist, onWaitlist, characterData, charactersOnWaitlist])

    return <WaitlistContext.Provider value={contextValue}>{children}</WaitlistContext.Provider>
}

function useWaitlist() {
    return useContext(WaitlistContext)
}

export { WaitlistProvider, useWaitlist }

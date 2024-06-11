import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { Character } from '@/types'

type ContextProps = {
    characters: Character[]
}

type ProviderProps = {
    characters: Character[]
}

const defaultContextProps: ContextProps = { characters: [] }

const WaitlistCharactersContext = createContext(defaultContextProps)

function WaitlistCharactersProvider({ characters, children }: PropsWithChildren<ProviderProps>) {
    const contextValue = useMemo(() => {
        return {
            characters,
        }
    }, [characters])

    return <WaitlistCharactersContext.Provider value={contextValue}>{children}</WaitlistCharactersContext.Provider>
}

function useWaitlistCharacters() {
    return useContext(WaitlistCharactersContext)
}

export { WaitlistCharactersProvider, useWaitlistCharacters }

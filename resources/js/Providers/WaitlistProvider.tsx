import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { Waitlist, WaitlistInfo } from '@/types'

type ContextProps = {
    waitlist: WaitlistInfo
    onWaitlist: boolean
    charactersOnWaitlist: number[]
}

type ProviderProps = {
    waitlist: Waitlist
}

const defaultContextProps: ContextProps = {
    waitlist: { id: '', name: '' },
    onWaitlist: false,
    charactersOnWaitlist: [],
}

const WaitlistContext = createContext(defaultContextProps)

function WaitlistProvider({ waitlist, children }: PropsWithChildren<ProviderProps>) {
    const { on_waitlist: onWaitlist = false } = waitlist
    const contextValue = useMemo(() => {
        return {
            waitlist,
            onWaitlist,
            charactersOnWaitlist: [],
        }
    }, [waitlist, onWaitlist])

    return <WaitlistContext.Provider value={contextValue}>{children}</WaitlistContext.Provider>
}

function useWaitlist() {
    return useContext(WaitlistContext)
}

export { WaitlistProvider, useWaitlist }

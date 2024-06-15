import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { WaitlistInfo, WaitlistJoinEntry, WaitlistUpdateEntry } from '@/types'
import { noop } from '@/utils'

type ContextProps = {
    joinWaitlistHandler: (waitlist: WaitlistInfo, entries: WaitlistJoinEntry[]) => void
    leaveWaitlistHandler: (waitlist: WaitlistInfo) => void
    updateCharacterEntryHandler: (waitlist: WaitlistInfo, entry: WaitlistUpdateEntry) => void
}

type WaitlistActionsOutput = ContextProps
type WaitlistScopedActionsOutput = {
    joinWaitlistHandler: (entries: WaitlistJoinEntry[]) => void
    leaveWaitlistHandler: () => void
    updateCharacterEntryHandler: (entry: WaitlistUpdateEntry) => void
}

type ProviderProps = {
    onJoinWaitlist: (waitlist: WaitlistInfo, entries: WaitlistJoinEntry[]) => void
    onLeaveWaitlist: (waitlist: WaitlistInfo) => void
    onCharacterUpdated: (waitlist: WaitlistInfo, entry: WaitlistUpdateEntry) => void
}

const defaultContextProps: ContextProps = {
    joinWaitlistHandler: noop,
    leaveWaitlistHandler: noop,
    updateCharacterEntryHandler: noop,
}

const WaitlistActionsContext = createContext(defaultContextProps)

function WaitlistActionsProvider({
    onJoinWaitlist,
    onLeaveWaitlist,
    onCharacterUpdated,
    children,
}: PropsWithChildren<ProviderProps>) {
    const contextValue = useMemo(() => {
        return {
            joinWaitlistHandler: onJoinWaitlist,
            leaveWaitlistHandler: onLeaveWaitlist,
            updateCharacterEntryHandler: onCharacterUpdated,
        }
    }, [onJoinWaitlist, onLeaveWaitlist, onCharacterUpdated])

    return <WaitlistActionsContext.Provider value={contextValue}>{children}</WaitlistActionsContext.Provider>
}

function useWaitlistActions(): WaitlistActionsOutput
function useWaitlistActions(waitlist: WaitlistInfo): WaitlistScopedActionsOutput

function useWaitlistActions(waitlist?: WaitlistInfo) {
    const actions = useContext(WaitlistActionsContext)

    if (!waitlist) return actions

    const { joinWaitlistHandler, leaveWaitlistHandler, updateCharacterEntryHandler } = actions
    return {
        joinWaitlistHandler: (entries: WaitlistJoinEntry[]) => joinWaitlistHandler(waitlist, entries),
        leaveWaitlistHandler: () => leaveWaitlistHandler(waitlist),
        updateCharacterEntryHandler: (entry: WaitlistUpdateEntry) => updateCharacterEntryHandler(waitlist, entry),
    }
}

export { WaitlistActionsProvider, useWaitlistActions }

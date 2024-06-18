import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { WaitlistCharacterEntry, WaitlistInfo, WaitlistJoinEntry, WaitlistUpdateEntry } from '@/types'
import { noop } from '@/utils'

type ContextProps = {
    joinWaitlistHandler: (waitlist: WaitlistInfo, entries: WaitlistJoinEntry[]) => void
    leaveWaitlistHandler: (waitlist: WaitlistInfo) => void
    updateCharacterEntryHandler: (waitlist: WaitlistInfo, entry: WaitlistUpdateEntry) => void
    dataSyncHandler: (waitlist: WaitlistInfo, data: WaitlistCharacterEntry[]) => void
}

type WaitlistActionsOutput = ContextProps
type WaitlistScopedActionsOutput = {
    joinWaitlistHandler: (entries: WaitlistJoinEntry[]) => void
    leaveWaitlistHandler: () => void
    updateCharacterEntryHandler: (entry: WaitlistUpdateEntry) => void
    dataSyncHandler: (data: WaitlistCharacterEntry[]) => void
}

type ProviderProps = {
    onJoinWaitlist: (waitlist: WaitlistInfo, entries: WaitlistJoinEntry[]) => void
    onLeaveWaitlist: (waitlist: WaitlistInfo) => void
    onCharacterUpdated: (waitlist: WaitlistInfo, entry: WaitlistUpdateEntry) => void
    onDataSync?: (waitlist: WaitlistInfo, data: WaitlistCharacterEntry[]) => void
}

const defaultContextProps: ContextProps = {
    joinWaitlistHandler: noop,
    leaveWaitlistHandler: noop,
    updateCharacterEntryHandler: noop,
    dataSyncHandler: noop,
}

const WaitlistActionsContext = createContext(defaultContextProps)

function WaitlistActionsProvider({
    onJoinWaitlist,
    onLeaveWaitlist,
    onCharacterUpdated,
    onDataSync,
    children,
}: PropsWithChildren<ProviderProps>) {
    const contextValue = useMemo(
        () => ({
            joinWaitlistHandler: onJoinWaitlist,
            leaveWaitlistHandler: onLeaveWaitlist,
            updateCharacterEntryHandler: onCharacterUpdated,
            dataSyncHandler: onDataSync || noop,
        }),
        [onJoinWaitlist, onLeaveWaitlist, onCharacterUpdated, onDataSync]
    )

    return <WaitlistActionsContext.Provider value={contextValue}>{children}</WaitlistActionsContext.Provider>
}

function useWaitlistActions(): WaitlistActionsOutput
function useWaitlistActions(waitlist: WaitlistInfo): WaitlistScopedActionsOutput

function useWaitlistActions(waitlist?: WaitlistInfo) {
    const actions = useContext(WaitlistActionsContext)

    if (!waitlist) return actions

    const { joinWaitlistHandler, leaveWaitlistHandler, updateCharacterEntryHandler, dataSyncHandler } = actions
    return {
        joinWaitlistHandler: (entries: WaitlistJoinEntry[]) => joinWaitlistHandler(waitlist, entries),
        leaveWaitlistHandler: () => leaveWaitlistHandler(waitlist),
        updateCharacterEntryHandler: (entry: WaitlistUpdateEntry) => updateCharacterEntryHandler(waitlist, entry),
        dataSyncHandler: (data: WaitlistCharacterEntry[]) => dataSyncHandler(waitlist, data),
    }
}

export { WaitlistActionsProvider, useWaitlistActions }

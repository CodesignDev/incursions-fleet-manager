import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { WaitlistInfo, WaitlistJoinEntry, WaitlistUpdateEntry } from '@/types'
import { noop } from '@/utils'

type ContextProps = {
    characterJoinedHandler: (waitlist: WaitlistInfo, entries: WaitlistJoinEntry[]) => void
    characterLeftHandler: (waitlist: WaitlistInfo) => void
    characterUpdatedHandler: (waitlist: WaitlistInfo, entry: WaitlistUpdateEntry) => void
}

type WaitlistActions = ContextProps
type WaitlistScopedActions = {
    characterJoinedHandler: (entries: WaitlistJoinEntry[]) => void
    characterLeftHandler: () => void
    characterUpdatedHandler: (entry: WaitlistUpdateEntry) => void
}

type ProviderProps = {
    onCharacterJoined: (waitlist: WaitlistInfo, entries: WaitlistJoinEntry[]) => void
    onCharacterLeft: (waitlist: WaitlistInfo) => void
    onCharacterUpdated: (waitlist: WaitlistInfo, entry: WaitlistUpdateEntry) => void
}

const defaultContextProps: ContextProps = {
    characterJoinedHandler: noop,
    characterLeftHandler: noop,
    characterUpdatedHandler: noop,
}

const WaitlistActionsContext = createContext(defaultContextProps)

function WaitlistActionsProvider({
    onCharacterJoined,
    onCharacterLeft,
    onCharacterUpdated,
    children,
}: PropsWithChildren<ProviderProps>) {
    const contextValue = useMemo(() => {
        return {
            characterJoinedHandler: onCharacterJoined,
            characterLeftHandler: onCharacterLeft,
            characterUpdatedHandler: onCharacterUpdated,
        }
    }, [onCharacterJoined, onCharacterLeft, onCharacterUpdated])

    return <WaitlistActionsContext.Provider value={contextValue}>{children}</WaitlistActionsContext.Provider>
}

function useWaitlistActions(): WaitlistActions
function useWaitlistActions(waitlist: WaitlistInfo): WaitlistScopedActions

function useWaitlistActions(waitlist?: WaitlistInfo) {
    const actions = useContext(WaitlistActionsContext)

    if (!waitlist) return actions

    const { characterJoinedHandler, characterLeftHandler, characterUpdatedHandler } = actions
    return {
        characterJoinedHandler: (entries: WaitlistJoinEntry[]) => characterJoinedHandler(waitlist, entries),
        characterLeftHandler: () => characterLeftHandler(waitlist),
        characterUpdatedHandler: (entry: WaitlistUpdateEntry) => characterUpdatedHandler(waitlist, entry),
    }
}

export { WaitlistActionsProvider, useWaitlistActions }

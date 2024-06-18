import { PropsWithChildren, useCallback } from 'react'

import { router } from '@inertiajs/react'

import { WaitlistActionsProvider } from '@/Providers/WaitlistActionsProvider'
import { WaitlistCharacterEntry, WaitlistInfo, WaitlistJoinEntry, WaitlistUpdateEntry } from '@/types'

export default function WaitlistActionsHandler({ children }: PropsWithChildren) {
    const handleJoin = useCallback((waitlist: WaitlistInfo, characters: WaitlistJoinEntry[]) => {
        if (characters.length < 1) return
        router.post(route('waitlist.join', waitlist), { characters })
    }, [])

    const handleLeave = useCallback((waitlist: WaitlistInfo) => {
        router.delete(route('waitlist.leave', waitlist))
    }, [])

    const handleUpdate = useCallback((waitlist: WaitlistInfo, data: WaitlistUpdateEntry) => {
        router.put(route('waitlist.update', waitlist), { ...data })
    }, [])

    return (
        <WaitlistActionsProvider
            onJoinWaitlist={handleJoin}
            onLeaveWaitlist={handleLeave}
            onCharacterUpdated={handleUpdate}
        >
            {children}
        </WaitlistActionsProvider>
    )
}

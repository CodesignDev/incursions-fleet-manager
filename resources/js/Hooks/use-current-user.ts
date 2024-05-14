import { useMemo } from 'react'

import usePageProps from '@/Hooks/use-page-props'
import { User } from '@/types'

type UserHookOutput = {
    user?: User
    isLoggedIn: boolean
}

type LoggedInUserHookOutput = Required<Omit<UserHookOutput, 'isLoggedIn'>>

export function useCurrentUser(): UserHookOutput {
    const { auth } = usePageProps()
    const { user } = auth

    const isLoggedIn = useMemo(() => user !== null, [user])

    return {
        user: user || undefined,
        isLoggedIn,
    }
}

export function useCurrentLoggedInUser(): LoggedInUserHookOutput {
    const { isLoggedIn, user, ...props } = useCurrentUser()
    if (!isLoggedIn) {
        throw new Error('This hook expects there to be a user that is logged in')
    }

    return { user: user!, ...props }
}

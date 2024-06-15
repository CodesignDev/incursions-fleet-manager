import { ForwardedRef, forwardRef, useCallback } from 'react'

import Button from '@/Components/Button'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { tw } from '@/utils'

type JoinButtonProps = {
    enabled?: boolean
    onClick?: () => void
    onJoinClick?: () => void
    onLeaveClick?: () => void
}

export default forwardRef(function JoinButton(
    { enabled = true, onClick, onJoinClick, onLeaveClick }: JoinButtonProps,
    ref: ForwardedRef<HTMLButtonElement>
) {
    const { onWaitlist = false } = useWaitlist()

    const handleButtonClick = useCallback(() => {
        onClick?.()
        onWaitlist ? onLeaveClick?.() : onJoinClick?.()
    }, [onWaitlist, onClick, onJoinClick, onLeaveClick])

    return (
        <div className="flex flex-col items-stretch sm:items-end sm:px-4">
            <Button
                variant={onWaitlist ? 'danger' : 'accept'}
                className={tw('max-sm:rounded-none max-sm:border-x-0', {
                    'dark:border-green-700 dark:bg-green-700 dark:text-white': !onWaitlist,
                    'dark:border-red-600 dark:bg-red-600 dark:text-white': onWaitlist,
                })}
                onClick={handleButtonClick}
                disabled={!enabled}
                ref={ref}
            >
                {onWaitlist ? 'Leave Waitlist' : 'Join Waitlist'}
            </Button>
        </div>
    )
})

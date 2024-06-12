import { useState } from 'react'

import WaitlistGrid from '@/Pages/Waitlist/Partials/WaitlistGrid'
import WaitlistTable from '@/Pages/Waitlist/Partials/WaitlistTable'
import { useWaitlistCharacters } from '@/Providers/WaitlistCharactersProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { WaitlistSelectionProvider } from '@/Providers/WaitlistSelectionProvider'
import { WaitlistInfo } from '@/types'
import { tw } from '@/utils'
import { getWaitlistedCharacters } from '@/utils/waitlist'

type WaitlistProps = {
    waitlist: WaitlistInfo
    className?: string
}

export default function Waitlist({ waitlist, className = '' }: WaitlistProps) {
    const { onWaitlist, charactersOnWaitlist } = useWaitlist()
    const { characters } = useWaitlistCharacters()

    const { waitlistedCharacters, remainingCharacters } = getWaitlistedCharacters(characters, charactersOnWaitlist)

    return (
        <div className={tw('space-y-2', className)}>
            <div className="space-y-6">
                {onWaitlist && waitlistedCharacters.length > 0 ? (
                    <>
                        <WaitlistTable
                            header="Characters currently on Waitlist"
                            characters={waitlistedCharacters}
                            showRowActions
                        />
                        <WaitlistTable
                            header="Add additional characters"
                            characters={remainingCharacters}
                            showRowActions
                        />
                    </>
                ) : (
                    <WaitlistSelectionProvider options={characters} initialSelectedOptions={[]}>
                        <WaitlistGrid header="Join the Waitlist" characters={characters} showSelectionCheckbox />
                    </WaitlistSelectionProvider>
                )}
            </div>

            {/* <div className="-mx-6 sm:mx-0"> */}
            {/*    <WaitlistJoinButton */}
            {/*        enableJoinButton={joinWaitlistButtonEnabled} */}
            {/*        onJoinWaitlistClick={() => handleJoinWaitlistClick()} */}
            {/*        onLeaveWaitlistClick={() => handleLeaveWaitlistClick()} */}
            {/*    /> */}
            {/* </div> */}
        </div>
    )
}

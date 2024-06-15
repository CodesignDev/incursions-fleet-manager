import { useCallback, useMemo, useState } from 'react'

import Tooltip from '@/Components/Tooltip'
import useStateWithTimeout from '@/Hooks/use-state-with-timeout'
import JoinButton from '@/Pages/Waitlist/Partials/JoinButton'
import WaitlistGrid from '@/Pages/Waitlist/Partials/WaitlistGrid'
import WaitlistTable from '@/Pages/Waitlist/Partials/WaitlistTable'
import { useWaitlistActions } from '@/Providers/WaitlistActionsProvider'
import { WaitlistCharacterDataProvider } from '@/Providers/WaitlistCharacterDataProvider'
import { WaitlistCharacterSelectionProvider } from '@/Providers/WaitlistCharacterSelectionProvider'
import { useWaitlistCharacters } from '@/Providers/WaitlistCharactersProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId, WaitlistCharacterEntry, WaitlistInfo } from '@/types'
import { getCharacterId, tw } from '@/utils'
import { getWaitlistedCharacters } from '@/utils/waitlist'

type WaitlistProps = {
    waitlist: WaitlistInfo
    className?: string
}

export default function Waitlist({ waitlist, className = '' }: WaitlistProps) {
    const { onWaitlist, charactersOnWaitlist } = useWaitlist()
    const { characters } = useWaitlistCharacters()
    const { joinWaitlistHandler, leaveWaitlistHandler } = useWaitlistActions(waitlist)

    const [selectedCharacters, setSelectedCharacters] = useState<CharacterOrId[]>([])
    const [characterData, setCharacterData] = useState<WaitlistCharacterEntry[]>([])

    const [showErrorTooltip, setShowErrorTooltip] = useStateWithTimeout(false)

    const { waitlistedCharacters, remainingCharacters } = getWaitlistedCharacters(characters, charactersOnWaitlist)

    const handleJoinButtonClick = useCallback(() => {
        const selectedCharacterIds = selectedCharacters.map(getCharacterId)
        const data = characterData.filter(
            ({ character, ship }) => selectedCharacterIds.includes(character) && ship !== ''
        )

        if (data.length > 0) {
            joinWaitlistHandler(data)
            return
        }

        setShowErrorTooltip(true, 5000)
    }, [joinWaitlistHandler, selectedCharacters, characterData])

    const handleLeaveButtonClick = useCallback(() => {
        leaveWaitlistHandler()
    }, [leaveWaitlistHandler])

    return (
        <div className={tw('space-y-4', className)}>
            <div className="space-y-6">
                <WaitlistCharacterDataProvider initialData={[]} onCharacterDataUpdate={setCharacterData}>
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
                        <WaitlistCharacterSelectionProvider
                            options={characters}
                            initialSelectedOptions={charactersOnWaitlist}
                            onSelectionChange={setSelectedCharacters}
                        >
                            <WaitlistGrid header="Join the Waitlist" characters={characters} showSelectionCheckbox />
                        </WaitlistCharacterSelectionProvider>
                    )}
                </WaitlistCharacterDataProvider>
            </div>

            <Tooltip
                showTooltip={showErrorTooltip}
                placement="left"
                content="There are no valid characters that can be added to the waitlist."
            >
                <JoinButton onJoinClick={handleJoinButtonClick} onLeaveClick={handleLeaveButtonClick} />
            </Tooltip>
        </div>
    )
}

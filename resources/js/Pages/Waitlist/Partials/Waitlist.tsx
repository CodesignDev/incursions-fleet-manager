import { useCallback, useState } from 'react'

import Tooltip from '@/Components/Tooltip'
import useStateWithTimeout from '@/Hooks/useStateWithTimeout'
import useWaitlistedCharacters from '@/Pages/Waitlist/Partials/Hooks/useWaitlistedCharacters'
import JoinButton from '@/Pages/Waitlist/Partials/JoinButton'
import WaitlistGrid from '@/Pages/Waitlist/Partials/WaitlistGrid'
import { useWaitlistActions } from '@/Providers/WaitlistActionsProvider'
import { WaitlistCharacterDataProvider } from '@/Providers/WaitlistCharacterDataProvider'
import { WaitlistCharacterSelectionProvider } from '@/Providers/WaitlistCharacterSelectionProvider'
import { useWaitlistCharacters } from '@/Providers/WaitlistCharactersProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId, WaitlistCharacterEntry, WaitlistInfo } from '@/types'
import { getCharacterId, tw } from '@/utils'

type WaitlistProps = {
    waitlist: WaitlistInfo
    className?: string
}

export default function Waitlist({ waitlist, className = '' }: WaitlistProps) {
    const { onWaitlist, charactersOnWaitlist, characterData } = useWaitlist()
    const { characters } = useWaitlistCharacters()
    const { joinWaitlistHandler, leaveWaitlistHandler } = useWaitlistActions(waitlist)

    const [selectedCharacters, setSelectedCharacters] = useState<CharacterOrId[]>([])
    const [currentCharacterData, setCurrentCharacterData] = useState<WaitlistCharacterEntry[]>([])

    const [showErrorTooltip, setShowErrorTooltip] = useStateWithTimeout(false)

    const [waitlistedCharacters, remainingCharacters] = useWaitlistedCharacters(characters, charactersOnWaitlist)

    const handleJoinButtonClick = useCallback(() => {
        const selectedCharacterIds = selectedCharacters.map(getCharacterId)
        const data = currentCharacterData.filter(
            ({ character, ship }) => selectedCharacterIds.includes(character) && ship !== ''
        )

        if (data.length > 0) {
            joinWaitlistHandler(data)
            return
        }

        setShowErrorTooltip(true, 5000)
    }, [joinWaitlistHandler, selectedCharacters, currentCharacterData, setShowErrorTooltip])

    const handleLeaveButtonClick = useCallback(() => {
        leaveWaitlistHandler()
    }, [leaveWaitlistHandler])

    return (
        <div className={tw('space-y-4', className)}>
            <div className="space-y-6">
                <WaitlistCharacterDataProvider
                    initialData={characterData}
                    onCharacterDataUpdate={setCurrentCharacterData}
                >
                    {onWaitlist && waitlistedCharacters.length > 0 ? (
                        <>
                            <WaitlistGrid
                                header="Characters currently on Waitlist"
                                characters={waitlistedCharacters}
                                showRowActions
                            />
                            <WaitlistGrid
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

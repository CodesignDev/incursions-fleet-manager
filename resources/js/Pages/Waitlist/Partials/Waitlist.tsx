import { useCallback, useEffect, useState } from 'react'

import { first, isEqual, sum } from 'lodash-es'

import Tooltip from '@/Components/Tooltip'
import usePrevious from '@/Hooks/usePrevious'
import useStateWithTimeout from '@/Hooks/useStateWithTimeout'
import useWaitlistedCharacters from '@/Pages/Waitlist/Partials/Hooks/useWaitlistedCharacters'
import JoinButton from '@/Pages/Waitlist/Partials/JoinButton'
import WaitlistGrid from '@/Pages/Waitlist/Partials/WaitlistGrid'
import { useWaitlistActions } from '@/Providers/WaitlistActionsProvider'
import { WaitlistCharacterDataProvider } from '@/Providers/WaitlistCharacterDataProvider'
import { WaitlistCharacterEntryEditProvider } from '@/Providers/WaitlistCharacterEntryEditProvider'
import { WaitlistCharacterSelectionProvider } from '@/Providers/WaitlistCharacterSelectionProvider'
import { useWaitlistCharacters } from '@/Providers/WaitlistCharactersProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId, WaitlistCharacterEntry, WaitlistInfo } from '@/types'
import { getCharacterId, tw } from '@/utils'
import { getCharacterDataDifferences, getWaitlistEntryDiffAction } from '@/utils/waitlist'

type WaitlistProps = {
    waitlist: WaitlistInfo
    className?: string
}

export default function Waitlist({ waitlist, className = '' }: WaitlistProps) {
    const { onWaitlist, charactersOnWaitlist, characterData } = useWaitlist()
    const { characters } = useWaitlistCharacters()
    const { joinWaitlistHandler, leaveWaitlistHandler, updateCharacterEntryHandler, dataSyncHandler } =
        useWaitlistActions(waitlist)

    const [selectedCharacters, setSelectedCharacters] = useState<CharacterOrId[]>([])
    const [currentCharacterData, setCurrentCharacterData] = useState<WaitlistCharacterEntry[]>(characterData || [])

    const previousCharacterData = usePrevious(currentCharacterData)

    const [showErrorTooltip, setShowErrorTooltip] = useStateWithTimeout(false)

    const [waitlistedCharacters, remainingCharacters] = useWaitlistedCharacters(characters, charactersOnWaitlist)

    useEffect(() => {
        if (!onWaitlist) return
        if (!previousCharacterData || isEqual(currentCharacterData, previousCharacterData)) return

        const diff = getCharacterDataDifferences(currentCharacterData, previousCharacterData)
        const { added, updated, removed } = diff

        // Count total changes
        const totalChanges = sum([added.length, updated.length, removed.length])
        if (totalChanges > 1) {
            dataSyncHandler(characterData)
            return
        }

        // Send relevant changes
        const item = first([...added, ...updated, ...removed]) as WaitlistCharacterEntry | undefined
        const action = getWaitlistEntryDiffAction(diff, item)
        if (item && action) updateCharacterEntryHandler({ action, ...item })
    }, [currentCharacterData, previousCharacterData])

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
                {onWaitlist && waitlistedCharacters.length > 0 ? (
                    <WaitlistCharacterDataProvider
                        key="active-waitlist-data"
                        initialData={characterData}
                        onCharacterDataUpdate={setCurrentCharacterData}
                    >
                        <WaitlistGrid
                            header="Characters currently on Waitlist"
                            characters={waitlistedCharacters}
                            showRowActions
                        >
                            {({ character }) => (
                                <WaitlistCharacterEntryEditProvider key={character.id}>
                                    {({ canEdit }) => (
                                        <WaitlistGrid.Row
                                            character={character}
                                            className={tw({ 'max-sm:bg-gray-50 max-sm:dark:bg-gray-700': canEdit })}
                                        />
                                    )}
                                </WaitlistCharacterEntryEditProvider>
                            )}
                        </WaitlistGrid>
                        <WaitlistGrid
                            key="waitlist-data"
                            header="Add additional characters"
                            characters={remainingCharacters}
                            noItemsMessage="There are no other characters available to add to the waitlist."
                            showRowActions
                        >
                            {({ character }) => (
                                <WaitlistCharacterEntryEditProvider key={character.id}>
                                    <WaitlistGrid.Row character={character} />
                                </WaitlistCharacterEntryEditProvider>
                            )}
                        </WaitlistGrid>
                    </WaitlistCharacterDataProvider>
                ) : (
                    <WaitlistCharacterDataProvider initialData={[]} onCharacterDataUpdate={setCurrentCharacterData}>
                        <WaitlistCharacterSelectionProvider
                            options={characters}
                            initialSelectedOptions={charactersOnWaitlist}
                            onSelectionChange={setSelectedCharacters}
                        >
                            <WaitlistGrid header="Join the Waitlist" characters={characters} showSelectionCheckbox />
                        </WaitlistCharacterSelectionProvider>
                    </WaitlistCharacterDataProvider>
                )}
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

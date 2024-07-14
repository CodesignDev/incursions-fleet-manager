import { useCallback, useEffect, useLayoutEffect, useMemo, useState } from 'react'

import { isEmpty } from 'lodash-es'
import { useDebounceCallback } from 'usehooks-ts'

import CharacterShipDisplay from '@/Pages/Waitlist/Partials/CharacterShipDisplay'
import CharacterShipInput from '@/Pages/Waitlist/Partials/CharacterShipInput'
import { useWaitlistCharacterData } from '@/Providers/WaitlistCharacterDataProvider'
import { useWaitlistCharacterEntryEditHandler } from '@/Providers/WaitlistCharacterEntryEditProvider'
import { useWaitlistCharacterSelector } from '@/Providers/WaitlistCharacterSelectionProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId, WaitlistCharacterEntryData } from '@/types'

type CharacterShipEntryProps = {
    character: CharacterOrId
    placeholder?: string
}

export default function CharacterShipEntry({ character, placeholder }: CharacterShipEntryProps) {
    const { onWaitlist = false, hasDoctrine, characterOnWaitlist = false } = useWaitlist(character)
    const { isSelected, selectOption } = useWaitlistCharacterSelector(character)

    const { isCurrentlyEditing, finishEditing, registerEventListeners } = useWaitlistCharacterEntryEditHandler()

    const [value, setValue] = useWaitlistCharacterData(character, hasDoctrine ? [] : '')

    const setValueDebounced = useDebounceCallback(setValue, 2000)

    const [internalValue, setInternalValue] = useState(value)

    const allowEditing = useMemo(
        () => !onWaitlist || !characterOnWaitlist || isCurrentlyEditing,
        [onWaitlist, characterOnWaitlist, isCurrentlyEditing]
    )

    const handleChange = useCallback(
        (inputValue: WaitlistCharacterEntryData) => {
            setInternalValue(inputValue)
            if (!onWaitlist) setValueDebounced(inputValue)
            if (!isSelected) selectOption(inputValue !== '')
        },
        [onWaitlist, isSelected, selectOption, setValueDebounced]
    )

    const syncChanges = useCallback(() => {
        const { isPending, flush } = setValueDebounced
        if (isPending()) flush()
    }, [setValueDebounced])

    const handleStartEdit = useCallback(() => {
        setInternalValue(value)
    }, [value])

    const handleSaveChanges = useCallback(() => {
        finishEditing()
        if (characterOnWaitlist) setValue(!isEmpty(internalValue) ? internalValue : null)
        if (!characterOnWaitlist && !isEmpty(internalValue)) setValue(internalValue)
    }, [characterOnWaitlist, internalValue, setValue, finishEditing])

    const handleDiscardChanges = useCallback(() => {
        finishEditing()
    }, [finishEditing])

    const handleRemoveEntry = useCallback(() => {
        setValue(null)
    }, [setValue])

    useEffect(() => {
        if (!onWaitlist) return
        setInternalValue(value)
    }, [value, onWaitlist])

    useLayoutEffect(() => {
        registerEventListeners({ onStartEditing: handleStartEdit })
    }, [registerEventListeners, handleStartEdit])

    useLayoutEffect(() => {
        registerEventListeners({ onSaveChanges: handleSaveChanges })
    }, [registerEventListeners, handleSaveChanges])

    useLayoutEffect(() => {
        registerEventListeners({ onDiscardChanges: handleDiscardChanges })
    }, [registerEventListeners, handleDiscardChanges])

    useLayoutEffect(() => {
        registerEventListeners({ onRemoveEntry: handleRemoveEntry })
    }, [registerEventListeners, handleRemoveEntry])

    if (!allowEditing) return <CharacterShipDisplay value={value} />

    return (
        <CharacterShipInput
            value={internalValue}
            placeholder={placeholder}
            onChange={handleChange}
            onBlur={syncChanges}
        />
    )
}

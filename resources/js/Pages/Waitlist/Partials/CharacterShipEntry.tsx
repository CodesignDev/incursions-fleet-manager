import { ChangeEventHandler, useCallback, useEffect, useLayoutEffect, useMemo, useState } from 'react'

import { useDebounceCallback } from 'usehooks-ts'

import TextInput from '@/Components/TextInput'
import { useWaitlistCharacterData } from '@/Providers/WaitlistCharacterDataProvider'
import { useWaitlistCharacterEntryEditHandler } from '@/Providers/WaitlistCharacterEntryEditProvider'
import { useWaitlistCharacterSelector } from '@/Providers/WaitlistCharacterSelectionProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId } from '@/types'

type CharacterShipEntryProps = {
    character: CharacterOrId
    placeholder?: string
}

export default function CharacterShipEntry({ character, placeholder }: CharacterShipEntryProps) {
    const { onWaitlist = false, characterOnWaitlist = false } = useWaitlist(character)
    const { isSelected, selectOption } = useWaitlistCharacterSelector(character)

    const { canEdit, finishEditing, registerEventListeners } = useWaitlistCharacterEntryEditHandler()

    const [value, setValue] = useWaitlistCharacterData(character, '')

    const setValueDebounced = useDebounceCallback(setValue, 2000)

    const [internalValue, setInternalValue] = useState(value)

    const allowEditing = useMemo(
        () => !onWaitlist || !characterOnWaitlist || canEdit,
        [onWaitlist, characterOnWaitlist, canEdit]
    )

    const handleChange: ChangeEventHandler<HTMLInputElement> = useCallback(
        (e) => {
            const inputValue = e.target.value

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
        if (characterOnWaitlist) setValue(internalValue !== '' ? internalValue : null)
        if (!characterOnWaitlist && internalValue !== '') setValue(internalValue)
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
    }, [handleStartEdit])

    useLayoutEffect(() => {
        registerEventListeners({ onSaveChanges: handleSaveChanges })
    }, [handleSaveChanges])

    useLayoutEffect(() => {
        registerEventListeners({ onDiscardChanges: handleDiscardChanges })
    }, [handleDiscardChanges])

    useLayoutEffect(() => {
        registerEventListeners({ onRemoveEntry: handleRemoveEntry })
    }, [handleRemoveEntry])

    if (!allowEditing) {
        return (
            <div className="flex w-full cursor-not-allowed select-none flex-wrap rounded-md border border-gray-300 bg-gray-100 px-2 py-2.5 text-sm text-gray-600 shadow-sm empty:h-10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                {value}
            </div>
        )
    }

    return (
        <TextInput
            className="w-full px-2 text-sm leading-6 disabled:select-none"
            placeholder={placeholder || 'Ships you can bring...'}
            value={internalValue}
            onChange={handleChange}
            onBlur={syncChanges}
        />
    )
}

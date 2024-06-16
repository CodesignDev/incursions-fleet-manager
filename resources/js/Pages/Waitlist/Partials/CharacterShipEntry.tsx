import { ChangeEventHandler, useCallback, useEffect, useMemo, useState } from 'react'

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
    const { onWaitlist = false } = useWaitlist()
    const { isSelected, selectOption } = useWaitlistCharacterSelector(character)

    const { canEdit, finishEditing } = useWaitlistCharacterEntryEditHandler({
        onSaveChanges: () => handleSaveChanges(),
        onDiscardChanges: () => handleDiscardChanges(),
        onRemoveEntry: () => handleRemoveEntry(),
    })

    const [value, setValue] = useWaitlistCharacterData(character, '')

    const setValueDebounced = useDebounceCallback(setValue, 2000)

    const [internalValue, setInternalValue] = useState(value)

    const allowEditing = useMemo(() => !onWaitlist || canEdit, [onWaitlist, canEdit])

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

    const handleSaveChanges = useCallback(() => {
        finishEditing()
        setValue(internalValue)
    }, [])

    const handleDiscardChanges = useCallback(() => {
        finishEditing()
        setInternalValue(value)
    }, [])

    const handleRemoveEntry = useCallback(() => {
        setValue(null)
    }, [])

    useEffect(() => {
        if (!onWaitlist) return
        setInternalValue(value)
    }, [value, onWaitlist])

    if (!allowEditing) {
        return <span>{value}</span>
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

import { ChangeEventHandler, useCallback, useState } from 'react'

import { useDebounceCallback } from 'usehooks-ts'

import TextInput from '@/Components/TextInput'
import { useWaitlistCharacterData } from '@/Providers/WaitlistCharacterDataProvider'
import { useWaitlistCharacterSelector } from '@/Providers/WaitlistCharacterSelectionProvider'
import { CharacterOrId } from '@/types'

type CharacterShipEntryProps = {
    character: CharacterOrId
    placeholder?: string
}

export default function CharacterShipEntry({ character, placeholder }: CharacterShipEntryProps) {
    const { isSelected, selectOption } = useWaitlistCharacterSelector(character)
    const [value, setValue] = useWaitlistCharacterData(character, '')

    const setValueDebounced = useDebounceCallback(setValue, 2000)

    const [internalValue, setInternalValue] = useState(value)

    const handleChange: ChangeEventHandler<HTMLInputElement> = useCallback(
        (e) => {
            const inputValue = e.target.value

            setInternalValue(inputValue)
            setValueDebounced(inputValue)
            if (!isSelected) selectOption(inputValue !== '')
        },
        [isSelected, selectOption, setValueDebounced]
    )

    const syncChanges = useCallback(() => {
        const { isPending, flush } = setValueDebounced
        if (isPending()) flush()
    }, [setValueDebounced])

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

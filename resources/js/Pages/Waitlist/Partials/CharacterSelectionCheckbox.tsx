import { ChangeEventHandler, InputHTMLAttributes, useCallback, useLayoutEffect, useMemo, useRef, useState } from 'react'

import Checkbox from '@/Components/Checkbox'
import { useWaitlistCharacterSelector } from '@/Providers/WaitlistCharacterSelectionProvider'
import { CharacterOrId } from '@/types'

type CheckboxProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'checked' | 'onChange'>

type CharacterSelectionCheckboxProps = CheckboxProps & {
    character: CharacterOrId
}

type ToggleAllCheckboxProps = CheckboxProps & {
    indeterminateToChecked?: boolean
}

function CharacterSelectionCheckbox({ character, ...props }: CharacterSelectionCheckboxProps) {
    const { isSelected, selectOption } = useWaitlistCharacterSelector(character)

    const handleSelectionChange: ChangeEventHandler<HTMLInputElement> = useCallback(
        (e) => {
            selectOption(e.target.checked)
        },
        [selectOption]
    )

    return <Checkbox checked={isSelected} onChange={handleSelectionChange} {...props} />
}

function ToggleAllCheckbox({ indeterminateToChecked = false, ...props }: ToggleAllCheckboxProps) {
    const [indeterminate, setIndeterminate] = useState(false)
    const [checked, setChecked] = useState(false)

    const { optionCount, selectedOptionCount, selectAllOptions } = useWaitlistCharacterSelector()

    const ref = useRef<HTMLInputElement>(null)

    useLayoutEffect(() => {
        const isIndeterminate = selectedOptionCount > 0 && selectedOptionCount < optionCount
        setIndeterminate(isIndeterminate)
        setChecked(selectedOptionCount === optionCount)

        if (ref.current) ref.current.indeterminate = isIndeterminate
    }, [optionCount, selectedOptionCount])

    const toggleAll = useCallback(() => {
        const newState = indeterminateToChecked ? !checked || indeterminate : !indeterminate && !checked

        setIndeterminate(false)
        setChecked(newState)
        selectAllOptions(newState)
    }, [checked, indeterminate, selectAllOptions])

    return <Checkbox ref={ref} checked={checked} onChange={toggleAll} {...props} />
}

CharacterSelectionCheckbox.ToggleAll = ToggleAllCheckbox

export default CharacterSelectionCheckbox

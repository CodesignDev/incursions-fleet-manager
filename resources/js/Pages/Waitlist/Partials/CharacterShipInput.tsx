import { ChangeEventHandler, FocusEventHandler, useCallback, useMemo } from 'react'

import MultiSelect from '@/Components/MultiSelect'
import TextInput from '@/Components/TextInput'
import { useDoctrines } from '@/Providers/DoctrineProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { DoctrineShipDropdown, WaitlistCharacterEntryData } from '@/types'

type CharacterShipInputProps = {
    value?: WaitlistCharacterEntryData
    placeholder?: string
    onChange?: (value: WaitlistCharacterEntryData) => void
    onBlur?: () => void
}

export default function CharacterShipInput({ value, placeholder, onChange, onBlur }: CharacterShipInputProps) {
    const { getShipsForDoctrine } = useDoctrines()
    const { hasDoctrine, doctrine } = useWaitlist()

    const doctrineOptions = useMemo(() => {
        if (!doctrine) return []
        return getShipsForDoctrine(doctrine, { asDropdownEntries: true })
    }, [doctrine, getShipsForDoctrine])

    const selectedOptions = useMemo(() => {
        if (!value || typeof value === 'string' || !doctrine) return []
        const doctrineShips = getShipsForDoctrine(doctrine, {
            asDropdownEntries: true,
            shipsOnly: true,
        }) as DoctrineShipDropdown[]
        const selectedDoctrineShips = value.map((ship) => doctrineShips.find((item) => item.value === ship))

        return selectedDoctrineShips.filter((item) => item) as DoctrineShipDropdown[]
    }, [value, doctrine, getShipsForDoctrine])

    const handleTextInputChange: ChangeEventHandler<HTMLInputElement> = useCallback(
        (e) => onChange?.(e.target.value),
        [onChange]
    )

    const handleDropdownChange = useCallback(
        (selections: readonly DoctrineShipDropdown[]) => {
            onChange?.(selections.map((item) => item.value))
        },
        [onChange]
    )

    const handleBlur: FocusEventHandler<HTMLInputElement> = useCallback(() => onBlur?.(), [onBlur])

    return hasDoctrine ? (
        <MultiSelect
            multiple
            options={doctrineOptions}
            placeholder="Ships you can bring..."
            value={selectedOptions}
            onChange={handleDropdownChange}
            onBlur={handleBlur}
            className="w-full"
        />
    ) : (
        <TextInput
            value={value}
            placeholder={placeholder || 'Ships you can bring...'}
            onChange={handleTextInputChange}
            onBlur={handleBlur}
            className="w-full px-2 text-sm leading-6 disabled:select-none"
        />
    )
}

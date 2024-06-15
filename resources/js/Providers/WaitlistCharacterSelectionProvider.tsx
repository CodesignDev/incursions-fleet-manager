import { createContext, PropsWithChildren, useCallback, useContext, useEffect, useMemo, useState } from 'react'

import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId } from '@/types'
import { getCharacterId, noop } from '@/utils'

type ContextProps = {
    options: CharacterOrId[]
    selectedOptions: CharacterOrId[]
    optionCount: number
    selectedOptionCount: number
    isOptionSelected: (item: CharacterOrId) => boolean
    selectOption: (item: CharacterOrId, selected: boolean) => void
    selectAllOptions: (selected: boolean) => void
}

type WaitlistSelectorOutput = ContextProps
type CharacterScopedWaitlistSelectorOutput = Pick<
    ContextProps,
    'options' | 'selectedOptions' | 'optionCount' | 'selectedOptionCount'
> & {
    isSelected: boolean
    selectOption: (selected?: boolean) => void
}

type ProviderProps = {
    options: CharacterOrId[]
    initialSelectedOptions?: CharacterOrId[]
    onSelectionChange?: (selectedOptions: CharacterOrId[]) => void
}

const defaultContextProps: ContextProps = {
    options: [],
    selectedOptions: [],
    optionCount: 0,
    selectedOptionCount: 0,
    isOptionSelected: () => false,
    selectOption: noop,
    selectAllOptions: noop,
}

const CharacterSelectionProvider = createContext(defaultContextProps)

function WaitlistCharacterSelectionProvider({
    options,
    initialSelectedOptions = [],
    onSelectionChange,
    children,
}: PropsWithChildren<ProviderProps>) {
    const { onWaitlist = false } = useWaitlist()
    const [selected, setSelected] = useState<number[]>(() => initialSelectedOptions.map(getCharacterId))

    const optionIds = useMemo(() => options.map(getCharacterId), [options])

    const selectedOptions = useMemo(
        () => options.filter((option) => selected.includes(getCharacterId(option))),
        [options, selected]
    )

    useEffect(() => {
        if (onWaitlist) setSelected([])
    }, [onWaitlist])

    const isOptionSelected = useCallback(
        (character: CharacterOrId) => {
            return selected.includes(getCharacterId(character))
        },
        [selected]
    )

    const handleSelectOption = useCallback((character: CharacterOrId, selectOption: boolean) => {
        const characterId = getCharacterId(character)
        setSelected((existing) =>
            selectOption ? [...existing, characterId] : existing.filter((option) => option !== characterId)
        )
    }, [])

    const handleSelectAllOptions = useCallback(
        (selectOptions: boolean) => {
            setSelected(selectOptions ? optionIds : [])
        },
        [options]
    )

    const contextValue: ContextProps = useMemo(
        () => ({
            options,
            selectedOptions,
            optionCount: options.length,
            selectedOptionCount: selectedOptions.length,
            isOptionSelected,
            selectOption: handleSelectOption,
            selectAllOptions: handleSelectAllOptions,
        }),
        [options, selected, isOptionSelected, handleSelectOption, handleSelectAllOptions]
    )

    useEffect(() => {
        onSelectionChange?.(selected)
    }, [selected, onSelectionChange])

    return <CharacterSelectionProvider.Provider value={contextValue}>{children}</CharacterSelectionProvider.Provider>
}

function useWaitlistCharacterSelector(): WaitlistSelectorOutput
function useWaitlistCharacterSelector(character: CharacterOrId): CharacterScopedWaitlistSelectorOutput

function useWaitlistCharacterSelector(character: CharacterOrId | null = null) {
    const context = useContext(CharacterSelectionProvider)

    if (!character) {
        return context
    }

    const { isOptionSelected, selectOption, selectAllOptions: _, ...rest } = context

    return {
        ...rest,
        isSelected: isOptionSelected(character),
        selectOption: (selected = true) => selectOption(character, selected),
    }
}

export { WaitlistCharacterSelectionProvider, useWaitlistCharacterSelector }

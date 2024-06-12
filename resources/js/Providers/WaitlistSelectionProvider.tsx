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

type UseWaitlistSelectorOutput = Omit<ContextProps, 'selectedValues'>
type CharacterScopedUseWaitlistSelectorOutput = Pick<
    ContextProps,
    'options' | 'selectedOptions' | 'optionCount' | 'selectedOptionCount'
> & {
    isSelected: boolean
    selectOption: (selected: boolean) => void
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

const WaitlistSelectionContext = createContext(defaultContextProps)

function WaitlistSelectionProvider({
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

    return <WaitlistSelectionContext.Provider value={contextValue}>{children}</WaitlistSelectionContext.Provider>
}

function useWaitlistSelector(): UseWaitlistSelectorOutput
function useWaitlistSelector(character: CharacterOrId): CharacterScopedUseWaitlistSelectorOutput

function useWaitlistSelector(character: CharacterOrId | null = null) {
    const context = useContext(WaitlistSelectionContext)

    if (!character) {
        return context
    }

    const { options, selectedOptions, isOptionSelected, selectOption } = context

    return {
        options,
        selectedOptions,
        isSelected: isOptionSelected(character),
        selectOption: (selected: boolean) => selectOption(character, selected),
    }
}

export { WaitlistSelectionProvider, useWaitlistSelector }

import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react'

import { Nullable, PropsWithChildrenPlusRenderProps } from '@/types'
import { noop, renderChildren } from '@/utils'

type NoopFunction = () => void

type ContextProps = {
    isCurrentlyEditing: boolean
    startEditing: () => void
    finishEditing: () => void
    handleActionButtonClick: (action: WaitlistEditActionType) => void
    registerEventListeners: (events: WaitlistCharacterEditEventHandlers) => void
}

type WaitlistCharacterEditEventHandlers = {
    onStartEditing?: Nullable<() => void>
    onFinishEditing?: Nullable<() => void>
    onSaveChanges?: Nullable<() => void>
    onDiscardChanges?: Nullable<() => void>
    onRemoveEntry?: Nullable<() => void>
}

type ProviderRenderProps = {
    isCurrentlyEditing: boolean
    startEditing: () => void
    finishEditing: () => void
}

type WaitlistEntryEditHandlerOutput = ContextProps

type WaitlistEditActionType = 'edit' | 'save' | 'discard' | 'remove'

const defaultContextProps: ContextProps = {
    isCurrentlyEditing: false,
    startEditing: noop,
    finishEditing: noop,
    handleActionButtonClick: noop,
    registerEventListeners: noop,
}

const CharacterEntryEditContext = createContext(defaultContextProps)

function WaitlistCharacterEntryEditProvider({ children }: PropsWithChildrenPlusRenderProps<ProviderRenderProps>) {
    const [isCurrentlyEditing, setIsCurrentlyEditing] = useState(false)

    const startEditingCallbackRef = useRef<NoopFunction>()
    const finishEditingCallbackRef = useRef<NoopFunction>()

    const saveChangesHandlerRef = useRef<NoopFunction>()
    const discardChangesHandlerRef = useRef<NoopFunction>()
    const removeEntryHandlerRef = useRef<NoopFunction>()

    const startEditing = useCallback(() => setIsCurrentlyEditing(true), [])
    const finishEditing = useCallback(() => setIsCurrentlyEditing(false), [])

    const handleActionButtonClick = useCallback(
        (action: WaitlistEditActionType) => {
            // eslint-disable-next-line default-case
            switch (action) {
                case 'edit':
                    startEditing()
                    break
                case 'save':
                    saveChangesHandlerRef.current?.()
                    break
                case 'discard':
                    discardChangesHandlerRef.current?.()
                    break
                case 'remove':
                    removeEntryHandlerRef.current?.()
                    break
            }
        },
        [startEditing]
    )

    const registerEventListeners = useCallback((events: WaitlistCharacterEditEventHandlers) => {
        const { onStartEditing, onFinishEditing, onSaveChanges, onDiscardChanges, onRemoveEntry } = events

        if (onStartEditing) startEditingCallbackRef.current = onStartEditing
        if (onFinishEditing) finishEditingCallbackRef.current = onFinishEditing

        if (onSaveChanges) saveChangesHandlerRef.current = onSaveChanges
        if (onDiscardChanges) discardChangesHandlerRef.current = onDiscardChanges
        if (onRemoveEntry) removeEntryHandlerRef.current = onRemoveEntry
    }, [])

    useEffect(() => {
        if (isCurrentlyEditing) {
            startEditingCallbackRef.current?.()
        } else {
            finishEditingCallbackRef.current?.()
        }
    }, [isCurrentlyEditing])

    const contextValue = useMemo(
        () => ({
            isCurrentlyEditing,
            startEditing,
            finishEditing,
            handleActionButtonClick,
            registerEventListeners,
        }),
        [isCurrentlyEditing, startEditing, finishEditing, handleActionButtonClick, registerEventListeners]
    )

    return (
        <CharacterEntryEditContext.Provider value={contextValue}>
            {renderChildren(children, {
                isCurrentlyEditing,
                startEditing,
                finishEditing,
            })}
        </CharacterEntryEditContext.Provider>
    )
}

function useWaitlistCharacterEntryEditHandler(): WaitlistEntryEditHandlerOutput {
    return useContext(CharacterEntryEditContext)
}

export { WaitlistCharacterEntryEditProvider, useWaitlistCharacterEntryEditHandler }

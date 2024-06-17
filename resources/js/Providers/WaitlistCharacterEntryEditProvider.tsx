import {
    createContext,
    MutableRefObject,
    PropsWithChildren,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react'

import { PropsWithChildrenPlusRenderProps } from '@/types'
import { noop, renderChildren } from '@/utils'

type NoopFunction = () => void

type ContextProps = {
    canEdit: boolean
    startEditing: () => void
    finishEditing: () => void
    handleActionButtonClick: (action: WaitlistEditActionType) => void
    startEditingCallback: MutableRefObject<NoopFunction | undefined>
    finishEditingCallback: MutableRefObject<NoopFunction | undefined>
    saveChangesHandler: MutableRefObject<NoopFunction | undefined>
    discardChangesHandler: MutableRefObject<NoopFunction | undefined>
    removeEntryHandler: MutableRefObject<NoopFunction | undefined>
}

type WaitlistCharacterEditHandlerOptions = {
    onStartEditing?: () => void
    onFinishEditing?: () => void
    onSaveChanges?: () => void
    onDiscardChanges?: () => void
    onRemoveEntry?: () => void
}

type ProviderRenderProps = {
    canEdit: boolean
    startEditing: () => void
    finishEditing: () => void
}

type WaitlistEntryEditHandlerOutput = Pick<
    ContextProps,
    'canEdit' | 'startEditing' | 'finishEditing' | 'handleActionButtonClick'
>

type WaitlistEditActionType = 'edit' | 'save' | 'discard' | 'remove'

const defaultContextProps: ContextProps = {
    canEdit: false,
    startEditing: noop,
    finishEditing: noop,
    handleActionButtonClick: noop,
    startEditingCallback: { current: noop },
    finishEditingCallback: { current: noop },
    saveChangesHandler: { current: noop },
    discardChangesHandler: { current: noop },
    removeEntryHandler: { current: noop },
}

const CharacterEntryEditContext = createContext(defaultContextProps)

function WaitlistCharacterEntryEditProvider({ children }: PropsWithChildrenPlusRenderProps<ProviderRenderProps>) {
    const [canEdit, setCanEdit] = useState(false)

    const startEditingCallbackRef = useRef<NoopFunction>()
    const finishEditingCallbackRef = useRef<NoopFunction>()

    const saveChangesHandlerRef = useRef<NoopFunction>()
    const discardChangesHandlerRef = useRef<NoopFunction>()
    const removeEntryHandlerRef = useRef<NoopFunction>()

    const startEditing = useCallback(() => setCanEdit(true), [])
    const finishEditing = useCallback(() => setCanEdit(false), [])

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

    useEffect(() => {
        if (canEdit) {
            startEditingCallbackRef.current?.()
        } else {
            finishEditingCallbackRef.current?.()
        }
    }, [canEdit])

    const contextValue = useMemo(
        () => ({
            canEdit,
            startEditing,
            finishEditing,
            handleActionButtonClick,
            startEditingCallback: startEditingCallbackRef,
            finishEditingCallback: finishEditingCallbackRef,
            saveChangesHandler: saveChangesHandlerRef,
            discardChangesHandler: discardChangesHandlerRef,
            removeEntryHandler: removeEntryHandlerRef,
        }),
        [canEdit, startEditing, finishEditing, handleActionButtonClick]
    )

    return (
        <CharacterEntryEditContext.Provider value={contextValue}>
            {renderChildren(children, {
                canEdit,
                startEditing,
                finishEditing,
            })}
        </CharacterEntryEditContext.Provider>
    )
}

function useWaitlistCharacterEntryEditHandler(
    options: WaitlistCharacterEditHandlerOptions = {}
): WaitlistEntryEditHandlerOutput {
    const { onStartEditing, onFinishEditing, onSaveChanges, onDiscardChanges, onRemoveEntry } = options

    const {
        startEditingCallback,
        finishEditingCallback,
        saveChangesHandler,
        discardChangesHandler,
        removeEntryHandler,
        ...context
    } = useContext(CharacterEntryEditContext)

    useEffect(() => {
        startEditingCallback.current = onStartEditing
    }, [onStartEditing]) // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        finishEditingCallback.current = onFinishEditing
    }, [onFinishEditing]) // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        saveChangesHandler.current = onSaveChanges
    }, [onSaveChanges]) // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        discardChangesHandler.current = onDiscardChanges
    }, [onDiscardChanges]) // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        removeEntryHandler.current = onRemoveEntry
    }, [onRemoveEntry]) // eslint-disable-line react-hooks/exhaustive-deps

    return context
}

export { WaitlistCharacterEntryEditProvider, useWaitlistCharacterEntryEditHandler }

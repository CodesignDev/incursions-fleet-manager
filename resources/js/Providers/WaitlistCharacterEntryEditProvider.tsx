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

import { noop } from '@/utils'

type NoopFunction = () => void

type ContextProps = {
    canEdit: boolean
    startEditing: () => void
    finishEditing: () => void
    handleActionButtonClick: (action: WaitlistEditActionType) => void
    saveChangesHandler: MutableRefObject<NoopFunction | undefined>
    discardChangesHandler: MutableRefObject<NoopFunction | undefined>
    removeEntryHandler: MutableRefObject<NoopFunction | undefined>
}

type WaitlistCharacterEditHandlerOptions = {
    onSaveChanges?: () => void
    onDiscardChanges?: () => void
    onRemoveEntry?: () => void
}

type WaitlistEntryEditHandlerOutput = Pick<
    ContextProps,
    'canEdit' | 'startEditing' | 'finishEditing' | 'handleActionButtonClick'
> & {}

type ProviderProps = {}

type WaitlistEditActionType = 'edit' | 'save' | 'discard' | 'remove'

const defaultContextProps: ContextProps = {
    canEdit: false,
    startEditing: noop,
    finishEditing: noop,
    handleActionButtonClick: noop,
    saveChangesHandler: { current: noop },
    discardChangesHandler: { current: noop },
    removeEntryHandler: { current: noop },
}

const CharacterEntryEditContext = createContext(defaultContextProps)

function WaitlistCharacterEntryEditProvider({ children }: PropsWithChildren<ProviderProps>) {
    const [canEdit, setCanEdit] = useState(false)

    const saveChangesHandlerRef = useRef<NoopFunction>()
    const discardChangesHandlerRef = useRef<NoopFunction>()
    const removeEntryHandlerRef = useRef<NoopFunction>()

    const startEditing = useCallback(() => setCanEdit(true), [])
    const finishEditing = useCallback(() => setCanEdit(false), [])

    const handleActionButtonClick = useCallback((action: WaitlistEditActionType) => {
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
    }, [])

    const contextValue = useMemo(
        () => ({
            canEdit,
            startEditing,
            finishEditing,
            handleActionButtonClick,
            saveChangesHandler: saveChangesHandlerRef,
            discardChangesHandler: discardChangesHandlerRef,
            removeEntryHandler: removeEntryHandlerRef,
        }),
        [canEdit, handleActionButtonClick]
    )

    return <CharacterEntryEditContext.Provider value={contextValue}>{children}</CharacterEntryEditContext.Provider>
}

function useWaitlistCharacterEntryEditHandler(
    options: WaitlistCharacterEditHandlerOptions = {}
): WaitlistEntryEditHandlerOutput {
    const { onSaveChanges, onDiscardChanges, onRemoveEntry } = options

    const { saveChangesHandler, discardChangesHandler, removeEntryHandler, ...context } =
        useContext(CharacterEntryEditContext)

    useEffect(() => {
        saveChangesHandler.current = onSaveChanges
    }, [onSaveChanges])

    useEffect(() => {
        discardChangesHandler.current = onDiscardChanges
    }, [onDiscardChanges])

    useEffect(() => {
        removeEntryHandler.current = onRemoveEntry
    }, [onRemoveEntry])

    return context
}

export { WaitlistCharacterEntryEditProvider, useWaitlistCharacterEntryEditHandler }

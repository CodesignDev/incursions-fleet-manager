/* eslint @typescript-eslint/no-use-before-define: ["error", { "functions": false }] --
 * Disable the no-use-before-define error for functions only, allows the relevant
 * components to be laid out in the order they are exported */

import { createContext, useContext, useMemo } from 'react'

import useElementId from '@/Hooks/use-element-id'
import CharacterSelectionCheckbox from '@/Pages/Waitlist/Partials/CharacterSelectionCheckbox'
import CharacterShipEntry from '@/Pages/Waitlist/Partials/CharacterShipEntry'
import { useWaitlistCharacterSelector } from '@/Providers/WaitlistCharacterSelectionProvider'
import { Character } from '@/types'
import { tw } from '@/utils'

type WaitlistGridContextProps = {
    includeSelectionCheckbox: boolean
    includeRowActions: boolean
}

type WaitlistGridProps = {
    characters?: Character[]
    header?: string
    showSelectionCheckbox?: boolean
    showRowActions?: boolean
    noItemsMessage?: string
}

type WaitlistElementProps = {
    borderClassName?: string
}

type WaitlistGridHeaderProps = WaitlistElementProps

type WaitlistGridRowProps = WaitlistElementProps & {
    character: Character
}

const defaultGridContextProps: WaitlistGridContextProps = {
    includeSelectionCheckbox: false,
    includeRowActions: false,
}

const WaitlistGridContext = createContext(defaultGridContextProps)

function WaitlistGrid({
    characters = [],
    header,
    showSelectionCheckbox = false,
    showRowActions = false,
    noItemsMessage = '',
}: WaitlistGridProps) {
    const tableContextValue = useMemo(
        () => ({
            includeSelectionCheckbox: showSelectionCheckbox,
            includeRowActions: showRowActions,
        }),
        [showSelectionCheckbox, showRowActions]
    )

    const headerBorderClassName = tw`border-b border-gray-200 dark:border-gray-600`
    const rowBorderClassName = tw`border-b border-gray-200 dark:border-gray-700`

    return (
        <div className="space-y-2">
            {header && <h3 className="px-4 font-semibold">{header}</h3>}
            <div className="inline-block min-w-full pt-2 align-middle">
                <WaitlistGridContext.Provider value={tableContextValue}>
                    <div
                        className={tw('grid min-w-full grid-cols-1 sm:grid-cols-[minmax(0,16rem)_1fr]', {
                            'grid-cols-[1fr_2rem] sm:grid-cols-[minmax(0,16rem)_1fr_2rem]': showRowActions,
                        })}
                    >
                        <GridHeader borderClassName={headerBorderClassName} />

                        {characters.length > 0 ? (
                            characters.map((character) => (
                                <GridRow
                                    key={character.id}
                                    character={character}
                                    borderClassName={rowBorderClassName}
                                />
                            ))
                        ) : (
                            <BlankGridRow label={noItemsMessage} />
                        )}
                    </div>
                </WaitlistGridContext.Provider>
            </div>
        </div>
    )
}

function GridHeader({ borderClassName }: WaitlistGridHeaderProps) {
    const { includeSelectionCheckbox, includeRowActions } = useContext(WaitlistGridContext)

    return (
        <>
            <div
                className={tw(
                    'flex flex-row items-center gap-x-6 px-4 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200',
                    borderClassName
                )}
            >
                {includeSelectionCheckbox && (
                    <div className="flex">
                        <CharacterSelectionCheckbox.ToggleAll indeterminateToChecked />
                    </div>
                )}
                <div>
                    <span>Character</span>
                    <span className="sm:hidden"> / Ship(s)</span>
                </div>
            </div>

            <div
                className={tw(
                    'hidden px-4 py-3.5 text-left text-sm font-semibold text-gray-900 sm:block dark:text-gray-200',
                    borderClassName
                )}
            >
                Ship(s)
            </div>

            {includeRowActions && <div className={tw('hidden sm:block', borderClassName)} />}
        </>
    )
}

function GridRow({ character, borderClassName }: WaitlistGridRowProps) {
    const { includeSelectionCheckbox, includeRowActions } = useContext(WaitlistGridContext)
    const { isSelected } = useWaitlistCharacterSelector(character)

    const checkboxId = useElementId(`checkbox-${character.id}`)

    return (
        <>
            <div
                className={tw(
                    'relative grid grid-cols-[min-content_1fr] grid-rows-[min-content_1fr] flex-row gap-x-6 gap-y-1.5 p-4 sm:flex sm:items-center',
                    { 'grid-cols-1': !includeSelectionCheckbox },
                    borderClassName
                )}
            >
                {includeSelectionCheckbox && (
                    <>
                        {isSelected && <div className="absolute inset-y-0 left-0 w-1 bg-primary-600" />}
                        <div className="row-span-2 flex py-0.5">
                            <CharacterSelectionCheckbox character={character} id={checkboxId} />
                        </div>
                    </>
                )}

                <label htmlFor={checkboxId} className="text-sm/5 font-medium">
                    {character.name}
                </label>

                <span className="text-sm sm:hidden">
                    <CharacterShipEntry character={character} />
                </span>
            </div>

            <div className={tw('hidden items-center p-4 text-sm sm:flex', borderClassName)}>
                <CharacterShipEntry character={character} />
            </div>

            {includeRowActions && <div className={tw('whitespace-nowrap px-4', borderClassName)}>Actions</div>}
        </>
    )
}

function BlankGridRow({ label = '' }) {
    return <div className="col-span-full p-4 text-sm italic">{label || 'There are no characters to display'}</div>
}

export default WaitlistGrid

/* eslint @typescript-eslint/no-use-before-define: ["error", { "functions": false }] --
 * Disable the no-use-before-define error for functions only, allows the relevant
 * components to be laid out in the order they are exported */

import { createContext, PropsWithChildren, ReactNode, useContext, useMemo } from 'react'

import useElementId from '@/Hooks/useElementId'
import CharacterSelectionCheckbox from '@/Pages/Waitlist/Partials/CharacterSelectionCheckbox'
import CharacterShipActions from '@/Pages/Waitlist/Partials/CharacterShipActions'
import CharacterShipEntry from '@/Pages/Waitlist/Partials/CharacterShipEntry'
import { useWaitlistCharacterSelector } from '@/Providers/WaitlistCharacterSelectionProvider'
import { Character, PropsWithChildrenPlusRenderProps } from '@/types'
import { renderChildren, tw } from '@/utils'

type WaitlistGridContextProps = {
    includeSelectionCheckbox: boolean
    includeRowActions: boolean
}

type WaitlistGridProps = {
    characters?: Character[]
    header?: string
    showSelectionCheckbox?: boolean
    showRowActions?: boolean
    noItems?: ReactNode
    noItemsMessage?: string
}

type WaitlistGridElementProps = {
    className?: string
}

type WaitlistGridWrapperProps = WaitlistGridElementProps
type WaitlistGridHeaderProps = WaitlistGridElementProps

type WaitlistGridRowProps = WaitlistGridElementProps & {
    character: Character
}

type WaitlistGridRenderProps = {
    character: Character
}

const defaultGridContextProps: WaitlistGridContextProps = {
    includeSelectionCheckbox: false,
    includeRowActions: false,
}

const WaitlistGridContext = createContext(defaultGridContextProps)

const headerBorderClassName = tw`border-b border-gray-200 dark:border-gray-600`
const rowBorderClassName = tw`border-b border-gray-200 dark:border-gray-700`

function WaitlistGrid({
    characters = [],
    header,
    showSelectionCheckbox = false,
    showRowActions = false,
    noItems,
    noItemsMessage = '',
    children,
}: PropsWithChildrenPlusRenderProps<WaitlistGridRenderProps, WaitlistGridProps>) {
    const tableContextValue = useMemo(
        () => ({
            includeSelectionCheckbox: showSelectionCheckbox,
            includeRowActions: showRowActions,
        }),
        [showSelectionCheckbox, showRowActions]
    )

    return (
        <div className="space-y-2">
            {header && <h3 className="px-4 font-semibold">{header}</h3>}

            <div className="inline-block min-w-full pt-2 align-middle">
                <WaitlistGridContext.Provider value={tableContextValue}>
                    <div
                        className={tw('grid min-w-full grid-cols-1 gap-x-2.5 sm:grid-cols-[minmax(0,16rem)_1fr]', {
                            'grid-cols-[1fr_2.5rem] sm:grid-cols-[minmax(0,16rem)_1fr_min-content]': showRowActions,
                        })}
                    >
                        <GridHeader />

                        {characters.length > 0
                            ? characters.map((character) =>
                                  renderChildren(children, { character }, () => (
                                      <GridRow key={character.id} character={character} />
                                  ))
                              )
                            : noItems || <BlankGridRow label={noItemsMessage} />}
                    </div>
                </WaitlistGridContext.Provider>
            </div>
        </div>
    )
}

function GridRowWrapper({ className = '', children }: PropsWithChildren<WaitlistGridWrapperProps>) {
    return <div className={tw('col-span-full grid grid-cols-subgrid px-4', className)}>{children}</div>
}

function GridHeader({ className = '' }: WaitlistGridHeaderProps) {
    const { includeSelectionCheckbox, includeRowActions } = useContext(WaitlistGridContext)

    return (
        <GridRowWrapper className={tw(headerBorderClassName, className)}>
            <div className="flex flex-row items-center gap-x-6 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">
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

            <div className="hidden py-3.5 text-left text-sm font-semibold text-gray-900 sm:block dark:text-gray-200">
                Ship(s)
            </div>

            {includeRowActions && <div />}
        </GridRowWrapper>
    )
}

function GridRow({ character, className = '' }: WaitlistGridRowProps) {
    const { includeSelectionCheckbox, includeRowActions } = useContext(WaitlistGridContext)
    const { isSelected } = useWaitlistCharacterSelector(character)

    const checkboxId = useElementId(`checkbox-${character.id}`)

    return (
        <GridRowWrapper className={tw(rowBorderClassName, className)}>
            <div
                className={tw(
                    'relative grid grid-cols-[min-content_1fr] grid-rows-[min-content_1fr] flex-row gap-x-6 gap-y-1.5 py-4 sm:flex sm:items-center',
                    { 'grid-cols-1': !includeSelectionCheckbox }
                )}
            >
                {includeSelectionCheckbox && (
                    <>
                        {isSelected && <div className="absolute inset-y-0 -left-4 w-1 bg-primary-600" />}
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

            <div className="hidden items-center py-4 text-sm sm:flex">
                <CharacterShipEntry character={character} />
            </div>

            {includeRowActions && (
                <div className="flex items-stretch justify-end py-4">
                    <CharacterShipActions character={character} />
                </div>
            )}
        </GridRowWrapper>
    )
}

function BlankGridRow({ label = '' }) {
    return <div className="col-span-full p-4 text-sm italic">{label || 'There are no characters to display'}</div>
}

WaitlistGrid.Row = GridRow
WaitlistGrid.BlankRow = BlankGridRow

export default WaitlistGrid

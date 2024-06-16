/* eslint @typescript-eslint/no-use-before-define: ["error", { "functions": false }] --
 * Disable the no-use-before-define error for functions only, allows the relevant
 * components to be laid out in the order they are exported */

import { createContext, useContext, useMemo } from 'react'

import Checkbox from '@/Components/Checkbox'
import useElementId from '@/Hooks/useElementId'
import { Character } from '@/types'
import { tw } from '@/utils'

type TableContextProps = {
    includeSelectionCheckbox: boolean
    includeRowActions: boolean
    columnCount: number
}

type WaitlistTableProps = {
    characters?: Character[]
    header?: string
    showSelectionCheckbox?: boolean
    showRowActions?: boolean
    noItemsMessage?: string
}

type WaitlistTableRowProps = {
    character: Character
}

const defaultTableContextProps: TableContextProps = {
    includeSelectionCheckbox: false,
    includeRowActions: false,
    columnCount: 0,
}

const TableContext = createContext(defaultTableContextProps)

const BaseColumnCount = 2

function WaitlistTable({
    characters = [],
    header,
    showSelectionCheckbox = false,
    showRowActions = false,
    noItemsMessage = '',
}: WaitlistTableProps) {
    const tableContextValue = useMemo(
        () => ({
            includeSelectionCheckbox: showSelectionCheckbox,
            includeRowActions: showRowActions,
            columnCount: Object.values([showSelectionCheckbox, showRowActions]).reduce(
                (acc, item) => acc + (item ? 1 : 0),
                BaseColumnCount
            ),
        }),
        [showSelectionCheckbox, showRowActions]
    )

    return (
        <div className="space-y-2">
            {header && <h3 className="px-6 font-semibold">{header}</h3>}
            <div className="inline-block min-w-full py-2 align-middle">
                <TableContext.Provider value={tableContextValue}>
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead>
                            <tr>
                                {showSelectionCheckbox && (
                                    <th scope="col" className="w-16 min-w-0 px-6">
                                        <div className="flex">
                                            <Checkbox />
                                        </div>
                                        {/* <WaitlistSelectionCheckbox toggleAll /> */}
                                    </th>
                                )}
                                <th
                                    scope="col"
                                    className={tw(
                                        'hidden w-48 py-3.5 pr-3 text-left text-sm font-semibold text-gray-900 sm:table-cell dark:text-gray-200',
                                        { 'pl-6': !showSelectionCheckbox }
                                    )}
                                >
                                    Character
                                </th>
                                <th
                                    scope="col"
                                    className={tw(
                                        'py-3.5 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-3 dark:text-gray-200',
                                        { 'pl-6': !showSelectionCheckbox }
                                    )}
                                >
                                    <span className="sm:hidden">Character / </span>
                                    Ship(s)
                                </th>
                                {showRowActions && <th className="hidden sm:table-cell sm:w-8" />}
                            </tr>
                        </thead>
                        <tbody>
                            {characters.length > 0 ? (
                                characters.map((character) => <TableRow key={character.id} character={character} />)
                            ) : (
                                <BlankRow label={noItemsMessage} />
                            )}
                        </tbody>
                    </table>
                </TableContext.Provider>
            </div>
        </div>
    )
}

function TableRow({ character }: WaitlistTableRowProps) {
    const { includeSelectionCheckbox, includeRowActions } = useContext(TableContext)

    const checkboxId = useElementId(`checkbox-${character.id}`)

    return (
        <tr className="border-b border-gray-200 dark:border-gray-700">
            {includeSelectionCheckbox && (
                <td className="relative h-1 p-4 px-6 sm:px-6">
                    {true && <div className="absolute inset-y-0 left-0 w-1 bg-primary-600" />}
                    <div className="my-2 flex h-full items-start sm:items-center">
                        <Checkbox id={checkboxId} />
                    </div>
                </td>
            )}

            <td
                className={tw('w-48 py-4 pr-3 text-sm/4 font-medium max-sm:hidden', {
                    'pl-6': !includeSelectionCheckbox,
                })}
            >
                <label className="block py-0.5" htmlFor={checkboxId}>
                    {character.name}
                </label>
            </td>

            <td
                className={tw('py-4 pr-3 text-sm sm:pl-3', {
                    'pl-6': !includeSelectionCheckbox,
                    'pr-6': !includeRowActions,
                })}
            >
                <div className="flex flex-col gap-y-2.5 sm:gap-y-0">
                    <label className="font-bold leading-4 sm:hidden" htmlFor={checkboxId}>
                        {character.name}
                    </label>
                    <div className="flex gap-x-4">
                        <div className="flex-1">Ship Entry</div>
                    </div>
                </div>
            </td>

            {includeRowActions && <td className="w-0 max-w-sm whitespace-nowrap pr-3 sm:w-8 sm:pr-6">Actions</td>}
        </tr>
    )
}

function BlankRow({ label = '' }) {
    const { columnCount } = useContext(TableContext)
    return (
        <tr>
            <td className="px-6 py-4 text-sm italic" colSpan={columnCount}>
                {label || 'There are no characters to display'}
            </td>
        </tr>
    )
}

export default WaitlistTable

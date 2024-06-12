/* eslint @typescript-eslint/no-use-before-define: ["error", { "functions": false }] --
 * Disable the no-use-before-define error for functions only, allows the relevant
 * components to be laid out in the order they are exported */

import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { Character } from '@/types'
import { renderChildren, tw } from '@/utils'

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
    children,
}: PropsWithChildren<WaitlistTableProps>) {
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
                                    <th scope="col" className="relative w-12 px-6">
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
                                characters.map((character) => renderChildren(children, { character }))
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

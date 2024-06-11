import { createContext, useContext, useMemo } from 'react'

import {
    Tab as BaseTab,
    TabGroupProps,
    TabListProps as BaseTabListProps,
    TabPanelProps,
    TabPanelsProps as BaseTabPanelsProps,
    TabProps,
} from '@headlessui/react'

import { renderChildren, tw } from '@/utils'

type TabListContextProps = {
    fullWidthTabs: boolean
}

type TabListProps = {
    tabPosition?: TabPosition
    fullWidthTabs?: boolean
}

type TabPanelsProps = {
    fixedHeight?: boolean
}

const defaultTabListContext: TabListContextProps = {
    fullWidthTabs: false,
}

const TabListContext = createContext(defaultTabListContext)

const tabPositions = {
    left: 'justify-start',
    center: 'justify-center',
    right: 'justify-end',
}
type TabPosition = keyof typeof tabPositions

function Tabs({ as: _ = 'div', ...props }: TabGroupProps<'div'>) {
    return <BaseTab.Group as="div" {...props} />
}

function TabList({
    as: _ = 'div',
    className,
    tabPosition = 'center',
    fullWidthTabs = false,
    children,
    ...props
}: BaseTabListProps<'div'> & TabListProps) {
    const contextValue = useMemo(() => ({ fullWidthTabs }), [fullWidthTabs])

    return (
        <TabListContext.Provider value={contextValue}>
            <BaseTab.List
                as="div"
                className={tw(
                    'mb-2 block border-b border-gray-300 dark:border-gray-600',
                    {
                        'flex flex-row items-center': !fullWidthTabs,
                        [tabPositions[tabPosition]]: !fullWidthTabs,
                    },
                    className
                )}
                {...props}
            >
                {(renderProps) => (
                    <div className={tw('-mb-px flex flex-row', { 'gap-x-6': !fullWidthTabs })}>
                        {renderChildren(children, renderProps)}
                    </div>
                )}
            </BaseTab.List>
        </TabListContext.Provider>
    )
}

function Tab({ as: _ = 'button', className, ...props }: TabProps<'button'>) {
    const { fullWidthTabs } = useContext(TabListContext)

    return (
        <BaseTab
            as="button"
            className={({ selected }) =>
                tw(
                    'group inline-flex items-center border-b-2 border-transparent px-1 py-2 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:outline-none dark:text-gray-400 dark:hover:border-gray-300 dark:hover:text-gray-300 dark:focus:border-gray-300 ',
                    {
                        'flex flex-1 flex-row justify-center': fullWidthTabs,
                        'border-primary-500 text-primary-600 hover:border-primary-500 focus:border-primary-500 dark:border-primary-500 dark:text-gray-200 dark:hover:border-primary-500 dark:hover:text-gray-200 dark:focus:border-primary-500':
                            selected,
                    },
                    className
                )
            }
            {...props}
        />
    )
}

function TabPanels({ as: _ = 'div', className, ...props }: BaseTabPanelsProps<'div'> & TabPanelsProps) {
    return <BaseTab.Panels className={tw('', className)} {...props} />
}

function TabPanel({ as: _ = 'div', className, ...props }: TabPanelProps<'div'>) {
    return <BaseTab.Panel as="div" className={tw('', className)} {...props} />
}

Tabs.TabGroup = Tabs
Tabs.TabList = TabList
Tabs.Tab = Tab
Tabs.TabPanels = TabPanels
Tabs.TabPanel = TabPanel

Tabs.Panels = TabPanels
Tabs.Panel = TabPanel

export default Tabs

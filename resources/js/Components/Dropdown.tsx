import {
    cloneElement,
    ForwardedRef,
    forwardRef,
    Fragment,
    HTMLAttributes,
    isValidElement,
    PropsWithChildren,
    ReactElement,
} from 'react'

import { Menu, MenuButtonProps, MenuItemProps, MenuItemsProps, MenuProps, Transition } from '@headlessui/react'
import { InertiaLinkProps } from '@inertiajs/react'

import BaseButton from '@/Components/Button'
import Link from '@/Components/Link'
import { DropdownButtonVariants } from '@/Styles/Button'
import { PropsWithChildrenPlusRenderProps } from '@/types'
import { processProps, renderChildren, tw } from '@/utils'

type DropdownButtonProps = {
    variant?: DropdownButtonVariants | ''
}

type DropdownItemsProps = {
    width?: DropdownWidths
}

type DropdownItemContainerProps = {
    className?: string
}

type DropdownItemButtonProps = {
    closeOnClick?: boolean
}

type DropdownLinkProps = {
    badgeCount?: number
    hideBadgeWhenZero?: boolean
    className?: string
}

type DropdownLinkRenderProps = {
    active: boolean
}

const dropdownItemWidths = {
    sm: 'w-40',
    md: 'w-48',
    lg: 'w-56',
    xl: 'w-64',
    '2xl': 'w-72',
    full: 'w-full',
}
type DropdownWidths = keyof typeof dropdownItemWidths

function getItemClasses<TRenderProps extends { active: boolean }>(
    className: string | ((props: TRenderProps) => string) | undefined,
    props: TRenderProps
) {
    const formattedClassName = processProps(className, props)
    const { active } = props

    return tw(
        'inline-flex w-full items-center gap-x-2 rounded p-2 text-left text-sm/5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:bg-gray-700',
        {
            'bg-gray-100 dark:bg-gray-700': active,
        },
        formattedClassName
    )
}

function Dropdown({ as: _ = 'div', className, children, ...props }: MenuProps<'div'>) {
    return (
        <Menu
            as="div"
            className={({ open }) => tw('relative inline-block text-left', { 'z-20': open }, className)}
            {...props}
        >
            {children}
        </Menu>
    )
}

const Button = forwardRef(function Button(
    { variant = 'dropdown', children, ...props }: MenuButtonProps<typeof BaseButton> & DropdownButtonProps,
    ref: ForwardedRef<HTMLButtonElement>
) {
    return (
        <Menu.Button as={BaseButton} variant={variant} {...props} ref={ref}>
            {children}
        </Menu.Button>
    )
})

const Items = forwardRef(function Items(
    { width = 'lg', className, children, ...props }: MenuItemsProps<'div'> & DropdownItemsProps,
    ref: ForwardedRef<HTMLDivElement>
) {
    return (
        <Transition
            as={Fragment}
            enter="transition ease-out duration-100"
            enterFrom="transform opacity-0 scale-95"
            enterTo="transform opacity-100 scale-100"
            leave="transition ease-in duration-75"
            leaveFrom="transform opacity-100 scale-100"
            leaveTo="transform opacity-0 scale-95"
        >
            <Menu.Items
                className={tw(
                    'absolute right-0 mt-2 origin-top-right divide-y divide-gray-200 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none dark:divide-gray-700 dark:bg-gray-800 dark:shadow-black/20 dark:ring-gray-700 dark:ring-white/10',
                    dropdownItemWidths[width],
                    className
                )}
                {...props}
                ref={ref}
            >
                {children}
            </Menu.Items>
        </Transition>
    )
})

const Item = forwardRef(function Item(
    { as: _ = 'div', className, children, ...props }: MenuItemProps<'div'>,
    ref: ForwardedRef<HTMLDivElement>
) {
    return (
        <Menu.Item as={Fragment} ref={ref}>
            {({ active, ...renderProps }) => (
                <div className={getItemClasses(className, { active, ...renderProps })} {...props}>
                    {renderChildren(children, { active, ...renderProps })}
                </div>
            )}
        </Menu.Item>
    )
})

const ItemContainer = forwardRef(function ItemContainer(
    { as: _ = Fragment, className = '', children }: MenuItemProps<typeof Fragment> & DropdownItemContainerProps,
    ref: ForwardedRef<HTMLElement>
) {
    return (
        <Menu.Item as={Fragment} ref={ref}>
            {(renderProps) =>
                renderChildren(children, renderProps, (child) =>
                    isValidElement(child)
                        ? // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                          cloneElement(child, {
                              ...child.props,
                              // eslint-disable-next-line @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-member-access
                              className: tw(child.props.className, getItemClasses(className, renderProps)),
                          })
                        : child
                ) as ReactElement
            }
        </Menu.Item>
    )
})

const ItemButton = forwardRef(function ItemButton(
    {
        className = '',
        onClick,
        closeOnClick = true,
        children,
        ...props
    }: MenuItemProps<'button'> & DropdownItemButtonProps,
    ref: ForwardedRef<HTMLElement>
) {
    return (
        <Menu.Item as={Fragment} ref={ref}>
            {({ active, close, ...renderProps }) => (
                <button
                    type="button"
                    className={getItemClasses(className, { active, close, ...renderProps })}
                    onClick={(e) => {
                        e.preventDefault()

                        onClick?.(e)
                        if (closeOnClick) close()
                    }}
                    {...props}
                >
                    {renderChildren(children, { active, close, ...renderProps })}
                </button>
            )}
        </Menu.Item>
    )
})

function BadgeWrapper({
    showBadge = false,
    count,
    children,
}: PropsWithChildren<{ showBadge: boolean; count: number }>) {
    if (!showBadge) return children

    return (
        <>
            <div className="flex flex-1 items-center gap-x-2">{children}</div>
            <span className="rounded-full bg-gray-500 px-1.5 py-0.5 text-xs font-medium text-gray-100 dark:bg-gray-600">
                {count}
            </span>
        </>
    )
}

const DropdownLink = forwardRef(function DropdownLink(
    {
        className = '',
        badgeCount,
        hideBadgeWhenZero = false,
        children,
        ...props
    }: PropsWithChildrenPlusRenderProps<DropdownLinkRenderProps, InertiaLinkProps> & DropdownLinkProps,
    ref: ForwardedRef<HTMLElement>
) {
    const showBadge = typeof badgeCount === 'number' && !(badgeCount === 0 && hideBadgeWhenZero)

    return (
        <Menu.Item ref={ref}>
            {({ active }) => (
                <Link className={getItemClasses(className, { active })} {...props}>
                    <BadgeWrapper showBadge={showBadge} count={badgeCount || 0}>
                        {renderChildren(children, { active })}
                    </BadgeWrapper>
                </Link>
            )}
        </Menu.Item>
    )
})

function ItemGroup({ className, children }: HTMLAttributes<HTMLDivElement>) {
    if (!children) return null

    return <div className={tw('px-1 py-1 empty:hidden', className)}>{children}</div>
}

// Standard components
Dropdown.Button = Button
Dropdown.Items = Items
Dropdown.Item = Item

// Base Menu component aliases
Dropdown.BaseButton = Menu.Button
Dropdown.BaseItem = Menu.Item

// Custom components
Dropdown.ItemContainer = ItemContainer
Dropdown.ItemLink = DropdownLink
Dropdown.ItemButton = ItemButton
Dropdown.ItemGroup = ItemGroup

// Custom component aliases
Dropdown.Link = DropdownLink

export default Dropdown

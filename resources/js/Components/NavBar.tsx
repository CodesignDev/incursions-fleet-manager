import { ChevronDownIcon } from '@heroicons/react/20/solid'
import { Link } from '@inertiajs/react'
import { useToggle } from 'usehooks-ts'

import ApplicationLogo from '@/Components/ApplicationLogo'
import Container from '@/Components/Container'
import Dropdown from '@/Components/Dropdown'
import NavBarLink from '@/Components/NavBarLink'
import { useCurrentLoggedInUser } from '@/Hooks/useCurrentUser'
import { NavBarLinks } from '@/Links/nav-bar'
import { UserMenuLinks } from '@/Links/user-menu'
import { parseLinks, tw } from '@/utils'

export default function NavBar() {
    const [showMobileNavigation, toggleMobileNavigation] = useToggle(false)
    const { user } = useCurrentLoggedInUser()

    const navBarLinks = parseLinks(NavBarLinks)
    const userMenuLinks = parseLinks(UserMenuLinks)

    return (
        <nav className="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
            <Container>
                <div className="flex h-16 sm:justify-between">
                    <div className="-ms-2 me-4 flex items-center sm:hidden">
                        <button
                            type="button"
                            onClick={() => toggleMobileNavigation()}
                            className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                        >
                            <svg className="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    className={!showMobileNavigation ? 'inline-flex' : 'hidden'}
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    className={showMobileNavigation ? 'inline-flex' : 'hidden'}
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <div className="flex">
                        <div className="flex shrink-0 items-center">
                            <Link href="/">
                                <ApplicationLogo className="block w-auto text-gray-800 dark:text-gray-200" />
                            </Link>
                        </div>

                        <div className="hidden space-x-4 sm:-my-px sm:ms-8 sm:flex">
                            {navBarLinks.map(({ key, href, active, label, ...props }) => (
                                <NavBarLink key={key} href={href} active={active} {...props}>
                                    {label}
                                </NavBarLink>
                            ))}
                        </div>
                    </div>

                    <div className="hidden sm:ms-6 sm:flex sm:items-center">
                        <div className="relative ms-3">
                            <Dropdown>
                                <Dropdown.Button className="rounded-md px-3 py-2.5 text-sm/4 font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    {user.name}
                                    <ChevronDownIcon className="-me-0.5 size-4" />
                                </Dropdown.Button>

                                <Dropdown.Items>
                                    {userMenuLinks.map(({ key, href, label, active: _, ...props }) => (
                                        <Dropdown.Link key={key} href={href} {...props}>
                                            {label}
                                        </Dropdown.Link>
                                    ))}
                                </Dropdown.Items>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </Container>

            <div className={tw(`sm:hidden`, showMobileNavigation ? 'block' : 'hidden')}>
                <div className="space-y-1 pb-3 pt-2">
                    {navBarLinks.map(({ key, href, label, active, ...props }) => (
                        <NavBarLink responsive key={key} href={href} active={active} {...props}>
                            {label}
                        </NavBarLink>
                    ))}
                </div>

                <div className="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
                    <div className="px-4">
                        <p className="text-base font-medium text-gray-800 dark:text-gray-200">{user.name}</p>
                    </div>

                    <div className="mt-3 space-y-1">
                        {userMenuLinks.map(({ key, href, label, ...props }) => (
                            <NavBarLink responsive key={key} href={href} className="px-6" {...props}>
                                {label}
                            </NavBarLink>
                        ))}
                    </div>
                </div>
            </div>
        </nav>
    )
}

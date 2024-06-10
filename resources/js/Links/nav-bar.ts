import { LinkPropsWithLabel } from '@/types'
import { routeIs } from '@/utils'

// eslint-disable-next-line import/prefer-default-export
export const NavBarLinks: Record<string, LinkPropsWithLabel> = {
    home: {
        route: () => route('dashboard'),
        label: 'Dashboard',
        active: () => route().current('dashboard'),
    },
    waitlist: {
        route: () => route('waitlist.dashboard'),
        label: 'Waitlist',
        active: () => routeIs('waitlist.*'),
    },
    fleets: {
        route: () => route('fleets.list'),
        label: 'Fleets',
        active: () => routeIs('fleets.*'),
    },
}

import { LinkPropsWithLabel } from '@/types'

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
    },
    fleets: {
        route: () => route('fleets.list'),
        label: 'Fleets',
    },
}

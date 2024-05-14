import { LinkPropsWithLabel } from '@/types'

// eslint-disable-next-line import/prefer-default-export
export const UserMenuLinks: Record<string, LinkPropsWithLabel> = {
    profile: {
        href: '#',
        label: 'Profile',
    },
    logout: {
        route: () => route('logout'),
        method: 'post',
        label: 'Log Out',
    },
}

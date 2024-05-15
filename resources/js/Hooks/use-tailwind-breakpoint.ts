import { useMediaQuery } from 'usehooks-ts'

import { tailwindScreens as breakpoints } from '@/utils'

type BreakpointKey = keyof typeof breakpoints

export default function useTailwindBreakpoint<K extends BreakpointKey>(breakpoint: K) {
    const breakpointValue = breakpoints[breakpoint]
    const matches = useMediaQuery(`(min-width: ${breakpointValue})`)
    const capitalizedKey = breakpoint[0].toUpperCase() + breakpoint.substring(1)

    type KeyAbove = `isAbove${Capitalize<K>}`
    type KeyBelow = `isBelow${Capitalize<K>}`

    return {
        [breakpoint]: Number(String(breakpointValue).replace(/[^0-9]/g, '')),
        [`isAbove${capitalizedKey}`]: matches,
        [`isBelow${capitalizedKey}`]: !matches,
    } as Record<K, number> & Record<KeyAbove | KeyBelow, boolean>
}

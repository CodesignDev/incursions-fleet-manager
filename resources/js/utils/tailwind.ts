import { clsx, ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

import { tailwindRemPixelSize, tailwindUnitRatio } from '@/utils/tailwind-config'

export function tw(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs))
}

export function convertTailwindUnitsToPixels(unitSize: number) {
    return unitSize * tailwindUnitRatio * tailwindRemPixelSize
}

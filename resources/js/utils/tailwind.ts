import { ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

// eslint-disable-next-line import/prefer-default-export
export function tw(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs))
}

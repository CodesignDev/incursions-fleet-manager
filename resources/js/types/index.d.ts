import type { Errors, ErrorBag } from '@inertiajs/core'
import type { Config as ZiggyConfig } from 'ziggy-js'
import { User } from '@/types/user'

// Index list: START - Automatically generated - DO NOT REMOVE
export * from './characters'
export * from './doctrine'
export * from './dropdown'
export * from './fleets'
export * from './links'
export * from './props'
export * from './user'
export * from './utils'
export * from './waitlist'
// Index list: END - Automatically generated - DO NOT REMOVE

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User
    }
    errors: Errors & ErrorBag
    ziggy?: ZiggyConfig
}

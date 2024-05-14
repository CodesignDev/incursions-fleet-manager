import type { Entries } from 'type-fest'

// eslint-disable-next-line @typescript-eslint/no-explicit-any
type StyleVariantKey<K extends string> = Record<K, any> | StyleVariantKey<K>[] | K

function processVariantKeys<K extends string>(variant: StyleVariantKey<K>): K[] {
    const keys: K[] = []

    // Process string key
    if (typeof variant === 'string') {
        return [variant]
    }

    // Attempt to process arrays / objects
    if (typeof variant === 'object') {
        // Loop through each array and process each key
        if (Array.isArray(variant)) {
            for (let i = 0; i < variant.length; i += 1) {
                if (variant[i]) {
                    const val = processVariantKeys(variant[i])
                    keys.push(...val)
                }
            }

            // Otherwise process the keys in objects that have a truthy value
        } else {
            const variantEntries = Object.entries(variant) as Entries<typeof variant>
            variantEntries.forEach(([key, value]) => {
                if (value) keys.push(key as K)
            })
        }
    }

    return keys
}

// eslint-disable-next-line import/prefer-default-export
export function applyStyleVariants<T extends Record<K, string>, K extends string>(
    styles: T,
    ...variants: StyleVariantKey<K>[]
): string {
    const toApply = processVariantKeys(variants)
    const stylesToApply: string[] = []

    toApply.forEach((style) => {
        if (style in styles) {
            stylesToApply.push(styles[style])
        }
    })

    return stylesToApply.join(' ')
}

import { isArray } from 'lodash-es'

export function flattenGroupedArray<T, TGroup>(
    entries: readonly T[] | TGroup[],
    isGroupPredicate: (entry: T | TGroup) => boolean,
    getValues: (entry: TGroup) => readonly T[]
): T[] {
    const flattenedEntries: T[] = []
    entries.forEach((entry) => {
        const newEntries = isGroupPredicate(entry)
            ? flattenGroupedArray(getValues(entry as TGroup), isGroupPredicate, getValues)
            : [entry as T]
        flattenedEntries.push(...newEntries)
    })

    return flattenedEntries
}

export function wrapArray<T>(thing: T | T[]) {
    return isArray(thing) ? thing : [thing]
}
